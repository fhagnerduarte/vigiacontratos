@extends('layout.layout')

@php
    $title = 'Configuracoes de Alertas';
    $subTitle = 'Definir prazos e ativacao dos alertas automaticos';
@endphp

@section('title', 'Configuracoes de Alertas')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <a href="{{ route('tenant.alertas.index') }}" class="btn btn-sm btn-outline-secondary">
        <iconify-icon icon="solar:arrow-left-bold" class="me-1"></iconify-icon> Voltar aos Alertas
    </a>
</div>

<div class="card radius-8 border-0">
    <div class="card-header">
        <h6 class="card-title mb-0">Prazos de Alerta (RN-015)</h6>
        <p class="text-sm text-secondary-light mb-0 mt-1">
            Configure quais prazos de antecedencia devem gerar alertas automaticos para vencimento de contratos.
        </p>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('tenant.alertas.salvar-configuracoes') }}">
            @csrf

            <div class="table-responsive">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>Dias de Antecedencia</th>
                            <th>Prioridade Padrao</th>
                            <th class="text-center">Ativo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($configuracoes as $index => $config)
                        <tr>
                            <td>
                                <input type="hidden" name="configuracoes[{{ $index }}][id]" value="{{ $config->id }}">
                                <span class="fw-semibold">{{ $config->dias_antecedencia }} dias</span>
                                <br>
                                <small class="text-secondary-light">antes do vencimento</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $config->prioridade_padrao->cor() }}-focus text-{{ $config->prioridade_padrao->cor() }}-main px-12 py-6 radius-4 fw-semibold text-sm">
                                    <iconify-icon icon="{{ $config->prioridade_padrao->icone() }}" class="me-1"></iconify-icon>
                                    {{ $config->prioridade_padrao->label() }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input type="hidden" name="configuracoes[{{ $index }}][is_ativo]" value="0">
                                    <input class="form-check-input" type="checkbox"
                                           name="configuracoes[{{ $index }}][is_ativo]"
                                           value="1"
                                           {{ $config->is_ativo ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-24 text-end">
                <button type="submit" class="btn btn-primary-600">
                    <iconify-icon icon="solar:disk-bold" class="me-1"></iconify-icon> Salvar Configuracoes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
