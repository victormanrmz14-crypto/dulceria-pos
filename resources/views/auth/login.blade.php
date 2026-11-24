<x-auth.layout title="Iniciar sesión">
    <div class="auth-intro">
        <h1>Bienvenido de nuevo</h1>
        <p>Inicia sesión para continuar con tu dulcería.</p>
    </div>
    <x-auth.feedback />
    <form method="POST" action="{{ route('login') }}" class="auth-form" data-auth-form>
        @csrf
        <x-auth.field name="email" label="Usuario o correo electrónico" :value="old('email')" autocomplete="username" required spellcheck="false" autocapitalize="none" />
        <x-auth.field name="password" label="Contraseña" type="password" autocomplete="current-password" required />
        <a href="{{ route('password.request') }}" class="auth-forgot" data-password-request>¿Olvidaste tu contraseña?</a>
        <x-auth.submit loading="Iniciando sesión…">Iniciar sesión</x-auth.submit>
    </form>
    <p class="auth-switch">¿Aún no tienes cuenta? <a href="{{ route('register') }}">Registra tu dulcería</a></p>
</x-auth.layout>
