@extends('layout.layout')

@php
    $title = 'Editar Usuario';
    $subTitle = 'Administracao';
@endphp

@section('title', 'Editar Usuario')

@section('content')
<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <h6 class="fw-semibold mb-24">Editar Usuario</h6>

        <form action="{{ route('tenant.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Nome <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="nome" value="{{ old('nome', $user->nome) }}"
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
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-control radius-8 @error('email') is-invalid @enderror"
                           placeholder="email@exemplo.com" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Nova Senha
                    </label>
                    <input type="password" name="password"
                           class="form-control radius-8 @error('password') is-invalid @enderror"
                           placeholder="Deixe em branco para manter a atual">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Confirmar Nova Senha
                    </label>
                    <input type="password" name="password_confirmation"
                           class="form-control radius-8"
                           placeholder="Repita a nova senha">
                </div>

                <div class="col-12"><hr class="my-8"></div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Perfil <span class="text-danger-main">*</span>
                    </label>
                    <select name="role_id" class="form-control radius-8 @error('role_id') is-invalid @enderror" required>
                        <option value="">Selecione um perfil</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
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
                    <select name="is_ativo" class="form-control radius-8">
                        <option value="1" {{ old('is_ativo', $user->is_ativo) ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ ! old('is_ativo', $user->is_ativo) ? 'selected' : '' }}>Inativo</option>
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
                    @php
                        $userSecretariaIds = old('secretarias', $user->secretarias->pluck('id')->toArray());
                    @endphp
                    <div class="row">
                        @foreach ($secretarias as $secretaria)
                            <div class="col-md-4 col-lg-3 mb-8">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="secretarias[]" value="{{ $secretaria->id }}"
                                           id="sec_{{ $secretaria->id }}"
                                           {{ in_array($secretaria->id, $userSecretariaIds) ? 'checked' : '' }}>
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
                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Atualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection
