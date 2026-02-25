@extends('layout.layout')

@php
    $title = 'Encerramento — ' . $contrato->numero;
    $subTitle = 'Gestao Contratual';
    $encerramento = $contrato->encerramento;
    $etapaAtual = $encerramento?->etapa_atual;
@endphp

@section('title', $title)

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

{{-- Header --}}
<div class="card radius-8 border-0 mb-24">
    <div class="card-body p-24">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="fw-semibold mb-8">
                    <iconify-icon icon="solar:lock-bold" class="text-secondary me-8"></iconify-icon>
                    Encerramento Contratual
                </h5>
                <p class="text-secondary-light mb-0">
                    Contrato {{ $contrato->numero }} — {{ $contrato->fornecedor?->razao_social ?? 'N/A' }}
                </p>
            </div>
            <div class="d-flex gap-8">
                <a href="{{ route('tenant.contratos.show', $contrato) }}" class="btn btn-outline-primary text-sm btn-sm px-12 py-8 radius-8">
                    <iconify-icon icon="lucide:arrow-left" class="me-4"></iconify-icon> Voltar ao Contrato
                </a>
            </div>
        </div>
    </div>
</div>

@if (!$encerramento)
    {{-- Estado: nao iniciado --}}
    <div class="card radius-8 border-0 mb-24">
        <div class="card-body p-24 text-center">
            <iconify-icon icon="solar:lock-unlocked-bold" class="text-4xl text-secondary-light d-block mb-16"></iconify-icon>
            <h5 class="fw-semibold mb-12">Iniciar Encerramento</h5>
            <p class="text-secondary-light mb-24">
                O encerramento contratual segue um workflow de 6 etapas conforme Lei 14.133/2021:
                verificacao financeira, termo provisorio, avaliacao fiscal, termo definitivo, quitacao e conclusao.
            </p>
            @if (auth()->user()->hasPermission('encerramento.iniciar'))
                <form action="{{ route('tenant.contratos.encerramento.iniciar', $contrato) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary radius-8"
                            onclick="return confirm('Deseja iniciar o processo de encerramento deste contrato?')">
                        <iconify-icon icon="solar:play-bold" class="me-4"></iconify-icon> Iniciar Processo de Encerramento
                    </button>
                </form>
            @endif
        </div>
    </div>
@else
    {{-- Barra de progresso --}}
    <div class="card radius-8 border-0 mb-24">
        <div class="card-body p-24">
            <div class="d-flex justify-content-between align-items-center mb-12">
                <span class="fw-semibold text-sm">Progresso do Encerramento</span>
                <span class="fw-semibold text-sm text-primary-600">{{ number_format($encerramento->percentual_progresso, 0) }}%</span>
            </div>
            <div class="progress mb-16" style="height: 8px;">
                <div class="progress-bar bg-primary-600" role="progressbar" style="width: {{ $encerramento->percentual_progresso }}%"></div>
            </div>

            {{-- Steps indicator --}}
            <div class="d-flex justify-content-between gap-2 flex-wrap">
                @foreach ($etapas as $etapa)
                    @if ($etapa === \App\Enums\EtapaEncerramento::Encerrado && $etapaAtual !== $etapa)
                        @continue
                    @endif
                    @php
                        $concluida = $etapaAtual->ordem() > $etapa->ordem();
                        $atual = $etapaAtual === $etapa;
                        $futura = $etapaAtual->ordem() < $etapa->ordem();
                    @endphp
                    <div class="text-center flex-fill">
                        <div class="d-flex justify-content-center mb-4">
                            @if ($concluida)
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white" style="width: 28px; height: 28px;">
                                    <iconify-icon icon="ic:baseline-check" class="text-sm"></iconify-icon>
                                </span>
                            @elseif ($atual)
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-600 text-white" style="width: 28px; height: 28px;">
                                    <iconify-icon icon="{{ $etapa->icone() }}" class="text-sm"></iconify-icon>
                                </span>
                            @else
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-neutral-200 text-neutral-500" style="width: 28px; height: 28px;">
                                    {{ $etapa->ordem() }}
                                </span>
                            @endif
                        </div>
                        <small class="{{ $atual ? 'fw-semibold text-primary-600' : ($concluida ? 'text-success-main' : 'text-secondary-light') }}">
                            {{ $etapa->label() }}
                        </small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Formulario da etapa atual --}}
    @if ($etapaAtual !== \App\Enums\EtapaEncerramento::Encerrado)
        <div class="card radius-8 border-0 mb-24">
            <div class="card-header bg-transparent border-0 p-24 pb-0">
                <h6 class="fw-semibold mb-0 d-flex align-items-center gap-8">
                    <iconify-icon icon="{{ $etapaAtual->icone() }}" class="text-{{ $etapaAtual->cor() }}-main text-xl"></iconify-icon>
                    Etapa {{ $etapaAtual->ordem() }}: {{ $etapaAtual->label() }}
                </h6>
            </div>
            <div class="card-body p-24">
                @if ($etapaAtual === \App\Enums\EtapaEncerramento::VerificacaoFinanceira)
                    {{-- Resumo financeiro --}}
                    <div class="row gy-3 mb-24">
                        <div class="col-md-4">
                            <div class="p-16 border rounded text-center">
                                <p class="text-secondary-light text-sm mb-4">Valor Global</p>
                                <h6 class="mb-0">R$ {{ number_format($contrato->valor_global, 2, ',', '.') }}</h6>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-16 border rounded text-center">
                                <p class="text-secondary-light text-sm mb-4">% Executado</p>
                                <h6 class="mb-0 text-{{ $contrato->percentual_executado > 100 ? 'danger' : 'success' }}-main">
                                    {{ number_format($contrato->percentual_executado, 2, ',', '.') }}%
                                </h6>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-16 border rounded text-center">
                                <p class="text-secondary-light text-sm mb-4">Execucoes Registradas</p>
                                <h6 class="mb-0">{{ $contrato->execucoesFinanceiras()->count() }}</h6>
                            </div>
                        </div>
                    </div>

                    @if (auth()->user()->hasPermission('encerramento.verificar_financeiro'))
                        <form action="{{ route('tenant.contratos.encerramento.verificar-financeiro', $contrato) }}" method="POST">
                            @csrf
                            <div class="mb-16">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Situacao Financeira <span class="text-danger-main">*</span></label>
                                <select name="verificacao_financeira_ok" class="form-select radius-8" required>
                                    <option value="">Selecione...</option>
                                    <option value="1">Aprovada — todas as obrigacoes financeiras estao regulares</option>
                                    <option value="0">Com ressalvas — ha pendencias financeiras</option>
                                </select>
                            </div>
                            <div class="mb-16">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Observacoes</label>
                                <textarea name="verificacao_financeira_obs" class="form-control radius-8" rows="3" maxlength="2000"
                                          placeholder="Observacoes sobre a verificacao financeira..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary radius-8">
                                <iconify-icon icon="solar:check-circle-bold" class="me-4"></iconify-icon> Registrar Verificacao
                            </button>
                        </form>
                    @endif

                @elseif ($etapaAtual === \App\Enums\EtapaEncerramento::TermoProvisorio)
                    <p class="text-secondary-light mb-16">
                        Registre o termo de recebimento provisorio do objeto contratado.
                        Informe o prazo (em dias) para observacao antes do recebimento definitivo.
                    </p>

                    @if (auth()->user()->hasPermission('encerramento.registrar_termo'))
                        <form action="{{ route('tenant.contratos.encerramento.termo-provisorio', $contrato) }}" method="POST">
                            @csrf
                            <div class="mb-16">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Prazo de Observacao (dias) <span class="text-danger-main">*</span></label>
                                <input type="number" name="termo_provisorio_prazo_dias" class="form-control radius-8"
                                       min="1" max="365" value="15" required style="max-width: 200px;">
                                <small class="text-secondary-light">Prazo usual: 15 dias (Lei 14.133 art. 140)</small>
                            </div>
                            <button type="submit" class="btn btn-primary radius-8">
                                <iconify-icon icon="solar:clipboard-check-bold" class="me-4"></iconify-icon> Registrar Termo Provisorio
                            </button>
                        </form>
                    @endif

                @elseif ($etapaAtual === \App\Enums\EtapaEncerramento::AvaliacaoFiscal)
                    <p class="text-secondary-light mb-16">
                        Registre a avaliacao do fiscal sobre o desempenho do fornecedor durante a execucao do contrato.
                    </p>

                    @if ($contrato->fiscalAtual)
                        <div class="alert alert-info-100 radius-8 mb-16">
                            <strong>Fiscal responsavel:</strong> {{ $contrato->fiscalAtual->nome }}
                            — Mat: {{ $contrato->fiscalAtual->matricula }}
                        </div>
                    @endif

                    @if (auth()->user()->hasPermission('encerramento.avaliar'))
                        <form action="{{ route('tenant.contratos.encerramento.avaliacao-fiscal', $contrato) }}" method="POST">
                            @csrf
                            <div class="mb-16">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Nota de Desempenho (1-10) <span class="text-danger-main">*</span></label>
                                <input type="number" name="avaliacao_fiscal_nota" class="form-control radius-8"
                                       min="1" max="10" step="0.5" required style="max-width: 150px;">
                            </div>
                            <div class="mb-16">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Observacoes da Avaliacao</label>
                                <textarea name="avaliacao_fiscal_obs" class="form-control radius-8" rows="3" maxlength="2000"
                                          placeholder="Observacoes sobre o desempenho do fornecedor..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary radius-8">
                                <iconify-icon icon="solar:user-check-bold" class="me-4"></iconify-icon> Registrar Avaliacao
                            </button>
                        </form>
                    @endif

                @elseif ($etapaAtual === \App\Enums\EtapaEncerramento::TermoDefinitivo)
                    <p class="text-secondary-light mb-16">
                        Registre o termo de recebimento definitivo. Esta acao confirma que o objeto contratado
                        foi entregue conforme especificacoes.
                    </p>

                    @if (auth()->user()->hasPermission('encerramento.registrar_termo'))
                        <form action="{{ route('tenant.contratos.encerramento.termo-definitivo', $contrato) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary radius-8"
                                    onclick="return confirm('Confirma o registro do termo de recebimento definitivo?')">
                                <iconify-icon icon="solar:document-add-bold" class="me-4"></iconify-icon> Registrar Termo Definitivo
                            </button>
                        </form>
                    @endif

                @elseif ($etapaAtual === \App\Enums\EtapaEncerramento::Quitacao)
                    <div class="alert alert-warning-100 radius-8 mb-16">
                        <strong>Atencao:</strong> Esta e a etapa final. Ao registrar a quitacao, o contrato sera
                        encerrado formalmente e todos os alertas pendentes serao resolvidos automaticamente.
                    </div>

                    @if (auth()->user()->hasPermission('encerramento.quitar'))
                        <form action="{{ route('tenant.contratos.encerramento.quitacao', $contrato) }}" method="POST">
                            @csrf
                            <div class="mb-16">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Observacoes da Quitacao</label>
                                <textarea name="quitacao_obs" class="form-control radius-8" rows="3" maxlength="2000"
                                          placeholder="Observacoes finais sobre a quitacao..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success radius-8"
                                    onclick="return confirm('Confirma a quitacao e encerramento formal do contrato? Esta acao nao pode ser desfeita.')">
                                <iconify-icon icon="solar:check-circle-bold" class="me-4"></iconify-icon> Registrar Quitacao e Encerrar
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    @endif

    {{-- Historico de etapas concluidas --}}
    <div class="card radius-8 border-0">
        <div class="card-header bg-transparent border-0 p-24 pb-0">
            <h6 class="fw-semibold mb-0">Historico do Encerramento</h6>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="px-16 py-12">Etapa</th>
                            <th class="px-16 py-12">Data</th>
                            <th class="px-16 py-12">Responsavel</th>
                            <th class="px-16 py-12">Detalhes</th>
                            <th class="px-16 py-12 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 1. Verificacao Financeira --}}
                        <tr>
                            <td class="px-16 py-12 fw-medium">1. Verificacao Financeira</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->verificacao_financeira_em ? $encerramento->verificacao_financeira_em->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->verificadorFinanceiro?->nome ?? '-' }}</td>
                            <td class="px-16 py-12 text-sm">
                                @if ($encerramento->verificacao_financeira_ok !== null)
                                    {{ $encerramento->verificacao_financeira_ok ? 'Aprovada' : 'Com ressalvas' }}
                                    @if ($encerramento->verificacao_financeira_obs)
                                        <br><small class="text-secondary-light">{{ Str::limit($encerramento->verificacao_financeira_obs, 80) }}</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-16 py-12 text-center">
                                @if ($encerramento->verificacao_financeira_em)
                                    <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">Concluida</span>
                                @elseif ($etapaAtual === \App\Enums\EtapaEncerramento::VerificacaoFinanceira)
                                    <span class="badge bg-primary-focus text-primary-main px-12 py-4 radius-4">Em andamento</span>
                                @else
                                    <span class="badge bg-neutral-200 text-neutral-600 px-12 py-4 radius-4">Pendente</span>
                                @endif
                            </td>
                        </tr>

                        {{-- 2. Termo Provisorio --}}
                        <tr>
                            <td class="px-16 py-12 fw-medium">2. Termo Provisorio</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->termo_provisorio_em ? $encerramento->termo_provisorio_em->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->registradorTermoProvisorio?->nome ?? '-' }}</td>
                            <td class="px-16 py-12 text-sm">
                                @if ($encerramento->termo_provisorio_prazo_dias)
                                    Prazo: {{ $encerramento->termo_provisorio_prazo_dias }} dias
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-16 py-12 text-center">
                                @if ($encerramento->termo_provisorio_em)
                                    <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">Concluida</span>
                                @elseif ($etapaAtual === \App\Enums\EtapaEncerramento::TermoProvisorio)
                                    <span class="badge bg-primary-focus text-primary-main px-12 py-4 radius-4">Em andamento</span>
                                @else
                                    <span class="badge bg-neutral-200 text-neutral-600 px-12 py-4 radius-4">Pendente</span>
                                @endif
                            </td>
                        </tr>

                        {{-- 3. Avaliacao Fiscal --}}
                        <tr>
                            <td class="px-16 py-12 fw-medium">3. Avaliacao Fiscal</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->avaliacao_fiscal_em ? $encerramento->avaliacao_fiscal_em->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->avaliadorFiscal?->nome ?? '-' }}</td>
                            <td class="px-16 py-12 text-sm">
                                @if ($encerramento->avaliacao_fiscal_nota)
                                    Nota: {{ number_format($encerramento->avaliacao_fiscal_nota, 1, ',', '.') }}/10
                                    @if ($encerramento->avaliacao_fiscal_obs)
                                        <br><small class="text-secondary-light">{{ Str::limit($encerramento->avaliacao_fiscal_obs, 80) }}</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-16 py-12 text-center">
                                @if ($encerramento->avaliacao_fiscal_em)
                                    <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">Concluida</span>
                                @elseif ($etapaAtual === \App\Enums\EtapaEncerramento::AvaliacaoFiscal)
                                    <span class="badge bg-primary-focus text-primary-main px-12 py-4 radius-4">Em andamento</span>
                                @else
                                    <span class="badge bg-neutral-200 text-neutral-600 px-12 py-4 radius-4">Pendente</span>
                                @endif
                            </td>
                        </tr>

                        {{-- 4. Termo Definitivo --}}
                        <tr>
                            <td class="px-16 py-12 fw-medium">4. Termo Definitivo</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->termo_definitivo_em ? $encerramento->termo_definitivo_em->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->registradorTermoDefinitivo?->nome ?? '-' }}</td>
                            <td class="px-16 py-12 text-sm">-</td>
                            <td class="px-16 py-12 text-center">
                                @if ($encerramento->termo_definitivo_em)
                                    <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">Concluida</span>
                                @elseif ($etapaAtual === \App\Enums\EtapaEncerramento::TermoDefinitivo)
                                    <span class="badge bg-primary-focus text-primary-main px-12 py-4 radius-4">Em andamento</span>
                                @else
                                    <span class="badge bg-neutral-200 text-neutral-600 px-12 py-4 radius-4">Pendente</span>
                                @endif
                            </td>
                        </tr>

                        {{-- 5. Quitacao --}}
                        <tr>
                            <td class="px-16 py-12 fw-medium">5. Quitacao</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->quitacao_em ? $encerramento->quitacao_em->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-16 py-12 text-sm">{{ $encerramento->registradorQuitacao?->nome ?? '-' }}</td>
                            <td class="px-16 py-12 text-sm">
                                @if ($encerramento->quitacao_obs)
                                    {{ Str::limit($encerramento->quitacao_obs, 80) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-16 py-12 text-center">
                                @if ($encerramento->quitacao_em)
                                    <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">Concluida</span>
                                @elseif ($etapaAtual === \App\Enums\EtapaEncerramento::Quitacao)
                                    <span class="badge bg-primary-focus text-primary-main px-12 py-4 radius-4">Em andamento</span>
                                @else
                                    <span class="badge bg-neutral-200 text-neutral-600 px-12 py-4 radius-4">Pendente</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Conclusao --}}
                        @if ($etapaAtual === \App\Enums\EtapaEncerramento::Encerrado)
                            <tr class="table-success">
                                <td class="px-16 py-12 fw-semibold" colspan="2">
                                    <iconify-icon icon="solar:lock-bold" class="me-4"></iconify-icon>
                                    Encerrado em {{ $encerramento->data_encerramento_efetivo?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-16 py-12" colspan="2">Contrato encerrado formalmente. Todos os alertas foram resolvidos.</td>
                                <td class="px-16 py-12 text-center">
                                    <span class="badge bg-success text-white px-12 py-4 radius-4">Finalizado</span>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@endsection
