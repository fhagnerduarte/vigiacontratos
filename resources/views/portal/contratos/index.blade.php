@extends('portal.layout')

@section('title', 'Contratos Publicos')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('portal.index', $tenant->slug) }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Contratos</li>
@endsection

@section('content')
<h2 class="portal-section-title">Contratos Publicos</h2>

{{-- Painel de Filtros --}}
<div class="portal-filter-panel">
    <form method="GET" action="{{ route('portal.contratos', $tenant->slug) }}">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <div class="portal-search-wrapper">
                    <iconify-icon icon="solar:magnifer-bold" width="16" class="search-icon"></iconify-icon>
                    <input type="text" name="busca" class="form-control" placeholder="Numero ou objeto do contrato..." value="{{ request('busca') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ano</label>
                <select name="ano" class="form-select">
                    <option value="">Todos</option>
                    @foreach($anos as $ano)
                        <option value="{{ $ano }}" {{ request('ano') == $ano ? 'selected' : '' }}>{{ $ano }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Secretaria</label>
                <select name="secretaria" class="form-select">
                    <option value="">Todas</option>
                    @foreach($secretarias as $sec)
                        <option value="{{ $sec->id }}" {{ request('secretaria') == $sec->id ? 'selected' : '' }}>{{ $sec->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach(\App\Enums\StatusContrato::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ ucfirst($status->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Modalidade</label>
                <select name="modalidade" class="form-select">
                    <option value="">Todas</option>
                    @foreach(\App\Enums\ModalidadeContratacao::cases() as $mod)
                        <option value="{{ $mod->value }}" {{ request('modalidade') == $mod->value ? 'selected' : '' }}>{{ $mod->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-9 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <iconify-icon icon="solar:magnifer-bold" width="16"></iconify-icon> Filtrar
                </button>
                <a href="{{ route('portal.contratos', $tenant->slug) }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>
</div>

{{-- Contador --}}
<div class="portal-result-counter">
    Exibindo {{ $contratos->firstItem() ?? 0 }} a {{ $contratos->lastItem() ?? 0 }} de {{ $contratos->total() }} contratos
</div>

{{-- Tabela de Contratos --}}
<div class="table-responsive">
    <table class="table portal-table">
        <thead>
            <tr>
                <th>Numero</th>
                <th>Objeto</th>
                <th>Fornecedor</th>
                <th>Secretaria</th>
                <th class="text-end">Valor Global</th>
                <th>Vigencia</th>
                <th>Status</th>
                <th class="text-center">Acao</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contratos as $contrato)
            <tr>
                <td class="fw-bold">{{ $contrato->numero }}</td>
                <td>{{ Str::limit($contrato->objeto, 55) }}</td>
                <td>{{ Str::limit($contrato->fornecedor?->razao_social, 30) }}</td>
                <td>{{ Str::limit($contrato->secretaria?->nome, 25) }}</td>
                <td class="text-end text-nowrap">R$ {{ number_format($contrato->valor_global, 2, ',', '.') }}</td>
                <td class="text-nowrap">{{ $contrato->data_inicio?->format('d/m/Y') }} a {{ $contrato->data_fim?->format('d/m/Y') }}</td>
                <td>
                    @php
                        $badgeClass = match($contrato->status?->value) {
                            'vigente' => 'portal-badge-vigente',
                            'vencido' => 'portal-badge-vencido',
                            'cancelado' => 'portal-badge-cancelado',
                            'suspenso' => 'portal-badge-suspenso',
                            'encerrado' => 'portal-badge-encerrado',
                            'rescindido' => 'portal-badge-rescindido',
                            default => 'bg-secondary text-white',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($contrato->status?->value) }}</span>
                </td>
                <td class="text-center">
                    <a href="{{ route('portal.contratos.show', [$tenant->slug, $contrato->numero]) }}" class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                        <iconify-icon icon="solar:eye-bold" width="16"></iconify-icon>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div class="portal-empty-state">
                        <iconify-icon icon="solar:document-text-bold" width="48"></iconify-icon>
                        <p>Nenhum contrato encontrado com os filtros selecionados.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginacao --}}
<div class="d-flex justify-content-center mt-3">
    {{ $contratos->withQueryString()->links() }}
</div>
@endsection
