@extends('layout.layout')

@php
    $title = 'Permissoes';
    $subTitle = 'Administracao';
@endphp

@section('title', 'Permissoes - ' . $role->descricao)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-4">Permissoes do Perfil</h6>
        <span class="badge bg-primary-focus text-primary-main px-12 py-6 radius-4">{{ $role->descricao }}</span>
        @if ($role->is_padrao)
            <span class="badge bg-info-focus text-info-main px-12 py-6 radius-4 ms-4">Padrao</span>
        @endif
    </div>
    <a href="{{ route('tenant.roles.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-12 py-8 radius-8 d-flex align-items-center gap-2">
        <iconify-icon icon="lucide:arrow-left" class="icon text-xl"></iconify-icon> Voltar
    </a>
</div>

<form action="{{ route('tenant.permissoes.update', $role) }}" method="POST">
    @csrf
    @method('PUT')

    @php
        $grupoLabels = [
            'contrato'      => 'Contratos',
            'aditivo'       => 'Aditivos',
            'fornecedor'    => 'Fornecedores',
            'secretaria'    => 'Secretarias',
            'documento'     => 'Documentos',
            'financeiro'    => 'Financeiro',
            'fiscal'        => 'Fiscais',
            'relatorio'     => 'Relatorios',
            'usuario'       => 'Usuarios',
            'configuracao'  => 'Configuracoes',
            'auditoria'     => 'Auditoria',
            'parecer'       => 'Pareceres',
            'workflow'      => 'Workflow',
        ];
    @endphp

    <div class="row">
        @foreach ($permissions as $grupo => $groupPermissions)
            <div class="col-md-6 col-lg-4 mb-24">
                <div class="card radius-8 border h-100">
                    <div class="card-header py-12 px-16">
                        <h6 class="card-title mb-0 text-sm fw-semibold">
                            {{ $grupoLabels[$grupo] ?? ucfirst($grupo) }}
                        </h6>
                    </div>
                    <div class="card-body px-16 py-12">
                        @foreach ($groupPermissions as $permission)
                            <div class="form-check mb-8">
                                <input class="form-check-input" type="checkbox"
                                       name="permissions[]" value="{{ $permission->id }}"
                                       id="perm_{{ $permission->id }}"
                                       {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}>
                                <label class="form-check-label text-sm" for="perm_{{ $permission->id }}">
                                    {{ $permission->descricao }}
                                    <small class="text-secondary-light d-block">{{ $permission->nome }}</small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex align-items-center gap-3 mt-8">
        <a href="{{ route('tenant.roles.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-16 py-10 radius-8">Cancelar</a>
        <button type="submit" class="btn btn-primary text-sm btn-sm px-16 py-10 radius-8">Salvar Permissoes</button>
    </div>
</form>
@endsection
