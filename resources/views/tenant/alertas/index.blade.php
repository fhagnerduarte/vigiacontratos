@extends('layout.layout')

@php
    $title = 'Alertas';
    $subTitle = 'Painel de monitoramento de vencimentos contratuais';
@endphp

@section('title', 'Alertas')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Painel de Alertas</h6>
    @if (auth()->user()->hasPermission('configuracao.editar'))
        <a href="{{ route('tenant.alertas.configuracoes') }}" class="btn btn-sm btn-outline-primary">
            <iconify-icon icon="solar:settings-bold" class="me-1"></iconify-icon> Configuracoes
        </a>
    @endif
</div>

{{-- 4 Cards de Indicadores (RN-055) --}}
<div class="row row-cols-xxxl-4 row-cols-lg-4 row-cols-sm-2 row-cols-1 gy-4 mb-24">
    {{-- Vencendo em 120 dias --}}
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 120 dias</p>
                        <h6 class="mb-0">{{ $indicadores['vencendo_120d'] }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-info-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:info-circle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vencendo em 60 dias --}}
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 60 dias</p>
                        <h6 class="mb-0">{{ $indicadores['vencendo_60d'] }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:clock-circle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vencendo em 30 dias --}}
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 30 dias</p>
                        <h6 class="mb-0">{{ $indicadores['vencendo_30d'] }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:danger-triangle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ja vencidos --}}
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Ja Vencidos</p>
                        <h6 class="mb-0 {{ $indicadores['vencidos'] > 0 ? 'text-danger-main' : '' }}">{{ $indicadores['vencidos'] }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:alarm-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros (RN-056) --}}
<div class="card radius-8 border-0 mb-24">
    <div class="card-header">
        <h6 class="card-title mb-0">Filtros</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('tenant.alertas.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Secretaria</label>
                <select name="secretaria_id" class="form-select select2" data-placeholder="Todas...">
                    <option value=""></option>
                    @foreach ($secretarias as $secretaria)
                        <option value="{{ $secretaria->id }}" {{ request('secretaria_id') == $secretaria->id ? 'selected' : '' }}>
                            {{ $secretaria->sigla }} - {{ $secretaria->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Prioridade</label>
                <select name="prioridade" class="form-select">
                    <option value="">Todas</option>
                    @foreach (\App\Enums\PrioridadeAlerta::cases() as $prioridade)
                        <option value="{{ $prioridade->value }}" {{ request('prioridade') == $prioridade->value ? 'selected' : '' }}>
                            {{ $prioridade->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo Evento</label>
                <select name="tipo_evento" class="form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Enums\TipoEventoAlerta::cases() as $tipo)
                        <option value="{{ $tipo->value }}" {{ request('tipo_evento') == $tipo->value ? 'selected' : '' }}>
                            {{ $tipo->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Nao resolvidos</option>
                    @foreach (\App\Enums\StatusAlerta::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo Contrato</label>
                <select name="tipo_contrato" class="form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Enums\TipoContrato::cases() as $tipo)
                        <option value="{{ $tipo->value }}" {{ request('tipo_contrato') == $tipo->value ? 'selected' : '' }}>
                            {{ $tipo->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary-600 w-100">
                    <iconify-icon icon="ion:search-outline"></iconify-icon>
                </button>
                <a href="{{ route('tenant.alertas.index') }}" class="btn btn-outline-secondary w-100" title="Limpar filtros">
                    <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabela de Alertas --}}
<div class="card radius-8 border-0">
    <div class="card-body p-0">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show m-16" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th>Prioridade</th>
                        <th>Contrato</th>
                        <th>Fornecedor</th>
                        <th>Evento</th>
                        <th>Vencimento</th>
                        <th>Dias</th>
                        <th>Status</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alertas as $alerta)
                    <tr>
                        <td>
                            <span class="badge bg-{{ $alerta->prioridade->cor() }}-focus text-{{ $alerta->prioridade->cor() }}-main px-12 py-6 radius-4 fw-semibold text-sm">
                                <iconify-icon icon="{{ $alerta->prioridade->icone() }}" class="me-1"></iconify-icon>
                                {{ $alerta->prioridade->label() }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('tenant.contratos.show', $alerta->contrato_id) }}" class="fw-semibold text-primary-600">
                                {{ $alerta->contrato->numero ?? '-' }}
                            </a>
                            <br>
                            <small class="text-secondary-light">{{ \Illuminate\Support\Str::limit($alerta->contrato->objeto ?? '', 40) }}</small>
                        </td>
                        <td>{{ $alerta->contrato->fornecedor->razao_social ?? '-' }}</td>
                        <td>{{ $alerta->tipo_evento->label() }}</td>
                        <td>{{ $alerta->data_vencimento->format('d/m/Y') }}</td>
                        <td>
                            <span class="fw-bold {{ $alerta->dias_para_vencimento <= 7 ? 'text-danger-main' : ($alerta->dias_para_vencimento <= 30 ? 'text-warning-main' : '') }}">
                                {{ $alerta->dias_para_vencimento }}d
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $alerta->status->cor() }}-focus text-{{ $alerta->status->cor() }}-main px-12 py-6 radius-4 fw-semibold text-sm">
                                {{ $alerta->status->label() }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('tenant.alertas.show', $alerta) }}" class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                                <iconify-icon icon="solar:eye-bold"></iconify-icon>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-16">
                            <iconify-icon icon="solar:check-circle-bold" class="text-success-main text-3xl mb-8"></iconify-icon>
                            <p class="text-secondary-light mb-0">Nenhum alerta encontrado. Todos os contratos estao em dia!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Paginacao --}}
        @if ($alertas->hasPages())
        <div class="px-24 py-16">
            {{ $alertas->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
