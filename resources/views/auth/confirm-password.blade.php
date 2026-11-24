<x-auth.layout title="Confirmar contraseña">
    <div class="auth-heading-icon"><x-auth.icon name="lock" /></div>
    <div class="auth-intro">
        <h1>Confirma tu contraseña</h1>
        <p>Para continuar en esta área, confirma la contraseña de tu cuenta.</p>
    </div>
    <x-auth.feedback />
    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form" data-auth-form>
        @csrf
        <x-auth.field name="password" label="Contraseña" type="password" autocomplete="current-password" required />
        <x-auth.submit loading="Confirmando…">Confirmar contraseña</x-auth.submit>
    </form>
    <a href="{{ route('login') }}" class="auth-back"><x-auth.icon name="arrow-left" /> Volver al inicio de sesión</a>
</x-auth.layout>
