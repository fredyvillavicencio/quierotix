<section>
    <header class="mb-4">
        <h2 class="h5 fw-semibold mb-1">Información del perfil</h2>
        <p class="text-muted mb-0">
            Actualiza la información de tu cuenta y tu correo electrónico.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="vstack gap-3">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="form-label">Nombre</label>
            <input id="name"
                   name="name"
                   type="text"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name) }}"
                   required
                   autofocus
                   autocomplete="name">

            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="email" class="form-label">Correo electrónico</label>
            <input id="email"
                   name="email"
                   type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}"
                   required
                   autocomplete="username">

            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="form-text mb-1">
                        Tu correo electrónico no está verificado.
                        <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                            Haz clic aquí para reenviar el correo de verificación.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success small mb-0">
                            Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-brand px-4">Guardar</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">Guardado.</span>
            @endif
        </div>
    </form>
</section>
