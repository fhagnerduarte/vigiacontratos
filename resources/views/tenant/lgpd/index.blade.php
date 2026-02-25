@extends('layout.layout')

@php
    $title = 'LGPD';
    $subTitle = 'Gestao de solicitacoes de protecao de dados';
@endphp

@section('title', 'LGPD — Solicitacoes')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Solicitacoes LGPD</h6>
    @if (auth()->user()->hasPermission('lgpd.solicitar'))
        <a href="{{ route('tenant.lgpd.create') }}" class="btn btn-sm btn-primary-600 d-flex align-items-center gap-4">
            <iconify-icon icon="ic:baseline-plus" class="text-lg"></iconify-icon> Nova Solicitacao
        </a>
    @endif
</div>

<div class="card">
    <div class="card-body">
        @if ($solicitacoes->isEmpty())
            <div class="text-center py-40">
                <iconify-icon icon="solar:shield-keyhole-bold" class="text-neutral-400 mb-12" style="font-size: 48px;"></iconify-icon>
                <p class="text-neutral-500">Nenhuma solicitacao LGPD registrada.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Entidade</th>
                            <th>Solicitante</th>
                            <th>Status</th>
                            <th>Data Solicitacao</th>
                            <th>Data Execucao</th>
                            <th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($solicitacoes as $solicitacao)
                            <tr>
                                <td>{{ $solicitacao->id }}</td>
                                <td>
                                    <span class="badge bg-info-focus text-info-main px-12 py-6 radius-4">
                                        {{ $solicitacao->tipo_solicitacao?->label() ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    {{ class_basename($solicitacao->entidade_tipo) }} #{{ $solicitacao->entidade_id }}
                                </td>
                                <td>{{ $solicitacao->solicitante }}</td>
                                <td>
                                    @if ($solicitacao->status === 'processado' && $solicitacao->tipo_solicitacao?->value === 'anonimizacao')
                                        <span class="badge bg-success-focus text-success-main px-12 py-6 radius-4">
                                            Anonimizado
                                        </span>
                                    @elseif ($solicitacao->jaProcessado)
                                        <span class="badge bg-success-focus text-success-main px-12 py-6 radius-4">
                                            Processado
                                        </span>
                                    @else
                                        <span class="badge bg-warning-focus text-warning-main px-12 py-6 radius-4">
                                            Pendente
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $solicitacao->data_solicitacao?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $solicitacao->data_execucao?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-4">
                                        <a href="{{ route('tenant.lgpd.show', $solicitacao) }}"
                                           class="btn btn-sm btn-outline-primary-600"
                                           title="Visualizar">
                                            <iconify-icon icon="solar:eye-bold" class="text-lg"></iconify-icon>
                                        </a>
                                        @if ($solicitacao->status === 'pendente' && !$solicitacao->jaProcessado && auth()->user()->hasPermission('lgpd.processar'))
                                            <a href="{{ route('tenant.lgpd.show', $solicitacao) }}"
                                               class="btn btn-sm btn-outline-success-600"
                                               title="Processar">
                                                <iconify-icon icon="solar:check-circle-bold" class="text-lg"></iconify-icon>
                                            </a>
                                        @endif
                                    </div>
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
