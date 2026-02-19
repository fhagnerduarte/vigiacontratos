# Tema — Padrões de Documentos

> Extraído de `banco-de-tema.md`. Carregar quando implementando Central de Documentos ou aba de documentos.
> Contém: Central de Documentos (index), Aba Documentos Expandida (show — tab #documentos).

---

## Padrões de Página

### Padrão: Central de Documentos (documentos/index.blade.php)

```html
@extends('layout.layout')

@php
    $title = 'Central de Documentos';
    $subTitle = 'Gestão centralizada de documentos contratuais';
    $script = '<script src="' . asset('assets/js/lib/dataTables.min.js') . '"></script>';
@endphp

@section('content')
<!-- Cards de Indicadores de Completude (RN-132) -->
<div class="row row-cols-lg-4 row-cols-sm-2 row-cols-1 gy-4 mb-24">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Documentação Completa</p>
                        <h6 class="mb-0 text-success-main">{{ $pctCompletos }}%</h6>
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
                        <h6 class="mb-0 text-danger-main">{{ $totalSemContratoOriginal }}</h6>
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
                        <h6 class="mb-0 text-warning-main">{{ $totalAditivosSemDoc }}</h6>
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
                        <p class="fw-medium text-primary-light mb-1">Secretarias com Pendências</p>
                        <h6 class="mb-0">{{ $secretariasPendentes }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:case-round-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Busca e Filtros (RN-131) -->
<div class="card mb-24">
    <div class="card-body p-20">
        <form id="filtros-documentos" method="GET">
            <div class="row gy-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="numero_contrato" placeholder="Número do contrato" value="{{ request('numero_contrato') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="tipo_documento">
                        <option value="">Todos os tipos</option>
                        @foreach($tiposDocumento as $tipo)
                        <option value="{{ $tipo->value }}" {{ request('tipo_documento') === $tipo->value ? 'selected' : '' }}>
                            {{ $tipo->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="secretaria_id">
                        <option value="">Todas as secretarias</option>
                        @foreach($secretarias as $sec)
                        <option value="{{ $sec->id }}" {{ request('secretaria_id') == $sec->id ? 'selected' : '' }}>{{ $sec->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="completude">
                        <option value="">Qualquer completude</option>
                        <option value="completo">Completo</option>
                        <option value="parcial">Parcial</option>
                        <option value="incompleto">Incompleto</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="data_upload_de" placeholder="Upload de">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="data_upload_ate" placeholder="Upload até">
                </div>
                <div class="col-md-6 d-flex gap-10 align-items-end justify-content-end">
                    <a href="{{ route('documentos.index') }}" class="btn btn-outline-secondary-600">Limpar</a>
                    <button type="submit" class="btn btn-primary-600">
                        <iconify-icon icon="ic:baseline-search" class="icon"></iconify-icon> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Listagem de Contratos com Completude -->
<div class="card basic-data-table">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Contratos e Documentação</h5>
    </div>
    <div class="card-body">
        <table class="table bordered-table mb-0" id="dataTable" data-page-length="15">
            <thead>
                <tr>
                    <th>Contrato</th>
                    <th>Objeto</th>
                    <th>Secretaria</th>
                    <th class="text-center">Documentos</th>
                    <th class="text-center">Completude</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contratos as $contrato)
                <tr>
                    <td>{{ $contrato->numero }}</td>
                    <td class="text-truncate" style="max-width: 200px;">{{ $contrato->objeto }}</td>
                    <td>{{ $contrato->secretaria->nome }}</td>
                    <td class="text-center">{{ $contrato->documentos->where('is_versao_atual', true)->count() }}</td>
                    <td class="text-center">
                        @php $completude = $contrato->status_completude; @endphp
                        @if($completude === 'completo')
                            <span class="badge bg-success-focus text-success-main px-12 py-6 radius-4">Completo</span>
                        @elseif($completude === 'parcial')
                            <span class="badge bg-warning-focus text-warning-main px-12 py-6 radius-4">Parcial</span>
                        @else
                            <span class="badge bg-danger-focus text-danger-main px-12 py-6 radius-4">Incompleto</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('contratos.show', $contrato) }}#documentos"
                           class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:folder-bold"></iconify-icon>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### Padrão: Aba Documentos Expandida (contratos/show.blade.php — tab #documentos)

```html
<!-- Aba Documentos — conteúdo expandido para Módulo 5 -->
<div class="tab-pane fade" id="documentos">

    <!-- Barra de Completude Documental -->
    <div class="d-flex align-items-center justify-content-between mb-20 p-16 border rounded
        {{ $contrato->status_completude === 'completo' ? 'bg-success-focus' :
           ($contrato->status_completude === 'parcial' ? 'bg-warning-focus' : 'bg-danger-focus') }}">
        <div class="d-flex align-items-center gap-12">
            @if($contrato->status_completude === 'completo')
                <iconify-icon icon="solar:check-circle-bold" class="text-success-main text-2xl"></iconify-icon>
                <span class="fw-semibold text-success-main">Documentação Completa</span>
            @elseif($contrato->status_completude === 'parcial')
                <iconify-icon icon="solar:danger-triangle-bold" class="text-warning-main text-2xl"></iconify-icon>
                <span class="fw-semibold text-warning-main">Documentação Parcial — itens pendentes no checklist</span>
            @else
                <iconify-icon icon="solar:close-circle-bold" class="text-danger-main text-2xl"></iconify-icon>
                <span class="fw-semibold text-danger-main">Documentação Incompleta — contrato original ausente</span>
            @endif
        </div>
        @can('create', App\Models\Documento::class)
        <button class="btn btn-primary-600 btn-sm" data-bs-toggle="modal" data-bs-target="#modalUploadDocumento">
            <iconify-icon icon="solar:upload-bold" class="icon"></iconify-icon> Adicionar Documento
        </button>
        @endcan
    </div>

    <!-- Checklist de Documentos Obrigatórios (RN-129) -->
    <div class="mb-24">
        <h6 class="fw-semibold mb-12">Checklist de Documentos Obrigatórios</h6>
        <div class="row gy-2">
            @foreach($checklistObrigatorio as $item)
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-8 p-12 border rounded">
                    @if($item['presente'])
                        <iconify-icon icon="ic:baseline-check-circle" class="text-success-main text-xl flex-shrink-0"></iconify-icon>
                    @else
                        <iconify-icon icon="ic:baseline-cancel" class="text-danger-main text-xl flex-shrink-0"></iconify-icon>
                    @endif
                    <span class="text-sm {{ $item['presente'] ? 'text-neutral-700' : 'text-danger-main fw-medium' }}">
                        {{ $item['label'] }}
                    </span>
                    @if($item['presente'])
                        <span class="badge bg-neutral-100 text-neutral-600 ms-auto text-xs">v{{ $item['versao'] }}</span>
                    @else
                        <span class="badge bg-danger-focus text-danger-main ms-auto text-xs">Pendente</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Documentos Agrupados por Tipo -->
    @foreach($documentosPorTipo as $tipo => $docs)
    <div class="mb-20">
        <h6 class="fw-semibold text-neutral-700 mb-12 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:folder-bold" class="text-primary-600"></iconify-icon>
            {{ $tipo }}
            <span class="badge bg-neutral-200 text-neutral-600 ms-2">{{ $docs->count() }}</span>
        </h6>
        @foreach($docs as $doc)
        <div class="d-flex align-items-center gap-12 p-12 border rounded mb-8
            {{ $doc->is_versao_atual ? '' : 'opacity-75 bg-neutral-50' }}">
            <iconify-icon icon="solar:file-bold" class="text-primary-600 text-xl flex-shrink-0"></iconify-icon>
            <div class="flex-grow-1">
                <p class="fw-medium mb-0 text-sm">{{ $doc->nome_original }}</p>
                <p class="text-neutral-400 text-xs mb-0">
                    v{{ $doc->versao }} — {{ number_format($doc->tamanho / 1024 / 1024, 2) }} MB
                    — {{ $doc->created_at->format('d/m/Y H:i') }}
                    — por {{ $doc->uploader->name }}
                    @if(!$doc->is_versao_atual)
                        <span class="badge bg-neutral-200 text-neutral-600 ms-2">Versão anterior</span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-8 flex-shrink-0">
                @can('download', $doc)
                <a href="{{ route('documentos.download', $doc) }}"
                   class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center">
                    <iconify-icon icon="solar:download-bold"></iconify-icon>
                </a>
                @endcan
                @can('delete', $doc)
                @if($doc->is_versao_atual)
                <form action="{{ route('documentos.destroy', $doc) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit"
                       class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                       onclick="return confirm('Excluir este documento?')">
                        <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>

<!-- Modal de Upload de Documento -->
<div class="modal fade" id="modalUploadDocumento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="documentable_type" value="App\Models\Contrato">
                <input type="hidden" name="documentable_id" value="{{ $contrato->id }}">
                <div class="modal-body">
                    <div class="mb-16">
                        <label class="form-label">Tipo de Documento <span class="text-danger-main">*</span></label>
                        <select class="form-select" name="tipo_documento" required>
                            <option value="">Selecione o tipo...</option>
                            @foreach($tiposDocumento as $tipo)
                            <option value="{{ $tipo->value }}">{{ $tipo->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-16">
                        <label class="form-label">Arquivo PDF <span class="text-danger-main">*</span></label>
                        <input type="file" class="form-control" name="arquivo" accept=".pdf" required>
                        <small class="text-neutral-400">Apenas PDF. Tamanho máximo: 20MB</small>
                    </div>
                    <div class="mb-16">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" name="descricao" placeholder="Descrição opcional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-600">
                        <iconify-icon icon="solar:upload-bold" class="icon"></iconify-icon> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---
