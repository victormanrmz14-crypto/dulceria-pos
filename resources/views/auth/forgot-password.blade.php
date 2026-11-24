<x-auth.layout title="Recuperar contraseña">
    <div class="auth-heading-icon"><x-auth.icon name="mail" /></div>
    <div class="auth-intro">
        <h1>Recupera tu acceso</h1>
        <p>Ingresa el correo de tu cuenta. Te enviaremos un enlace para restablecer tu contraseña.</p>
    </div>
    <x-auth.feedback />
    <form method="POST" action="{{ route('password.email') }}" class="auth-form" data-auth-form>
        @csrf
        <x-auth.field name="email" label="Correo electrónico" type="email" :value="old('email')" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" required />
        <x-auth.submit loading="Enviando enlace…">Enviar enlace de recuperación</x-auth.submit>
    </form>
    <a href="{{ route('login') }}" class="auth-back"><x-auth.icon name="arrow-left" /> Volver al inicio de sesión</a>
</x-auth.layout>
