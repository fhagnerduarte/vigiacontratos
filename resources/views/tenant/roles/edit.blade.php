@extends('layout.layout')

@php
    $title = 'Editar Perfil';
    $subTitle = 'Administracao';
@endphp

@section('title', 'Editar Perfil')

@section('content')
<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <h6 class="fw-semibold mb-24">Editar Perfil</h6>

        @if ($role->is_padrao)
            <div class="alert alert-info radius-8 mb-24" role="alert">
                Este e um perfil padrao do sistema. O identificador nao pode ser alterado.
            </div>
        @endif

        <form action="{{ route('tenant.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Identificador
                    </label>
                    @if ($role->is_padrao)
                        <input type="text" value="{{ $role->nome }}"
                               class="form-control radius-8" disabled>
                    @else
                        <input type="text" name="nome" value="{{ old('nome', $role->nome) }}"
                               class="form-control radius-8 @error('nome') is-invalid @enderror"
                               placeholder="ex: supervisor_obras" required>
                        <small class="text-secondary-light">Apenas letras minusculas e underscores.</small>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Descricao <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="descricao" value="{{ old('descricao', $role->descricao) }}"
                           class="form-control radius-8 @error('descricao') is-invalid @enderror"
                           placeholder="Nome amigavel do perfil" required>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if (! $role->is_padrao)
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-8">Status</label>
                        <select name="is_ativo" class="form-control radius-8 form-select select2">
                            <option value="1" {{ old('is_ativo', $role->is_ativo) ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ ! old('is_ativo', $role->is_ativo) ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                @endif
            </div>

            <div class="d-flex align-items-center gap-3 mt-24">
                <a href="{{ route('tenant.roles.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Atualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection
