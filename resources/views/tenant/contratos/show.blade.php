@extends('layout.layout')

@php
    $title = 'Contrato ' . $contrato->numero;
    $subTitle = 'Gestao Contratual';
@endphp

@section('title', 'Contrato ' . $contrato->numero)

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

{{-- Header --}}
<div class="card radius-8 border-0 mb-24">
    <div class="card-body p-24">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="fw-semibold mb-8">{{ $contrato->numero }}</h5>
                <p class="text-secondary-light mb-0">{{ $contrato->objeto }}</p>
            </div>
            <div class="d-flex gap-8 align-items-center flex-wrap">
                <span class="badge bg-{{ $contrato->status->cor() }}-focus text-{{ $contrato->status->cor() }}-main px-20 py-9 radius-4 text-sm">
                    {{ $contrato->status->label() }}
                </span>
                <span class="badge bg-{{ $contrato->nivel_risco->cor() }}-focus text-{{ $contrato->nivel_risco->cor() }}-main px-20 py-9 radius-4 text-sm">
                    Risco: {{ $contrato->nivel_risco->label() }} ({{ $contrato->score_risco }}pts)
                </span>
                @if ($contrato->dias_para_vencimento >= 0 && $contrato->status === \App\Enums\StatusContrato::Vigente)
                    <span class="badge bg-info-focus text-info-main px-16 py-9 radius-4 text-sm">
                        {{ $contrato->dias_para_vencimento }} dias restantes
                    </span>
                @endif
                @if (auth()->user()->hasPermission('contrato.editar') && $contrato->status !== \App\Enums\StatusContrato::Vencido)
                    <a href="{{ route('tenant.contratos.edit', $contrato) }}" class="btn btn-primary text-sm btn-sm px-12 py-8 radius-8">
                        <iconify-icon icon="lucide:edit" class="me-4"></iconify-icon> Editar
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Abas --}}
<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <ul class="nav nav-tabs bordered-tab" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-dados">Dados Gerais</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-fiscal">Fiscal</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-financeiro">Financeiro</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-documentos">Documentos</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-aditivos">Aditivos</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-auditoria">Auditoria</a></li>
        </ul>

        <div class="tab-content mt-24">

            {{-- ABA: Dados Gerais --}}
            <div class="tab-pane fade show active" id="tab-dados">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-secondary-light">Numero:</dt>
                            <dd class="col-sm-7 fw-medium">{{ $contrato->numero }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Tipo:</dt>
                            <dd class="col-sm-7">{{ $contrato->tipo->label() }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Modalidade:</dt>
                            <dd class="col-sm-7">{{ $contrato->modalidade_contratacao->label() }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Processo:</dt>
                            <dd class="col-sm-7">{{ $contrato->numero_processo }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Secretaria:</dt>
                            <dd class="col-sm-7">{{ $contrato->secretaria->nome ?? '-' }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Unid. Gestora:</dt>
                            <dd class="col-sm-7">{{ $contrato->unidade_gestora ?? '-' }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Fornecedor:</dt>
                            <dd class="col-sm-7">{{ $contrato->fornecedor->razao_social ?? '-' }} <br><code>{{ $contrato->fornecedor->cnpj ?? '' }}</code></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-secondary-light">Vigencia:</dt>
                            <dd class="col-sm-7">{{ $contrato->data_inicio->format('d/m/Y') }} a {{ $contrato->data_fim->format('d/m/Y') }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Prazo:</dt>
                            <dd class="col-sm-7">{{ $contrato->prazo_meses }} meses</dd>

                            <dt class="col-sm-5 text-secondary-light">Prorrogacao:</dt>
                            <dd class="col-sm-7">{{ $contrato->prorrogacao_automatica ? 'Sim' : 'Nao' }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Valor Global:</dt>
                            <dd class="col-sm-7 fw-semibold">R$ {{ number_format($contrato->valor_global, 2, ',', '.') }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Valor Mensal:</dt>
                            <dd class="col-sm-7">{{ $contrato->valor_mensal ? 'R$ ' . number_format($contrato->valor_mensal, 2, ',', '.') : '-' }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Categoria:</dt>
                            <dd class="col-sm-7">{{ $contrato->categoria?->label() ?? '-' }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Gestor:</dt>
                            <dd class="col-sm-7">{{ $contrato->gestor_nome ?? '-' }}</dd>

                            @if ($contrato->fundamento_legal)
                                <dt class="col-sm-5 text-secondary-light">Fund. Legal:</dt>
                                <dd class="col-sm-7">{{ $contrato->fundamento_legal }}</dd>
                            @endif

                            @if ($contrato->responsavel_tecnico)
                                <dt class="col-sm-5 text-secondary-light">Resp. Tecnico:</dt>
                                <dd class="col-sm-7">{{ $contrato->responsavel_tecnico }}</dd>
                            @endif
                        </dl>
                    </div>
                    @if ($contrato->observacoes)
                        <div class="col-12 mt-16">
                            <strong class="text-secondary-light">Observacoes:</strong>
                            <p class="mt-4">{{ $contrato->observacoes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ABA: Fiscal --}}
            <div class="tab-pane fade" id="tab-fiscal">
                {{-- Fiscal Atual --}}
                @if ($contrato->fiscalAtual)
                    <div class="alert alert-success-100 radius-8 mb-16">
                        <strong>Fiscal Atual:</strong> {{ $contrato->fiscalAtual->nome }}
                        — Mat: {{ $contrato->fiscalAtual->matricula }}
                        — {{ $contrato->fiscalAtual->cargo }}
                        @if ($contrato->fiscalAtual->email)
                            — {{ $contrato->fiscalAtual->email }}
                        @endif
                        <span class="text-secondary-light ms-8">(desde {{ $contrato->fiscalAtual->data_inicio->format('d/m/Y') }})</span>
                    </div>
                @else
                    <div class="alert alert-warning-100 radius-8 mb-16">
                        <strong>Sem fiscal designado.</strong> Todo contrato ativo deve ter um fiscal (RN-024).
                    </div>
                @endif

                {{-- Historico de Fiscais --}}
                @if ($contrato->fiscais->count() > 1)
                    <h6 class="fw-semibold mb-12">Historico de Fiscais</h6>
                    <table class="table table-hover mb-24">
                        <thead>
                            <tr>
                                <th class="px-16 py-12">Nome</th>
                                <th class="px-16 py-12">Matricula</th>
                                <th class="px-16 py-12">Cargo</th>
                                <th class="px-16 py-12">Periodo</th>
                                <th class="px-16 py-12 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contrato->fiscais as $fiscal)
                                <tr>
                                    <td class="px-16 py-12">{{ $fiscal->nome }}</td>
                                    <td class="px-16 py-12">{{ $fiscal->matricula }}</td>
                                    <td class="px-16 py-12">{{ $fiscal->cargo }}</td>
                                    <td class="px-16 py-12">
                                        {{ $fiscal->data_inicio->format('d/m/Y') }}
                                        {{ $fiscal->data_fim ? ' a ' . $fiscal->data_fim->format('d/m/Y') : ' — atual' }}
                                    </td>
                                    <td class="px-16 py-12 text-center">
                                        @if ($fiscal->is_atual)
                                            <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">Atual</span>
                                        @else
                                            <span class="badge bg-neutral-200 text-neutral-600 px-12 py-4 radius-4">Anterior</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- Formulario de Troca/Designacao de Fiscal --}}
                @if (auth()->user()->hasPermission('fiscal.criar') && $contrato->status !== \App\Enums\StatusContrato::Vencido)
                    <hr class="my-16">
                    <h6 class="fw-semibold mb-12">{{ $contrato->fiscalAtual ? 'Trocar Fiscal' : 'Designar Fiscal' }}</h6>
                    <form action="{{ route('tenant.contratos.fiscais.store', $contrato) }}" method="POST">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Nome <span class="text-danger-main">*</span></label>
                                <input type="text" name="nome" class="form-control radius-8" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Matricula <span class="text-danger-main">*</span></label>
                                <input type="text" name="matricula" class="form-control radius-8" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Cargo <span class="text-danger-main">*</span></label>
                                <input type="text" name="cargo" class="form-control radius-8" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">E-mail</label>
                                <input type="email" name="email" class="form-control radius-8">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">
                                    {{ $contrato->fiscalAtual ? 'Trocar Fiscal' : 'Designar Fiscal' }}
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            {{-- ABA: Financeiro --}}
            <div class="tab-pane fade" id="tab-financeiro">
                {{-- Percentual Executado --}}
                <div class="mb-24">
                    <div class="d-flex justify-content-between align-items-center mb-8">
                        <strong>Percentual Executado</strong>
                        <span class="fw-semibold {{ $contrato->percentual_executado > 100 ? 'text-danger-main' : 'text-primary-600' }}">
                            {{ number_format($contrato->percentual_executado, 2, ',', '.') }}%
                        </span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar {{ $contrato->percentual_executado > 100 ? 'bg-danger' : 'bg-primary-600' }}"
                             role="progressbar"
                             style="width: {{ min($contrato->percentual_executado, 100) }}%">
                        </div>
                    </div>
                    @if ($contrato->percentual_executado > 100)
                        <small class="text-danger-main mt-4 d-block">
                            Alerta: valor executado ultrapassou o valor contratado (RN-033).
                        </small>
                    @endif
                </div>

                {{-- Lista de Execucoes --}}
                <h6 class="fw-semibold mb-12">Execucoes Financeiras</h6>
                <table class="table table-hover mb-24">
                    <thead>
                        <tr>
                            <th class="px-16 py-12">Data</th>
                            <th class="px-16 py-12">Descricao</th>
                            <th class="px-16 py-12">Valor</th>
                            <th class="px-16 py-12">Nota Fiscal</th>
                            <th class="px-16 py-12">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contrato->execucoesFinanceiras as $exec)
                            <tr>
                                <td class="px-16 py-12">{{ $exec->data_execucao->format('d/m/Y') }}</td>
                                <td class="px-16 py-12">{{ $exec->descricao }}</td>
                                <td class="px-16 py-12">R$ {{ number_format($exec->valor, 2, ',', '.') }}</td>
                                <td class="px-16 py-12">{{ $exec->numero_nota_fiscal ?? '-' }}</td>
                                <td class="px-16 py-12">{{ $exec->registrador->nome ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary-light py-16">Nenhuma execucao financeira registrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Formulario de Nova Execucao --}}
                @if (auth()->user()->hasPermission('financeiro.registrar_empenho') && $contrato->status !== \App\Enums\StatusContrato::Vencido)
                    <hr class="my-16">
                    <h6 class="fw-semibold mb-12">Registrar Execucao</h6>
                    <form action="{{ route('tenant.contratos.execucoes.store', $contrato) }}" method="POST">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Descricao <span class="text-danger-main">*</span></label>
                                <input type="text" name="descricao" class="form-control radius-8" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Valor (R$) <span class="text-danger-main">*</span></label>
                                <input type="text" name="valor" class="form-control radius-8" data-mask="valor" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Data <span class="text-danger-main">*</span></label>
                                <input type="date" name="data_execucao" class="form-control radius-8" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Nota Fiscal</label>
                                <input type="text" name="numero_nota_fiscal" class="form-control radius-8">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Registrar Execucao</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            {{-- ABA: Documentos --}}
            <div class="tab-pane fade" id="tab-documentos">
                @if ($contrato->documentos->count() > 0)
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="px-16 py-12">Documento</th>
                                <th class="px-16 py-12">Tipo</th>
                                <th class="px-16 py-12">Tamanho</th>
                                <th class="px-16 py-12">Upload em</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contrato->documentos as $doc)
                                <tr>
                                    <td class="px-16 py-12">{{ $doc->nome_original }}</td>
                                    <td class="px-16 py-12">{{ $doc->tipo_documento->label() }}</td>
                                    <td class="px-16 py-12">{{ number_format($doc->tamanho_bytes / 1024, 0) }} KB</td>
                                    <td class="px-16 py-12">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center text-secondary-light py-24">
                        <iconify-icon icon="solar:folder-bold" class="text-4xl mb-8 d-block"></iconify-icon>
                        Nenhum documento anexado. A gestao de documentos sera expandida na Fase 3b.
                    </div>
                @endif
            </div>

            {{-- ABA: Aditivos --}}
            <div class="tab-pane fade" id="tab-aditivos">
                <div class="text-center text-secondary-light py-24">
                    <iconify-icon icon="solar:add-circle-bold" class="text-4xl mb-8 d-block"></iconify-icon>
                    Modulo de aditivos disponivel na Fase 3c.
                </div>
            </div>

            {{-- ABA: Auditoria --}}
            <div class="tab-pane fade" id="tab-auditoria">
                @if (auth()->user()->hasPermission('auditoria.visualizar'))
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="px-16 py-12">Data/Hora</th>
                                <th class="px-16 py-12">Usuario</th>
                                <th class="px-16 py-12">Perfil</th>
                                <th class="px-16 py-12">Campo</th>
                                <th class="px-16 py-12">Anterior</th>
                                <th class="px-16 py-12">Novo</th>
                                <th class="px-16 py-12">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contrato->historicoAlteracoes as $hist)
                                <tr>
                                    <td class="px-16 py-12 text-sm">{{ $hist->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="px-16 py-12 text-sm">{{ $hist->user->nome ?? '-' }}</td>
                                    <td class="px-16 py-12 text-sm">{{ $hist->role_nome }}</td>
                                    <td class="px-16 py-12 text-sm fw-medium">{{ $hist->campo_alterado }}</td>
                                    <td class="px-16 py-12 text-sm text-danger-main">{{ Str::limit($hist->valor_anterior ?? '—', 50) }}</td>
                                    <td class="px-16 py-12 text-sm text-success-main">{{ Str::limit($hist->valor_novo ?? '—', 50) }}</td>
                                    <td class="px-16 py-12 text-sm"><code>{{ $hist->ip_address }}</code></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary-light py-16">Nenhum registro de auditoria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <div class="text-center text-secondary-light py-24">
                        Voce nao possui permissao para visualizar o historico de auditoria.
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mascara monetaria para form de execucao financeira
    document.querySelectorAll('[data-mask="valor"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            var value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = value;
        });
    });

    // Converter valor antes do submit
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            form.querySelectorAll('[data-mask="valor"]').forEach(function(input) {
                var val = input.value.replace(/\./g, '').replace(',', '.');
                input.value = val;
            });
        });
    });
});
</script>
@endpush
