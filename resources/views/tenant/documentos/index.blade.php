@extends('layout.layout')

@php
    $title = 'Central de Documentos';
    $subTitle = 'Gestao centralizada de documentos contratuais';
@endphp

@section('title', 'Central de Documentos')

@section('content')

{{-- Cards de Indicadores de Completude (RN-132) --}}
<div class="row row-cols-lg-4 row-cols-sm-2 row-cols-1 gy-4 mb-24">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Documentacao Completa</p>
                        <h6 class="mb-0 text-success-main">{{ $indicadores['pct_completos'] }}%</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:check-circle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Sem Contrato Original</p>
                        <h6 class="mb-0 text-danger-main">{{ $indicadores['total_sem_contrato_original'] }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:danger-triangle-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Aditivos sem Documento</p>
                        <h6 class="mb-0 text-warning-main">{{ $indicadores['total_aditivos_sem_doc'] }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:folder-error-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Secretarias com Pendencias</p>
                        <h6 class="mb-0">{{ $indicadores['secretarias_pendentes'] }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:case-round-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Busca e Filtros (RN-131) --}}
<div class="card mb-24">
    <div class="card-body p-20">
        <form id="filtros-documentos" method="GET" action="{{ route('tenant.documentos.index') }}">
            <div class="row gy-3">
                <div class="col-md-3">
                    <input type="text" class="form-control radius-8" name="numero_contrato" placeholder="Numero do contrato" value="{{ request('numero_contrato') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select radius-8 select2" name="tipo_documento">
                        <option value="">Todos os tipos</option>
                        @foreach ($tiposDocumento as $tipo)
                            <option value="{{ $tipo->value }}" {{ request('tipo_documento') === $tipo->value ? 'selected' : '' }}>
                                {{ $tipo->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select radius-8 select2" name="secretaria_id">
                        <option value="">Todas as secretarias</option>
                        @foreach ($secretarias as $sec)
                            <option value="{{ $sec->id }}" {{ request('secretaria_id') == $sec->id ? 'selected' : '' }}>{{ $sec->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select radius-8 select2" name="completude">
                        <option value="">Qualquer completude</option>
                        <option value="completo" {{ request('completude') === 'completo' ? 'selected' : '' }}>Completo</option>
                        <option value="parcial" {{ request('completude') === 'parcial' ? 'selected' : '' }}>Parcial</option>
                        <option value="incompleto" {{ request('completude') === 'incompleto' ? 'selected' : '' }}>Incompleto</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control radius-8" name="data_upload_de" placeholder="Upload de" value="{{ request('data_upload_de') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control radius-8" name="data_upload_ate" placeholder="Upload ate" value="{{ request('data_upload_ate') }}">
                </div>
                <div class="col-md-6 d-flex gap-10 align-items-end justify-content-end">
                    <a href="{{ route('tenant.documentos.index') }}" class="btn btn-outline-secondary-600 radius-8">Limpar</a>
                    <button type="submit" class="btn btn-primary-600 radius-8">
                        <iconify-icon icon="ic:baseline-search" class="icon"></iconify-icon> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Listagem de Contratos com Completude --}}
<div class="card radius-8 border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-24 py-16">Contrato</th>
                        <th class="px-24 py-16">Objeto</th>
                        <th class="px-24 py-16">Secretaria</th>
                        <th class="px-24 py-16 text-center">Documentos</th>
                        <th class="px-24 py-16 text-center">Completude</th>
                        <th class="px-24 py-16 text-center">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contratos as $contrato)
                        @php $completude = $contrato->status_completude; @endphp
                        <tr>
                            <td class="px-24 py-16 fw-medium">
                                <a href="{{ route('tenant.contratos.show', $contrato) }}" class="text-primary-600 fw-semibold">
                                    {{ $contrato->numero }}
                                </a>
                            </td>
                            <td class="px-24 py-16" title="{{ $contrato->objeto }}">{{ Str::limit($contrato->objeto, 50) }}</td>
                            <td class="px-24 py-16">{{ $contrato->secretaria->nome ?? '-' }}</td>
                            <td class="px-24 py-16 text-center">{{ $contrato->documentos->count() }}</td>
                            <td class="px-24 py-16 text-center">
                                <span class="badge bg-{{ $completude->cor() }}-focus text-{{ $completude->cor() }}-main px-12 py-6 radius-4">
                                    {{ $completude->label() }}
                                </span>
                            </td>
                            <td class="px-24 py-16 text-center">
                                <a href="{{ route('tenant.contratos.show', $contrato) }}#tab-documentos"
                                   class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                   title="Ver documentos">
                                    <iconify-icon icon="solar:folder-bold"></iconify-icon>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary-light py-24">Nenhum contrato encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if ($contratos->hasPages())
    <div class="mt-16">
        {{ $contratos->links() }}
    </div>
@endif

@endsection
