@php
    $locationId = $location['id'] ?? '';
    $locationName = $location['nombre'] ?? '';
    $locationPrice = $location['precio'] ?? '';
    $locationSeats = $location['total_asientos'] ?? '';
    $soldSeats = (int) ($location['vendidos'] ?? 0);
@endphp

<div class="location-row border rounded p-3 mb-3" data-location-row>
    <input type="hidden" name="locaciones[{{ $index }}][id]" value="{{ $locationId }}">

    <div class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Nombre de la locación</label>
            <input type="text"
                   name="locaciones[{{ $index }}][nombre]"
                   value="{{ $locationName }}"
                   class="form-control @error("locaciones.{$index}.nombre") is-invalid @enderror"
                   maxlength="100"
                   placeholder="Ej. General, VIP, Platea">
            @error("locaciones.{$index}.nombre")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Precio por ticket</label>
            <div class="input-group">
                <span class="input-group-text">Q</span>
                <input type="number"
                       name="locaciones[{{ $index }}][precio]"
                       value="{{ $locationPrice }}"
                       class="form-control @error("locaciones.{$index}.precio") is-invalid @enderror"
                       min="0"
                       max="99999999.99"
                       step="0.01"
                       inputmode="decimal">
                @error("locaciones.{$index}.precio")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Cantidad de asientos</label>
            <input type="number"
                   name="locaciones[{{ $index }}][total_asientos]"
                   value="{{ $locationSeats }}"
                   class="form-control @error("locaciones.{$index}.total_asientos") is-invalid @enderror"
                   min="1"
                   max="1000000"
                   step="1">
            @error("locaciones.{$index}.total_asientos")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-1">
            @if($soldSeats > 0)
                <button type="button" class="btn btn-outline-danger w-100" disabled
                        title="No puedes eliminar una locación con tickets vendidos">
                    <i class="bi bi-lock"></i>
                </button>
            @else
                <button type="button" class="btn btn-outline-danger w-100" data-remove-location>
                    <i class="bi bi-trash"></i>
                </button>
            @endif
        </div>
    </div>

    @if($soldSeats > 0)
        <div class="form-text text-warning mt-2">
            <i class="bi bi-ticket-perforated"></i>
            Esta locación tiene {{ number_format($soldSeats) }} tickets vendidos; no puede eliminarse ni reducir su capacidad por debajo de esa cantidad.
        </div>
    @endif

    @error("locaciones.{$index}.id")
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>
