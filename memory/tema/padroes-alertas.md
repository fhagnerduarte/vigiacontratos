# Tema — Padrões de Alertas

> Extraído de `banco-de-tema.md`. Carregar quando implementando alertas, notificações ou configuração de alertas.
> Contém: Dashboard de Alertas, Configuração de Alertas (Admin), Badge de Notificação no Navbar.

---

## Padrões de Página

### Padrão: Dashboard de Alertas

```html
@extends('layout.layout')

@php
    $title = 'Alertas';
    $subTitle = 'Dashboard de alertas de vencimento';
    $script = '<script src="' . asset('assets/js/lib/apexcharts.min.js') . '"></script>';
@endphp

@section('content')
<!-- Cards de Indicadores por Faixa de Vencimento -->
<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 120 dias</p>
                        <h6 class="mb-0">{{ $vencendo120d }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-info-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:bell-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 60 dias</p>
                        <h6 class="mb-0">{{ $vencendo60d }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:bell-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 30 dias</p>
                        <h6 class="mb-0">{{ $vencendo30d }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:bell-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Vencidos</p>
                        <h6 class="mb-0 text-danger-main">{{ $vencidos }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:danger-triangle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-5 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Secretarias em Risco</p>
                        <h6 class="mb-0">{{ $secretariasRisco }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:case-round-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mt-24">
    <div class="card-body p-20">
        <div class="row gy-3">
            <div class="col-md-3">
                <select class="form-select" name="secretaria_id">
                    <option value="">Todas as Secretarias</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="prioridade">
                    <option value="">Todas as Prioridades</option>
                    <option value="informativo">Informativo</option>
                    <option value="atencao">Atenção</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="tipo_contrato">
                    <option value="">Todos os Tipos</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="faixa_valor">
                    <option value="">Todas as Faixas</option>
                    <option value="ate_100k">Até R$ 100.000</option>
                    <option value="100k_500k">R$ 100.000 - R$ 500.000</option>
                    <option value="acima_500k">Acima de R$ 500.000</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Alertas -->
<div class="card basic-data-table mt-24">
    <div class="card-header">
        <h5 class="card-title mb-0">Alertas Ativos</h5>
    </div>
    <div class="card-body">
        <table class="table bordered-table mb-0" id="dataTable" data-page-length="10">
            <thead>
                <tr>
                    <th>Contrato</th>
                    <th>Objeto</th>
                    <th>Secretaria</th>
                    <th>Vencimento</th>
                    <th>Dias Restantes</th>
                    <th>Prioridade</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Linha de alerta com badge de prioridade -->
                <tr>
                    <td>001/2026</td>
                    <td>Transporte escolar</td>
                    <td>Educação</td>
                    <td>30/06/2026</td>
                    <td>25</td>
                    <td>
                        <span class="badge bg-danger-focus text-danger-main px-20 py-9 radius-4">Urgente</span>
                    </td>
                    <td>
                        <span class="badge bg-warning-focus text-warning-main px-20 py-9 radius-4">Enviado</span>
                    </td>
                    <td>
                        <a href="#" class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:eye-bold"></iconify-icon>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### Padrão: Configuração de Alertas (Admin)

```html
<!-- Dentro da tab "Alertas" em configuracoes/index.blade.php -->
<div class="tab-pane fade show active" id="alertas">
    <h6 class="mb-16">Prazos de Antecedência para Alertas</h6>
    <p class="text-neutral-600 mb-24">Configure quantos dias antes do vencimento o sistema deve gerar alertas.</p>

    <form action="{{ route('configuracoes.alertas.update') }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($configuracoes as $config)
        <div class="row align-items-center mb-16 pb-16 border-bottom">
            <div class="col-md-3">
                <label class="form-label fw-semibold mb-0">{{ $config->dias_antecedencia }} dias antes</label>
            </div>
            <div class="col-md-3">
                <!-- Badge de prioridade -->
                @if($config->prioridade === 'urgente')
                    <span class="badge bg-danger-focus text-danger-main px-20 py-9 radius-4">Urgente</span>
                @elseif($config->prioridade === 'atencao')
                    <span class="badge bg-warning-focus text-warning-main px-20 py-9 radius-4">Atenção</span>
                @else
                    <span class="badge bg-info-focus text-info-main px-20 py-9 radius-4">Informativo</span>
                @endif
            </div>
            <div class="col-md-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="ativo[{{ $config->id }}]"
                           {{ $config->is_ativo ? 'checked' : '' }}>
                    <label class="form-check-label">Ativo</label>
                </div>
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-end mt-24">
            <button type="submit" class="btn btn-primary-600">Salvar Configurações</button>
        </div>
    </form>
</div>
```

### Padrão: Badge de Notificação no Navbar

```html
<!-- Dentro do navbar — sino de notificações -->
<div class="dropdown">
    <button class="position-relative" data-bs-toggle="dropdown">
        <iconify-icon icon="solar:bell-bold" class="text-2xl"></iconify-icon>
        @if($alertasPendentes > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger-main">
                {{ $alertasPendentes > 99 ? '99+' : $alertasPendentes }}
            </span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 350px;">
        <div class="p-16 border-bottom">
            <h6 class="mb-0">Alertas de Vencimento</h6>
        </div>
        <div class="max-h-300-px overflow-auto">
            @foreach($alertasRecentes as $alerta)
            <a href="{{ route('alertas.show', $alerta) }}" class="d-flex align-items-start gap-12 p-16 border-bottom hover-bg-neutral-50">
                <!-- Ícone por prioridade -->
                @if($alerta->prioridade === 'urgente')
                    <span class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <iconify-icon icon="solar:danger-triangle-bold"></iconify-icon>
                    </span>
                @elseif($alerta->prioridade === 'atencao')
                    <span class="w-32-px h-32-px bg-warning-focus text-warning-main rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <iconify-icon icon="solar:bell-bold"></iconify-icon>
                    </span>
                @else
                    <span class="w-32-px h-32-px bg-info-focus text-info-main rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <iconify-icon icon="solar:info-circle-bold"></iconify-icon>
                    </span>
                @endif
                <div>
                    <p class="fw-medium text-neutral-900 mb-4">{{ $alerta->contrato->numero }}</p>
                    <p class="text-neutral-600 text-sm mb-0">{{ $alerta->mensagem }}</p>
                    <p class="text-neutral-400 text-xs mb-0">{{ $alerta->created_at->diffForHumans() }}</p>
                </div>
            </a>
            @endforeach
        </div>
        <a href="{{ route('alertas.index') }}" class="d-block text-center py-12 fw-medium text-primary-600">
            Ver todos os alertas
        </a>
    </div>
</div>
```
