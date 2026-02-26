@extends('layout.layout')

@php
    $title = 'Contrato ' . $contrato->numero;
    $subTitle = 'Gestao Contratual';
@endphp

@section('title', 'Contrato ' . $contrato->numero)

@section('content')

{{-- Banner IRREGULAR (RN-046) --}}
@if ($contrato->is_irregular)
    <div class="alert alert-danger radius-8 mb-24 d-flex align-items-start gap-3" role="alert">
        <iconify-icon icon="solar:danger-triangle-bold" class="text-danger-main text-2xl mt-2 flex-shrink-0"></iconify-icon>
        <div>
            <h6 class="fw-semibold text-danger-main mb-4">Contrato IRREGULAR (RN-046)</h6>
            <p class="mb-4">Este contrato esta vencido e em situacao irregular. A edicao esta bloqueada (RN-006).</p>
            <p class="mb-0">Para regularizar: registre um <strong>aditivo com justificativa retroativa</strong> ou <strong>encerre formalmente</strong> o contrato.</p>
        </div>
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
                @if ($contrato->is_irregular)
                    <span class="badge bg-danger text-white px-20 py-9 radius-4 text-sm">
                        IRREGULAR
                    </span>
                @endif
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
                @if (auth()->user()->hasPermission('encerramento.visualizar') && in_array($contrato->status, [\App\Enums\StatusContrato::Vigente, \App\Enums\StatusContrato::Vencido]))
                    <a href="{{ route('tenant.contratos.encerramento.show', $contrato) }}" class="btn btn-outline-secondary text-sm btn-sm px-12 py-8 radius-8">
                        <iconify-icon icon="solar:lock-bold" class="me-4"></iconify-icon> Encerrar Contrato
                    </a>
                @endif
                @if ($contrato->status === \App\Enums\StatusContrato::Encerrado && $contrato->encerramento)
                    <a href="{{ route('tenant.contratos.encerramento.show', $contrato) }}" class="btn btn-outline-info text-sm btn-sm px-12 py-8 radius-8">
                        <iconify-icon icon="solar:eye-bold" class="me-4"></iconify-icon> Ver Encerramento
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
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-ocorrencias">Ocorrencias @if($resumoOcorrencias['pendentes'] > 0)<span class="badge bg-warning-focus text-warning-main ms-4">{{ $resumoOcorrencias['pendentes'] }}</span>@endif</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-relatorios-fiscais">Rel. Fiscais</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-conformidade">Conformidade</a></li>
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
                            <dd class="col-sm-7">
                                @if ($contrato->gestor)
                                    {{ $contrato->gestor->nome }} — Mat: {{ $contrato->gestor->matricula }}
                                    <br><small class="text-secondary-light">{{ $contrato->gestor->cargo }}</small>
                                @else
                                    {{ $contrato->gestor_nome ?? '-' }}
                                @endif
                            </dd>

                            @if ($contrato->data_assinatura)
                                <dt class="col-sm-5 text-secondary-light">Assinatura:</dt>
                                <dd class="col-sm-7">{{ $contrato->data_assinatura->format('d/m/Y') }}</dd>
                            @endif

                            @if ($contrato->regime_execucao)
                                <dt class="col-sm-5 text-secondary-light">Regime Exec.:</dt>
                                <dd class="col-sm-7">{{ match($contrato->regime_execucao) {
                                    'empreitada_integral'  => 'Empreitada Integral',
                                    'preco_unitario'       => 'Preco Unitario',
                                    'preco_global'         => 'Preco Global',
                                    'tarefa'               => 'Tarefa',
                                    'contratacao_integrada'=> 'Contratacao Integrada',
                                    default                => $contrato->regime_execucao,
                                } }}</dd>
                            @endif

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
                    @if ($contrato->condicoes_pagamento)
                        <div class="col-12 mt-8">
                            <strong class="text-secondary-light">Condicoes de Pagamento:</strong>
                            <p class="mt-4">{{ $contrato->condicoes_pagamento }}</p>
                        </div>
                    @endif
                    @if ($contrato->garantias)
                        <div class="col-12 mt-8">
                            <strong class="text-secondary-light">Garantias:</strong>
                            <p class="mt-4">{{ $contrato->garantias }}</p>
                        </div>
                    @endif
                </div>

                {{-- Publicacao --}}
                <div class="row mt-24 pt-16 border-top">
                    <div class="col-12 mb-12 d-flex align-items-center gap-12">
                        <h6 class="fw-semibold mb-0">Publicacao</h6>
                        @if ($contrato->data_publicacao)
                            <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">
                                <iconify-icon icon="ic:baseline-check-circle" class="me-4"></iconify-icon> Publicado
                            </span>
                        @else
                            <span class="badge bg-danger-focus text-danger-main px-12 py-4 radius-4">
                                <iconify-icon icon="ic:baseline-cancel" class="me-4"></iconify-icon> Nao publicado
                            </span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-secondary-light">Data Publicacao:</dt>
                            <dd class="col-sm-7">{{ $contrato->data_publicacao ? $contrato->data_publicacao->format('d/m/Y') : '-' }}</dd>

                            <dt class="col-sm-5 text-secondary-light">Veiculo:</dt>
                            <dd class="col-sm-7">{{ $contrato->veiculo_publicacao ?? '-' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-secondary-light">Link Transparencia:</dt>
                            <dd class="col-sm-7">
                                @if ($contrato->link_transparencia)
                                    <a href="{{ $contrato->link_transparencia }}" target="_blank" rel="noopener noreferrer" class="text-primary-600">
                                        <iconify-icon icon="lucide:external-link" class="me-4"></iconify-icon> Acessar portal
                                    </a>
                                @else
                                    -
                                @endif
                            </dd>
                        </dl>
                    </div>

                    {{-- Classificacao e Portal --}}
                    <div class="col-md-6 mt-16">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-secondary-light">Classificacao:</dt>
                            <dd class="col-sm-7">
                                @if ($contrato->classificacao_sigilo)
                                    <span class="badge bg-{{ $contrato->classificacao_sigilo->cor() }}-focus text-{{ $contrato->classificacao_sigilo->cor() }}-main px-12 py-4 radius-4">
                                        <iconify-icon icon="{{ $contrato->classificacao_sigilo->icone() }}" class="me-4"></iconify-icon>
                                        {{ $contrato->classificacao_sigilo->label() }}
                                    </span>
                                @else
                                    -
                                @endif
                            </dd>

                            <dt class="col-sm-5 text-secondary-light">Portal Publico:</dt>
                            <dd class="col-sm-7">
                                @if ($contrato->publicado_portal)
                                    <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">
                                        <iconify-icon icon="ic:baseline-check-circle" class="me-4"></iconify-icon> Visivel no Portal
                                    </span>
                                @else
                                    <span class="badge bg-neutral-200 text-secondary-light px-12 py-4 radius-4">
                                        <iconify-icon icon="ic:baseline-cancel" class="me-4"></iconify-icon> Nao publicado no Portal
                                    </span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                    @if ($contrato->justificativa_sigilo)
                        <div class="col-md-6 mt-16">
                            <dl class="row mb-0">
                                <dt class="col-sm-5 text-secondary-light">Justificativa Sigilo:</dt>
                                <dd class="col-sm-7">{{ $contrato->justificativa_sigilo }}</dd>
                            </dl>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ABA: Fiscal --}}
            <div class="tab-pane fade" id="tab-fiscal">
                {{-- Fiscal Atual --}}
                @if ($contrato->fiscalAtual)
                    <div class="alert alert-success-100 radius-8 mb-16">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                            <div>
                                <strong>Fiscal Titular:</strong> {{ $contrato->fiscalAtual->nome }}
                                — Mat: {{ $contrato->fiscalAtual->matricula }}
                                — {{ $contrato->fiscalAtual->cargo }}
                                @if ($contrato->fiscalAtual->email)
                                    — {{ $contrato->fiscalAtual->email }}
                                @endif
                                <span class="text-secondary-light ms-8">(desde {{ $contrato->fiscalAtual->data_inicio->format('d/m/Y') }})</span>
                                @if ($contrato->fiscalAtual->portaria_designacao ?? $contrato->portaria_designacao ?? false)
                                    <br><small class="text-secondary-light">Portaria: {{ $contrato->fiscalAtual->portaria_designacao ?? $contrato->portaria_designacao }}</small>
                                @endif
                                @if ($contrato->fiscalAtual->data_ultimo_relatorio ?? false)
                                    <br><small class="text-secondary-light">Ultimo relatorio: {{ $contrato->fiscalAtual->data_ultimo_relatorio->format('d/m/Y') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if ($contrato->fiscalSubstitutoAtual ?? $contrato->fiscalSubstituto ?? false)
                        @php $substituto = $contrato->fiscalSubstitutoAtual ?? $contrato->fiscalSubstituto; @endphp
                        <div class="alert alert-info-100 radius-8 mb-16">
                            <strong>Fiscal Substituto:</strong> {{ $substituto->nome }}
                            — Mat: {{ $substituto->matricula }}
                            — {{ $substituto->cargo }}
                            @if ($substituto->email)
                                — {{ $substituto->email }}
                            @endif
                        </div>
                    @endif
                @else
                    <div class="alert alert-warning-100 radius-8 mb-16">
                        <strong>Sem fiscal designado.</strong> Todo contrato ativo deve ter um fiscal (RN-024).
                    </div>
                @endif

                {{-- Portaria e Ultimo Relatorio (campos do contrato) --}}
                @if ($contrato->portaria_designacao || $contrato->data_ultimo_relatorio ?? false)
                    <div class="row gy-2 mb-16">
                        @if ($contrato->portaria_designacao)
                            <div class="col-md-6">
                                <div class="p-12 border rounded">
                                    <span class="text-secondary-light text-sm">Portaria de Designacao:</span>
                                    <p class="fw-medium mb-0 mt-4">{{ $contrato->portaria_designacao }}</p>
                                </div>
                            </div>
                        @endif
                        @if ($contrato->data_ultimo_relatorio ?? false)
                            <div class="col-md-6">
                                <div class="p-12 border rounded">
                                    <span class="text-secondary-light text-sm">Data do Ultimo Relatorio:</span>
                                    <p class="fw-medium mb-0 mt-4">{{ $contrato->data_ultimo_relatorio->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endif
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
                            <div class="col-md-8">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Servidor <span class="text-danger-main">*</span></label>
                                <select name="servidor_id"
                                        class="form-control radius-8 form-select select2 @error('servidor_id') is-invalid @enderror"
                                        data-placeholder="Selecione o servidor fiscal..." required>
                                    <option value=""></option>
                                    @foreach ($servidores as $serv)
                                        <option value="{{ $serv->id }}">
                                            {{ $serv->nome }} — Mat: {{ $serv->matricula }} — {{ $serv->cargo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('servidor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 d-flex align-items-end gap-2">
                                <a href="{{ route('tenant.servidores.create') }}" target="_blank"
                                   class="btn btn-outline-primary text-sm btn-sm px-16 py-10 radius-8">
                                    <iconify-icon icon="lucide:plus" class="me-4"></iconify-icon> Novo servidor
                                </a>
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
                {{-- Resumo Financeiro (IMP-053) --}}
                <div class="row gy-3 mb-24">
                    <div class="col-md-3">
                        <div class="card border radius-8 p-16 text-center">
                            <small class="text-secondary-light">Valor Global</small>
                            <h6 class="fw-bold text-primary-600 mt-4">R$ {{ number_format($contrato->valor_global, 2, ',', '.') }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border radius-8 p-16 text-center">
                            <small class="text-secondary-light">Valor Empenhado</small>
                            <h6 class="fw-bold {{ ($contrato->valor_empenhado ?? 0) > 0 ? 'text-info-main' : 'text-secondary-light' }} mt-4">
                                @if ($contrato->valor_empenhado)
                                    R$ {{ number_format($contrato->valor_empenhado, 2, ',', '.') }}
                                @else
                                    Nao informado
                                @endif
                            </h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border radius-8 p-16 text-center">
                            <small class="text-secondary-light">Saldo Contratual</small>
                            <h6 class="fw-bold {{ ($contrato->saldo_contratual ?? 0) < 0 ? 'text-danger-main' : 'text-success-main' }} mt-4">
                                @if ($contrato->saldo_contratual !== null)
                                    R$ {{ number_format($contrato->saldo_contratual, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border radius-8 p-16 text-center">
                            <small class="text-secondary-light">Executado</small>
                            <h6 class="fw-bold {{ $contrato->percentual_executado > 100 ? 'text-danger-main' : 'text-primary-600' }} mt-4">
                                {{ number_format($contrato->percentual_executado, 2, ',', '.') }}%
                            </h6>
                        </div>
                    </div>
                </div>

                {{-- Barra de progresso --}}
                <div class="mb-24">
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
                    @if ($contrato->valor_empenhado && $contrato->saldo_contratual !== null && $contrato->saldo_contratual < 0)
                        <small class="text-danger-main mt-4 d-block">
                            Alerta: empenho insuficiente — pagamentos excedem o valor empenhado.
                        </small>
                    @endif
                </div>

                {{-- Lista de Execucoes --}}
                <h6 class="fw-semibold mb-12">Execucoes Financeiras</h6>
                <table class="table table-hover mb-24">
                    <thead>
                        <tr>
                            <th class="px-16 py-12">Data</th>
                            <th class="px-16 py-12">Tipo</th>
                            <th class="px-16 py-12">Descricao</th>
                            <th class="px-16 py-12">Valor</th>
                            <th class="px-16 py-12">Nota Fiscal</th>
                            <th class="px-16 py-12">Competencia</th>
                            <th class="px-16 py-12">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contrato->execucoesFinanceiras as $exec)
                            <tr>
                                <td class="px-16 py-12">{{ $exec->data_execucao->format('d/m/Y') }}</td>
                                <td class="px-16 py-12">
                                    <span class="badge bg-{{ $exec->tipo_execucao?->cor() ?? 'primary' }}-focus text-{{ $exec->tipo_execucao?->cor() ?? 'primary' }}-main px-12 py-6 radius-4 fw-medium text-xs">
                                        {{ $exec->tipo_execucao?->label() ?? 'Pagamento' }}
                                    </span>
                                </td>
                                <td class="px-16 py-12">{{ $exec->descricao }}</td>
                                <td class="px-16 py-12">R$ {{ number_format($exec->valor, 2, ',', '.') }}</td>
                                <td class="px-16 py-12">{{ $exec->numero_nota_fiscal ?? '-' }}</td>
                                <td class="px-16 py-12">{{ $exec->competencia ?? '-' }}</td>
                                <td class="px-16 py-12">{{ $exec->registrador->nome ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary-light py-16">Nenhuma execucao financeira registrada.</td>
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
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Tipo</label>
                                <select name="tipo_execucao" class="form-select select2 radius-8" data-placeholder="Selecione o tipo">
                                    @foreach (\App\Enums\TipoExecucaoFinanceira::cases() as $tipo)
                                        <option value="{{ $tipo->value }}" {{ $tipo === \App\Enums\TipoExecucaoFinanceira::Pagamento ? 'selected' : '' }}>
                                            {{ $tipo->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Nota Fiscal</label>
                                <input type="text" name="numero_nota_fiscal" class="form-control radius-8">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">N. Empenho</label>
                                <input type="text" name="numero_empenho" class="form-control radius-8">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Competencia</label>
                                <input type="month" name="competencia" class="form-control radius-8">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8 w-100">Registrar</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            {{-- ABA: Documentos (Expandida — Modulo 5) --}}
            <div class="tab-pane fade" id="tab-documentos">

                {{-- Barra de Completude Documental (RN-128) --}}
                @php $completude = $contrato->status_completude; @endphp
                <div class="d-flex align-items-center justify-content-between mb-20 p-16 border rounded
                    bg-{{ $completude->cor() }}-focus">
                    <div class="d-flex align-items-center gap-12">
                        <iconify-icon icon="{{ $completude->icone() }}" class="text-{{ $completude->cor() }}-main text-2xl"></iconify-icon>
                        <span class="fw-semibold text-{{ $completude->cor() }}-main">{{ $completude->descricao() }}</span>
                    </div>
                    <div class="d-flex gap-8">
                        @if (auth()->user()->hasPermission('documento.download'))
                            <a href="{{ route('tenant.relatorios.documentos-contrato', $contrato) }}" class="btn btn-outline-success-600 btn-sm radius-8 d-flex align-items-center gap-4">
                                <iconify-icon icon="solar:file-download-bold" class="icon"></iconify-icon> Exportar PDF
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('documento.criar') && $contrato->status !== \App\Enums\StatusContrato::Vencido)
                            <button class="btn btn-primary-600 btn-sm radius-8" data-bs-toggle="modal" data-bs-target="#modalUploadDocumento">
                                <iconify-icon icon="solar:upload-bold" class="icon"></iconify-icon> Adicionar Documento
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Checklist de Documentos Obrigatorios (RN-129) --}}
                <div class="mb-24">
                    <h6 class="fw-semibold mb-12">Checklist de Documentos Obrigatorios</h6>
                    <div class="row gy-2">
                        @foreach ($checklistObrigatorio as $item)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-8 p-12 border rounded">
                                    @if ($item['presente'])
                                        <iconify-icon icon="ic:baseline-check-circle" class="text-success-main text-xl flex-shrink-0"></iconify-icon>
                                    @else
                                        <iconify-icon icon="ic:baseline-cancel" class="text-danger-main text-xl flex-shrink-0"></iconify-icon>
                                    @endif
                                    <span class="text-sm {{ $item['presente'] ? 'text-neutral-700' : 'text-danger-main fw-medium' }}">
                                        {{ $item['label'] }}
                                    </span>
                                    @if ($item['presente'])
                                        <span class="badge bg-neutral-100 text-neutral-600 ms-auto text-xs">v{{ $item['versao'] }}</span>
                                    @else
                                        <span class="badge bg-danger-focus text-danger-main ms-auto text-xs">Pendente</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Documentos Agrupados por Tipo --}}
                @if ($documentosPorTipo->count() > 0)
                    @foreach ($documentosPorTipo as $tipoLabel => $docs)
                        <div class="mb-20">
                            <h6 class="fw-semibold text-neutral-700 mb-12 d-flex align-items-center gap-8">
                                <iconify-icon icon="solar:folder-bold" class="text-primary-600"></iconify-icon>
                                {{ $tipoLabel }}
                                <span class="badge bg-neutral-200 text-neutral-600 ms-2">{{ $docs->count() }}</span>
                            </h6>
                            @foreach ($docs as $doc)
                                <div class="d-flex align-items-center gap-12 p-12 border rounded mb-8
                                    {{ $doc->is_versao_atual ? '' : 'opacity-75 bg-neutral-50' }}">
                                    <iconify-icon icon="solar:file-bold" class="text-primary-600 text-xl flex-shrink-0"></iconify-icon>
                                    <div class="flex-grow-1">
                                        <p class="fw-medium mb-0 text-sm">
                                            {{ $doc->nome_original }}
                                            @if ($doc->integridade_comprometida)
                                                <span class="badge bg-danger-focus text-danger-main px-8 py-4 radius-4 text-xs ms-4" title="Integridade comprometida — download bloqueado">
                                                    <iconify-icon icon="solar:danger-triangle-bold"></iconify-icon> Comprometido
                                                </span>
                                            @endif
                                        </p>
                                        <p class="text-neutral-400 text-xs mb-0">
                                            v{{ $doc->versao }}
                                            — {{ number_format($doc->tamanho / 1024 / 1024, 2) }} MB
                                            — {{ $doc->created_at->format('d/m/Y H:i') }}
                                            — por {{ $doc->uploader->nome ?? '-' }}
                                            @if (!$doc->is_versao_atual)
                                                <span class="badge bg-neutral-200 text-neutral-600 ms-2">Versao anterior</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="d-flex gap-8 flex-shrink-0">
                                        @if (auth()->user()->hasPermission('documento.visualizar'))
                                            <a href="{{ route('tenant.documentos.download', $doc) }}"
                                               class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                               title="Download">
                                                <iconify-icon icon="solar:download-bold"></iconify-icon>
                                            </a>
                                        @endif
                                        @if (auth()->user()->hasPermission('auditoria.verificar_integridade'))
                                            <form action="{{ route('tenant.documentos.verificar-integridade', $doc) }}" method="POST" class="d-inline"
                                                  data-confirm="Verificar integridade deste documento?">
                                                @csrf
                                                <button type="submit"
                                                   class="w-32-px h-32-px bg-warning-focus text-warning-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                                   title="Verificar integridade">
                                                    <iconify-icon icon="solar:shield-check-bold"></iconify-icon>
                                                </button>
                                            </form>
                                        @endif
                                        @if (auth()->user()->hasPermission('documento.excluir') && $doc->is_versao_atual)
                                            <form action="{{ route('tenant.documentos.destroy', $doc) }}" method="POST" class="d-inline"
                                                  data-confirm="Excluir este documento?"
                                                  data-confirm-title="Excluir documento">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                   class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                                   title="Excluir">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-secondary-light py-24">
                        <iconify-icon icon="solar:folder-bold" class="text-4xl mb-8 d-block"></iconify-icon>
                        Nenhum documento anexado a este contrato.
                    </div>
                @endif
            </div>

            {{-- Modal de Upload de Documento --}}
            <div class="modal fade" id="modalUploadDocumento" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Adicionar Documento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('tenant.contratos.documentos.store', $contrato) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-16">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Tipo de Documento <span class="text-danger-main">*</span></label>
                                    <select class="form-select radius-8 select2" name="tipo_documento" required>
                                        <option value="">Selecione o tipo...</option>
                                        @foreach ($tiposDocumento as $tipo)
                                            <option value="{{ $tipo->value }}">{{ $tipo->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-16">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Arquivo PDF <span class="text-danger-main">*</span></label>
                                    <input type="file" class="form-control radius-8" name="arquivo" accept=".pdf" required>
                                    <small class="text-neutral-400">Apenas PDF. Tamanho maximo: 20MB</small>
                                </div>
                                <div class="mb-16">
                                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Descricao</label>
                                    <input type="text" class="form-control radius-8" name="descricao" placeholder="Descricao opcional">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary-600 radius-8" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary-600 radius-8">
                                    <iconify-icon icon="solar:upload-bold" class="icon"></iconify-icon> Enviar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ABA: Ocorrencias (IMP-054) --}}
            <div class="tab-pane fade" id="tab-ocorrencias">
                {{-- Resumo --}}
                <div class="row gy-3 mb-24">
                    <div class="col-md-3">
                        <div class="p-16 border rounded text-center">
                            <span class="text-secondary-light text-sm">Total</span>
                            <h4 class="fw-bold mt-4 mb-0">{{ $resumoOcorrencias['total'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-16 border rounded text-center bg-warning-focus">
                            <span class="text-secondary-light text-sm">Pendentes</span>
                            <h4 class="fw-bold mt-4 mb-0 text-warning-main">{{ $resumoOcorrencias['pendentes'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-16 border rounded text-center bg-success-focus">
                            <span class="text-secondary-light text-sm">Resolvidas</span>
                            <h4 class="fw-bold mt-4 mb-0 text-success-main">{{ $resumoOcorrencias['resolvidas'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-16 border rounded text-center bg-danger-focus">
                            <span class="text-secondary-light text-sm">Vencidas</span>
                            <h4 class="fw-bold mt-4 mb-0 text-danger-main">{{ $resumoOcorrencias['vencidas'] }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Tabela de Ocorrencias --}}
                <table class="table table-hover mb-24">
                    <thead>
                        <tr>
                            <th class="px-16 py-12">Data</th>
                            <th class="px-16 py-12">Tipo</th>
                            <th class="px-16 py-12">Descricao</th>
                            <th class="px-16 py-12">Prazo</th>
                            <th class="px-16 py-12 text-center">Status</th>
                            <th class="px-16 py-12">Registrado por</th>
                            @if (auth()->user()->hasPermission('ocorrencia.resolver'))
                                <th class="px-16 py-12 text-center">Acao</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contrato->ocorrencias as $ocorrencia)
                            <tr>
                                <td class="px-16 py-12 text-sm">{{ $ocorrencia->data_ocorrencia->format('d/m/Y') }}</td>
                                <td class="px-16 py-12">
                                    <span class="badge bg-{{ $ocorrencia->tipo_ocorrencia->cor() }}-focus text-{{ $ocorrencia->tipo_ocorrencia->cor() }}-main px-12 py-4 radius-4">
                                        <iconify-icon icon="{{ $ocorrencia->tipo_ocorrencia->icone() }}" class="me-4"></iconify-icon>
                                        {{ $ocorrencia->tipo_ocorrencia->label() }}
                                    </span>
                                </td>
                                <td class="px-16 py-12 text-sm">{{ Str::limit($ocorrencia->descricao, 80) }}</td>
                                <td class="px-16 py-12 text-sm">
                                    @if ($ocorrencia->prazo_providencia)
                                        <span class="{{ !$ocorrencia->resolvida && $ocorrencia->prazo_providencia->lt(now()) ? 'text-danger-main fw-bold' : '' }}">
                                            {{ $ocorrencia->prazo_providencia->format('d/m/Y') }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-16 py-12 text-center">
                                    @if ($ocorrencia->resolvida)
                                        <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">Resolvida</span>
                                        <br><small class="text-secondary-light">{{ $ocorrencia->resolvida_em?->format('d/m/Y') }} por {{ $ocorrencia->resolvidaPor?->nome ?? '-' }}</small>
                                    @elseif ($ocorrencia->prazo_providencia && $ocorrencia->prazo_providencia->lt(now()))
                                        <span class="badge bg-danger-focus text-danger-main px-12 py-4 radius-4">Vencida</span>
                                    @else
                                        <span class="badge bg-warning-focus text-warning-main px-12 py-4 radius-4">Pendente</span>
                                    @endif
                                </td>
                                <td class="px-16 py-12 text-sm">{{ $ocorrencia->registradoPor?->nome ?? '-' }}</td>
                                @if (auth()->user()->hasPermission('ocorrencia.resolver'))
                                    <td class="px-16 py-12 text-center">
                                        @if (!$ocorrencia->resolvida)
                                            <form action="{{ route('tenant.ocorrencias.resolver', $ocorrencia) }}" method="POST" class="d-inline"
                                                  data-confirm="Confirma a resolucao desta ocorrencia?">
                                                @csrf
                                                <button type="submit" class="btn btn-success-600 text-sm btn-sm px-12 py-6 radius-4">
                                                    <iconify-icon icon="solar:check-circle-bold" class="me-4"></iconify-icon> Resolver
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary-light py-16">Nenhuma ocorrencia registrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Formulario de Nova Ocorrencia --}}
                @if (auth()->user()->hasPermission('ocorrencia.criar') && $contrato->status !== \App\Enums\StatusContrato::Encerrado)
                    <hr class="my-16">
                    <h6 class="fw-semibold mb-12">Registrar Ocorrencia</h6>
                    <form action="{{ route('tenant.contratos.ocorrencias.store', $contrato) }}" method="POST">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Tipo <span class="text-danger-main">*</span></label>
                                <select name="tipo_ocorrencia" class="form-control radius-8 form-select select2 @error('tipo_ocorrencia') is-invalid @enderror"
                                        data-placeholder="Selecione o tipo..." required>
                                    <option value=""></option>
                                    @foreach (\App\Enums\TipoOcorrencia::cases() as $tipo)
                                        <option value="{{ $tipo->value }}">{{ $tipo->label() }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_ocorrencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Data da Ocorrencia <span class="text-danger-main">*</span></label>
                                <input type="date" name="data_ocorrencia" value="{{ old('data_ocorrencia', date('Y-m-d')) }}"
                                       class="form-control radius-8 @error('data_ocorrencia') is-invalid @enderror" required>
                                @error('data_ocorrencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Prazo para Providencia</label>
                                <input type="date" name="prazo_providencia" value="{{ old('prazo_providencia') }}"
                                       class="form-control radius-8 @error('prazo_providencia') is-invalid @enderror">
                                @error('prazo_providencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Descricao <span class="text-danger-main">*</span></label>
                                <textarea name="descricao" rows="3" class="form-control radius-8 @error('descricao') is-invalid @enderror"
                                          placeholder="Descreva a ocorrencia detalhadamente (min. 10 caracteres)..." required>{{ old('descricao') }}</textarea>
                                @error('descricao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Providencia Recomendada</label>
                                <textarea name="providencia" rows="2" class="form-control radius-8 @error('providencia') is-invalid @enderror"
                                          placeholder="Providencia a ser tomada...">{{ old('providencia') }}</textarea>
                                @error('providencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">
                                    <iconify-icon icon="solar:add-circle-bold" class="me-4"></iconify-icon> Registrar Ocorrencia
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            {{-- ABA: Relatorios Fiscais (IMP-054) --}}
            <div class="tab-pane fade" id="tab-relatorios-fiscais">
                {{-- Resumo --}}
                <div class="row gy-3 mb-24">
                    <div class="col-md-3">
                        <div class="p-16 border rounded text-center">
                            <span class="text-secondary-light text-sm">Total Relatorios</span>
                            <h4 class="fw-bold mt-4 mb-0">{{ $resumoRelatoriosFiscais['total'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-16 border rounded text-center bg-success-focus">
                            <span class="text-secondary-light text-sm">Conformes</span>
                            <h4 class="fw-bold mt-4 mb-0 text-success-main">{{ $resumoRelatoriosFiscais['conformes'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-16 border rounded text-center bg-danger-focus">
                            <span class="text-secondary-light text-sm">Nao Conformes</span>
                            <h4 class="fw-bold mt-4 mb-0 text-danger-main">{{ $resumoRelatoriosFiscais['nao_conformes'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-16 border rounded text-center bg-primary-focus">
                            <span class="text-secondary-light text-sm">Nota Media</span>
                            <h4 class="fw-bold mt-4 mb-0 text-primary-main">{{ $resumoRelatoriosFiscais['nota_media'] ?? '—' }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Tabela de Relatorios Fiscais --}}
                <table class="table table-hover mb-24">
                    <thead>
                        <tr>
                            <th class="px-16 py-12">Periodo</th>
                            <th class="px-16 py-12">Fiscal</th>
                            <th class="px-16 py-12">Atividades</th>
                            <th class="px-16 py-12 text-center">Conformidade</th>
                            <th class="px-16 py-12 text-center">Nota</th>
                            <th class="px-16 py-12 text-center">Ocorrencias</th>
                            <th class="px-16 py-12">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contrato->relatoriosFiscais as $relatorio)
                            <tr>
                                <td class="px-16 py-12 text-sm">
                                    {{ $relatorio->periodo_inicio->format('d/m/Y') }} a {{ $relatorio->periodo_fim->format('d/m/Y') }}
                                </td>
                                <td class="px-16 py-12 text-sm">{{ $relatorio->fiscal?->nome ?? '-' }}</td>
                                <td class="px-16 py-12 text-sm">{{ Str::limit($relatorio->descricao_atividades, 80) }}</td>
                                <td class="px-16 py-12 text-center">
                                    @if ($relatorio->conformidade_geral)
                                        <span class="badge bg-success-focus text-success-main px-12 py-4 radius-4">Conforme</span>
                                    @else
                                        <span class="badge bg-danger-focus text-danger-main px-12 py-4 radius-4">Nao Conforme</span>
                                    @endif
                                </td>
                                <td class="px-16 py-12 text-center fw-bold">
                                    @if ($relatorio->nota_desempenho)
                                        <span class="text-{{ $relatorio->nota_desempenho >= 7 ? 'success' : ($relatorio->nota_desempenho >= 5 ? 'warning' : 'danger') }}-main">
                                            {{ $relatorio->nota_desempenho }}/10
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-16 py-12 text-center">{{ $relatorio->ocorrencias_no_periodo }}</td>
                                <td class="px-16 py-12 text-sm">{{ $relatorio->registradoPor?->nome ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary-light py-16">Nenhum relatorio fiscal registrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Formulario de Novo Relatorio Fiscal --}}
                @if (auth()->user()->hasPermission('relatorio_fiscal.criar') && $contrato->status !== \App\Enums\StatusContrato::Encerrado)
                    <hr class="my-16">
                    <h6 class="fw-semibold mb-12">Registrar Relatorio Fiscal</h6>
                    <form action="{{ route('tenant.contratos.relatorios-fiscais.store', $contrato) }}" method="POST">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Periodo Inicio <span class="text-danger-main">*</span></label>
                                <input type="date" name="periodo_inicio" value="{{ old('periodo_inicio') }}"
                                       class="form-control radius-8 @error('periodo_inicio') is-invalid @enderror" required>
                                @error('periodo_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Periodo Fim <span class="text-danger-main">*</span></label>
                                <input type="date" name="periodo_fim" value="{{ old('periodo_fim') }}"
                                       class="form-control radius-8 @error('periodo_fim') is-invalid @enderror" required>
                                @error('periodo_fim') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Nota de Desempenho (1-10)</label>
                                <input type="number" name="nota_desempenho" value="{{ old('nota_desempenho') }}" min="1" max="10"
                                       class="form-control radius-8 @error('nota_desempenho') is-invalid @enderror" placeholder="1 a 10">
                                @error('nota_desempenho') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Descricao das Atividades <span class="text-danger-main">*</span></label>
                                <textarea name="descricao_atividades" rows="3" class="form-control radius-8 @error('descricao_atividades') is-invalid @enderror"
                                          placeholder="Descreva as atividades fiscalizadas no periodo (min. 10 caracteres)..." required>{{ old('descricao_atividades') }}</textarea>
                                @error('descricao_atividades') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Conformidade Geral <span class="text-danger-main">*</span></label>
                                <select name="conformidade_geral" class="form-control radius-8 form-select select2 @error('conformidade_geral') is-invalid @enderror"
                                        data-placeholder="Conforme?" required>
                                    <option value=""></option>
                                    <option value="1" {{ old('conformidade_geral') == '1' ? 'selected' : '' }}>Conforme</option>
                                    <option value="0" {{ old('conformidade_geral') == '0' ? 'selected' : '' }}>Nao Conforme</option>
                                </select>
                                @error('conformidade_geral') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Observacoes</label>
                                <textarea name="observacoes" rows="2" class="form-control radius-8 @error('observacoes') is-invalid @enderror"
                                          placeholder="Observacoes adicionais...">{{ old('observacoes') }}</textarea>
                                @error('observacoes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">
                                    <iconify-icon icon="solar:document-add-bold" class="me-4"></iconify-icon> Registrar Relatorio
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            {{-- ABA: Conformidade por Fase (IMP-050) --}}
            <div class="tab-pane fade" id="tab-conformidade">
                {{-- Barra de Conformidade Global --}}
                <div class="d-flex align-items-center justify-content-between mb-20 p-16 border rounded
                    bg-{{ $percentualGlobal >= 80 ? 'success' : ($percentualGlobal >= 50 ? 'warning' : 'danger') }}-focus">
                    <div class="d-flex align-items-center gap-12">
                        <iconify-icon icon="solar:shield-check-bold" class="text-{{ $percentualGlobal >= 80 ? 'success' : ($percentualGlobal >= 50 ? 'warning' : 'danger') }}-main text-2xl"></iconify-icon>
                        <div>
                            <span class="fw-semibold text-{{ $percentualGlobal >= 80 ? 'success' : ($percentualGlobal >= 50 ? 'warning' : 'danger') }}-main">
                                Conformidade Global: {{ number_format($percentualGlobal, 1, ',', '.') }}%
                            </span>
                            <p class="text-sm text-secondary-light mb-0">Lei 14.133/2021 — 7 fases do ciclo contratual</p>
                        </div>
                    </div>
                </div>

                <div class="progress mb-24" style="height: 10px;">
                    <div class="progress-bar bg-{{ $percentualGlobal >= 80 ? 'success' : ($percentualGlobal >= 50 ? 'warning' : 'danger') }}"
                         role="progressbar"
                         style="width: {{ $percentualGlobal }}%">
                    </div>
                </div>

                {{-- Accordion por Fase --}}
                <div class="accordion" id="accordionConformidade">
                    @foreach ($conformidadeFases as $faseKey => $dados)
                        @php
                            $corSemaforo = match ($dados['semaforo']) {
                                'verde' => 'success',
                                'amarelo' => 'warning',
                                default => 'danger',
                            };
                        @endphp
                        <div class="card border-0 mb-8">
                            <div class="card-header p-0 bg-transparent" id="headingConf{{ $loop->index }}">
                                <button class="btn w-100 text-start d-flex align-items-center gap-12 p-12 collapsed"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapseConf{{ $loop->index }}"
                                        aria-expanded="false">
                                    {{-- Semaforo --}}
                                    <span class="d-inline-block rounded-circle flex-shrink-0"
                                          style="width: 12px; height: 12px; background: var(--bs-{{ $corSemaforo }});">
                                    </span>
                                    <iconify-icon icon="{{ $dados['icone'] }}" class="text-primary-600 text-lg"></iconify-icon>
                                    <span class="fw-medium text-sm">{{ $dados['fase']->ordem() }}. {{ $dados['label'] }}</span>

                                    {{-- Badge de conformidade --}}
                                    <span class="badge bg-{{ $corSemaforo }}-focus text-{{ $corSemaforo }}-main ms-auto px-12 py-4 radius-4">
                                        {{ $dados['total_presentes'] }}/{{ $dados['total_obrigatorios'] }}
                                        ({{ number_format($dados['percentual'], 0) }}%)
                                    </span>
                                </button>
                            </div>
                            <div id="collapseConf{{ $loop->index }}" class="collapse" data-bs-parent="#accordionConformidade">
                                <div class="card-body py-8 px-16">
                                    @if ($dados['total_obrigatorios'] > 0)
                                        <div class="progress mb-12" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $corSemaforo }}"
                                                 style="width: {{ $dados['percentual'] }}%"></div>
                                        </div>
                                        @php
                                            $checklistFase = \App\Services\ChecklistService::obterChecklistPorFase($contrato, $dados['fase']);
                                        @endphp
                                        <div class="row gy-2">
                                            @foreach ($checklistFase as $item)
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center gap-8 p-8 border rounded">
                                                        @if ($item['presente'])
                                                            <iconify-icon icon="ic:baseline-check-circle" class="text-success-main text-lg flex-shrink-0"></iconify-icon>
                                                        @else
                                                            <iconify-icon icon="ic:baseline-cancel" class="text-danger-main text-lg flex-shrink-0"></iconify-icon>
                                                        @endif
                                                        <span class="text-sm {{ $item['presente'] ? 'text-neutral-700' : 'text-danger-main fw-medium' }}">
                                                            {{ $item['label'] }}
                                                        </span>
                                                        @if ($item['presente'])
                                                            <span class="badge bg-neutral-100 text-neutral-600 ms-auto text-xs">v{{ $item['versao'] }}</span>
                                                        @else
                                                            <span class="badge bg-danger-focus text-danger-main ms-auto text-xs">Pendente</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-secondary-light mb-0">Nenhum documento obrigatorio configurado para esta fase.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ABA: Aditivos --}}
            <div class="tab-pane fade" id="tab-aditivos">

                {{-- Resumo de Aditivos --}}
                @php
                    $aditivosVigentes = $contrato->aditivos->where('status.value', 'vigente');
                    $somaAcrescimos = $aditivosVigentes->sum('valor_acrescimo');
                    $somaSupressoes = $aditivosVigentes->sum('valor_supressao');
                    $valorOriginal = \App\Services\AditivoService::obterValorOriginal($contrato);
                    $percentualAcumulado = $valorOriginal > 0 ? round(($somaAcrescimos / $valorOriginal) * 100, 2) : 0;
                    $limiteLegalContrato = \App\Services\AditivoService::verificarLimiteLegal($contrato, $percentualAcumulado);
                @endphp

                @if ($contrato->aditivos->count() > 0)
                    {{-- Cards de Resumo --}}
                    <div class="row gy-4 mb-24">
                        <div class="col-md-3">
                            <div class="p-16 border rounded text-center">
                                <p class="text-secondary-light text-sm mb-4">Total Aditivos</p>
                                <h5 class="mb-0">{{ $contrato->aditivos->count() }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-16 border rounded text-center">
                                <p class="text-secondary-light text-sm mb-4">Acrescimos</p>
                                <h6 class="mb-0 text-success-main">+ R$ {{ number_format($somaAcrescimos, 2, ',', '.') }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-16 border rounded text-center">
                                <p class="text-secondary-light text-sm mb-4">Supressoes</p>
                                <h6 class="mb-0 text-danger-main">- R$ {{ number_format($somaSupressoes, 2, ',', '.') }}</h6>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-16 border rounded text-center">
                                <p class="text-secondary-light text-sm mb-4">% Acumulado</p>
                                <h6 class="mb-0 text-{{ $percentualAcumulado > $limiteLegalContrato['limite'] ? 'danger' : ($percentualAcumulado > $limiteLegalContrato['limite'] * 0.8 ? 'warning' : 'success') }}-main">
                                    {{ number_format($percentualAcumulado, 2, ',', '.') }}%
                                </h6>
                            </div>
                        </div>
                    </div>

                    {{-- Barra de Limite Legal --}}
                    <div class="mb-24">
                        <div class="d-flex justify-content-between mb-8">
                            <span class="text-sm fw-medium">Limite Legal: {{ number_format($limiteLegalContrato['limite'], 0) }}%</span>
                            <span class="text-sm text-{{ !$limiteLegalContrato['dentro_limite'] ? 'danger' : 'success' }}-main fw-bold">
                                {{ number_format($percentualAcumulado, 2, ',', '.') }}% / {{ number_format($limiteLegalContrato['limite'], 0) }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ !$limiteLegalContrato['dentro_limite'] ? 'danger' : ($percentualAcumulado > $limiteLegalContrato['limite'] * 0.8 ? 'warning' : 'success') }}"
                                 style="width: {{ min(100, ($percentualAcumulado / max(1, $limiteLegalContrato['limite'])) * 100) }}%">
                            </div>
                        </div>
                    </div>

                    {{-- Tabela de Aditivos --}}
                    <table class="table table-hover mb-24">
                        <thead>
                            <tr>
                                <th class="px-16 py-12">#</th>
                                <th class="px-16 py-12">Tipo</th>
                                <th class="px-16 py-12">Data Assinatura</th>
                                <th class="px-16 py-12">Acrescimo</th>
                                <th class="px-16 py-12">Supressao</th>
                                <th class="px-16 py-12">% Acumulado</th>
                                <th class="px-16 py-12 text-center">Status</th>
                                <th class="px-16 py-12 text-center">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contrato->aditivos->sortBy('numero_sequencial') as $adit)
                                <tr>
                                    <td class="px-16 py-12 fw-medium">{{ $adit->numero_sequencial }}o</td>
                                    <td class="px-16 py-12">
                                        <span class="badge bg-{{ $adit->tipo->cor() }}-focus text-{{ $adit->tipo->cor() }}-main px-12 py-4 radius-4">
                                            {{ $adit->tipo->label() }}
                                        </span>
                                    </td>
                                    <td class="px-16 py-12">{{ $adit->data_assinatura->format('d/m/Y') }}</td>
                                    <td class="px-16 py-12 text-success-main">
                                        {{ $adit->valor_acrescimo ? '+ R$ ' . number_format($adit->valor_acrescimo, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-16 py-12 text-danger-main">
                                        {{ $adit->valor_supressao ? '- R$ ' . number_format($adit->valor_supressao, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-16 py-12">
                                        <span class="badge bg-{{ $adit->percentual_acumulado > 20 ? 'warning' : 'success' }}-focus text-{{ $adit->percentual_acumulado > 20 ? 'warning' : 'success' }}-main px-8 py-4 radius-4">
                                            {{ number_format($adit->percentual_acumulado, 2, ',', '.') }}%
                                        </span>
                                    </td>
                                    <td class="px-16 py-12 text-center">
                                        <span class="badge bg-{{ $adit->status->cor() }}-focus text-{{ $adit->status->cor() }}-main px-12 py-4 radius-4">
                                            {{ $adit->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-16 py-12 text-center">
                                        <a href="{{ route('tenant.aditivos.show', $adit) }}"
                                           class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                           title="Detalhes">
                                            <iconify-icon icon="solar:eye-bold"></iconify-icon>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center text-secondary-light py-24">
                        <iconify-icon icon="solar:add-circle-bold" class="text-4xl mb-8 d-block"></iconify-icon>
                        Nenhum aditivo registrado para este contrato.
                    </div>
                @endif

                {{-- Botao para adicionar aditivo --}}
                @if (auth()->user()->hasPermission('aditivo.criar') && in_array($contrato->status, [\App\Enums\StatusContrato::Vigente, \App\Enums\StatusContrato::Vencido]))
                    <div class="text-center mt-16">
                        @if ($contrato->status === \App\Enums\StatusContrato::Vencido)
                            <div class="alert alert-warning radius-8 mb-12">
                                <small>Contrato vencido: aditivo retroativo exigira justificativa formal (RN-052).</small>
                            </div>
                        @endif
                        <a href="{{ route('tenant.contratos.aditivos.create', $contrato) }}"
                           class="btn btn-primary-600 radius-8">
                            <iconify-icon icon="solar:add-circle-bold" class="icon"></iconify-icon>
                            {{ $contrato->status === \App\Enums\StatusContrato::Vencido ? 'Adicionar Aditivo Retroativo' : 'Adicionar Aditivo' }}
                        </a>
                    </div>
                @endif
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
