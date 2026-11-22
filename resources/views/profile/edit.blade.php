@extends('layouts.admin')

@section('title', 'Mi perfil')

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h3 fw-semibold mb-1">Mi perfil</h1>
            <p class="mb-0">Administra tu información personal y tus credenciales.</p>
        </div>

        <a href="{{ route('home') }}" class="btn btn-light fw-semibold">
            <i class="bi bi-house-door me-1" aria-hidden="true"></i>Volver al inicio
        </a>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 p-lg-5">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 p-lg-5">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
@endsection
