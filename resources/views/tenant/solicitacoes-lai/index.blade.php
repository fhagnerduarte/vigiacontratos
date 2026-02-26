@extends('layout.layout')

@php
    $title = 'Transparência';
    $subTitle = 'Solicitações LAI (e-SIC)';
@endphp

@section('title', 'Solicitações LAI')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Solicitações de Acesso à Informação</h6>
</div>

{{-- Resumo --}}
<div class="row g-3 mb-24">
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-12">
                <h4 class="text-primary fw-bold mb-0">{{ $resumo['total'] }}</h4>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-12">
                <h4 class="text-warning fw-bold mb-0">{{ $resumo['pendentes'] }}</h4>
                <small class="text-muted">Pendentes</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-12">
                <h4 class="text-success fw-bold mb-0">{{ $resumo['respondidas'] }}</h4>
                <small class="text-muted">Respondidas</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-12">
                <h4 class="text-danger fw-bold mb-0">{{ $resumo['vencidas'] }}</h4>
                <small class="text-muted">Vencidas</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-12">
                <h4 class="text-info fw-bold mb-0">{{ $resumo['tempo_medio_resposta'] }} dias</h4>
                <small class="text-muted">Tempo Médio de Resposta</small>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-24">
    <div class="card-body py-12">
        <form action="{{ route('tenant.solicitacoes-lai.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-4">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($statusOptions as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-4">Vencidas</label>
                <select name="vencidas" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="1" {{ request('vencidas') === '1' ? 'selected' : '' }}>Apenas Vencidas</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-4">Busca</label>
                <input type="text" name="busca" class="form-control form-control-sm"
                       placeholder="Protocolo, nome ou assunto" value="{{ request('busca') }}">
            </div>
            <div class="col-md-3 d-flex gap-4">
                <button type="submit" class="btn btn-sm btn-primary-600">Filtrar</button>
                <a href="{{ route('tenant.solicitacoes-lai.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </div>
</div>

{{-- Tabela --}}
<div class="card">
    <div class="card-body">
        @if ($solicitacoes->isEmpty())
            <div class="text-center py-40">
                <iconify-icon icon="solar:inbox-bold" class="text-neutral-400 mb-12" style="font-size: 48px;"></iconify-icon>
                <p class="text-neutral-500">Nenhuma solicitação LAI encontrada.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Solicitante</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Prazo</th>
                            <th>Recebida em</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($solicitacoes as $solicitacao)
                            <tr>
                                <td class="fw-semibold">{{ $solicitacao->protocolo }}</td>
                                <td>{{ $solicitacao->nome_solicitante }}</td>
                                <td>{{ Str::limit($solicitacao->assunto, 40) }}</td>
                                <td>
                                    <span class="badge bg-{{ $solicitacao->status->cor() }}-focus text-{{ $solicitacao->status->cor() }}-main px-12 py-6 radius-4">
                                        {{ $solicitacao->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    @if (!$solicitacao->status->isFinalizado())
                                        @if ($solicitacao->is_vencida)
                                            <span class="text-danger fw-bold">Vencida</span>
                                        @elseif ($solicitacao->dias_restantes <= 5)
                                            <span class="text-warning fw-bold">{{ $solicitacao->dias_restantes }}d</span>
                                        @else
                                            <span class="text-success">{{ $solicitacao->dias_restantes }}d</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $solicitacao->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('tenant.solicitacoes-lai.show', $solicitacao) }}"
                                       class="btn btn-sm btn-outline-primary-600" title="Visualizar">
                                        <iconify-icon icon="solar:eye-bold" class="text-lg"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-24">
                {{ $solicitacoes->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
