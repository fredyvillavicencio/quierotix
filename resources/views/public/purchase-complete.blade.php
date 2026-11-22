@extends('layouts.public')

@section('title', 'Compra completada | '.config('app.name', 'QuieroTIX.com'))
@section('meta_description', 'Tu compra se completó correctamente. Pronto recibirás un email con tus tickets.')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5 text-center">
                        <div class="success-icon mb-3">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h1 class="h3 fw-bold mb-2">¡Compra completada!</h1>
                        <p class="text-muted mb-0">
                            Pronto recibirás un email con tus tickets.
                        </p>
                        <p class="text-muted small mb-0 mt-3">
                            Si no lo ves en tu bandeja de entrada en unos minutos, revisa la carpeta de spam o correo no deseado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
