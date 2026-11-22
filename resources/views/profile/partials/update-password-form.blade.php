<section>
    <header class="mb-4">
        <h2 class="h5 fw-semibold mb-1">Cambiar contraseña</h2>
        <p class="text-muted mb-0">
            Usa una contraseña larga y aleatoria para mantener tu cuenta segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="vstack gap-3">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="form-label">Contraseña actual</label>
            <input id="update_password_current_password"
                   name="current_password"
                   type="password"
                   class="form-control @if ($errors->updatePassword->has('current_password')) is-invalid @endif"
                   autocomplete="current-password">

            @if ($errors->updatePassword->has('current_password'))
                <div class="invalid-feedback">
                    {{ $errors->updatePassword->first('current_password') }}
                </div>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="form-label">Nueva contraseña</label>
            <input id="update_password_password"
                   name="password"
                   type="password"
                   class="form-control @if ($errors->updatePassword->has('password')) is-invalid @endif"
                   autocomplete="new-password">

            @if ($errors->updatePassword->has('password'))
                <div class="invalid-feedback">
                    {{ $errors->updatePassword->first('password') }}
                </div>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="form-label">Confirmar contraseña</label>
            <input id="update_password_password_confirmation"
                   name="password_confirmation"
                   type="password"
                   class="form-control @if ($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
                   autocomplete="new-password">

            @if ($errors->updatePassword->has('password_confirmation'))
                <div class="invalid-feedback">
                    {{ $errors->updatePassword->first('password_confirmation') }}
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-brand px-4">Guardar</button>

            @if (session('status') === 'password-updated')
                <span class="text-success small">Guardado.</span>
            @endif
        </div>
    </form>
</section>
