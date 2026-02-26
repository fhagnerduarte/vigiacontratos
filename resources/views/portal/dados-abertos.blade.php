@extends('portal.layout')

@section('title', 'Dados Abertos')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('portal.index', $tenant->slug) }}">Início</a></li>
    <li class="breadcrumb-item active" aria-current="page">Dados Abertos</li>
@endsection

@section('content')
<h2 class="portal-section-title">Dados Abertos</h2>

{{-- Info Card --}}
<div class="portal-info-card">
    <iconify-icon icon="solar:info-circle-bold" width="22"></iconify-icon>
    <div>
        <p>Conforme a Lei de Acesso à Informação (Lei 12.527/2011), disponibilizamos os dados contratuais do município em formatos abertos para download, consulta e integração com outros sistemas.</p>
    </div>
</div>

{{-- Cards de Download --}}
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card portal-card h-100">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <iconify-icon icon="solar:code-bold" width="48" style="color: var(--portal-primary);"></iconify-icon>
                </div>
                <h5 class="fw-bold mb-2">JSON</h5>
                <p class="text-muted mb-3">Formato padrão para integração com sistemas e APIs. Ideal para desenvolvedores e aplicações automatizadas.</p>
                <a href="{{ route('portal.dados-abertos', [$tenant->slug, 'formato' => 'json']) }}" class="btn btn-primary px-4">
                    <iconify-icon icon="solar:download-bold" width="16"></iconify-icon> Download JSON
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card portal-card h-100">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <iconify-icon icon="solar:table-bold" width="48" style="color: #28a745;"></iconify-icon>
                </div>
                <h5 class="fw-bold mb-2">CSV</h5>
                <p class="text-muted mb-3">Formato compatível com planilhas (Excel, Google Sheets, LibreOffice). Ideal para análise de dados e relatórios.</p>
                <a href="{{ route('portal.dados-abertos', [$tenant->slug, 'formato' => 'csv']) }}" class="btn btn-success px-4">
                    <iconify-icon icon="solar:download-bold" width="16"></iconify-icon> Download CSV
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Sobre Dados Abertos --}}
<div class="card portal-card">
    <div class="card-header">
        <h5 class="mb-0"><iconify-icon icon="solar:question-circle-bold" width="18"></iconify-icon> O que são Dados Abertos?</h5>
    </div>
    <div class="card-body">
        <p>Dados abertos são informações públicas disponibilizadas em formatos que permitem leitura por máquinas, sem restrições de uso, redistribuição ou reaproveitamento.</p>
        <p>Os arquivos disponibilizados incluem informações sobre:</p>
        <ul>
            <li>Número, ano e objeto dos contratos</li>
            <li>Tipo, status e modalidade de contratação</li>
            <li>Dados do fornecedor (razão social e CNPJ)</li>
            <li>Secretaria responsável</li>
            <li>Valores (global e mensal)</li>
            <li>Datas de vigência, assinatura e publicação</li>
            <li>Número do processo e fonte de recurso</li>
        </ul>
        <p class="mb-0 text-muted">Referência legal: <strong>Lei 12.527/2011</strong> (Lei de Acesso à Informação) e <strong>Decreto 8.777/2016</strong> (Política de Dados Abertos).</p>
    </div>
</div>
@endsection
