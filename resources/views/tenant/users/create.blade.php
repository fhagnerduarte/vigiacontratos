@extends('layout.layout')

@php
    $title = 'Novo Usuario';
    $subTitle = 'Administracao';
@endphp

@section('title', 'Novo Usuario')

@section('content')
<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <h6 class="fw-semibold mb-24">Cadastrar Usuario</h6>

        <form action="{{ route('tenant.users.store') }}" method="POST">
            @csrf

            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Nome <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="nome" value="{{ old('nome') }}"
                           class="form-control radius-8 @error('nome') is-invalid @enderror"
                           placeholder="Nome completo" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        E-mail <span class="text-danger-main">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control radius-8 @error('email') is-invalid @enderror"
                           placeholder="email@exemplo.com" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Senha <span class="text-danger-main">*</span>
                    </label>
                    <input type="password" name="password"
                           class="form-control radius-8 @error('password') is-invalid @enderror"
                           placeholder="Minimo 8 caracteres" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Confirmar Senha <span class="text-danger-main">*</span>
                    </label>
                    <input type="password" name="password_confirmation"
                           class="form-control radius-8"
                           placeholder="Repita a senha" required>
                </div>

                <div class="col-12"><hr class="my-8"></div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Perfil <span class="text-danger-main">*</span>
                    </label>
                    <select name="role_id" class="form-control radius-8 form-select select2 @error('role_id') is-invalid @enderror" required>
                        <option value="">Selecione um perfil</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->descricao }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Status</label>
                    <select name="is_ativo" class="form-control radius-8 form-select select2">
                        <option value="1" {{ old('is_ativo', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ old('is_ativo') === '0' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>

                <div class="col-12"><hr class="my-8"></div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Secretarias Vinculadas
                    </label>
                    <p class="text-secondary-light text-sm mb-12">
                        Perfis estrategicos (Administrador, Controladoria, Gabinete) acessam todas as secretarias automaticamente.
                    </p>
                    <div class="row">
                        @foreach ($secretarias as $secretaria)
                            <div class="col-md-4 col-lg-3 mb-8">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="secretarias[]" value="{{ $secretaria->id }}"
                                           id="sec_{{ $secretaria->id }}"
                                           {{ in_array($secretaria->id, old('secretarias', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label text-sm" for="sec_{{ $secretaria->id }}">
                                        {{ $secretaria->sigla ? $secretaria->sigla . ' - ' : '' }}{{ $secretaria->nome }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('secretarias')
                        <div class="text-danger text-sm mt-4">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-24">
                <a href="{{ route('tenant.users.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
