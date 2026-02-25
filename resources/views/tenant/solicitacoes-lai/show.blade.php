@extends('layout.layout')

@php
    $title = 'Transparencia';
    $subTitle = 'Detalhes da Solicitacao LAI';
@endphp

@section('title', 'Solicitacao ' . $solicitacao->protocolo)

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-4">{{ $solicitacao->protocolo }}</h6>
        <span class="badge bg-{{ $solicitacao->status->cor() }}-focus text-{{ $solicitacao->status->cor() }}-main px-16 py-8 radius-4 fs-6">
            <iconify-icon icon="{{ $solicitacao->status->icone() }}" class="me-4"></iconify-icon>
            {{ $solicitacao->status->label() }}
        </span>
    </div>
    <a href="{{ route('tenant.solicitacoes-lai.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-4">
        <iconify-icon icon="solar:arrow-left-bold" class="text-lg"></iconify-icon> Voltar
    </a>
</div>

<div class="row g-3">
    {{-- Coluna principal --}}
    <div class="col-lg-8">
        {{-- Dados da solicitacao --}}
        <div class="card mb-24">
            <div class="card-header">
                <h6 class="mb-0">Dados da Solicitacao</h6>
            </div>
            <div class="card-body">
                <div class="row mb-16">
                    <div class="col-md-6">
                        <small class="text-muted">Solicitante</small>
                        <p class="fw-semibold mb-0">{{ $solicitacao->nome_solicitante }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">E-mail</small>
                        <p class="fw-semibold mb-0">{{ $solicitacao->email_solicitante }}</p>
                    </div>
                </div>
                <div class="row mb-16">
                    <div class="col-md-6">
                        <small class="text-muted">Telefone</small>
                        <p class="fw-semibold mb-0">{{ $solicitacao->telefone_solicitante ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Data de Registro</small>
                        <p class="fw-semibold mb-0">{{ $solicitacao->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="mb-16">
                    <small class="text-muted">Assunto</small>
                    <p class="fw-semibold mb-0">{{ $solicitacao->assunto }}</p>
                </div>
                <div>
                    <small class="text-muted">Descricao</small>
                    <p class="mb-0">{{ $solicitacao->descricao }}</p>
                </div>
            </div>
        </div>

        {{-- Resposta (se houver) --}}
        @if ($solicitacao->resposta)
            <div class="card mb-24">
                <div class="card-header bg-{{ $solicitacao->status === \App\Enums\StatusSolicitacaoLai::Indeferida ? 'danger' : 'success' }}-focus">
                    <h6 class="mb-0">
                        {{ $solicitacao->status === \App\Enums\StatusSolicitacaoLai::Indeferida ? 'Indeferimento' : 'Resposta' }}
                        @if ($solicitacao->classificacao_resposta)
                            — {{ $solicitacao->classificacao_resposta->label() }}
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-8">{{ $solicitacao->resposta }}</p>
                    <small class="text-muted">
                        Respondido por {{ $solicitacao->respondente?->nome ?? 'Sistema' }}
                        em {{ $solicitacao->data_resposta?->format('d/m/Y H:i') }}
                    </small>
                </div>
            </div>
        @endif

        {{-- Formularios de acao --}}
        @if (!$solicitacao->status->isFinalizado())

            {{-- Marcar em analise --}}
            @if ($solicitacao->status === \App\Enums\StatusSolicitacaoLai::Recebida && auth()->user()->hasPermission('lai.analisar'))
                <div class="card mb-24">
                    <div class="card-body">
                        <form action="{{ route('tenant.solicitacoes-lai.analisar', $solicitacao) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning d-flex align-items-center gap-8">
                                <iconify-icon icon="solar:magnifer-bold" class="text-lg"></iconify-icon>
                                Marcar como Em Analise
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Responder --}}
            @if ($solicitacao->status->permiteResposta() && auth()->user()->hasPermission('lai.responder'))
                <div class="card mb-24">
                    <div class="card-header">
                        <h6 class="mb-0">Registrar Resposta</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('tenant.solicitacoes-lai.responder', $solicitacao) }}" method="POST">
                            @csrf
                            <div class="mb-16">
                                <label for="classificacao_resposta" class="form-label">Classificacao <span class="text-danger">*</span></label>
                                <select name="classificacao_resposta" id="classificacao_resposta" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    @foreach (\App\Enums\ClassificacaoRespostaLai::cases() as $c)
                                        <option value="{{ $c->value }}">{{ $c->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-16">
                                <label for="resposta" class="form-label">Resposta <span class="text-danger">*</span></label>
                                <textarea name="resposta" id="resposta" class="form-control" rows="5"
                                          placeholder="Minimo 20 caracteres" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success d-flex align-items-center gap-8">
                                <iconify-icon icon="solar:check-circle-bold" class="text-lg"></iconify-icon>
                                Enviar Resposta
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Prorrogar --}}
            @if ($solicitacao->is_prorrogavel && auth()->user()->hasPermission('lai.prorrogar'))
                <div class="card mb-24">
                    <div class="card-header">
                        <h6 class="mb-0">Prorrogar Prazo (+10 dias)</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-16">
                            Conforme LAI art. 11, §2o, e permitida uma unica prorrogacao de 10 dias mediante justificativa expressa.
                        </p>
                        <form action="{{ route('tenant.solicitacoes-lai.prorrogar', $solicitacao) }}" method="POST">
                            @csrf
                            <div class="mb-16">
                                <label for="justificativa_prorrogacao" class="form-label">Justificativa <span class="text-danger">*</span></label>
                                <textarea name="justificativa_prorrogacao" id="justificativa_prorrogacao" class="form-control" rows="3"
                                          placeholder="Minimo 20 caracteres" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-8">
                                <iconify-icon icon="solar:clock-circle-bold" class="text-lg"></iconify-icon>
                                Prorrogar Prazo
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Indeferir --}}
            @if (!$solicitacao->status->isFinalizado() && auth()->user()->hasPermission('lai.indeferir'))
                <div class="card mb-24">
                    <div class="card-header bg-danger-focus">
                        <h6 class="mb-0">Indeferir Solicitacao</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('tenant.solicitacoes-lai.indeferir', $solicitacao) }}" method="POST">
                            @csrf
                            <div class="mb-16">
                                <label for="classificacao_indeferir" class="form-label">Classificacao <span class="text-danger">*</span></label>
                                <select name="classificacao_resposta" id="classificacao_indeferir" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    @foreach (\App\Enums\ClassificacaoRespostaLai::cases() as $c)
                                        <option value="{{ $c->value }}">{{ $c->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-16">
                                <label for="resposta_indeferir" class="form-label">Justificativa do Indeferimento <span class="text-danger">*</span></label>
                                <textarea name="resposta" id="resposta_indeferir" class="form-control" rows="4"
                                          placeholder="Minimo 20 caracteres" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger d-flex align-items-center gap-8">
                                <iconify-icon icon="solar:close-circle-bold" class="text-lg"></iconify-icon>
                                Indeferir
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Coluna lateral --}}
    <div class="col-lg-4">
        {{-- Informacoes de prazo --}}
        <div class="card mb-24">
            <div class="card-header">
                <h6 class="mb-0">Prazos</h6>
            </div>
            <div class="card-body">
                <div class="mb-12">
                    <small class="text-muted">Prazo Legal (20 dias)</small>
                    <p class="fw-semibold mb-0">{{ $solicitacao->prazo_legal->format('d/m/Y') }}</p>
                </div>
                @if ($solicitacao->prazo_estendido)
                    <div class="mb-12">
                        <small class="text-muted">Prazo Estendido (+10 dias)</small>
                        <p class="fw-semibold mb-0">{{ $solicitacao->prazo_estendido->format('d/m/Y') }}</p>
                    </div>
                @endif
                @if (!$solicitacao->status->isFinalizado())
                    <div>
                        <small class="text-muted">Situacao</small>
                        <p class="fw-semibold mb-0">
                            @if ($solicitacao->is_vencida)
                                <span class="text-danger">Vencida ha {{ abs($solicitacao->dias_restantes) }} dias</span>
                            @elseif ($solicitacao->dias_restantes <= 5)
                                <span class="text-warning">{{ $solicitacao->dias_restantes }} dias restantes</span>
                            @else
                                <span class="text-success">{{ $solicitacao->dias_restantes }} dias restantes</span>
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Timeline do historico --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Historico</h6>
            </div>
            <div class="card-body">
                @forelse ($solicitacao->historicos as $historico)
                    <div class="d-flex gap-12 {{ !$loop->last ? 'mb-16 pb-16 border-bottom' : '' }}">
                        <div class="flex-shrink-0">
                            @php $statusEnum = \App\Enums\StatusSolicitacaoLai::tryFrom($historico->status_novo); @endphp
                            <span class="badge bg-{{ $statusEnum?->cor() ?? 'secondary' }} rounded-circle p-8">
                                <iconify-icon icon="{{ $statusEnum?->icone() ?? 'solar:record-bold' }}" class="text-sm"></iconify-icon>
                            </span>
                        </div>
                        <div>
                            <p class="fw-semibold mb-0 small">{{ $statusEnum?->label() ?? $historico->status_novo }}</p>
                            <small class="text-muted">{{ $historico->created_at->format('d/m/Y H:i') }}</small>
                            @if ($historico->user)
                                <br><small class="text-muted">por {{ $historico->user->nome }}</small>
                            @endif
                            @if ($historico->observacao)
                                <p class="text-muted small mb-0 mt-4">{{ Str::limit($historico->observacao, 80) }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center mb-0">Sem registros.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
