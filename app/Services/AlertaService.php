<?php

namespace App\Services;

use App\Enums\CategoriaContrato;
use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use App\Enums\TipoDocumentoContratual;
use App\Enums\TipoEventoAlerta;
use App\Jobs\ProcessarAlertaJob;
use App\Models\Aditivo;
use App\Models\Alerta;
use App\Models\ConfiguracaoAlerta;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AlertaService
{
    /**
     * Motor principal: verifica todos os contratos vigentes e gera alertas (RN-014).
     * Tambem atualiza status de contratos vencidos (RN-008).
     *
     * @return array{alertas_gerados: int, contratos_vencidos: int, notificacoes_reenvio: int}
     */
    public static function verificarVencimentos(): array
    {
        $resultado = [
            'alertas_gerados' => 0,
            'contratos_vencidos' => 0,
            'notificacoes_reenvio' => 0,
        ];

        // 1. Marcar contratos vencidos automaticamente (RN-008)
        $resultado['contratos_vencidos'] = self::marcarContratosVencidos();

        // 2. Buscar configuracoes ativas
        $configuracoes = ConfiguracaoAlerta::where('is_ativo', true)
            ->orderByDesc('dias_antecedencia')
            ->get();

        if ($configuracoes->isEmpty()) {
            return $resultado;
        }

        // 3. Buscar contratos vigentes com data_fim futura
        $contratos = Contrato::where('status', StatusContrato::Vigente->value)
            ->whereNotNull('data_fim')
            ->with(['fiscalAtual.servidor', 'secretaria'])
            ->get();

        foreach ($contratos as $contrato) {
            $diasRestantes = $contrato->dias_para_vencimento;

            // Tipo principal: vencimento_vigencia (RN-045)
            foreach ($configuracoes as $config) {
                if ($diasRestantes <= $config->dias_antecedencia && $diasRestantes >= 0) {
                    $alerta = self::gerarAlerta(
                        $contrato,
                        TipoEventoAlerta::VencimentoVigencia,
                        $config->dias_antecedencia,
                        $diasRestantes,
                        $contrato->data_fim
                    );

                    if ($alerta) {
                        $resultado['alertas_gerados']++;
                    }
                }
            }

            // Tipo: termino_aditivo — verifica aditivos vigentes com nova_data_fim (RN-045)
            $aditivosVigentes = $contrato->aditivosVigentes()
                ->whereNotNull('nova_data_fim')
                ->get();

            foreach ($aditivosVigentes as $aditivo) {
                $diasAditivo = (int) now()->startOfDay()
                    ->diffInDays($aditivo->nova_data_fim->startOfDay(), false);

                foreach ($configuracoes as $config) {
                    if ($diasAditivo <= $config->dias_antecedencia && $diasAditivo >= 0) {
                        $alerta = self::gerarAlerta(
                            $contrato,
                            TipoEventoAlerta::TerminoAditivo,
                            $config->dias_antecedencia,
                            $diasAditivo,
                            $aditivo->nova_data_fim
                        );

                        if ($alerta) {
                            $resultado['alertas_gerados']++;
                        }
                    }
                }
            }
        }

        // 4. Verificar incompletude documental (RN-125, RN-126, RN-127)
        $resultado['alertas_gerados'] += self::verificarIncompletudeDocumental();

        // 5. Re-enviar notificacoes para alertas pendentes nao resolvidos (RN-054)
        $resultado['notificacoes_reenvio'] = self::reenviarNotificacoesPendentes();

        return $resultado;
    }

    /**
     * Gera um alerta com verificacao de deduplicacao (RN-016).
     * Retorna null se alerta ja existe para o mesmo contrato+tipo+dias.
     */
    public static function gerarAlerta(
        Contrato $contrato,
        TipoEventoAlerta $tipoEvento,
        int $diasAntecedenciaConfig,
        int $diasRestantes,
        $dataVencimento
    ): ?Alerta {
        // Deduplicacao: verifica se ja existe alerta nao-resolvido (RN-016)
        $existente = Alerta::where('contrato_id', $contrato->id)
            ->where('tipo_evento', $tipoEvento->value)
            ->where('dias_antecedencia_config', $diasAntecedenciaConfig)
            ->naoResolvidos()
            ->exists();

        if ($existente) {
            return null;
        }

        // Determinar prioridade (RN-043 + RN-051)
        $prioridade = self::determinarPrioridade($diasRestantes, $contrato->categoria);

        // Gerar mensagem descritiva
        $mensagem = self::gerarMensagem($contrato, $tipoEvento, $diasRestantes);

        $alerta = Alerta::create([
            'contrato_id' => $contrato->id,
            'tipo_evento' => $tipoEvento->value,
            'prioridade' => $prioridade->value,
            'status' => StatusAlerta::Pendente->value,
            'dias_para_vencimento' => $diasRestantes,
            'dias_antecedencia_config' => $diasAntecedenciaConfig,
            'data_vencimento' => $dataVencimento,
            'data_disparo' => now(),
            'mensagem' => $mensagem,
            'tentativas_envio' => 0,
        ]);

        // Despachar job para envio de notificacoes
        $destinatarios = self::obterDestinatarios($contrato);
        if (!empty($destinatarios)) {
            ProcessarAlertaJob::dispatch(
                $alerta,
                $destinatarios,
                config('database.connections.tenant.database')
            );
        }

        return $alerta;
    }

    /**
     * Determina prioridade automatica (RN-043).
     * Contratos essenciais elevam prioridade em um nivel (RN-051).
     */
    public static function determinarPrioridade(
        int $diasRestantes,
        ?CategoriaContrato $categoria
    ): PrioridadeAlerta {
        $prioridade = match (true) {
            $diasRestantes <= 7 => PrioridadeAlerta::Urgente,
            $diasRestantes <= 30 => PrioridadeAlerta::Atencao,
            default => PrioridadeAlerta::Informativo,
        };

        // Elevacao para contratos essenciais (RN-051)
        if ($categoria === CategoriaContrato::Essencial) {
            $prioridade = match ($prioridade) {
                PrioridadeAlerta::Informativo => PrioridadeAlerta::Atencao,
                PrioridadeAlerta::Atencao => PrioridadeAlerta::Urgente,
                PrioridadeAlerta::Urgente => PrioridadeAlerta::Urgente,
            };
        }

        return $prioridade;
    }

    /**
     * Resolve todos os alertas pendentes de um contrato (RN-017, RN-053).
     * Chamado quando aditivo de prazo e registrado ou contrato e encerrado.
     */
    public static function resolverAlertasPorContrato(
        Contrato $contrato,
        ?User $user = null
    ): int {
        return Alerta::where('contrato_id', $contrato->id)
            ->pendentes()
            ->update([
                'status' => StatusAlerta::Resolvido->value,
                'resolvido_por' => $user?->id,
                'resolvido_em' => now(),
            ]);
    }

    /**
     * Resolve um alerta individual manualmente.
     */
    public static function resolverManualmente(
        Alerta $alerta,
        User $user
    ): Alerta {
        if ($alerta->status === StatusAlerta::Resolvido) {
            throw new \RuntimeException('Este alerta ja foi resolvido.');
        }

        $alerta->update([
            'status' => StatusAlerta::Resolvido->value,
            'resolvido_por' => $user->id,
            'resolvido_em' => now(),
        ]);

        return $alerta->fresh();
    }

    /**
     * Marca alerta como visualizado pelo usuario.
     */
    public static function marcarVisualizado(Alerta $alerta, User $user): void
    {
        if (in_array($alerta->status, [StatusAlerta::Pendente, StatusAlerta::Enviado])) {
            $alerta->update([
                'status' => StatusAlerta::Visualizado->value,
                'visualizado_por' => $user->id,
                'visualizado_em' => now(),
            ]);
        }
    }

    /**
     * Identifica destinatarios do alerta baseado no contrato (RN-047).
     *
     * @return array<int, array{user: ?User, email: string, tipo: string}>
     */
    public static function obterDestinatarios(Contrato $contrato): array
    {
        $destinatarios = [];

        // 1. Fiscal atual do contrato
        $fiscal = $contrato->fiscalAtual;
        if ($fiscal && $fiscal->email) {
            $user = null;
            if ($fiscal->servidor_id) {
                $user = User::where('email', $fiscal->email)->first();
            }
            $destinatarios[] = [
                'user' => $user,
                'email' => $fiscal->email,
                'tipo' => 'fiscal',
            ];
        }

        // 2. Usuarios com perfil secretario associados a secretaria do contrato
        if ($contrato->secretaria_id) {
            $secretarios = User::where('is_ativo', true)
                ->whereHas('role', fn ($q) => $q->where('nome', 'secretario'))
                ->whereHas('secretarias', fn ($q) => $q->where('secretarias.id', $contrato->secretaria_id))
                ->get();

            foreach ($secretarios as $user) {
                $destinatarios[] = [
                    'user' => $user,
                    'email' => $user->email,
                    'tipo' => 'secretario',
                ];
            }
        }

        // 3. Controladores internos (perfil controladoria)
        $controladores = User::where('is_ativo', true)
            ->whereHas('role', fn ($q) => $q->where('nome', 'controladoria'))
            ->get();

        foreach ($controladores as $user) {
            $destinatarios[] = [
                'user' => $user,
                'email' => $user->email,
                'tipo' => 'controlador',
            ];
        }

        // 4. Admin geral se contrato essencial (RN-047 + RN-051)
        if ($contrato->categoria === CategoriaContrato::Essencial) {
            $admins = User::where('is_ativo', true)
                ->whereHas('role', fn ($q) => $q->where('nome', 'administrador_geral'))
                ->get();

            foreach ($admins as $user) {
                $destinatarios[] = [
                    'user' => $user,
                    'email' => $user->email,
                    'tipo' => 'administrador',
                ];
            }
        }

        // Deduplicar por email
        $vistos = [];
        $resultado = [];
        foreach ($destinatarios as $dest) {
            if (!empty($dest['email']) && !in_array($dest['email'], $vistos)) {
                $vistos[] = $dest['email'];
                $resultado[] = $dest;
            }
        }

        return $resultado;
    }

    /**
     * Indicadores para dashboard de alertas (RN-055).
     *
     * @return array{vencendo_120d: int, vencendo_60d: int, vencendo_30d: int, vencidos: int}
     */
    public static function gerarIndicadoresDashboard(): array
    {
        $hoje = now()->startOfDay();

        return [
            'vencendo_120d' => Contrato::where('status', StatusContrato::Vigente->value)
                ->whereBetween('data_fim', [$hoje, $hoje->copy()->addDays(120)])
                ->count(),
            'vencendo_60d' => Contrato::where('status', StatusContrato::Vigente->value)
                ->whereBetween('data_fim', [$hoje, $hoje->copy()->addDays(60)])
                ->count(),
            'vencendo_30d' => Contrato::where('status', StatusContrato::Vigente->value)
                ->whereBetween('data_fim', [$hoje, $hoje->copy()->addDays(30)])
                ->count(),
            'vencidos' => Contrato::where('status', StatusContrato::Vencido->value)->count(),
        ];
    }

    /**
     * Verifica incompletude documental e gera alertas (RN-125, RN-126, RN-127).
     */
    public static function verificarIncompletudeDocumental(): int
    {
        $alertasGerados = 0;

        // RN-125: Aditivos sem documento tipo aditivo_doc
        $aditivos = Aditivo::whereNotIn('status', ['cancelado'])
            ->with('contrato')
            ->get();

        foreach ($aditivos as $aditivo) {
            if (!$aditivo->contrato) {
                continue;
            }

            $temDoc = Documento::where('documentable_type', Aditivo::class)
                ->where('documentable_id', $aditivo->id)
                ->where('tipo_documento', TipoDocumentoContratual::AditivoDoc->value)
                ->where('is_versao_atual', true)
                ->whereNull('deleted_at')
                ->exists();

            if (!$temDoc) {
                $alerta = self::gerarAlertaDocumental(
                    $aditivo->contrato,
                    TipoEventoAlerta::AditivoSemDocumento,
                    "Aditivo #{$aditivo->numero_sequencial} do contrato {$aditivo->contrato->numero} nao possui documento do tipo 'Aditivo' vinculado."
                );

                if ($alerta) {
                    $alertasGerados++;
                }
            }
        }

        // RN-126: Prorrogacao de prazo sem parecer_juridico
        $aditivosPrazo = Aditivo::whereIn('tipo', [
            TipoAditivo::Prazo->value,
            TipoAditivo::PrazoEValor->value,
            TipoAditivo::Misto->value,
        ])
            ->whereNotIn('status', ['cancelado'])
            ->with('contrato')
            ->get();

        foreach ($aditivosPrazo as $aditivo) {
            if (!$aditivo->contrato) {
                continue;
            }

            $temParecer = Documento::where('documentable_type', Aditivo::class)
                ->where('documentable_id', $aditivo->id)
                ->where('tipo_documento', TipoDocumentoContratual::ParecerJuridico->value)
                ->where('is_versao_atual', true)
                ->whereNull('deleted_at')
                ->exists();

            if (!$temParecer) {
                $alerta = self::gerarAlertaDocumental(
                    $aditivo->contrato,
                    TipoEventoAlerta::ProrrogacaoSemParecer,
                    "Prorrogacao #{$aditivo->numero_sequencial} do contrato {$aditivo->contrato->numero} nao possui Parecer Juridico vinculado."
                );

                if ($alerta) {
                    $alertasGerados++;
                }
            }
        }

        // RN-127: Contratos >R$500k sem publicacao_oficial
        $contratosAltoValor = Contrato::where('status', StatusContrato::Vigente->value)
            ->where('valor_global', '>', 500000)
            ->get();

        foreach ($contratosAltoValor as $contrato) {
            $temPublicacao = Documento::where('documentable_type', Contrato::class)
                ->where('documentable_id', $contrato->id)
                ->where('tipo_documento', TipoDocumentoContratual::PublicacaoOficial->value)
                ->where('is_versao_atual', true)
                ->whereNull('deleted_at')
                ->exists();

            if (!$temPublicacao) {
                $alerta = self::gerarAlertaDocumental(
                    $contrato,
                    TipoEventoAlerta::ContratoSemPublicacao,
                    "Contrato {$contrato->numero} (R$ " . number_format((float) $contrato->valor_global, 2, ',', '.') . ") nao possui Publicacao Oficial."
                );

                if ($alerta) {
                    $alertasGerados++;
                }
            }
        }

        return $alertasGerados;
    }

    /**
     * Gera alerta de incompletude documental com deduplicacao (RN-125/126/127).
     */
    private static function gerarAlertaDocumental(
        Contrato $contrato,
        TipoEventoAlerta $tipoEvento,
        string $mensagem,
    ): ?Alerta {
        // Deduplicacao: verificar se ja existe alerta nao-resolvido do mesmo tipo
        $existente = Alerta::where('contrato_id', $contrato->id)
            ->where('tipo_evento', $tipoEvento->value)
            ->naoResolvidos()
            ->exists();

        if ($existente) {
            return null;
        }

        $alerta = Alerta::create([
            'contrato_id' => $contrato->id,
            'tipo_evento' => $tipoEvento->value,
            'prioridade' => PrioridadeAlerta::Atencao->value,
            'status' => StatusAlerta::Pendente->value,
            'dias_para_vencimento' => $contrato->dias_para_vencimento ?? 0,
            'dias_antecedencia_config' => 0,
            'data_vencimento' => $contrato->data_fim,
            'data_disparo' => now(),
            'mensagem' => $mensagem,
            'tentativas_envio' => 0,
        ]);

        $destinatarios = self::obterDestinatarios($contrato);
        if (!empty($destinatarios)) {
            ProcessarAlertaJob::dispatch(
                $alerta,
                $destinatarios,
                config('database.connections.tenant.database')
            );
        }

        return $alerta;
    }

    // --- Metodos privados ---

    /**
     * Marca contratos com data_fim < hoje como vencidos e irregulares (RN-008, RN-046).
     */
    private static function marcarContratosVencidos(): int
    {
        return Contrato::where('status', StatusContrato::Vigente->value)
            ->where('data_fim', '<', now()->startOfDay())
            ->update([
                'status' => StatusContrato::Vencido->value,
                'is_irregular' => true,
            ]);
    }

    /**
     * Remove flag IRREGULAR quando contrato e regularizado (RN-046).
     * Chamado quando aditivo retroativo e registrado ou contrato e encerrado formalmente.
     */
    public static function regularizarContrato(Contrato $contrato): void
    {
        if ($contrato->is_irregular) {
            $contrato->updateQuietly(['is_irregular' => false]);
        }
    }

    /**
     * Re-envia notificacoes para alertas nao resolvidos (RN-054).
     * Alerta continua gerando notificacoes enquanto nao resolvido.
     */
    private static function reenviarNotificacoesPendentes(): int
    {
        $alertasAtivos = Alerta::pendentes()
            ->with('contrato.fiscalAtual.servidor', 'contrato.secretaria')
            ->get();

        $count = 0;
        foreach ($alertasAtivos as $alerta) {
            // Evitar re-envio excessivo: so reenvia se ultimo envio foi ha mais de 24h
            $ultimoLog = $alerta->logNotificacoes()
                ->where('sucesso', true)
                ->orderByDesc('data_envio')
                ->first();

            if ($ultimoLog && $ultimoLog->data_envio->diffInHours(now()) < 24) {
                continue;
            }

            $destinatarios = self::obterDestinatarios($alerta->contrato);
            if (!empty($destinatarios)) {
                ProcessarAlertaJob::dispatch(
                    $alerta,
                    $destinatarios,
                    config('database.connections.tenant.database')
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Gera mensagem descritiva para o alerta.
     */
    private static function gerarMensagem(
        Contrato $contrato,
        TipoEventoAlerta $tipoEvento,
        int $diasRestantes
    ): string {
        $numero = $contrato->numero;
        $evento = $tipoEvento->label();

        if ($diasRestantes <= 0) {
            return "ATENCAO: O contrato {$numero} esta VENCIDO. Evento: {$evento}. Regularizacao imediata necessaria.";
        }

        return "O contrato {$numero} vencera em {$diasRestantes} dia(s). Evento: {$evento}. Prazo para regularizacao esta se esgotando.";
    }
}
