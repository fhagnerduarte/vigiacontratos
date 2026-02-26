@extends('layout.layout')

@php
    $title = 'Auditoria';
    $subTitle = 'Detalhe do registro de auditoria';
    $tipoLabel = match($tipo) {
        'alteracao' => 'Alteração',
        'login' => 'Login',
        'acesso_documento' => 'Acesso a Documento',
        default => $tipo,
    };
@endphp

@section('title', 'Auditoria - Detalhe')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Detalhe do Registro</h6>
    <a href="{{ route('tenant.auditoria.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-4">
        <iconify-icon icon="solar:arrow-left-bold" class="text-lg"></iconify-icon> Voltar
    </a>
</div>

<div class="row gy-4">
    {{-- Card principal --}}
    <div class="col-lg-8">
        <div class="card radius-8 border-0">
            <div class="card-header d-flex align-items-center gap-8">
                @if ($tipo === 'alteracao')
                    <span class="badge bg-info-focus text-info-main px-12 py-6 radius-4 fw-semibold">
                        <iconify-icon icon="solar:refresh-circle-bold" class="me-1"></iconify-icon> Alteração
                    </span>
                @elseif ($tipo === 'login')
                    @if ($registro->success)
                        <span class="badge bg-success-focus text-success-main px-12 py-6 radius-4 fw-semibold">
                            <iconify-icon icon="solar:login-2-bold" class="me-1"></iconify-icon> Login Bem-sucedido
                        </span>
                    @else
                        <span class="badge bg-danger-focus text-danger-main px-12 py-6 radius-4 fw-semibold">
                            <iconify-icon icon="solar:login-2-bold" class="me-1"></iconify-icon> Login Falhado
                        </span>
                    @endif
                @else
                    <span class="badge bg-warning-focus text-warning-main px-12 py-6 radius-4 fw-semibold">
                        <iconify-icon icon="solar:folder-open-bold" class="me-1"></iconify-icon> Acesso a Documento
                    </span>
                @endif
                <span class="text-secondary-light text-sm">{{ $registro->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            <tr>
                                <th class="text-nowrap" style="width: 180px;">Data/Hora</th>
                                <td>{{ $registro->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Usuário</th>
                                <td>{{ $registro->user?->nome ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>IP</th>
                                <td><code>{{ $registro->ip_address ?? '-' }}</code></td>
                            </tr>

                            @if ($tipo === 'alteracao')
                                <tr>
                                    <th>Perfil</th>
                                    <td>{{ $registro->role_nome ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Entidade</th>
                                    <td>{{ class_basename($registro->auditable_type) }} #{{ $registro->auditable_id }}</td>
                                </tr>
                                <tr>
                                    <th>Campo Alterado</th>
                                    <td class="fw-medium">{{ $registro->campo_alterado }}</td>
                                </tr>
                                <tr>
                                    <th>Valor Anterior</th>
                                    <td class="text-danger-main">{{ $registro->valor_anterior ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Valor Novo</th>
                                    <td class="text-success-main">{{ $registro->valor_novo ?? '—' }}</td>
                                </tr>
                            @elseif ($tipo === 'login')
                                <tr>
                                    <th>Resultado</th>
                                    <td>
                                        @if ($registro->success)
                                            <span class="text-success-main fw-medium">Bem-sucedido</span>
                                        @else
                                            <span class="text-danger-main fw-medium">Falhado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>User-Agent</th>
                                    <td><small class="text-secondary-light">{{ $registro->user_agent ?? '-' }}</small></td>
                                </tr>
                            @else
                                <tr>
                                    <th>Ação</th>
                                    <td>
                                        <span class="fw-medium">{{ ucfirst($registro->acao->value) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Documento</th>
                                    <td>{{ $registro->documento?->nome_arquivo ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tipo Documento</th>
                                    <td>{{ $registro->documento?->tipo_documento?->label() ?? '-' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Card de Contexto (mini-timeline) --}}
    <div class="col-lg-4">
        <div class="card radius-8 border-0">
            <div class="card-header">
                <h6 class="card-title mb-0">Atividade Relacionada</h6>
            </div>
            <div class="card-body p-0">
                @if ($contexto->isEmpty())
                    <div class="p-16 text-center">
                        <p class="text-secondary-light mb-0 text-sm">Nenhuma atividade relacionada encontrada.</p>
                    </div>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($contexto as $ctx)
                        <li class="d-flex gap-12 p-16 border-bottom">
                            <div class="w-32-px h-32-px bg-primary-100 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                @if ($tipo === 'alteracao')
                                    <iconify-icon icon="solar:refresh-circle-bold" class="text-primary-600 text-sm"></iconify-icon>
                                @elseif ($tipo === 'login')
                                    <iconify-icon icon="solar:login-2-bold" class="{{ $ctx->success ? 'text-success-main' : 'text-danger-main' }} text-sm"></iconify-icon>
                                @else
                                    <iconify-icon icon="solar:folder-open-bold" class="text-warning-main text-sm"></iconify-icon>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-sm mb-4">
                                    @if ($tipo === 'alteracao')
                                        <span class="fw-medium">{{ $ctx->campo_alterado }}</span>
                                        <br><small class="text-danger-main">{{ \Illuminate\Support\Str::limit($ctx->valor_anterior ?? '—', 30) }}</small>
                                        <small> → </small>
                                        <small class="text-success-main">{{ \Illuminate\Support\Str::limit($ctx->valor_novo ?? '—', 30) }}</small>
                                    @elseif ($tipo === 'login')
                                        <span class="fw-medium {{ $ctx->success ? 'text-success-main' : 'text-danger-main' }}">
                                            {{ $ctx->success ? 'Login bem-sucedido' : 'Login falhado' }}
                                        </span>
                                        <br><small class="text-secondary-light">{{ $ctx->ip_address }}</small>
                                    @else
                                        <span class="fw-medium">{{ ucfirst($ctx->acao->value) }}</span>
                                        <br><small class="text-secondary-light">{{ $ctx->user?->nome ?? '-' }}</small>
                                    @endif
                                </p>
                                <small class="text-secondary-light">{{ $ctx->created_at->format('d/m/Y H:i:s') }}</small>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
