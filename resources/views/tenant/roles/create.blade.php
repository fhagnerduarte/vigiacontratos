@extends('layout.layout')

@php
    $title = 'Novo Perfil';
    $subTitle = 'Administração';
@endphp

@section('title', 'Novo Perfil')

@section('content')
<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <h6 class="fw-semibold mb-24">Cadastrar Perfil Customizado</h6>

        <form action="{{ route('tenant.roles.store') }}" method="POST">
            @csrf

            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Identificador <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="nome" value="{{ old('nome') }}"
                           class="form-control radius-8 @error('nome') is-invalid @enderror"
                           placeholder="ex: supervisor_obras" required>
                    <small class="text-secondary-light">Apenas letras minúsculas e underscores.</small>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Descrição <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="descricao" value="{{ old('descricao') }}"
                           class="form-control radius-8 @error('descricao') is-invalid @enderror"
                           placeholder="Nome amigável do perfil" required>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-24">
                <a href="{{ route('tenant.roles.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
