<?php

namespace App\Services;

use App\Enums\AcaoLogDocumento;
use App\Enums\StatusCompletudeDocumental;
use App\Enums\StatusContrato;
use App\Enums\StatusIntegridade;
use App\Enums\TipoDocumentoContratual;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\LogAcessoDocumento;
use App\Models\LogIntegridadeDocumento;
use App\Models\User;
use App\Notifications\IntegridadeComprometidaNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoService
{
    /**
     * Checklist de documentos obrigatorios por padrao (RN-129).
     * Configuravel pelo admin no futuro.
     */
    public const CHECKLIST_OBRIGATORIO = [
        TipoDocumentoContratual::ContratoOriginal,
        TipoDocumentoContratual::PublicacaoOficial,
        TipoDocumentoContratual::ParecerJuridico,
        TipoDocumentoContratual::NotaEmpenho,
    ];

    /**
     * Upload de documento com versionamento automatico (RN-120).
     */
    public static function upload(
        UploadedFile $arquivo,
        Model $documentable,
        TipoDocumentoContratual $tipoDocumento,
        User $user,
        string $ip,
        ?string $descricao = null,
    ): Documento {
        return DB::connection('tenant')->transaction(function () use (
            $arquivo, $documentable, $tipoDocumento, $user, $ip, $descricao
        ) {
            // Validacao de magic bytes para PDF (segunda camada de seguranca)
            self::validarMagicBytes($arquivo);

            // Determinar contrato_id para path (documentable pode ser Contrato ou Aditivo)
            $contratoId = $documentable instanceof Contrato
                ? $documentable->id
                : $documentable->contrato_id;

            // Determinar versao (RN-120)
            $versaoAnterior = Documento::where('documentable_type', get_class($documentable))
                ->where('documentable_id', $documentable->id)
                ->where('tipo_documento', $tipoDocumento->value)
                ->versaoAtual()
                ->first();

            $versao = 1;
            if ($versaoAnterior) {
                $versao = $versaoAnterior->versao + 1;
                // Marcar versao anterior como nao-atual (versionamento nao-destrutivo)
                $versaoAnterior->update(['is_versao_atual' => false]);
            }

            // Gerar nome padronizado (RN-121)
            $numeroContrato = $documentable instanceof Contrato
                ? $documentable->numero
                : $documentable->contrato->numero ?? 'sem-numero';
            $numeroContrato = str_replace('/', '-', $numeroContrato);
            $nomeArquivo = "contrato_{$numeroContrato}_{$tipoDocumento->value}_v{$versao}.pdf";

            // Path de storage isolado por contrato e tipo (ADR-033)
            $storagePath = "documentos/contratos/{$contratoId}/{$tipoDocumento->value}";
            $caminhoCompleto = $arquivo->storeAs($storagePath, $nomeArquivo, 'local');

            // Hash SHA-256 para integridade (ADR-047, RN-220)
            $hashIntegridade = hash_file('sha256', $arquivo->getRealPath());

            // Criar registro do documento
            $documento = Documento::create([
                'documentable_type' => get_class($documentable),
                'documentable_id' => $documentable->id,
                'tipo_documento' => $tipoDocumento->value,
                'nome_original' => $arquivo->getClientOriginalName(),
                'nome_arquivo' => $nomeArquivo,
                'descricao' => $descricao,
                'caminho' => $caminhoCompleto,
                'tamanho' => $arquivo->getSize(),
                'mime_type' => $arquivo->getMimeType(),
                'hash_integridade' => $hashIntegridade,
                'versao' => $versao,
                'is_versao_atual' => true,
                'uploaded_by' => $user->id,
            ]);

            // Registrar log de acesso (RN-122)
            self::registrarLog($documento, $user, AcaoLogDocumento::Upload, $ip);

            // Se houve substituicao, registrar log de substituicao na versao anterior
            if ($versaoAnterior) {
                self::registrarLog($versaoAnterior, $user, AcaoLogDocumento::Substituicao, $ip);
            }

            return $documento;
        });
    }

    /**
     * Download autenticado de documento com registro de log (RN-122).
     */
    public static function download(Documento $documento, User $user, string $ip): StreamedResponse
    {
        if ($documento->integridade_comprometida) {
            throw new \RuntimeException(
                'Download bloqueado: a integridade deste documento esta comprometida. Contate o administrador do sistema.'
            );
        }

        self::registrarLog($documento, $user, AcaoLogDocumento::Download, $ip);

        return Storage::disk('local')->download(
            $documento->caminho,
            $documento->nome_original,
            ['Content-Type' => $documento->mime_type]
        );
    }

    /**
     * Exclusao logica de documento com registro de log (RN-134).
     */
    public static function excluir(Documento $documento, User $user, string $ip): void
    {
        self::registrarLog($documento, $user, AcaoLogDocumento::Exclusao, $ip);
        $documento->delete(); // SoftDeletes
    }

    /**
     * Calcular completude documental de um contrato (RN-128).
     */
    public static function calcularCompletude(Contrato $contrato): StatusCompletudeDocumental
    {
        return $contrato->status_completude;
    }

    /**
     * Verificar documentos pendentes do checklist obrigatorio (RN-129).
     *
     * @return array<array{tipo: TipoDocumentoContratual, label: string, presente: bool, versao: int|null}>
     */
    public static function verificarChecklist(Contrato $contrato): array
    {
        $documentosAtuais = $contrato->documentos
            ->where('is_versao_atual', true)
            ->whereNull('deleted_at');

        $checklist = [];
        foreach (self::CHECKLIST_OBRIGATORIO as $tipo) {
            $doc = $documentosAtuais->firstWhere('tipo_documento', $tipo);
            $checklist[] = [
                'tipo' => $tipo,
                'label' => $tipo->label(),
                'presente' => $doc !== null,
                'versao' => $doc?->versao,
            ];
        }

        return $checklist;
    }

    /**
     * Gerar indicadores do dashboard de documentos (RN-132).
     *
     * @return array{pct_completos: float, total_sem_contrato_original: int, total_aditivos_sem_doc: int, secretarias_pendentes: int}
     */
    public static function gerarIndicadoresDashboard(): array
    {
        $contratosAtivos = Contrato::where('status', StatusContrato::Vigente)->get();
        $totalAtivos = $contratosAtivos->count();

        if ($totalAtivos === 0) {
            return [
                'pct_completos' => 0,
                'total_sem_contrato_original' => 0,
                'total_aditivos_sem_doc' => 0,
                'secretarias_pendentes' => 0,
            ];
        }

        // Eager load documentos para evitar N+1
        $contratosAtivos->load(['documentos' => fn ($q) => $q->versaoAtual()]);

        $completos = 0;
        $semContratoOriginal = 0;
        $secretariasComPendencia = [];

        foreach ($contratosAtivos as $contrato) {
            $completude = $contrato->status_completude;

            if ($completude === StatusCompletudeDocumental::Completo) {
                $completos++;
            }

            if ($completude === StatusCompletudeDocumental::Incompleto) {
                $semContratoOriginal++;
            }

            if ($completude !== StatusCompletudeDocumental::Completo) {
                $secretariasComPendencia[$contrato->secretaria_id] = true;
            }
        }

        // Total aditivos sem documento = 0 por enquanto (aditivos serao Fase 3c)
        $totalAditivosSemDoc = 0;

        return [
            'pct_completos' => $totalAtivos > 0 ? round(($completos / $totalAtivos) * 100, 1) : 0,
            'total_sem_contrato_original' => $semContratoOriginal,
            'total_aditivos_sem_doc' => $totalAditivosSemDoc,
            'secretarias_pendentes' => count($secretariasComPendencia),
        ];
    }

    /**
     * Verificar integridade SHA-256 de um documento (RN-221).
     * Recalcula o hash e compara com o armazenado.
     */
    public static function verificarIntegridade(Documento $documento): StatusIntegridade
    {
        if (empty($documento->hash_integridade)) {
            return StatusIntegridade::Ok;
        }

        if (empty($documento->caminho) || ! Storage::disk('local')->exists($documento->caminho)) {
            LogIntegridadeDocumento::create([
                'documento_id' => $documento->id,
                'hash_esperado' => $documento->hash_integridade,
                'hash_calculado' => null,
                'status' => StatusIntegridade::ArquivoAusente,
                'detectado_em' => now(),
            ]);

            $documento->update(['integridade_comprometida' => true]);
            self::notificarIntegridadeComprometida($documento);

            return StatusIntegridade::ArquivoAusente;
        }

        $caminhoCompleto = Storage::disk('local')->path($documento->caminho);
        $hashRecalculado = hash_file('sha256', $caminhoCompleto);

        if ($hashRecalculado === $documento->hash_integridade) {
            LogIntegridadeDocumento::create([
                'documento_id' => $documento->id,
                'hash_esperado' => $documento->hash_integridade,
                'hash_calculado' => $hashRecalculado,
                'status' => StatusIntegridade::Ok,
                'detectado_em' => now(),
            ]);

            return StatusIntegridade::Ok;
        }

        LogIntegridadeDocumento::create([
            'documento_id' => $documento->id,
            'hash_esperado' => $documento->hash_integridade,
            'hash_calculado' => $hashRecalculado,
            'status' => StatusIntegridade::Divergente,
            'detectado_em' => now(),
        ]);

        $documento->update(['integridade_comprometida' => true]);
        self::notificarIntegridadeComprometida($documento);

        return StatusIntegridade::Divergente;
    }

    /**
     * Notificar administradores sobre integridade comprometida.
     */
    private static function notificarIntegridadeComprometida(Documento $documento): void
    {
        $admins = User::whereHas('role', fn ($q) => $q->where('nome', 'administrador_geral'))->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new IntegridadeComprometidaNotification($documento));
        }
    }

    /**
     * Registrar log de acesso a documento (RN-122, ADR-035).
     */
    private static function registrarLog(
        Documento $documento,
        User $user,
        AcaoLogDocumento $acao,
        string $ip,
    ): void {
        LogAcessoDocumento::create([
            'documento_id' => $documento->id,
            'user_id' => $user->id,
            'acao' => $acao->value,
            'ip_address' => $ip,
        ]);
    }

    /**
     * Validar magic bytes do arquivo PDF (segunda camada de seguranca).
     *
     * @throws \RuntimeException Se o arquivo nao e um PDF valido.
     */
    private static function validarMagicBytes(UploadedFile $arquivo): void
    {
        $handle = fopen($arquivo->getRealPath(), 'rb');
        $header = fread($handle, 5);
        fclose($handle);

        if ($header !== '%PDF-') {
            throw new \RuntimeException('O arquivo enviado nao e um PDF valido.');
        }
    }
}
