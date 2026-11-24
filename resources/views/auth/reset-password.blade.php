<x-auth.layout title="Restablecer contraseña">
    <div class="auth-heading-icon"><x-auth.icon name="lock" /></div>
    <div class="auth-intro">
        <h1>Tu nueva contraseña</h1>
        <p>Elige una contraseña para volver a tu dulcería.</p>
    </div>
    <x-auth.feedback />
    <form method="POST" action="{{ route('password.store') }}" class="auth-form" data-auth-form>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-auth.field name="email" label="Correo electrónico" type="email" :value="old('email', $request->email)" autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" required />
        <x-auth.field name="password" label="Nueva contraseña" type="password" autocomplete="new-password" hint="Usa al menos 8 caracteres." required />
        <x-auth.field name="password_confirmation" label="Confirmar contraseña" type="password" autocomplete="new-password" required />
        <x-auth.submit loading="Restableciendo contraseña…">Restablecer contraseña</x-auth.submit>
    </form>
    <a href="{{ route('login') }}" class="auth-back"><x-auth.icon name="arrow-left" /> Volver al inicio de sesión</a>
</x-auth.layout>
