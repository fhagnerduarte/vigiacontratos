@extends('layout.layout')

@php
    $title = 'Editar Contrato';
    $subTitle = 'Gestao Contratual';
@endphp

@section('title', 'Editar Contrato')

@section('content')

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

<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <div class="d-flex justify-content-between align-items-center mb-24">
            <h6 class="fw-semibold mb-0">Editar Contrato {{ $contrato->numero }}</h6>
            <span class="badge bg-{{ $contrato->status->cor() }}-focus text-{{ $contrato->status->cor() }}-main px-16 py-6 radius-4">
                {{ $contrato->status->label() }}
            </span>
        </div>

        <form action="{{ route('tenant.contratos.update', $contrato) }}" method="POST" id="edit-form">
            @csrf
            @method('PUT')

            {{-- Identificacao --}}
            <h6 class="fw-semibold text-primary-light mb-16 mt-0">Identificacao</h6>
            <div class="row gy-3 mb-24">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Numero</label>
                    <input type="text" class="form-control radius-8" value="{{ $contrato->numero }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Nro. Processo Administrativo <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="numero_processo" value="{{ old('numero_processo', $contrato->numero_processo) }}"
                           class="form-control radius-8 @error('numero_processo') is-invalid @enderror" required>
                    @error('numero_processo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Modalidade de Contratacao <span class="text-danger-main">*</span>
                    </label>
                    <select name="modalidade_contratacao" id="modalidade_contratacao"
                            class="form-control radius-8 form-select select2 @error('modalidade_contratacao') is-invalid @enderror" required>
                        @foreach (\App\Enums\ModalidadeContratacao::cases() as $mod)
                            <option value="{{ $mod->value }}" {{ old('modalidade_contratacao', $contrato->modalidade_contratacao->value) === $mod->value ? 'selected' : '' }}>{{ $mod->label() }}</option>
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
                        @foreach (\App\Enums\TipoContrato::cases() as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo', $contrato->tipo->value) === $tipo->value ? 'selected' : '' }}>{{ $tipo->label() }}</option>
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
                        @foreach ($secretarias as $sec)
                            <option value="{{ $sec->id }}" {{ old('secretaria_id', $contrato->secretaria_id) == $sec->id ? 'selected' : '' }}>{{ $sec->nome }}</option>
                        @endforeach
                    </select>
                    @error('secretaria_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Unidade Gestora</label>
                    <input type="text" name="unidade_gestora" value="{{ old('unidade_gestora', $contrato->unidade_gestora) }}"
                           class="form-control radius-8 @error('unidade_gestora') is-invalid @enderror">
                    @error('unidade_gestora')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Objeto <span class="text-danger-main">*</span>
                    </label>
                    <textarea name="objeto" rows="3"
                              class="form-control radius-8 @error('objeto') is-invalid @enderror" required>{{ old('objeto', $contrato->objeto) }}</textarea>
                    @error('objeto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6" id="campo-fundamento-legal"
                     style="{{ in_array(old('modalidade_contratacao', $contrato->modalidade_contratacao->value), ['dispensa', 'inexigibilidade']) ? '' : 'display:none' }}">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Fundamento Legal <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="fundamento_legal" value="{{ old('fundamento_legal', $contrato->fundamento_legal) }}"
                           class="form-control radius-8 @error('fundamento_legal') is-invalid @enderror">
                    @error('fundamento_legal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6" id="campo-responsavel-tecnico"
                     style="{{ old('tipo', $contrato->tipo->value) === 'obra' ? '' : 'display:none' }}">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Responsavel Tecnico <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="responsavel_tecnico" value="{{ old('responsavel_tecnico', $contrato->responsavel_tecnico) }}"
                           class="form-control radius-8 @error('responsavel_tecnico') is-invalid @enderror">
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
                            <option value="{{ $serv->id }}" {{ old('servidor_id', $contrato->servidor_id) == $serv->id ? 'selected' : '' }}>
                                {{ $serv->nome }} — Mat: {{ $serv->matricula }}
                            </option>
                        @endforeach
                    </select>
                    @error('servidor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-secondary-light mt-4 d-block">
                        Nao encontrou o servidor?
                        <a href="{{ route('tenant.servidores.create') }}" target="_blank">Cadastrar novo servidor</a>
                    </small>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Data de Assinatura</label>
                    <input type="date" name="data_assinatura" value="{{ old('data_assinatura', $contrato->data_assinatura?->format('Y-m-d')) }}"
                           class="form-control radius-8 @error('data_assinatura') is-invalid @enderror">
                    @error('data_assinatura')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-16">

            {{-- Fornecedor --}}
            <h6 class="fw-semibold text-primary-light mb-16">Fornecedor</h6>
            <div class="row gy-3 mb-24">
                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Fornecedor <span class="text-danger-main">*</span>
                    </label>
                    <select name="fornecedor_id"
                            class="form-control radius-8 form-select select2 @error('fornecedor_id') is-invalid @enderror" required>
                        @foreach ($fornecedores as $forn)
                            <option value="{{ $forn->id }}" {{ old('fornecedor_id', $contrato->fornecedor_id) == $forn->id ? 'selected' : '' }}>
                                {{ $forn->razao_social }} — {{ $forn->cnpj }}
                            </option>
                        @endforeach
                    </select>
                    @error('fornecedor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-16">

            {{-- Financeiro --}}
            <h6 class="fw-semibold text-primary-light mb-16">Dados Financeiros</h6>
            <div class="row gy-3 mb-24">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Valor Global (R$) <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="valor_global" value="{{ old('valor_global', number_format($contrato->valor_global, 2, ',', '.')) }}"
                           class="form-control radius-8 @error('valor_global') is-invalid @enderror"
                           data-mask="valor" required>
                    @error('valor_global')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Valor Mensal (R$)</label>
                    <input type="text" name="valor_mensal" value="{{ old('valor_mensal', $contrato->valor_mensal ? number_format($contrato->valor_mensal, 2, ',', '.') : '') }}"
                           class="form-control radius-8 @error('valor_mensal') is-invalid @enderror"
                           data-mask="valor">
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
                            <option value="{{ $tp->value }}" {{ old('tipo_pagamento', $contrato->tipo_pagamento?->value) === $tp->value ? 'selected' : '' }}>{{ $tp->label() }}</option>
                        @endforeach
                    </select>
                    @error('tipo_pagamento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Fonte de Recurso</label>
                    <input type="text" name="fonte_recurso" value="{{ old('fonte_recurso', $contrato->fonte_recurso) }}"
                           class="form-control radius-8">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Dotacao Orcamentaria</label>
                    <input type="text" name="dotacao_orcamentaria" value="{{ old('dotacao_orcamentaria', $contrato->dotacao_orcamentaria) }}"
                           class="form-control radius-8">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Numero do Empenho</label>
                    <input type="text" name="numero_empenho" value="{{ old('numero_empenho', $contrato->numero_empenho) }}"
                           class="form-control radius-8">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Categoria</label>
                    <select name="categoria" class="form-control radius-8 form-select select2">
                        <option value="">Selecione...</option>
                        @foreach (\App\Enums\CategoriaContrato::cases() as $cat)
                            <option value="{{ $cat->value }}" {{ old('categoria', $contrato->categoria?->value) === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Categoria de Servico</label>
                    <select name="categoria_servico" class="form-control radius-8 form-select select2">
                        <option value="">Selecione...</option>
                        @foreach (\App\Enums\CategoriaServico::cases() as $cs)
                            <option value="{{ $cs->value }}" {{ old('categoria_servico', $contrato->categoria_servico?->value) === $cs->value ? 'selected' : '' }}>{{ $cs->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Regime de Execucao</label>
                    <select name="regime_execucao"
                            class="form-control radius-8 form-select select2 @error('regime_execucao') is-invalid @enderror"
                            data-placeholder="Selecione o regime...">
                        <option value=""></option>
                        <option value="empreitada_integral" {{ old('regime_execucao', $contrato->regime_execucao) === 'empreitada_integral' ? 'selected' : '' }}>Empreitada Integral</option>
                        <option value="preco_unitario" {{ old('regime_execucao', $contrato->regime_execucao) === 'preco_unitario' ? 'selected' : '' }}>Preco Unitario</option>
                        <option value="preco_global" {{ old('regime_execucao', $contrato->regime_execucao) === 'preco_global' ? 'selected' : '' }}>Preco Global</option>
                        <option value="tarefa" {{ old('regime_execucao', $contrato->regime_execucao) === 'tarefa' ? 'selected' : '' }}>Tarefa</option>
                        <option value="contratacao_integrada" {{ old('regime_execucao', $contrato->regime_execucao) === 'contratacao_integrada' ? 'selected' : '' }}>Contratacao Integrada</option>
                    </select>
                    @error('regime_execucao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Condicoes de Pagamento</label>
                    <textarea name="condicoes_pagamento" rows="3"
                              class="form-control radius-8 @error('condicoes_pagamento') is-invalid @enderror"
                              placeholder="Descreva as condicoes de pagamento previstas no contrato">{{ old('condicoes_pagamento', $contrato->condicoes_pagamento) }}</textarea>
                    @error('condicoes_pagamento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Garantias</label>
                    <textarea name="garantias" rows="3"
                              class="form-control radius-8 @error('garantias') is-invalid @enderror"
                              placeholder="Descreva as garantias exigidas (caucao, seguro, fianca bancaria, etc.)">{{ old('garantias', $contrato->garantias) }}</textarea>
                    @error('garantias')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-16">

            {{-- Vigencia --}}
            <h6 class="fw-semibold text-primary-light mb-16">Vigencia</h6>
            <div class="row gy-3 mb-24">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Data de Inicio <span class="text-danger-main">*</span>
                    </label>
                    <input type="date" name="data_inicio" value="{{ old('data_inicio', $contrato->data_inicio->format('Y-m-d')) }}"
                           class="form-control radius-8 @error('data_inicio') is-invalid @enderror" required>
                    @error('data_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Data de Fim <span class="text-danger-main">*</span>
                    </label>
                    <input type="date" name="data_fim" value="{{ old('data_fim', $contrato->data_fim->format('Y-m-d')) }}"
                           class="form-control radius-8 @error('data_fim') is-invalid @enderror" required>
                    @error('data_fim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="hidden" name="prorrogacao_automatica" value="0">
                        <input class="form-check-input" type="checkbox" name="prorrogacao_automatica" value="1"
                               id="prorrogacao_automatica" {{ old('prorrogacao_automatica', $contrato->prorrogacao_automatica) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-primary-light text-sm" for="prorrogacao_automatica">
                            Prorrogacao automatica
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Observacoes</label>
                    <textarea name="observacoes" rows="3"
                              class="form-control radius-8">{{ old('observacoes', $contrato->observacoes) }}</textarea>
                </div>
            </div>

            <hr class="my-16">

            {{-- Publicacao --}}
            <h6 class="fw-semibold text-primary-light mb-16">Publicacao</h6>
            <div class="row gy-3 mb-24">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Data de Publicacao</label>
                    <input type="date" name="data_publicacao" value="{{ old('data_publicacao', $contrato->data_publicacao?->format('Y-m-d')) }}"
                           class="form-control radius-8 @error('data_publicacao') is-invalid @enderror">
                    @error('data_publicacao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Veiculo de Publicacao</label>
                    <input type="text" name="veiculo_publicacao" value="{{ old('veiculo_publicacao', $contrato->veiculo_publicacao) }}"
                           class="form-control radius-8 @error('veiculo_publicacao') is-invalid @enderror"
                           placeholder="Ex: Diario Oficial do Municipio">
                    @error('veiculo_publicacao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Link Transparencia</label>
                    <input type="url" name="link_transparencia" value="{{ old('link_transparencia', $contrato->link_transparencia) }}"
                           class="form-control radius-8 @error('link_transparencia') is-invalid @enderror"
                           placeholder="https://...">
                    @error('link_transparencia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Classificacao de Sigilo</label>
                    <select name="classificacao_sigilo" id="classificacao_sigilo"
                            class="form-select radius-8 @error('classificacao_sigilo') is-invalid @enderror">
                        @foreach (\App\Enums\ClassificacaoSigilo::cases() as $classificacao)
                            <option value="{{ $classificacao->value }}" {{ old('classificacao_sigilo', $contrato->classificacao_sigilo?->value ?? 'publico') === $classificacao->value ? 'selected' : '' }}>
                                {{ $classificacao->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('classificacao_sigilo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @php $sigiloAtual = old('classificacao_sigilo', $contrato->classificacao_sigilo?->value ?? 'publico'); @endphp

                <div class="col-md-4" id="publicado_portal_wrapper" style="{{ $sigiloAtual !== 'publico' ? 'display:none' : '' }}">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Portal de Transparencia</label>
                    <div class="form-check form-switch mt-8">
                        <input type="hidden" name="publicado_portal" value="0">
                        <input class="form-check-input" type="checkbox" name="publicado_portal" id="publicado_portal" value="1"
                               {{ old('publicado_portal', $contrato->publicado_portal) ? 'checked' : '' }}>
                        <label class="form-check-label" for="publicado_portal">Publicar no Portal Publico</label>
                    </div>
                    @error('publicado_portal')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12" id="justificativa_sigilo_wrapper" style="{{ $sigiloAtual === 'publico' ? 'display:none' : '' }}">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Justificativa de Sigilo</label>
                    <textarea name="justificativa_sigilo" rows="3"
                              class="form-control radius-8 @error('justificativa_sigilo') is-invalid @enderror"
                              placeholder="Justifique a classificacao de sigilo (obrigatorio para contratos nao publicos)">{{ old('justificativa_sigilo', $contrato->justificativa_sigilo) }}</textarea>
                    @error('justificativa_sigilo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('tenant.contratos.show', $contrato) }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Atualizar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    // Campos condicionais
    var modalidadeSelect = document.getElementById('modalidade_contratacao');
    var fundamentoLegal = document.getElementById('campo-fundamento-legal');
    if (modalidadeSelect) {
        modalidadeSelect.addEventListener('change', function() {
            var sensiveis = ['dispensa', 'inexigibilidade'];
            fundamentoLegal.style.display = sensiveis.includes(this.value) ? '' : 'none';
        });
    }

    var tipoSelect = document.getElementById('tipo_contrato');
    var responsavelTecnico = document.getElementById('campo-responsavel-tecnico');
    if (tipoSelect) {
        tipoSelect.addEventListener('change', function() {
            responsavelTecnico.style.display = this.value === 'obra' ? '' : 'none';
        });
    }

    // Mascara monetaria
    document.querySelectorAll('[data-mask="valor"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            var value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = value;
        });
    });

    document.getElementById('edit-form').addEventListener('submit', function() {
        document.querySelectorAll('[data-mask="valor"]').forEach(function(input) {
            var val = input.value.replace(/\./g, '').replace(',', '.');
            input.value = val;
        });
    });
});
</script>
@endpush
