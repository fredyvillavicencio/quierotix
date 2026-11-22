@php
    $flashMessages = [
        'success' => ['style' => 'success', 'icon' => 'check-circle-fill', 'label' => '¡Éxito!'],
        'error' => ['style' => 'danger', 'icon' => 'exclamation-triangle-fill', 'label' => 'Error:'],
        'warning' => ['style' => 'warning', 'icon' => 'exclamation-triangle-fill', 'label' => 'Advertencia:'],
        'info' => ['style' => 'info', 'icon' => 'info-circle-fill', 'label' => 'Información:'],
    ];
@endphp

@foreach($flashMessages as $key => $flash)
    @if(session()->has($key))
        <div class="alert alert-{{ $flash['style'] }} alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-{{ $flash['icon'] }} me-2"></i>
            <strong>{{ $flash['label'] }}</strong> {{ session($key) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
@endforeach
