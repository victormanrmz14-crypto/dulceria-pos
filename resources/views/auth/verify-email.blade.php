<x-auth.layout title="Verifica tu correo">
    <div class="auth-heading-icon"><x-auth.icon name="mail" /></div>
    <div class="auth-intro">
        <h1>Verifica tu correo</h1>
        <p>Abre el enlace que enviamos a tu correo para verificar tu cuenta. Si no lo encuentras, revisa la carpeta de correo no deseado o solicita otro.</p>
    </div>
    <x-auth.feedback />
    <form method="POST" action="{{ route('verification.send') }}" class="auth-form" data-auth-form>
        @csrf
        <x-auth.submit loading="Enviando correo…">Reenviar correo de verificación</x-auth.submit>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="auth-secondary-form">
        @csrf
        <button type="submit" class="auth-secondary-button">Cerrar sesión</button>
    </form>
</x-auth.layout>
