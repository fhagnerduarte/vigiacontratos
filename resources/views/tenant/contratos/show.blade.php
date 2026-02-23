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
                            <dd class="col-sm-7">
                                @if ($contrato->gestor)
                                    {{ $contrato->gestor->nome }} — Mat: {{ $contrato->gestor->matricula }}
                                    <br><small class="text-secondary-light">{{ $contrato->gestor->cargo }}</small>
                                @else
                                    {{ $contrato->gestor_nome ?? '-' }}
                                @endif
                            </dd>

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
                                            <form action="{{ route('tenant.documentos.verificar-integridade', $doc) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                   class="w-32-px h-32-px bg-warning-focus text-warning-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                                   onclick="return confirm('Verificar integridade deste documento?')"
                                                   title="Verificar integridade">
                                                    <iconify-icon icon="solar:shield-check-bold"></iconify-icon>
                                                </button>
                                            </form>
                                        @endif
                                        @if (auth()->user()->hasPermission('documento.excluir') && $doc->is_versao_atual)
                                            <form action="{{ route('tenant.documentos.destroy', $doc) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                   class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                                   onclick="return confirm('Excluir este documento?')"
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
