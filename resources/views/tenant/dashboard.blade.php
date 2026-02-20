@extends('layout.layout')

@php
    $title = 'Dashboard';
    $subTitle = 'Painel Principal';
@endphp

@section('title', 'Dashboard')

@section('content')
<div class="row gy-4">
    <div class="col-xxl-8">
        <div class="card radius-8 border-0">
            <div class="card-body p-24">
                <h6 class="fw-semibold text-lg mb-8">Bem-vindo ao {{ config('app.name') }}!</h6>
                <p class="text-secondary-light mb-16">
                    Olá, <strong>{{ auth()->user()->nome }}</strong>! Este é o seu painel de gestão contratual.
                </p>
                <p class="text-secondary-light mb-0">
                    Utilize o menu lateral para navegar entre os módulos do sistema.
                </p>
            </div>
        </div>
    </div>
    <div class="col-xxl-4">
        <div class="card radius-8 border-0">
            <div class="card-body p-24">
                <div class="d-flex align-items-center gap-3 mb-16">
                    <span class="w-44-px h-44-px bg-primary-50 text-primary-600 rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                        <iconify-icon icon="solar:info-circle-bold" class="icon text-xxl"></iconify-icon>
                    </span>
                    <div>
                        <h6 class="fw-semibold mb-0">Sistema em construção</h6>
                    </div>
                </div>
                <p class="text-secondary-light text-sm mb-0">
                    Os módulos de contratos, aditivos, alertas e relatórios serão disponibilizados nas próximas atualizações.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
