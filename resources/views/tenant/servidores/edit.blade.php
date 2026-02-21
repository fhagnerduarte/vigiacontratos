@extends('layout.layout')

@php
    $title = 'Editar Servidor';
    $subTitle = 'Cadastros';
@endphp

@section('title', 'Editar Servidor')

@section('content')
<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <h6 class="fw-semibold mb-24">Editar Servidor — {{ $servidor->nome }}</h6>

        <form action="{{ route('tenant.servidores.update', $servidor) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row gy-3">
                {{-- Identificacao --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Nome Completo <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="nome" value="{{ old('nome', $servidor->nome) }}"
                           class="form-control radius-8 @error('nome') is-invalid @enderror"
                           placeholder="Nome completo do servidor" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">CPF</label>
                    <input type="text" name="cpf" value="{{ old('cpf', $servidor->cpf) }}"
                           class="form-control radius-8 @error('cpf') is-invalid @enderror"
                           placeholder="000.000.000-00" maxlength="14" data-mask="cpf">
                    @error('cpf')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Matricula <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="matricula" value="{{ old('matricula', $servidor->matricula) }}"
                           class="form-control radius-8 @error('matricula') is-invalid @enderror"
                           placeholder="MAT-0000" required>
                    @error('matricula')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Cargo / Funcao <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="cargo" value="{{ old('cargo', $servidor->cargo) }}"
                           class="form-control radius-8 @error('cargo') is-invalid @enderror"
                           placeholder="Cargo ou funcao exercida" required>
                    @error('cargo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Secretaria (Lotacao)</label>
                    <select name="secretaria_id"
                            class="form-control radius-8 form-select select2 @error('secretaria_id') is-invalid @enderror">
                        <option value="">Selecione uma secretaria...</option>
                        @foreach ($secretarias as $sec)
                            <option value="{{ $sec->id }}" {{ old('secretaria_id', $servidor->secretaria_id) == $sec->id ? 'selected' : '' }}>
                                {{ $sec->nome }}
                            </option>
                        @endforeach
                    </select>
                    @error('secretaria_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contato --}}
                <div class="col-12"><hr class="my-8"></div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">E-mail Institucional</label>
                    <input type="email" name="email" value="{{ old('email', $servidor->email) }}"
                           class="form-control radius-8 @error('email') is-invalid @enderror"
                           placeholder="servidor@prefeitura.gov.br">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Telefone</label>
                    <input type="text" name="telefone" value="{{ old('telefone', $servidor->telefone) }}"
                           class="form-control radius-8 @error('telefone') is-invalid @enderror"
                           placeholder="(00) 0000-0000" maxlength="15" data-mask="telefone">
                    @error('telefone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Status</label>
                    <div class="form-check form-switch mt-8">
                        <input type="hidden" name="is_ativo" value="0">
                        <input class="form-check-input" type="checkbox" name="is_ativo" value="1"
                               id="is_ativo" {{ old('is_ativo', $servidor->is_ativo) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_ativo">Ativo</label>
                    </div>
                </div>

                {{-- Observacoes --}}
                <div class="col-12"><hr class="my-8"></div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Observacoes</label>
                    <textarea name="observacoes" rows="3"
                              class="form-control radius-8 @error('observacoes') is-invalid @enderror"
                              placeholder="Observacoes adicionais sobre o servidor">{{ old('observacoes', $servidor->observacoes) }}</textarea>
                    @error('observacoes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-24">
                <a href="{{ route('tenant.servidores.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Atualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection
