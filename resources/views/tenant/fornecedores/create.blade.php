@extends('layout.layout')

@php
    $title = 'Novo Fornecedor';
    $subTitle = 'Cadastros';
@endphp

@section('title', 'Novo Fornecedor')

@section('content')
<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <h6 class="fw-semibold mb-24">Cadastrar Fornecedor</h6>

        <form action="{{ route('tenant.fornecedores.store') }}" method="POST">
            @csrf

            <div class="row gy-3">
                {{-- Identificacao --}}
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        Razao Social <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="razao_social" value="{{ old('razao_social') }}"
                           class="form-control radius-8 @error('razao_social') is-invalid @enderror"
                           placeholder="Razao social do fornecedor" required>
                    @error('razao_social')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                        CNPJ <span class="text-danger-main">*</span>
                    </label>
                    <input type="text" name="cnpj" value="{{ old('cnpj') }}"
                           class="form-control radius-8 @error('cnpj') is-invalid @enderror"
                           placeholder="00.000.000/0001-00" maxlength="18" data-mask="cnpj" required>
                    @error('cnpj')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Nome Fantasia</label>
                    <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}"
                           class="form-control radius-8 @error('nome_fantasia') is-invalid @enderror"
                           placeholder="Nome fantasia">
                    @error('nome_fantasia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Representante Legal</label>
                    <input type="text" name="representante_legal" value="{{ old('representante_legal') }}"
                           class="form-control radius-8 @error('representante_legal') is-invalid @enderror"
                           placeholder="Nome do representante legal">
                    @error('representante_legal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contato --}}
                <div class="col-12"><hr class="my-8"></div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control radius-8 @error('email') is-invalid @enderror"
                           placeholder="email@exemplo.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Telefone</label>
                    <input type="text" name="telefone" value="{{ old('telefone') }}"
                           class="form-control radius-8 @error('telefone') is-invalid @enderror"
                           placeholder="(00) 0000-0000" maxlength="15" data-mask="telefone">
                    @error('telefone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Endereco --}}
                <div class="col-12"><hr class="my-8"></div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Endereco</label>
                    <input type="text" name="endereco" value="{{ old('endereco') }}"
                           class="form-control radius-8 @error('endereco') is-invalid @enderror"
                           placeholder="Rua, numero, complemento">
                    @error('endereco')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Cidade</label>
                    <input type="text" name="cidade" value="{{ old('cidade') }}"
                           class="form-control radius-8 @error('cidade') is-invalid @enderror"
                           placeholder="Cidade">
                    @error('cidade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-1">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">UF</label>
                    <input type="text" name="uf" value="{{ old('uf') }}"
                           class="form-control radius-8 @error('uf') is-invalid @enderror"
                           placeholder="UF" maxlength="2">
                    @error('uf')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">CEP</label>
                    <input type="text" name="cep" value="{{ old('cep') }}"
                           class="form-control radius-8 @error('cep') is-invalid @enderror"
                           placeholder="00000-000" maxlength="9" data-mask="cep">
                    @error('cep')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Observacoes --}}
                <div class="col-12"><hr class="my-8"></div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-8">Observacoes</label>
                    <textarea name="observacoes" rows="3"
                              class="form-control radius-8 @error('observacoes') is-invalid @enderror"
                              placeholder="Observacoes adicionais sobre o fornecedor">{{ old('observacoes') }}</textarea>
                    @error('observacoes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-24">
                <a href="{{ route('tenant.fornecedores.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
                <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
