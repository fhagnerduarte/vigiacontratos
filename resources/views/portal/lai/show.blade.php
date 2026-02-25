@extends('portal.layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Solicitacao {{ $solicitacao->protocolo }}</h2>
                <p class="text-muted mb-0">Registrada em {{ $solicitacao->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <span class="badge bg-{{ $solicitacao->status->cor() }} fs-6 px-3 py-2">
                {{ $solicitacao->status->label() }}
            </span>
        </div>

        {{-- Informacoes da solicitacao --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Dados da Solicitacao</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Protocolo:</strong> {{ $solicitacao->protocolo }}
                    </div>
                    <div class="col-md-6">
                        <strong>Solicitante:</strong> {{ $solicitacao->nome_solicitante }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Prazo Legal:</strong> {{ $solicitacao->prazo_legal->format('d/m/Y') }}
                        @if ($solicitacao->prazo_estendido)
                            <br><strong>Prazo Estendido:</strong> {{ $solicitacao->prazo_estendido->format('d/m/Y') }}
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if (!$solicitacao->status->isFinalizado())
                            <strong>Dias Restantes:</strong>
                            @if ($solicitacao->is_vencida)
                                <span class="text-danger fw-bold">Vencida ({{ abs($solicitacao->dias_restantes) }} dias)</span>
                            @else
                                <span class="{{ $solicitacao->dias_restantes <= 5 ? 'text-warning fw-bold' : 'text-success' }}">
                                    {{ $solicitacao->dias_restantes }} dias
                                </span>
                            @endif
                        @else
                            <strong>Respondida em:</strong> {{ $solicitacao->data_resposta?->format('d/m/Y H:i') ?? '-' }}
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Assunto:</strong> {{ $solicitacao->assunto }}
                </div>
                <div>
                    <strong>Descricao:</strong>
                    <p class="mt-1">{{ $solicitacao->descricao }}</p>
                </div>
            </div>
        </div>

        {{-- Resposta (se houver) --}}
        @if ($solicitacao->resposta)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-{{ $solicitacao->status === \App\Enums\StatusSolicitacaoLai::Indeferida ? 'danger' : 'success' }} text-white">
                    <h5 class="mb-0">
                        {{ $solicitacao->status === \App\Enums\StatusSolicitacaoLai::Indeferida ? 'Indeferimento' : 'Resposta' }}
                        @if ($solicitacao->classificacao_resposta)
                            — {{ $solicitacao->classificacao_resposta->label() }}
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <p>{{ $solicitacao->resposta }}</p>
                </div>
            </div>
        @endif

        {{-- Prorrogacao (se houver) --}}
        @if ($solicitacao->justificativa_prorrogacao)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Prorrogacao de Prazo</h5>
                </div>
                <div class="card-body">
                    <p><strong>Data:</strong> {{ $solicitacao->data_prorrogacao?->format('d/m/Y H:i') }}</p>
                    <p><strong>Novo prazo:</strong> {{ $solicitacao->prazo_estendido?->format('d/m/Y') }}</p>
                    <p><strong>Justificativa:</strong> {{ $solicitacao->justificativa_prorrogacao }}</p>
                </div>
            </div>
        @endif

        {{-- Timeline do historico --}}
        @if ($solicitacao->historicos->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Historico</h5>
                </div>
                <div class="card-body">
                    @foreach ($solicitacao->historicos->sortBy('created_at') as $historico)
                        <div class="d-flex mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                            <div class="me-3">
                                <span class="badge bg-secondary rounded-pill">{{ $loop->iteration }}</span>
                            </div>
                            <div>
                                <strong>{{ \App\Enums\StatusSolicitacaoLai::tryFrom($historico->status_novo)?->label() ?? $historico->status_novo }}</strong>
                                <br>
                                <small class="text-muted">{{ $historico->created_at->format('d/m/Y H:i') }}</small>
                                @if ($historico->observacao)
                                    <p class="mb-0 mt-1 text-muted">{{ $historico->observacao }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="d-flex gap-2">
            <a href="{{ route('portal.lai.create', $tenant->slug) }}" class="btn btn-outline-primary">Nova Solicitacao</a>
            <a href="{{ route('portal.lai.consultar', $tenant->slug) }}" class="btn btn-outline-secondary">Consultar Outra</a>
            <a href="{{ route('portal.index', $tenant->slug) }}" class="btn btn-outline-secondary">Voltar ao Portal</a>
        </div>
    </div>
</div>
@endsection
