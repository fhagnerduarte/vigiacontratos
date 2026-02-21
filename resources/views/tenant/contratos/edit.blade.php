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
