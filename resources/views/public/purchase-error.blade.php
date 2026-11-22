@extends('layouts.public')

@section('title', 'Error en la compra | '.config('app.name', 'QuieroTIX.com'))
@section('meta_description', 'Tuvimos un problema al procesar tu compra. Intenta nuevamente.')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5 text-center">
                        <div class="error-icon mb-3">
                            <i class="bi bi-exclamation-circle-fill"></i>
                        </div>
                        <h1 class="h3 fw-bold mb-2">No pudimos procesar tu compra</h1>
                        <p class="text-muted mb-4">
                            Tuvimos un problema al procesar tu compra, intenta nuevamente.
                        </p>
                        <button type="button"
                                class="btn btn-brand px-4"
                                onclick="if (window.history.length > 1) { history.back(); } else { window.location.href = '{{ route('home') }}'; }">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .error-icon {
            font-size: 3.5rem;
            color: #dc3545;
            line-height: 1;
        }
    </style>
@endpush
