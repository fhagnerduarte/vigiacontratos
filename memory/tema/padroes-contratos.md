# Tema — Padrões de Contratos

> Extraído de `banco-de-tema.md`. Carregar quando implementando cadastro de contratos ou dashboard principal.
> Contém: Formulário Multi-etapa / Wizard (6 etapas), Dashboard principal.

---

## Padrões de Página

### Padrão: Formulário Multi-etapa / Wizard (Cadastro de Contrato)

```html
@extends('layout.layout')

@php
    $title = 'Novo Contrato';
    $subTitle = 'Cadastro inteligente de contrato';
@endphp

@section('content')
<!-- Indicador de Etapas -->
<div class="card mb-24">
    <div class="card-body py-20">
        <ul class="wizard-steps d-flex justify-content-between list-unstyled mb-0">
            <li class="wizard-step active" data-step="1">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-primary-600 rounded-circle d-flex align-items-center justify-content-center text-white fw-semibold">1</span>
                    <span class="fw-medium text-primary-600">Identificação</span>
                </div>
            </li>
            <li class="wizard-step" data-step="2">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">2</span>
                    <span class="fw-medium text-neutral-600">Fornecedor</span>
                </div>
            </li>
            <li class="wizard-step" data-step="3">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">3</span>
                    <span class="fw-medium text-neutral-600">Financeiro</span>
                </div>
            </li>
            <li class="wizard-step" data-step="4">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">4</span>
                    <span class="fw-medium text-neutral-600">Vigência</span>
                </div>
            </li>
            <li class="wizard-step" data-step="5">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">5</span>
                    <span class="fw-medium text-neutral-600">Fiscal</span>
                </div>
            </li>
            <li class="wizard-step" data-step="6">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">6</span>
                    <span class="fw-medium text-neutral-600">Documentos</span>
                </div>
            </li>
        </ul>
    </div>
</div>

<!-- Formulário com Etapas -->
<form action="{{ route('contratos.store') }}" method="POST" enctype="multipart/form-data" id="wizard-form">
    @csrf

    <!-- Etapa 1: Identificação -->
    <div class="card wizard-panel" data-step="1">
        <div class="card-header">
            <h5 class="card-title mb-0">Identificação do Contrato</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-20">
                    <label class="form-label">Número do Contrato</label>
                    <input type="text" class="form-control" name="numero" placeholder="001/2026" required>
                </div>
                <div class="col-md-4 mb-20">
                    <label class="form-label">Tipo de Contrato</label>
                    <select class="form-select" name="tipo" required>
                        <option value="">Selecione...</option>
                    </select>
                </div>
                <div class="col-md-4 mb-20">
                    <label class="form-label">Modalidade</label>
                    <select class="form-select" name="modalidade_contratacao" required>
                        <option value="">Selecione...</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-20">
                    <label class="form-label">Objeto</label>
                    <textarea class="form-control" name="objeto" rows="3" required></textarea>
                </div>
            </div>
            <!-- Secretaria, processo, fundamento legal... -->
        </div>
    </div>

    <!-- Etapa 2: Fornecedor (hidden por padrão) -->
    <div class="card wizard-panel d-none" data-step="2">
        <!-- Selecionar fornecedor existente ou cadastrar novo -->
    </div>

    <!-- Etapa 3: Financeiro (hidden) -->
    <div class="card wizard-panel d-none" data-step="3">
        <!-- Valor global, mensal, tipo pagamento, dotação, empenho -->
    </div>

    <!-- Etapa 4: Vigência (hidden) -->
    <div class="card wizard-panel d-none" data-step="4">
        <!-- Datas, prazo, prorrogação automática -->
    </div>

    <!-- Etapa 5: Fiscal (hidden) -->
    <div class="card wizard-panel d-none" data-step="5">
        <!-- Nome, matrícula, cargo, email -->
    </div>

    <!-- Etapa 6: Documentos (hidden) -->
    <div class="card wizard-panel d-none" data-step="6">
        <!-- Upload múltiplo com classificação por tipo -->
    </div>

    <!-- Navegação -->
    <div class="d-flex justify-content-between mt-24">
        <button type="button" class="btn btn-outline-secondary-600 wizard-prev d-none">
            <iconify-icon icon="ic:baseline-arrow-back" class="icon"></iconify-icon> Anterior
        </button>
        <div class="ms-auto d-flex gap-10">
            <a href="{{ route('contratos.index') }}" class="btn btn-outline-secondary-600">Cancelar</a>
            <button type="button" class="btn btn-primary-600 wizard-next">
                Próximo <iconify-icon icon="ic:baseline-arrow-forward" class="icon"></iconify-icon>
            </button>
            <button type="submit" class="btn btn-success-600 wizard-submit d-none">
                <iconify-icon icon="ic:baseline-check" class="icon"></iconify-icon> Salvar Contrato
            </button>
        </div>
    </div>
</form>
@endsection
```

### Padrão: Dashboard

```html
@extends('layout.layout')

@php
    $title = 'Dashboard';
    $subTitle = 'Visão geral';
    $script = '<script src="' . asset('assets/js/lib/apexcharts.min.js') . '"></script>
               <script src="' . asset('assets/js/dashboardChart.js') . '"></script>';
@endphp

@section('content')
<!-- Cards de Estatísticas -->
<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Contratos Vigentes</p>
                        <h6 class="mb-0">125</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:document-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- mais cards -->
</div>

<!-- Gráficos -->
<div class="row gy-4 mt-1">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Contratos por Mês</h5>
            </div>
            <div class="card-body">
                <div id="chart-contratos"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Por Tipo</h5>
            </div>
            <div class="card-body">
                <div id="chart-tipos"></div>
            </div>
        </div>
    </div>
</div>
@endsection
```
