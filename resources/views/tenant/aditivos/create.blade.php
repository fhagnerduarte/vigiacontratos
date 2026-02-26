@extends('layout.layout')

@php
    $title = 'Novo Aditivo';
    $subTitle = 'Contratos';
@endphp

@section('title', 'Novo Aditivo')

@section('content')

{{-- Breadcrumb customizado --}}
<div class="d-flex flex-wrap align-items-center gap-2 mb-24">
    <a href="{{ route('tenant.contratos.index') }}" class="text-secondary-light hover-text-primary fw-medium">Contratos</a>
    <span class="text-secondary-light">/</span>
    <a href="{{ route('tenant.contratos.show', $contrato) }}" class="text-secondary-light hover-text-primary fw-medium">{{ $contrato->numero }}</a>
    <span class="text-secondary-light">/</span>
    <span class="text-primary-light fw-semibold">Novo Aditivo</span>
</div>

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

{{-- Alerta de limite legal --}}
@if (!$limiteLegal['dentro_limite'] && $limiteLegal['is_bloqueante'])
    <div class="alert alert-danger radius-8 mb-24" role="alert">
        <div class="d-flex align-items-start gap-2">
            <iconify-icon icon="solar:danger-triangle-bold" class="text-danger-main text-xl mt-2"></iconify-icon>
            <div>
                <strong>Limite legal excedido!</strong>
                <p class="mb-0 mt-4">
                    O percentual acumulado de aditivos ({{ number_format($percentualAcumuladoAtual, 2, ',', '.') }}%) ultrapassa o limite legal de {{ number_format($limiteLegal['limite'], 2, ',', '.') }}%.
                    O salvamento deste aditivo está bloqueado. Consulte o setor jurídico antes de prosseguir.
                </p>
            </div>
        </div>
    </div>
@elseif (!$limiteLegal['dentro_limite'] && !$limiteLegal['is_bloqueante'])
    <div class="alert alert-warning radius-8 mb-24" role="alert">
        <div class="d-flex align-items-start gap-2">
            <iconify-icon icon="solar:danger-triangle-bold" class="text-warning-main text-xl mt-2"></iconify-icon>
            <div>
                <strong>Atenção: limite legal próximo de ser excedido!</strong>
                <p class="mb-0 mt-4">
                    O percentual acumulado de aditivos ({{ number_format($percentualAcumuladoAtual, 2, ',', '.') }}%) está próximo ou acima do limite de {{ number_format($limiteLegal['limite'], 2, ',', '.') }}%.
                    É obrigatório informar uma justificativa para o excesso de limite no campo abaixo.
                </p>
            </div>
        </div>
    </div>
@endif

{{-- Card resumo do contrato --}}
<div class="card radius-8 border-0 mb-24">
    <div class="card-body p-24">
        <h6 class="fw-semibold mb-16">
            <iconify-icon icon="solar:document-text-bold" class="text-primary-600 me-4"></iconify-icon>
            Contrato {{ $contrato->numero }}
        </h6>
        <div class="row gy-3">
            <div class="col-md-3">
                <span class="text-secondary-light text-sm d-block mb-4">Fornecedor</span>
                <span class="fw-medium text-primary-light">{{ $contrato->fornecedor->razao_social ?? '---' }}</span>
            </div>
            <div class="col-md-2">
                <span class="text-secondary-light text-sm d-block mb-4">Valor Global</span>
                <span class="fw-semibold text-primary-light">R$ {{ number_format($contrato->valor_global, 2, ',', '.') }}</span>
            </div>
            <div class="col-md-2">
                <span class="text-secondary-light text-sm d-block mb-4">Vigência Atual</span>
                <span class="fw-medium text-primary-light">{{ $contrato->data_fim->format('d/m/Y') }}</span>
            </div>
            <div class="col-md-2">
                <span class="text-secondary-light text-sm d-block mb-4">Sequencial</span>
                <span class="fw-semibold text-primary-light">{{ $proximoSequencial }}o Aditivo</span>
            </div>
            <div class="col-md-3">
                <span class="text-secondary-light text-sm d-block mb-4">Percentual Acumulado</span>
                <div class="d-flex align-items-center gap-2">
                    <div class="progress w-100" style="height: 8px;">
                        @php
                            $percentualBarra = min($percentualAcumuladoAtual, 100);
                            $corBarra = $limiteLegal['dentro_limite'] ? 'bg-success-main' : ($limiteLegal['is_bloqueante'] ? 'bg-danger-main' : 'bg-warning-main');
                        @endphp
                        <div class="progress-bar {{ $corBarra }}" role="progressbar"
                             style="width: {{ $percentualBarra }}%"
                             aria-valuenow="{{ $percentualAcumuladoAtual }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <span class="fw-semibold text-sm text-nowrap">{{ number_format($percentualAcumuladoAtual, 1, ',', '.') }}%</span>
                </div>
                <small class="text-secondary-light">Limite: {{ number_format($limiteLegal['limite'], 0, ',', '.') }}%</small>
            </div>
        </div>
    </div>
</div>

{{-- Formulário principal --}}
<form action="{{ route('tenant.contratos.aditivos.store', $contrato) }}" method="POST" id="aditivo-form">
    @csrf

    <div class="card radius-8 border-0 mb-24">
        <div class="card-body p-24">
            <h6 class="fw-semibold mb-24">Dados do Aditivo</h6>

            <div class="row gy-3">
                {{-- Tipo de Aditivo --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Tipo de Aditivo <span class="text-danger-main">*</span>
                    </label>
                    <select name="tipo" id="tipo_aditivo"
                            class="form-control radius-8 form-select select2 @error('tipo') is-invalid @enderror"
                            data-placeholder="Selecione o tipo de aditivo..." required>
                        <option value=""></option>
                        @foreach (\App\Enums\TipoAditivo::cases() as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo') === $tipo->value ? 'selected' : '' }}>
                                {{ $tipo->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Data de Assinatura --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Data de Assinatura <span class="text-danger-main">*</span>
                    </label>
                    <input type="date" name="data_assinatura" value="{{ old('data_assinatura') }}"
                           class="form-control radius-8 @error('data_assinatura') is-invalid @enderror" required>
                    @error('data_assinatura')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Data de Início de Vigência --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Data Início Vigência
                    </label>
                    <input type="date" name="data_inicio_vigencia" value="{{ old('data_inicio_vigencia') }}"
                           class="form-control radius-8 @error('data_inicio_vigencia') is-invalid @enderror">
                    @error('data_inicio_vigencia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Fundamentação Legal --}}
                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Fundamentação Legal <span class="text-danger-main">*</span>
                    </label>
                    <textarea name="fundamentacao_legal" rows="2"
                              class="form-control radius-8 @error('fundamentacao_legal') is-invalid @enderror"
                              placeholder="Artigo, inciso, lei ou norma que fundamenta este aditivo" required>{{ old('fundamentacao_legal') }}</textarea>
                    @error('fundamentacao_legal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Justificativa --}}
                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Justificativa <span class="text-danger-main">*</span>
                    </label>
                    <textarea name="justificativa" rows="3"
                              class="form-control radius-8 @error('justificativa') is-invalid @enderror"
                              placeholder="Justificativa administrativa para a celebração deste aditivo" required>{{ old('justificativa') }}</textarea>
                    @error('justificativa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Justificativa Técnica --}}
                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Justificativa Técnica <span class="text-danger-main">*</span>
                    </label>
                    <textarea name="justificativa_tecnica" rows="3"
                              class="form-control radius-8 @error('justificativa_tecnica') is-invalid @enderror"
                              placeholder="Justificativa técnica que embasa a necessidade deste aditivo" required>{{ old('justificativa_tecnica') }}</textarea>
                    @error('justificativa_tecnica')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-24">

            {{-- Seção: Prazo (prazo, prazo_e_valor, misto) --}}
            <div id="secao-prazo" style="display: none;">
                <h6 class="fw-semibold text-primary-light mb-16">
                    <iconify-icon icon="solar:calendar-bold" class="me-4"></iconify-icon>
                    Alteração de Prazo
                </h6>
                <div class="row gy-3 mb-24">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                            Nova Data de Fim <span class="text-danger-main">*</span>
                        </label>
                        <input type="date" name="nova_data_fim" value="{{ old('nova_data_fim') }}"
                               class="form-control radius-8 @error('nova_data_fim') is-invalid @enderror"
                               min="{{ $contrato->data_fim->addDay()->format('Y-m-d') }}">
                        @error('nova_data_fim')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-secondary-light mt-4 d-block">
                            Data de fim atual: <strong>{{ $contrato->data_fim->format('d/m/Y') }}</strong>
                        </small>
                    </div>
                </div>
            </div>

            {{-- Seção: Valor (valor, prazo_e_valor, misto, reequilíbrio) --}}
            <div id="secao-valor" style="display: none;">
                <h6 class="fw-semibold text-primary-light mb-16">
                    <iconify-icon icon="solar:dollar-minimalistic-bold" class="me-4"></iconify-icon>
                    Acréscimo de Valor
                </h6>
                <div class="row gy-3 mb-24">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                            Valor do Acréscimo (R$) <span class="text-danger-main">*</span>
                        </label>
                        <input type="text" name="valor_acrescimo" value="{{ old('valor_acrescimo') }}"
                               class="form-control radius-8 @error('valor_acrescimo') is-invalid @enderror"
                               placeholder="0,00" data-mask="valor">
                        @error('valor_acrescimo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Seção: Supressão (supressão, misto) --}}
            <div id="secao-supressao" style="display: none;">
                <h6 class="fw-semibold text-primary-light mb-16">
                    <iconify-icon icon="solar:minus-circle-bold" class="me-4"></iconify-icon>
                    Supressão de Valor
                </h6>
                <div class="row gy-3 mb-24">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                            Valor da Supressão (R$) <span class="text-danger-main">*</span>
                        </label>
                        <input type="text" name="valor_supressao" value="{{ old('valor_supressao') }}"
                               class="form-control radius-8 @error('valor_supressao') is-invalid @enderror"
                               placeholder="0,00" data-mask="valor">
                        @error('valor_supressao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Seção: Reequilíbrio (reequilíbrio) --}}
            <div id="secao-reequilibrio" style="display: none;">
                <h6 class="fw-semibold text-primary-light mb-16">
                    <iconify-icon icon="solar:chart-bold" class="me-4"></iconify-icon>
                    Reequilíbrio Econômico-Financeiro
                </h6>
                <div class="row gy-3 mb-24">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                            Motivo do Reequilíbrio <span class="text-danger-main">*</span>
                        </label>
                        <textarea name="motivo_reequilibrio" rows="3"
                                  class="form-control radius-8 @error('motivo_reequilibrio') is-invalid @enderror"
                                  placeholder="Descreva o motivo que justifica o reequilíbrio econômico-financeiro">{{ old('motivo_reequilibrio') }}</textarea>
                        @error('motivo_reequilibrio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                            Índice Utilizado <span class="text-danger-main">*</span>
                        </label>
                        <select name="indice_utilizado" id="indice_utilizado"
                                class="form-control radius-8 form-select select2 @error('indice_utilizado') is-invalid @enderror"
                                data-placeholder="Selecione o índice...">
                            <option value=""></option>
                            <option value="IPCA" {{ old('indice_utilizado') === 'IPCA' ? 'selected' : '' }}>IPCA</option>
                            <option value="INCC" {{ old('indice_utilizado') === 'INCC' ? 'selected' : '' }}>INCC</option>
                            <option value="IGPM" {{ old('indice_utilizado') === 'IGPM' ? 'selected' : '' }}>IGP-M</option>
                            <option value="Outro" {{ old('indice_utilizado') === 'Outro' ? 'selected' : '' }}>Outro</option>
                        </select>
                        @error('indice_utilizado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                            Valor Anterior (R$)
                        </label>
                        <input type="text" name="valor_anterior_reequilibrio"
                               value="{{ old('valor_anterior_reequilibrio', number_format($contrato->valor_global, 2, ',', '.')) }}"
                               class="form-control radius-8 @error('valor_anterior_reequilibrio') is-invalid @enderror"
                               data-mask="valor" readonly>
                        @error('valor_anterior_reequilibrio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-secondary-light mt-4 d-block">Valor global atual do contrato</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                            Valor Reajustado (R$) <span class="text-danger-main">*</span>
                        </label>
                        <input type="text" name="valor_reajustado" value="{{ old('valor_reajustado') }}"
                               class="form-control radius-8 @error('valor_reajustado') is-invalid @enderror"
                               placeholder="0,00" data-mask="valor">
                        @error('valor_reajustado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-24">

            {{-- Justificativa Excesso de Limite (condicional) --}}
            <div id="secao-excesso-limite" style="{{ (!$limiteLegal['dentro_limite'] && !$limiteLegal['is_bloqueante']) ? '' : 'display: none;' }}">
                <div class="row gy-3 mb-24">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                            Justificativa para Excesso de Limite <span class="text-danger-main">*</span>
                        </label>
                        <textarea name="justificativa_excesso_limite" rows="3"
                                  class="form-control radius-8 @error('justificativa_excesso_limite') is-invalid @enderror"
                                  placeholder="Justifique a necessidade de ultrapassar o limite legal de aditivos">{{ old('justificativa_excesso_limite') }}</textarea>
                        @error('justificativa_excesso_limite')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Justificativa Retroativa (RN-052) — obrigatória se contrato vencido --}}
            @if ($exigeJustificativaRetroativa)
                <div class="alert alert-danger radius-8 mb-16">
                    <div class="d-flex align-items-start gap-2">
                        <iconify-icon icon="solar:danger-triangle-bold" class="text-danger-main text-xl mt-2"></iconify-icon>
                        <div>
                            <strong>Aditivo Retroativo (RN-052)</strong>
                            <p class="mb-0 mt-4">
                                Este contrato está vencido. A justificativa retroativa é obrigatória para fundamentar a regularização.
                                Descreva os motivos que justificam o aditivo após o vencimento do contrato original.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row gy-3 mb-24">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                            Justificativa Retroativa <span class="text-danger-main">*</span>
                        </label>
                        <textarea name="justificativa_retroativa" rows="4"
                                  class="form-control radius-8 @error('justificativa_retroativa') is-invalid @enderror"
                                  placeholder="Descreva detalhadamente os motivos que justificam a celebração deste aditivo após o vencimento do contrato. Mínimo 50 caracteres." required>{{ old('justificativa_retroativa') }}</textarea>
                        @error('justificativa_retroativa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            {{-- Observações --}}
            <div class="row gy-3">
                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Observações</label>
                    <textarea name="observacoes" rows="3"
                              class="form-control radius-8 @error('observacoes') is-invalid @enderror"
                              placeholder="Observações adicionais sobre este aditivo (opcional)">{{ old('observacoes') }}</textarea>
                    @error('observacoes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Botões de ação --}}
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('tenant.contratos.show', $contrato) }}"
           class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">
            Cancelar
        </a>
        @if (!$limiteLegal['is_bloqueante'] || $limiteLegal['dentro_limite'])
            <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">
                <iconify-icon icon="lucide:check" class="me-4"></iconify-icon> Salvar Aditivo
            </button>
        @else
            <button type="button" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8 opacity-50" disabled
                    title="Salvamento bloqueado: limite legal excedido">
                <iconify-icon icon="lucide:check" class="me-4"></iconify-icon> Salvar Aditivo
            </button>
        @endif
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tipoSelect = document.getElementById('tipo_aditivo');
    var secaoPrazo = document.getElementById('secao-prazo');
    var secaoValor = document.getElementById('secao-valor');
    var secaoSupressao = document.getElementById('secao-supressao');
    var secaoReequilibrio = document.getElementById('secao-reequilibrio');
    var valorAnteriorInput = document.querySelector('input[name="valor_anterior_reequilibrio"]');
    var valorGlobalContrato = '{{ number_format($contrato->valor_global, 2, ',', '.') }}';

    // Tipos que mostram cada secao
    var tiposPrazo = ['prazo', 'prazo_e_valor', 'misto'];
    var tiposValor = ['valor', 'prazo_e_valor', 'misto', 'reequilibrio'];
    var tiposSupressao = ['supressao', 'misto'];
    var tiposReequilibrio = ['reequilibrio'];

    function toggleSecoes(tipo) {
        secaoPrazo.style.display = tiposPrazo.includes(tipo) ? '' : 'none';
        secaoValor.style.display = tiposValor.includes(tipo) ? '' : 'none';
        secaoSupressao.style.display = tiposSupressao.includes(tipo) ? '' : 'none';
        secaoReequilibrio.style.display = tiposReequilibrio.includes(tipo) ? '' : 'none';

        // Auto-fill valor_anterior_reequilibrio ao selecionar reequilibrio
        if (tipo === 'reequilibrio' && valorAnteriorInput) {
            valorAnteriorInput.value = valorGlobalContrato;
        }
    }

    // Evento change no select (compativel com Select2)
    if (tipoSelect) {
        $(tipoSelect).on('change', function() {
            toggleSecoes(this.value);
        });

        // Estado inicial (para preservar old() em caso de validacao)
        if (tipoSelect.value) {
            toggleSecoes(tipoSelect.value);
        }
    }

    // Mascara monetaria
    document.querySelectorAll('[data-mask="valor"]').forEach(function(input) {
        if (input.readOnly) return; // Nao aplica evento de input em campos readonly

        input.addEventListener('input', function(e) {
            var value = e.target.value.replace(/\D/g, '');
            if (value === '') {
                e.target.value = '';
                return;
            }
            value = (parseInt(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = value;
        });

        input.addEventListener('blur', function(e) {
            var val = e.target.value.replace(/\./g, '').replace(',', '.');
            if (val && !isNaN(val)) {
                e.target.value = parseFloat(val).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        });
    });

    // Antes de submeter, converter valores monetarios para formato numerico
    document.getElementById('aditivo-form').addEventListener('submit', function() {
        document.querySelectorAll('[data-mask="valor"]').forEach(function(input) {
            var val = input.value.replace(/\./g, '').replace(',', '.');
            input.value = val;
        });
    });
});
</script>
@endpush
