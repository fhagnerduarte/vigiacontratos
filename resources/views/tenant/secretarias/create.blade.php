@extends('layout.layout')

@php
    $title = 'Nova Secretaria';
    $subTitle = 'Cadastros';
@endphp

@section('title', 'Nova Secretaria')

@section('content')
<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <h6 class="fw-semibold mb-24">Cadastrar Secretaria</h6>

        <form action="{{ route('tenant.secretarias.store') }}" method="POST">
            @csrf

            <div class="row gy-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Nome <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="nome" value="{{ old('nome') }}"
                           class="form-control radius-8 @error('nome') is-invalid @enderror"
                           placeholder="Nome da secretaria" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Sigla</label>
                    <input type="text" name="sigla" value="{{ old('sigla') }}"
                           class="form-control radius-8 @error('sigla') is-invalid @enderror"
                           placeholder="Ex: SMS, SME" maxlength="20">
                    @error('sigla')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Responsável</label>
                    <input type="text" name="responsavel" value="{{ old('responsavel') }}"
                           class="form-control radius-8 @error('responsavel') is-invalid @enderror"
                           placeholder="Nome do responsável">
                    @error('responsavel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control radius-8 @error('email') is-invalid @enderror"
                           placeholder="email@exemplo.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Telefone</label>
                    <input type="text" name="telefone" value="{{ old('telefone') }}"
                           class="form-control radius-8 @error('telefone') is-invalid @enderror"
                           placeholder="(00) 0000-0000" maxlength="15" data-mask="telefone">
                    @error('telefone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-24">
                <a href="{{ route('tenant.secretarias.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
