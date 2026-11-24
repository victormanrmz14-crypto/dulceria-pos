@if(session('status'))
    <div class="auth-notice auth-notice-success" role="status">
        <strong>{{ session('status') === 'verification-link-sent' ? 'Correo de verificación enviado.' : 'Solicitud completada.' }}</strong>
        @if(session('status') === 'verification-link-sent')
            <p>Revisa tu bandeja de entrada para verificar tu correo.</p>
        @else
            <p>{{ session('status') }}</p>
        @endif
    </div>
@endif
@if($errors->any())
    <div class="auth-notice auth-notice-error" role="alert" tabindex="-1" data-error-summary>
        <strong>Revisa los datos para continuar.</strong>
        <ul>
            @foreach($errors->messages() as $field => $messages)
                <li><a href="#{{ $field }}">{{ $messages[0] }}</a></li>
            @endforeach
        </ul>
    </div>
@endif
