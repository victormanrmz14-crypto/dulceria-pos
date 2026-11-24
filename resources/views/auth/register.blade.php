<x-auth.layout title="Registra tu dulcería" :wide="true">
    <div class="auth-intro">
        <h1>Registra tu dulcería</h1>
        <p>Crea el espacio de tu negocio y tu cuenta de administrador.</p>
    </div>
    <x-auth.feedback />
    <form method="POST" action="{{ route('register') }}" class="auth-form" data-auth-form>
        @csrf
        <fieldset class="auth-section">
            <legend>Datos de tu negocio</legend>
            <x-auth.field name="negocio" label="Nombre de tu dulcería" :value="old('negocio')" :optional="true" maxlength="200" autocomplete="organization" placeholder="Ej. Dulcería La Esperanza…" />
        </fieldset>
        <fieldset class="auth-section">
            <legend>Crea tu cuenta de administrador</legend>
            <div class="auth-account-grid">
                <x-auth.field name="nombre" label="Tu nombre" :value="old('nombre')" :optional="true" maxlength="150" autocomplete="name" />
                <x-auth.field name="email" label="Correo electrónico" type="email" :value="old('email')" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" maxlength="255" required />
            </div>
            <div class="auth-password-grid">
                <x-auth.field name="password" label="Contraseña" type="password" autocomplete="new-password" hint="Usa al menos 8 caracteres." required />
                <x-auth.field name="password_confirmation" label="Confirmar contraseña" type="password" autocomplete="new-password" required />
            </div>
        </fieldset>
        <x-auth.submit loading="Creando tu dulcería…">Crear mi dulcería</x-auth.submit>
    </form>
    <p class="auth-cashier-note"><strong>¿Eres cajero?</strong> Tu administrador crea tu cuenta. No necesitas registrar otro negocio.</p>
    <p class="auth-switch">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</x-auth.layout>
