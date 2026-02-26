@extends('layout.layout')

@php
    $title = 'Novo Contrato';
    $subTitle = 'Gestão Contratual';
@endphp

@section('title', 'Novo Contrato')

@section('content')

{{-- Erros de validação --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show radius-8 mb-24" role="alert">
        <strong>Verifique os campos abaixo:</strong>
        <ul class="mb-0 mt-8">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

<form action="{{ route('tenant.contratos.store') }}" method="POST" id="wizard-form">
    @csrf

    {{-- Indicador de progresso --}}
    <div class="card radius-8 border-0 mb-24">
        <div class="card-body p-24">
            <ul class="d-flex align-items-center justify-content-between gap-2 flex-wrap list-unstyled mb-0" id="wizard-steps">
                <li class="wizard-step active text-center" data-step="1">
                    <div class="w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center mx-auto mb-8 bg-primary-600 text-white fw-semibold">1</div>
                    <span class="text-sm fw-medium">Identificação</span>
                </li>
                <li class="wizard-step text-center" data-step="2">
                    <div class="w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center mx-auto mb-8 bg-neutral-200 text-secondary-light fw-semibold">2</div>
                    <span class="text-sm fw-medium">Fornecedor</span>
                </li>
                <li class="wizard-step text-center" data-step="3">
                    <div class="w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center mx-auto mb-8 bg-neutral-200 text-secondary-light fw-semibold">3</div>
                    <span class="text-sm fw-medium">Financeiro</span>
                </li>
                <li class="wizard-step text-center" data-step="4">
                    <div class="w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center mx-auto mb-8 bg-neutral-200 text-secondary-light fw-semibold">4</div>
                    <span class="text-sm fw-medium">Vigência</span>
                </li>
                <li class="wizard-step text-center" data-step="5">
                    <div class="w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center mx-auto mb-8 bg-neutral-200 text-secondary-light fw-semibold">5</div>
                    <span class="text-sm fw-medium">Fiscal</span>
                </li>
                <li class="wizard-step text-center" data-step="6">
                    <div class="w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center mx-auto mb-8 bg-neutral-200 text-secondary-light fw-semibold">6</div>
                    <span class="text-sm fw-medium">Revisão</span>
                </li>
            </ul>
        </div>
    </div>

    {{-- ETAPA 1 — Identificação --}}
    <div class="card radius-8 border-0 mb-24 wizard-panel" data-step="1">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-24">Etapa 1: Identificação do Contrato</h6>
            <div class="row gy-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Ano <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="ano" value="{{ old('ano', date('Y')) }}"
                           class="form-control radius-8 @error('ano') is-invalid @enderror"
                           maxlength="4" required>
                    @error('ano')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Nro. Processo Administrativo <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="numero_processo" value="{{ old('numero_processo') }}"
                           class="form-control radius-8 @error('numero_processo') is-invalid @enderror"
                           placeholder="Ex: 2026.001234" required>
                    @error('numero_processo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Modalidade de Contratação <span class="text-danger-main">*</span>
                    </label>
                    <select name="modalidade_contratacao" id="modalidade_contratacao"
                            class="form-control radius-8 form-select select2 @error('modalidade_contratacao') is-invalid @enderror" required>
                        <option value="">Selecione...</option>
                        @foreach (\App\Enums\ModalidadeContratacao::cases() as $mod)
                            <option value="{{ $mod->value }}" {{ old('modalidade_contratacao') === $mod->value ? 'selected' : '' }}>{{ $mod->label() }}</option>
                        @endforeach
                    </select>
                    @error('modalidade_contratacao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Tipo de Contrato <span class="text-danger-main">*</span>
                    </label>
                    <select name="tipo" id="tipo_contrato"
                            class="form-control radius-8 form-select select2 @error('tipo') is-invalid @enderror" required>
                        <option value="">Selecione...</option>
                        @foreach (\App\Enums\TipoContrato::cases() as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo') === $tipo->value ? 'selected' : '' }}>{{ $tipo->label() }}</option>
                        @endforeach
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Secretaria <span class="text-danger-main">*</span>
                    </label>
                    <select name="secretaria_id"
                            class="form-control radius-8 form-select select2 @error('secretaria_id') is-invalid @enderror" required>
                        <option value="">Selecione...</option>
                        @foreach ($secretarias as $sec)
                            <option value="{{ $sec->id }}" {{ old('secretaria_id') == $sec->id ? 'selected' : '' }}>{{ $sec->nome }}</option>
                        @endforeach
                    </select>
                    @error('secretaria_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Unidade Gestora</label>
                    <input type="text" name="unidade_gestora" value="{{ old('unidade_gestora') }}"
                           class="form-control radius-8 @error('unidade_gestora') is-invalid @enderror"
                           placeholder="Subdivisão da secretaria">
                    @error('unidade_gestora')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Objeto <span class="text-danger-main">*</span>
                    </label>
                    <textarea name="objeto" rows="3"
                              class="form-control radius-8 @error('objeto') is-invalid @enderror"
                              placeholder="Descreva o objeto do contrato" required>{{ old('objeto') }}</textarea>
                    @error('objeto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campos condicionais --}}
                <div class="col-md-6" id="campo-fundamento-legal" style="{{ in_array(old('modalidade_contratacao'), ['dispensa', 'inexigibilidade']) ? '' : 'display:none' }}">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Fundamento Legal <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="fundamento_legal" value="{{ old('fundamento_legal') }}"
                           class="form-control radius-8 @error('fundamento_legal') is-invalid @enderror"
                           placeholder="Ex: Art. 24, II da Lei 8.666/93">
                    @error('fundamento_legal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6" id="campo-responsavel-tecnico" style="{{ old('tipo') === 'obra' ? '' : 'display:none' }}">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Responsável Técnico <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="responsavel_tecnico" value="{{ old('responsavel_tecnico') }}"
                           class="form-control radius-8 @error('responsavel_tecnico') is-invalid @enderror"
                           placeholder="Nome do responsável técnico">
                    @error('responsavel_tecnico')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Gestor do Contrato</label>
                    <select name="servidor_id"
                            class="form-control radius-8 form-select select2 @error('servidor_id') is-invalid @enderror">
                        <option value="">Selecione um servidor...</option>
                        @foreach ($servidores as $serv)
                            <option value="{{ $serv->id }}" {{ old('servidor_id') == $serv->id ? 'selected' : '' }}>
                                {{ $serv->nome }} — Mat: {{ $serv->matricula }}
                            </option>
                        @endforeach
                    </select>
                    @error('servidor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-secondary-light mt-4 d-block">
                        Não encontrou o servidor?
                        <a href="{{ route('tenant.servidores.create') }}" target="_blank">Cadastrar novo servidor</a>
                    </small>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Data de Assinatura</label>
                    <input type="date" name="data_assinatura" value="{{ old('data_assinatura') }}"
                           class="form-control radius-8 @error('data_assinatura') is-invalid @enderror">
                    @error('data_assinatura')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ETAPA 2 — Fornecedor --}}
    <div class="card radius-8 border-0 mb-24 wizard-panel d-none" data-step="2">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-24">Etapa 2: Fornecedor</h6>
            <div class="row gy-3">
                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Selecionar Fornecedor <span class="text-danger-main">*</span>
                    </label>
                    <select name="fornecedor_id"
                            class="form-control radius-8 form-select select2 @error('fornecedor_id') is-invalid @enderror" required>
                        <option value="">Selecione um fornecedor...</option>
                        @foreach ($fornecedores as $forn)
                            <option value="{{ $forn->id }}" {{ old('fornecedor_id') == $forn->id ? 'selected' : '' }}>
                                {{ $forn->razao_social }} — {{ $forn->cnpj }}
                            </option>
                        @endforeach
                    </select>
                    @error('fornecedor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-secondary-light mt-4 d-block">
                        Não encontrou o fornecedor?
                        <a href="{{ route('tenant.fornecedores.create') }}" target="_blank">Cadastrar novo fornecedor</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- ETAPA 3 — Financeiro --}}
    <div class="card radius-8 border-0 mb-24 wizard-panel d-none" data-step="3">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-24">Etapa 3: Dados Financeiros</h6>
            <div class="row gy-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Valor Global (R$) <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="valor_global" value="{{ old('valor_global') }}"
                           class="form-control radius-8 @error('valor_global') is-invalid @enderror"
                           placeholder="0,00" data-mask="valor" required>
                    @error('valor_global')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Valor Mensal (R$)</label>
                    <input type="text" name="valor_mensal" value="{{ old('valor_mensal') }}"
                           class="form-control radius-8 @error('valor_mensal') is-invalid @enderror"
                           placeholder="0,00" data-mask="valor">
                    @error('valor_mensal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Tipo de Pagamento</label>
                    <select name="tipo_pagamento"
                            class="form-control radius-8 form-select select2 @error('tipo_pagamento') is-invalid @enderror">
                        <option value="">Selecione...</option>
                        @foreach (\App\Enums\TipoPagamento::cases() as $tp)
                            <option value="{{ $tp->value }}" {{ old('tipo_pagamento') === $tp->value ? 'selected' : '' }}>{{ $tp->label() }}</option>
                        @endforeach
                    </select>
                    @error('tipo_pagamento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Fonte de Recurso</label>
                    <input type="text" name="fonte_recurso" value="{{ old('fonte_recurso') }}"
                           class="form-control radius-8 @error('fonte_recurso') is-invalid @enderror">
                    @error('fonte_recurso')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Dotação Orçamentária</label>
                    <input type="text" name="dotacao_orcamentaria" value="{{ old('dotacao_orcamentaria') }}"
                           class="form-control radius-8 @error('dotacao_orcamentaria') is-invalid @enderror">
                    @error('dotacao_orcamentaria')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Número do Empenho</label>
                    <input type="text" name="numero_empenho" value="{{ old('numero_empenho') }}"
                           class="form-control radius-8 @error('numero_empenho') is-invalid @enderror">
                    @error('numero_empenho')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Categoria</label>
                    <select name="categoria"
                            class="form-control radius-8 form-select select2 @error('categoria') is-invalid @enderror">
                        <option value="">Selecione...</option>
                        @foreach (\App\Enums\CategoriaContrato::cases() as $cat)
                            <option value="{{ $cat->value }}" {{ old('categoria') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                        @endforeach
                    </select>
                    @error('categoria')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Categoria de Serviço</label>
                    <select name="categoria_servico"
                            class="form-control radius-8 form-select select2 @error('categoria_servico') is-invalid @enderror">
                        <option value="">Selecione...</option>
                        @foreach (\App\Enums\CategoriaServico::cases() as $cs)
                            <option value="{{ $cs->value }}" {{ old('categoria_servico') === $cs->value ? 'selected' : '' }}>{{ $cs->label() }}</option>
                        @endforeach
                    </select>
                    @error('categoria_servico')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Regime de Execução</label>
                    <select name="regime_execucao"
                            class="form-control radius-8 form-select select2 @error('regime_execucao') is-invalid @enderror"
                            data-placeholder="Selecione o regime...">
                        <option value=""></option>
                        <option value="empreitada_integral" {{ old('regime_execucao') === 'empreitada_integral' ? 'selected' : '' }}>Empreitada Integral</option>
                        <option value="preco_unitario" {{ old('regime_execucao') === 'preco_unitario' ? 'selected' : '' }}>Preço Unitário</option>
                        <option value="preco_global" {{ old('regime_execucao') === 'preco_global' ? 'selected' : '' }}>Preço Global</option>
                        <option value="tarefa" {{ old('regime_execucao') === 'tarefa' ? 'selected' : '' }}>Tarefa</option>
                        <option value="contratacao_integrada" {{ old('regime_execucao') === 'contratacao_integrada' ? 'selected' : '' }}>Contratação Integrada</option>
                    </select>
                    @error('regime_execucao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Condições de Pagamento</label>
                    <textarea name="condicoes_pagamento" rows="3"
                              class="form-control radius-8 @error('condicoes_pagamento') is-invalid @enderror"
                              placeholder="Descreva as condições de pagamento previstas no contrato">{{ old('condicoes_pagamento') }}</textarea>
                    @error('condicoes_pagamento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Garantias</label>
                    <textarea name="garantias" rows="3"
                              class="form-control radius-8 @error('garantias') is-invalid @enderror"
                              placeholder="Descreva as garantias exigidas (caução, seguro, fiança bancária, etc.)">{{ old('garantias') }}</textarea>
                    @error('garantias')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ETAPA 4 — Vigência --}}
    <div class="card radius-8 border-0 mb-24 wizard-panel d-none" data-step="4">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-24">Etapa 4: Vigência</h6>
            <div class="row gy-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Data de Início <span class="text-danger-main">*</span>
                    </label>
                    <input type="date" name="data_inicio" value="{{ old('data_inicio') }}"
                           class="form-control radius-8 @error('data_inicio') is-invalid @enderror" required>
                    @error('data_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Data de Fim <span class="text-danger-main">*</span>
                    </label>
                    <input type="date" name="data_fim" value="{{ old('data_fim') }}"
                           class="form-control radius-8 @error('data_fim') is-invalid @enderror" required>
                    @error('data_fim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="hidden" name="prorrogacao_automatica" value="0">
                        <input class="form-check-input" type="checkbox" name="prorrogacao_automatica" value="1"
                               id="prorrogacao_automatica" {{ old('prorrogacao_automatica') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-primary-light text-sm" for="prorrogacao_automatica">
                            Prorrogação automática
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Observações</label>
                    <textarea name="observacoes" rows="3"
                              class="form-control radius-8 @error('observacoes') is-invalid @enderror"
                              placeholder="Observações gerais sobre o contrato">{{ old('observacoes') }}</textarea>
                    @error('observacoes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Data de Publicação</label>
                    <input type="date" name="data_publicacao" value="{{ old('data_publicacao') }}"
                           class="form-control radius-8 @error('data_publicacao') is-invalid @enderror">
                    @error('data_publicacao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Veículo de Publicação</label>
                    <input type="text" name="veiculo_publicacao" value="{{ old('veiculo_publicacao') }}"
                           class="form-control radius-8 @error('veiculo_publicacao') is-invalid @enderror"
                           placeholder="Ex: Diário Oficial do Município">
                    @error('veiculo_publicacao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Link Transparência</label>
                    <input type="url" name="link_transparencia" value="{{ old('link_transparencia') }}"
                           class="form-control radius-8 @error('link_transparencia') is-invalid @enderror"
                           placeholder="https://...">
                    @error('link_transparencia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Classificação de Sigilo</label>
                    <select name="classificacao_sigilo" id="classificacao_sigilo"
                            class="form-select radius-8 @error('classificacao_sigilo') is-invalid @enderror">
                        @foreach (\App\Enums\ClassificacaoSigilo::cases() as $classificacao)
                            <option value="{{ $classificacao->value }}" {{ old('classificacao_sigilo', 'publico') === $classificacao->value ? 'selected' : '' }}>
                                {{ $classificacao->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('classificacao_sigilo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4" id="publicado_portal_wrapper" style="{{ old('classificacao_sigilo', 'publico') !== 'publico' ? 'display:none' : '' }}">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Portal de Transparência</label>
                    <div class="form-check form-switch mt-8">
                        <input type="hidden" name="publicado_portal" value="0">
                        <input class="form-check-input" type="checkbox" name="publicado_portal" id="publicado_portal" value="1"
                               {{ old('publicado_portal') ? 'checked' : '' }}>
                        <label class="form-check-label" for="publicado_portal">Publicar no Portal Público</label>
                    </div>
                    @error('publicado_portal')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12" id="justificativa_sigilo_wrapper" style="{{ old('classificacao_sigilo', 'publico') === 'publico' ? 'display:none' : '' }}">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Justificativa de Sigilo</label>
                    <textarea name="justificativa_sigilo" rows="3"
                              class="form-control radius-8 @error('justificativa_sigilo') is-invalid @enderror"
                              placeholder="Justifique a classificação de sigilo (obrigatório para contratos não públicos)">{{ old('justificativa_sigilo') }}</textarea>
                    @error('justificativa_sigilo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ETAPA 5 — Fiscal --}}
    <div class="card radius-8 border-0 mb-24 wizard-panel d-none" data-step="5">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-24">Etapa 5: Fiscal do Contrato</h6>
            <p class="text-secondary-light mb-16">O fiscal é obrigatório para contratos ativos (RN-024).</p>
            <div class="row gy-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Servidor Fiscal <span class="text-danger-main">*</span>
                    </label>
                    <select name="fiscal_servidor_id"
                            class="form-control radius-8 form-select select2 @error('fiscal_servidor_id') is-invalid @enderror"
                            data-placeholder="Selecione o servidor fiscal..." required>
                        <option value=""></option>
                        @foreach ($servidores as $serv)
                            <option value="{{ $serv->id }}" {{ old('fiscal_servidor_id') == $serv->id ? 'selected' : '' }}>
                                {{ $serv->nome }} — Mat: {{ $serv->matricula }} — {{ $serv->cargo }}
                            </option>
                        @endforeach
                    </select>
                    @error('fiscal_servidor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="{{ route('tenant.servidores.create') }}" target="_blank"
                       class="btn btn-outline-primary text-sm btn-sm px-16 py-10 radius-8 w-100">
                        <iconify-icon icon="lucide:plus" class="me-4"></iconify-icon> Cadastrar novo servidor
                    </a>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Fiscal Substituto</label>
                    <select name="fiscal_substituto_servidor_id"
                            class="form-control radius-8 form-select select2 @error('fiscal_substituto_servidor_id') is-invalid @enderror"
                            data-placeholder="Selecione o fiscal substituto (opcional)...">
                        <option value=""></option>
                        @foreach ($servidores as $serv)
                            <option value="{{ $serv->id }}" {{ old('fiscal_substituto_servidor_id') == $serv->id ? 'selected' : '' }}>
                                {{ $serv->nome }} — Mat: {{ $serv->matricula }} — {{ $serv->cargo }}
                            </option>
                        @endforeach
                    </select>
                    @error('fiscal_substituto_servidor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Portaria de Designação</label>
                    <input type="text" name="portaria_designacao" value="{{ old('portaria_designacao') }}"
                           class="form-control radius-8 @error('portaria_designacao') is-invalid @enderror"
                           placeholder="Ex: Portaria no 123/2026">
                    @error('portaria_designacao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ETAPA 6 — Revisão --}}
    <div class="card radius-8 border-0 mb-24 wizard-panel d-none" data-step="6">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-24">Etapa 6: Revisão Final</h6>
            <p class="text-secondary-light mb-16">Revise os dados antes de salvar. O número do contrato será gerado automaticamente.</p>
            <div class="alert alert-info-100 radius-8">
                <iconify-icon icon="solar:info-circle-bold" class="text-info-main me-8"></iconify-icon>
                Após salvar, você poderá anexar documentos na tela de detalhes do contrato.
            </div>
        </div>
    </div>

    {{-- Navegação do Wizard --}}
    <div class="d-flex align-items-center justify-content-between gap-3">
        <a href="{{ route('tenant.contratos.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary text-sm btn-sm px-16 py-10 radius-8 d-none" id="wizard-prev">
                <iconify-icon icon="lucide:arrow-left" class="me-4"></iconify-icon> Anterior
            </button>
            <button type="button" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8" id="wizard-next">
                Próximo <iconify-icon icon="lucide:arrow-right" class="ms-4"></iconify-icon>
            </button>
            <button type="submit" class="btn btn-success text-sm btn-sm px-16 py-10 radius-8 d-none" id="wizard-submit">
                <iconify-icon icon="lucide:check" class="me-4"></iconify-icon> Salvar Contrato
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const panels = document.querySelectorAll('.wizard-panel');
    const steps = document.querySelectorAll('.wizard-step');
    const btnPrev = document.getElementById('wizard-prev');
    const btnNext = document.getElementById('wizard-next');
    const btnSubmit = document.getElementById('wizard-submit');
    const totalSteps = panels.length;
    let currentStep = 1;

    function showStep(step) {
        panels.forEach(p => p.classList.add('d-none'));
        document.querySelector(`.wizard-panel[data-step="${step}"]`).classList.remove('d-none');

        steps.forEach(s => {
            const stepNum = parseInt(s.dataset.step);
            const circle = s.querySelector('div');
            if (stepNum <= step) {
                circle.className = 'w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center mx-auto mb-8 bg-primary-600 text-white fw-semibold';
            } else {
                circle.className = 'w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center mx-auto mb-8 bg-neutral-200 text-secondary-light fw-semibold';
            }
        });

        btnPrev.classList.toggle('d-none', step === 1);
        btnNext.classList.toggle('d-none', step === totalSteps);
        btnSubmit.classList.toggle('d-none', step !== totalSteps);

        currentStep = step;
    }

    btnNext.addEventListener('click', function() {
        if (currentStep < totalSteps) {
            showStep(currentStep + 1);
        }
    });

    btnPrev.addEventListener('click', function() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    });

    // Campos condicionais — classificacao de sigilo
    var classificacaoSelect = document.getElementById('classificacao_sigilo');
    var portalWrapper = document.getElementById('publicado_portal_wrapper');
    var justificativaWrapper = document.getElementById('justificativa_sigilo_wrapper');
    if (classificacaoSelect) {
        classificacaoSelect.addEventListener('change', function() {
            var isPublico = this.value === 'publico';
            portalWrapper.style.display = isPublico ? '' : 'none';
            justificativaWrapper.style.display = isPublico ? 'none' : '';
            if (!isPublico) {
                document.getElementById('publicado_portal').checked = false;
            }
        });
    }

    // Campos condicionais — modalidade
    const modalidadeSelect = document.getElementById('modalidade_contratacao');
    const fundamentoLegal = document.getElementById('campo-fundamento-legal');
    if (modalidadeSelect) {
        modalidadeSelect.addEventListener('change', function() {
            const sensiveis = ['dispensa', 'inexigibilidade'];
            fundamentoLegal.style.display = sensiveis.includes(this.value) ? '' : 'none';
        });
    }

    // Campos condicionais — tipo obra
    const tipoSelect = document.getElementById('tipo_contrato');
    const responsavelTecnico = document.getElementById('campo-responsavel-tecnico');
    if (tipoSelect) {
        tipoSelect.addEventListener('change', function() {
            responsavelTecnico.style.display = this.value === 'obra' ? '' : 'none';
        });
    }

    // Mascara monetaria
    document.querySelectorAll('[data-mask="valor"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = value;
        });

        input.addEventListener('blur', function(e) {
            // Converte para formato numerico para envio
            let val = e.target.value.replace(/\./g, '').replace(',', '.');
            if (val && !isNaN(val)) {
                e.target.value = parseFloat(val).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        });
    });

    // Antes de submeter, converter valores monetarios para formato numerico
    document.getElementById('wizard-form').addEventListener('submit', function() {
        document.querySelectorAll('[data-mask="valor"]').forEach(function(input) {
            let val = input.value.replace(/\./g, '').replace(',', '.');
            input.value = val;
        });
    });
});
</script>
@endpush
