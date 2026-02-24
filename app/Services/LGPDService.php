<?php

namespace App\Services;

use App\Enums\TipoSolicitacaoLGPD;
use App\Models\Fiscal;
use App\Models\Fornecedor;
use App\Models\LogLgpdSolicitacao;
use App\Models\Servidor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LGPDService
{
    /**
     * Campos PII anonimizaveis por entidade (ADR-057).
     * Tabelas de auditoria sao IMUTAVEIS e nunca tocadas.
     */
    private const CAMPOS_ANONIMIZAVEIS = [
        Fornecedor::class => [
            'razao_social',
            'nome_fantasia',
            'representante_legal',
            'email',
            'telefone',
            'endereco',
            // cnpj preservado: obrigacao legal de registro publico
            // cep removido: campo curto (10 chars), nao e PII sensivel isolado
        ],
        Fiscal::class => [
            'nome',
            'email',
            // matricula preservada: vinculo funcional publico
        ],
        Servidor::class => [
            'nome',
            'email',
            'telefone',
            // cpf removido: campo curto (14 chars). Substituido por mascara '***.***.***-**'
        ],
        User::class => [
            'nome',
            'email',
            // Somente usuarios inativos podem ser anonimizados
        ],
    ];

    /**
     * Campos PII com tratamento especial (mascara em vez de hash) por limite de tamanho.
     * Formato: [entidade => [campo => mascara]]
     */
    private const CAMPOS_MASCARADOS = [
        Fornecedor::class => [
            'cep' => '***.**-***',
        ],
        Servidor::class => [
            'cpf' => '***.***.***-**',
        ],
    ];

    /**
     * Anonimizar dados PII de um Fornecedor (ADR-057, RN-213).
     */
    public static function anonimizarFornecedor(
        Fornecedor $fornecedor,
        string $solicitante,
        ?string $justificativa = null,
        ?User $executor = null,
    ): LogLgpdSolicitacao {
        return self::anonimizar($fornecedor, $solicitante, $justificativa, $executor);
    }

    /**
     * Anonimizar dados PII de um Fiscal (ADR-057, RN-213).
     */
    public static function anonimizarFiscal(
        Fiscal $fiscal,
        string $solicitante,
        ?string $justificativa = null,
        ?User $executor = null,
    ): LogLgpdSolicitacao {
        return self::anonimizar($fiscal, $solicitante, $justificativa, $executor);
    }

    /**
     * Anonimizar dados PII de um Servidor (ADR-057, RN-213).
     */
    public static function anonimizarServidor(
        Servidor $servidor,
        string $solicitante,
        ?string $justificativa = null,
        ?User $executor = null,
    ): LogLgpdSolicitacao {
        return self::anonimizar($servidor, $solicitante, $justificativa, $executor);
    }

    /**
     * Anonimizar dados PII de um Usuario desativado (ADR-057, RN-213).
     *
     * @throws \RuntimeException Se o usuario ainda esta ativo.
     */
    public static function anonimizarUsuario(
        User $user,
        string $solicitante,
        ?string $justificativa = null,
        ?User $executor = null,
    ): LogLgpdSolicitacao {
        if ($user->is_ativo) {
            throw new \RuntimeException(
                'Nao e possivel anonimizar usuario ativo. Desative o usuario antes de anonimizar.'
            );
        }

        return self::anonimizar($user, $solicitante, $justificativa, $executor);
    }

    /**
     * Gerar valor anonimizado: ANONIMIZADO_ + 8 primeiros chars do SHA-256.
     * Formato rastreavel por ordem judicial sem revelar dado original.
     */
    public static function gerarValorAnonimizado(string $valorOriginal): string
    {
        $hash = substr(hash('sha256', $valorOriginal), 0, 8);

        return "ANONIMIZADO_{$hash}";
    }

    /**
     * Verifica se uma entidade ja foi anonimizada anteriormente.
     */
    public static function jaAnonimizado(Model $entidade): bool
    {
        return LogLgpdSolicitacao::where('entidade_tipo', get_class($entidade))
            ->where('entidade_id', $entidade->id)
            ->where('tipo_solicitacao', TipoSolicitacaoLGPD::Anonimizacao->value)
            ->where('status', 'processado')
            ->exists();
    }

    /**
     * Retorna a lista de campos anonimizaveis para uma entidade.
     *
     * @return string[]
     */
    public static function camposAnonimizaveis(string $entityClass): array
    {
        return self::CAMPOS_ANONIMIZAVEIS[$entityClass] ?? [];
    }

    /**
     * Logica core de anonimizacao. Envolve em transaction na connection tenant.
     * Usa saveQuietly() para nao disparar observers de auditoria.
     */
    private static function anonimizar(
        Model $entidade,
        string $solicitante,
        ?string $justificativa,
        ?User $executor,
    ): LogLgpdSolicitacao {
        $entityClass = get_class($entidade);

        if (!isset(self::CAMPOS_ANONIMIZAVEIS[$entityClass])) {
            throw new \InvalidArgumentException(
                "Entidade {$entityClass} nao possui campos anonimizaveis definidos."
            );
        }

        if (self::jaAnonimizado($entidade)) {
            throw new \RuntimeException(
                "Entidade {$entityClass} #{$entidade->id} ja foi anonimizada anteriormente."
            );
        }

        $campos = self::CAMPOS_ANONIMIZAVEIS[$entityClass];
        $mascaras = self::CAMPOS_MASCARADOS[$entityClass] ?? [];
        $camposAnonimizados = [];

        return DB::connection('tenant')->transaction(function () use (
            $entidade, $campos, $mascaras, $solicitante, $justificativa, $executor, &$camposAnonimizados
        ) {
            // Anonimizar campos PII com hash
            foreach ($campos as $campo) {
                $valorOriginal = $entidade->{$campo};

                if ($valorOriginal !== null && !str_starts_with((string) $valorOriginal, 'ANONIMIZADO_')) {
                    $entidade->{$campo} = self::gerarValorAnonimizado((string) $valorOriginal);
                    $camposAnonimizados[] = $campo;
                }
            }

            // Aplicar mascaras em campos com tamanho limitado
            foreach ($mascaras as $campo => $mascara) {
                $valorOriginal = $entidade->{$campo};

                if ($valorOriginal !== null && $valorOriginal !== $mascara) {
                    $entidade->{$campo} = $mascara;
                    $camposAnonimizados[] = $campo;
                }
            }

            if (!empty($camposAnonimizados)) {
                $entidade->saveQuietly();
            }

            $log = LogLgpdSolicitacao::create([
                'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
                'entidade_tipo' => get_class($entidade),
                'entidade_id' => $entidade->id,
                'solicitante' => $solicitante,
                'justificativa' => $justificativa,
                'status' => 'processado',
                'campos_anonimizados' => $camposAnonimizados,
                'executado_por' => $executor?->id,
                'data_solicitacao' => now(),
                'data_execucao' => now(),
            ]);

            Log::info('LGPD: Anonimizacao executada', [
                'entidade' => get_class($entidade),
                'entidade_id' => $entidade->id,
                'campos' => $camposAnonimizados,
                'solicitante' => $solicitante,
            ]);

            return $log;
        });
    }
}
