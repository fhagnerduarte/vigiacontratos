@extends('layout.layout')

@php
    $title = 'LGPD';
    $subTitle = 'Nova solicitacao de protecao de dados';
@endphp

@section('title', 'LGPD — Nova Solicitacao')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Nova Solicitacao LGPD</h6>
    <a href="{{ route('tenant.lgpd.index') }}" class="btn btn-sm btn-outline-secondary-600">
        <iconify-icon icon="solar:arrow-left-bold" class="me-1"></iconify-icon> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-24">
        <form action="{{ route('tenant.lgpd.store') }}" method="POST">
            @csrf

            <div class="row gy-3">
                {{-- Tipo de Solicitacao --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tipo de Solicitacao <span class="text-danger">*</span></label>
                    <select name="tipo_solicitacao" class="form-select select2" data-placeholder="Selecione o tipo">
                        <option value=""></option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo_solicitacao') === $tipo->value ? 'selected' : '' }}>
                                {{ $tipo->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_solicitacao')
                        <div class="text-danger text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tipo de Entidade --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tipo de Entidade <span class="text-danger">*</span></label>
                    <select name="entidade_tipo" id="entidade_tipo" class="form-select select2" data-placeholder="Selecione o tipo de entidade">
                        <option value=""></option>
                        <option value="fornecedor" {{ old('entidade_tipo') === 'fornecedor' ? 'selected' : '' }}>Fornecedor</option>
                        <option value="fiscal" {{ old('entidade_tipo') === 'fiscal' ? 'selected' : '' }}>Fiscal</option>
                        <option value="servidor" {{ old('entidade_tipo') === 'servidor' ? 'selected' : '' }}>Servidor</option>
                        <option value="usuario" {{ old('entidade_tipo') === 'usuario' ? 'selected' : '' }}>Usuario (inativo)</option>
                    </select>
                    @error('entidade_tipo')
                        <div class="text-danger text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Selects dinamicos por tipo de entidade --}}
                <div class="col-md-12" id="entidade_wrapper">
                    {{-- Fornecedor --}}
                    <div class="entidade-select" id="select_fornecedor" style="display:none;">
                        <label class="form-label fw-semibold">Fornecedor <span class="text-danger">*</span></label>
                        <select name="entidade_id_fornecedor" class="form-select select2" data-placeholder="Selecione o fornecedor">
                            <option value=""></option>
                            @foreach ($fornecedores as $f)
                                <option value="{{ $f->id }}" {{ (int) old('entidade_id') === $f->id && old('entidade_tipo') === 'fornecedor' ? 'selected' : '' }}>
                                    {{ $f->razao_social }} ({{ $f->cnpj }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fiscal --}}
                    <div class="entidade-select" id="select_fiscal" style="display:none;">
                        <label class="form-label fw-semibold">Fiscal <span class="text-danger">*</span></label>
                        <select name="entidade_id_fiscal" class="form-select select2" data-placeholder="Selecione o fiscal">
                            <option value=""></option>
                            @foreach ($fiscais as $f)
                                <option value="{{ $f->id }}" {{ (int) old('entidade_id') === $f->id && old('entidade_tipo') === 'fiscal' ? 'selected' : '' }}>
                                    {{ $f->nome }} ({{ $f->matricula }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Servidor --}}
                    <div class="entidade-select" id="select_servidor" style="display:none;">
                        <label class="form-label fw-semibold">Servidor <span class="text-danger">*</span></label>
                        <select name="entidade_id_servidor" class="form-select select2" data-placeholder="Selecione o servidor">
                            <option value=""></option>
                            @foreach ($servidores as $s)
                                <option value="{{ $s->id }}" {{ (int) old('entidade_id') === $s->id && old('entidade_tipo') === 'servidor' ? 'selected' : '' }}>
                                    {{ $s->nome }} ({{ $s->cpf }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Usuario --}}
                    <div class="entidade-select" id="select_usuario" style="display:none;">
                        <label class="form-label fw-semibold">Usuario (inativo) <span class="text-danger">*</span></label>
                        <select name="entidade_id_usuario" class="form-select select2" data-placeholder="Selecione o usuario">
                            <option value=""></option>
                            @foreach ($usuarios as $u)
                                <option value="{{ $u->id }}" {{ (int) old('entidade_id') === $u->id && old('entidade_tipo') === 'usuario' ? 'selected' : '' }}>
                                    {{ $u->nome }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Campo hidden para entidade_id --}}
                <input type="hidden" name="entidade_id" id="entidade_id" value="{{ old('entidade_id') }}">

                {{-- Justificativa --}}
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Justificativa <span class="text-danger">*</span></label>
                    <textarea name="justificativa" class="form-control" rows="4" minlength="10" maxlength="500"
                              placeholder="Descreva o motivo da solicitacao (minimo 10 caracteres)...">{{ old('justificativa') }}</textarea>
                    @error('justificativa')
                        <div class="text-danger text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                @error('entidade_id')
                    <div class="col-md-12">
                        <div class="text-danger text-sm">{{ $message }}</div>
                    </div>
                @enderror
            </div>

            <div class="d-flex align-items-center gap-3 mt-24">
                <a href="{{ route('tenant.lgpd.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">
                    <iconify-icon icon="solar:shield-keyhole-bold" class="me-1"></iconify-icon> Registrar Solicitacao
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {
    var $tipoSelect = $('#entidade_tipo');
    var $entidadeIdHidden = $('#entidade_id');

    function showEntitySelect(tipo) {
        $('.entidade-select').hide();
        if (tipo) {
            $('#select_' + tipo).show();
        }
    }

    // Sync entidade_id hidden com o select visivel
    function syncEntidadeId() {
        var tipo = $tipoSelect.val();
        if (!tipo) { $entidadeIdHidden.val(''); return; }
        var val = $('[name="entidade_id_' + tipo + '"]').val();
        $entidadeIdHidden.val(val || '');
    }

    // Select2 dispara eventos jQuery — usar .on('change')
    $tipoSelect.on('change', function() {
        showEntitySelect($(this).val());
        // Limpar selects de entidade ao trocar tipo
        $('[name^="entidade_id_"]').val(null).trigger('change.select2');
        $entidadeIdHidden.val('');
    });

    // Observar mudancas nos selects de entidade (Select2 jQuery events)
    $('[name^="entidade_id_"]').on('change', syncEntidadeId);

    // Init: mostrar o select correto baseado no old()
    var oldTipo = $tipoSelect.val();
    if (oldTipo) showEntitySelect(oldTipo);
    syncEntidadeId();
});
</script>
@endpush
