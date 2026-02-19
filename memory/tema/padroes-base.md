# Tema — Padrões Base de Página

> Extraído de `banco-de-tema.md`. Carregar quando implementando listagens, formulários simples ou páginas de detalhes.
> Contém: Padrão Listagem (CRUD Index), Formulário Simples (Create/Edit), Detalhes com Abas (Show).

---

## Padrões de Página

### Padrão: Listagem (CRUD — Index)

```html
@extends('layout.layout')

@php
    $title = 'Contratos';
    $subTitle = 'Listagem de contratos';
    $script = '<script src="' . asset('assets/js/lib/dataTables.min.js') . '"></script>';
@endphp

@section('content')
<div class="card basic-data-table">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Contratos</h5>
        <a href="{{ route('contratos.create') }}" class="btn btn-primary-600">
            <iconify-icon icon="ic:baseline-plus" class="icon"></iconify-icon> Novo Contrato
        </a>
    </div>
    <div class="card-body">
        <table class="table bordered-table mb-0" id="dataTable" data-page-length="10">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Objeto</th>
                    <th>Fornecedor</th>
                    <th>Vigência</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- linhas da tabela -->
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### Padrão: Formulário Simples (Create / Edit)

```html
@extends('layout.layout')

@php
    $title = 'Novo Fornecedor';
    $subTitle = 'Cadastro de fornecedor';
@endphp

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Dados do Fornecedor</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('fornecedores.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-20">
                    <label class="form-label">Razão Social</label>
                    <input type="text" class="form-control" name="razao_social" required>
                </div>
                <div class="col-md-6 mb-20">
                    <label class="form-label">CNPJ</label>
                    <input type="text" class="form-control" name="cnpj" required>
                </div>
            </div>
            <!-- mais campos -->
            <div class="d-flex gap-10 justify-content-end">
                <a href="{{ route('fornecedores.index') }}" class="btn btn-outline-secondary-600">Cancelar</a>
                <button type="submit" class="btn btn-primary-600">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
```

### Padrão: Detalhes com Abas (Show — Contrato)

```html
@extends('layout.layout')

@php
    $title = 'Contrato ' . $contrato->numero;
    $subTitle = 'Detalhes do contrato';
@endphp

@section('content')
<!-- Cabeçalho com Score de Risco -->
<div class="card mb-24">
    <div class="card-body p-24">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-4">{{ $contrato->numero }}</h5>
                <p class="text-neutral-600 mb-0">{{ $contrato->objeto }}</p>
            </div>
            <div class="d-flex gap-12 align-items-center">
                <!-- Badge de Status -->
                <span class="badge bg-success-focus text-success-main px-20 py-9 radius-4">Vigente</span>
                <!-- Badge de Risco -->
                <span class="badge bg-warning-focus text-warning-main px-20 py-9 radius-4">
                    🟡 Risco Médio (Score: 35)
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Abas de Conteúdo -->
<div class="card">
    <div class="card-body p-24">
        <ul class="nav bordered-tab" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#dados">Dados Gerais</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#fiscal">Fiscal</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#financeiro">Financeiro</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#documentos">Documentos</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#aditivos">Aditivos</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#auditoria">Auditoria</a></li>
        </ul>
        <div class="tab-content mt-24">
            <div class="tab-pane fade show active" id="dados">
                <!-- Dados gerais do contrato em grid -->
            </div>
            <div class="tab-pane fade" id="fiscal">
                <!-- Fiscal atual + histórico de trocas -->
            </div>
            <div class="tab-pane fade" id="financeiro">
                <!-- Execuções financeiras + percentual executado + barra de progresso -->
            </div>
            <div class="tab-pane fade" id="documentos">
                <!-- Módulo 5: barra de completude + checklist obrigatório + documentos agrupados por tipo + modal upload -->
                <!-- Ver padrão "Aba Documentos Expandida" para implementação completa -->
            </div>
            <div class="tab-pane fade" id="aditivos">
                <!-- Timeline/lista de aditivos -->
            </div>
            <div class="tab-pane fade" id="auditoria">
                <!-- Log de alterações (campo, anterior, novo, quem, quando) -->
            </div>
        </div>
    </div>
</div>
@endsection
```
