@extends('layout.layout')

@php
    $title = 'Relatorios';
    $subTitle = 'Monitoramento';
@endphp

@section('title', 'Relatorios')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-4">Central de Relatorios</h6>
        <p class="text-neutral-500 text-sm mb-0">Gere relatorios em PDF e exporte dados em Excel</p>
    </div>
</div>

{{-- SECAO 1: Tribunal de Contas --}}
@if (auth()->user()->hasPermission('relatorio.gerar') || auth()->user()->hasPermission('documento.download') || auth()->user()->hasPermission('painel-risco.exportar'))
<div class="mb-24">
    <h6 class="text-neutral-600 fw-semibold text-sm mb-16">
        <iconify-icon icon="solar:shield-check-bold" class="text-primary-600 me-4"></iconify-icon>
        Tribunal de Contas / Conformidade
    </h6>
    <div class="row g-16">
        {{-- Card: Documentos por Contrato --}}
        @if (auth()->user()->hasPermission('documento.download'))
        <div class="col-md-6 col-lg-4">
            <div class="card radius-8 border-0 h-100">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center gap-12 mb-16">
                        <div class="w-40-px h-40-px bg-primary-100 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:document-text-bold" class="text-primary-600 text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0 text-sm">Documentos por Contrato</h6>
                            <span class="text-neutral-500 text-xs">RN-133 — Relatorio TCE</span>
                        </div>
                    </div>
                    <p class="text-neutral-500 text-sm mb-16">Lista todos os documentos vinculados a um contrato com status de completude.</p>
                    <form method="GET" id="form-doc-contrato">
                        <div class="mb-12">
                            <select name="contrato_id" id="select-contrato-doc" class="form-select select2" data-placeholder="Selecione um contrato..." required>
                                <option value=""></option>
                                @foreach ($contratos as $c)
                                    <option value="{{ $c->id }}">{{ $c->numero }}/{{ $c->ano }} — {{ \Illuminate\Support\Str::limit($c->objeto, 50) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" id="btn-doc-contrato" class="btn btn-outline-primary-600 btn-sm d-flex align-items-center gap-4">
                            <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Gerar PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Card: Conformidade Documental --}}
        @if (auth()->user()->hasPermission('relatorio.gerar'))
        <div class="col-md-6 col-lg-4">
            <div class="card radius-8 border-0 h-100">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center gap-12 mb-16">
                        <div class="w-40-px h-40-px bg-success-100 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:shield-check-bold" class="text-success-600 text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0 text-sm">Conformidade Documental</h6>
                            <span class="text-neutral-500 text-xs">RN-225 — Integridade SHA-256</span>
                        </div>
                    </div>
                    <p class="text-neutral-500 text-sm mb-16">Verifica integridade de todos os documentos com hash SHA-256. Instrumento de defesa para auditoria externa.</p>
                    <a href="{{ route('tenant.relatorios.conformidade-documental') }}" class="btn btn-outline-success-600 btn-sm d-flex align-items-center gap-4" style="width: fit-content;">
                        <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Gerar PDF
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Card: Risco TCE (link existente) --}}
        @if (auth()->user()->hasPermission('painel-risco.exportar'))
        <div class="col-md-6 col-lg-4">
            <div class="card radius-8 border-0 h-100">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center gap-12 mb-16">
                        <div class="w-40-px h-40-px bg-danger-100 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:danger-triangle-bold" class="text-danger-600 text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0 text-sm">Relatorio de Risco — TCE</h6>
                            <span class="text-neutral-500 text-xs">RN-150 — Defesa Administrativa</span>
                        </div>
                    </div>
                    <p class="text-neutral-500 text-sm mb-16">Analise de risco contratual com score, categorias e plano de acao sugerido para o TCE.</p>
                    <a href="{{ route('tenant.painel-risco.exportar-tce') }}" class="btn btn-outline-danger-600 btn-sm d-flex align-items-center gap-4" style="width: fit-content;">
                        <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Gerar PDF
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- SECAO 2: Auditoria --}}
@if (auth()->user()->hasPermission('relatorio.gerar'))
<div class="mb-24">
    <h6 class="text-neutral-600 fw-semibold text-sm mb-16">
        <iconify-icon icon="solar:lock-password-bold" class="text-warning-600 me-4"></iconify-icon>
        Auditoria e Conformidade
    </h6>
    <div class="row g-16">
        <div class="col-md-6 col-lg-4">
            <div class="card radius-8 border-0 h-100">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center gap-12 mb-16">
                        <div class="w-40-px h-40-px bg-warning-100 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:clipboard-list-bold" class="text-warning-600 text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0 text-sm">Logs de Auditoria</h6>
                            <span class="text-neutral-500 text-xs">RN-222 — PDF ou CSV</span>
                        </div>
                    </div>
                    <p class="text-neutral-500 text-sm mb-16">Relatorio filtravel por periodo, tipo de acao, usuario e entidade. Exportavel em PDF e CSV.</p>
                    <a href="{{ route('tenant.relatorios.auditoria') }}" class="btn btn-outline-warning-600 btn-sm d-flex align-items-center gap-4" style="width: fit-content;">
                        <iconify-icon icon="solar:filter-bold" class="text-lg"></iconify-icon> Configurar Filtros
                    </a>
                </div>
            </div>
        </div>

        {{-- Card: Efetividade Mensal --}}
        <div class="col-md-6 col-lg-4">
            <div class="card radius-8 border-0 h-100">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center gap-12 mb-16">
                        <div class="w-40-px h-40-px bg-success-100 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:graph-up-bold" class="text-success-600 text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0 text-sm">Efetividade Mensal</h6>
                            <span class="text-neutral-500 text-xs">RN-057 — Alertas</span>
                        </div>
                    </div>
                    <p class="text-neutral-500 text-sm mb-16">Contratos regularizados a tempo vs. vencidos sem acao. Mede a efetividade do sistema de alertas.</p>
                    <a href="{{ route('tenant.relatorios.efetividade-mensal') }}" class="btn btn-outline-success-600 btn-sm d-flex align-items-center gap-4" style="width: fit-content;">
                        <iconify-icon icon="solar:chart-2-bold" class="text-lg"></iconify-icon> Gerar Relatorio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- SECAO 3: Transparencia LAI --}}
@if (auth()->user()->hasPermission('lai.relatorio'))
<div class="mb-24">
    <h6 class="text-neutral-600 fw-semibold text-sm mb-16">
        <iconify-icon icon="solar:eye-bold" class="text-success-600 me-4"></iconify-icon>
        Transparencia LAI — Lei 12.527/2011
    </h6>
    <div class="row g-16">
        <div class="col-md-6 col-lg-4">
            <div class="card radius-8 border-0 h-100">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center gap-12 mb-16">
                        <div class="w-40-px h-40-px bg-success-100 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:eye-bold" class="text-success-600 text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0 text-sm">Relatorio de Transparencia</h6>
                            <span class="text-neutral-500 text-xs">LAI 12.527/2011</span>
                        </div>
                    </div>
                    <p class="text-neutral-500 text-sm mb-16">Indicadores de publicacao no portal, classificacao de sigilo e status das solicitacoes SIC/e-SIC.</p>
                    <a href="{{ route('tenant.relatorios.lai') }}" class="btn btn-outline-success-600 btn-sm d-flex align-items-center gap-4" style="width: fit-content;">
                        <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Gerar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- SECAO 4: Exportar Listagens --}}
<div class="mb-24">
    <h6 class="text-neutral-600 fw-semibold text-sm mb-16">
        <iconify-icon icon="solar:chart-2-bold" class="text-info-600 me-4"></iconify-icon>
        Exportar Listagens (Excel)
    </h6>
    <div class="row g-16">
        @if (auth()->user()->hasPermission('contrato.visualizar'))
        <div class="col-md-6 col-lg-4">
            <div class="card radius-8 border-0 h-100">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center gap-12 mb-16">
                        <div class="w-40-px h-40-px bg-info-100 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:document-bold" class="text-info-600 text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0 text-sm">Contratos</h6>
                            <span class="text-neutral-500 text-xs">Listagem completa em .xlsx</span>
                        </div>
                    </div>
                    <p class="text-neutral-500 text-sm mb-16">Exporta todos os contratos com filtros aplicados da pagina de listagem.</p>
                    <a href="{{ route('tenant.exportar.contratos') }}" class="btn btn-outline-info-600 btn-sm d-flex align-items-center gap-4" style="width: fit-content;">
                        <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Exportar Excel
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if (auth()->user()->hasPermission('alerta.visualizar'))
        <div class="col-md-6 col-lg-4">
            <div class="card radius-8 border-0 h-100">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center gap-12 mb-16">
                        <div class="w-40-px h-40-px bg-info-100 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:bell-bold" class="text-info-600 text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0 text-sm">Alertas</h6>
                            <span class="text-neutral-500 text-xs">Listagem completa em .xlsx</span>
                        </div>
                    </div>
                    <p class="text-neutral-500 text-sm mb-16">Exporta alertas de vencimento nao-resolvidos com dados do contrato.</p>
                    <a href="{{ route('tenant.exportar.alertas') }}" class="btn btn-outline-info-600 btn-sm d-flex align-items-center gap-4" style="width: fit-content;">
                        <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Exportar Excel
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if (auth()->user()->hasPermission('fornecedor.visualizar'))
        <div class="col-md-6 col-lg-4">
            <div class="card radius-8 border-0 h-100">
                <div class="card-body p-20">
                    <div class="d-flex align-items-center gap-12 mb-16">
                        <div class="w-40-px h-40-px bg-info-100 rounded-circle d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:buildings-bold" class="text-info-600 text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-0 text-sm">Fornecedores</h6>
                            <span class="text-neutral-500 text-xs">Listagem completa em .xlsx</span>
                        </div>
                    </div>
                    <p class="text-neutral-500 text-sm mb-16">Exporta todos os fornecedores cadastrados com quantidade de contratos.</p>
                    <a href="{{ route('tenant.exportar.fornecedores') }}" class="btn btn-outline-info-600 btn-sm d-flex align-items-center gap-4" style="width: fit-content;">
                        <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Exportar Excel
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnDocContrato = document.getElementById('btn-doc-contrato');
        if (btnDocContrato) {
            btnDocContrato.addEventListener('click', function () {
                const select = document.getElementById('select-contrato-doc');
                const contratoId = select.value;
                if (!contratoId) {
                    alert('Selecione um contrato para gerar o relatorio.');
                    return;
                }
                const url = '{{ url("contratos") }}/' + contratoId + '/relatorio-documentos';
                window.location.href = url;
            });
        }
    });
</script>
@endpush
