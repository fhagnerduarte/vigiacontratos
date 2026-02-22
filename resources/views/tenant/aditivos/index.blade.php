@extends('layout.layout')

@php
    $title = 'Aditivos';
    $subTitle = 'Dashboard de aditivos contratuais';
@endphp

@section('title', 'Aditivos')

@section('content')

{{-- Cards de Indicadores --}}
<div class="row row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4 mb-24">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Aditivos no Ano</p>
                        <h6 class="mb-0">{{ $totalAditivosAno }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:add-circle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Valor Total Acrescido</p>
                        <h6 class="mb-0 text-success-main">R$ {{ number_format($valorTotalAcrescido, 2, ',', '.') }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:wallet-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">% Medio de Acrescimo</p>
                        <h6 class="mb-0 text-warning-main">{{ number_format($percentualMedioAcrescimo, 2, ',', '.') }}%</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:chart-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Rankings --}}
<div class="row gy-4">
    {{-- Ranking Contratos Mais Alterados (Top 10) --}}
    <div class="col-lg-7">
        <div class="card radius-8 border-0 h-100">
            <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center">
                <h6 class="text-lg fw-semibold mb-0">Contratos Mais Alterados (Top 10)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead>
                            <tr>
                                <th class="px-16 py-12">Contrato</th>
                                <th class="px-16 py-12 text-center">Aditivos</th>
                                <th class="px-16 py-12 text-end">% Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rankingContratosMaisAlterados as $item)
                            <tr>
                                <td class="px-16 py-12">
                                    <a href="{{ route('tenant.contratos.show', $item->contrato_id) }}" class="text-primary-600 fw-medium">
                                        {{ $item->numero }}
                                    </a>
                                </td>
                                <td class="px-16 py-12 text-center">{{ $item->total_aditivos }}</td>
                                <td class="px-16 py-12 text-end">
                                    <span class="badge bg-{{ $item->percentual_acumulado > 20 ? 'warning' : 'success' }}-focus text-{{ $item->percentual_acumulado > 20 ? 'warning' : 'success' }}-main px-12 py-4 radius-4">
                                        {{ number_format($item->percentual_acumulado, 2, ',', '.') }}%
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary-light py-16">Nenhum aditivo registrado neste ano.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Ranking Secretarias com Mais Aditivos --}}
    <div class="col-lg-5">
        <div class="card radius-8 border-0 h-100">
            <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center">
                <h6 class="text-lg fw-semibold mb-0">Secretarias com Mais Aditivos</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead>
                            <tr>
                                <th class="px-16 py-12">Secretaria</th>
                                <th class="px-16 py-12 text-center">Aditivos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rankingSecretarias as $item)
                            <tr>
                                <td class="px-16 py-12">{{ $item->nome }}</td>
                                <td class="px-16 py-12 text-center">{{ $item->total_aditivos }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-secondary-light py-16">Nenhum aditivo registrado neste ano.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
