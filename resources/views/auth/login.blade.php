<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dulcería POS — Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #8B0000;
            overflow: hidden;
            position: relative;
        }

        .circle {
            position: fixed;
            border-radius: 50%;
            border: 50px solid rgba(255,255,255,0.06);
            pointer-events: none;
        }
        .circle-1 { width: 500px; height: 500px; top: -150px; left: -150px; }
        .circle-2 { width: 400px; height: 400px; bottom: -120px; right: -120px; }
        .circle-3 { width: 250px; height: 250px; top: 50%; left: 5%; transform: translateY(-50%); }

        .flip-container {
            width: 400px;
            height: 480px;
            perspective: 1200px;
            position: relative;
            z-index: 2;
        }

        .flip-container.flipped .flipper {
            transform: rotateY(180deg);
        }

        .flipper {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.55s cubic-bezier(.22,1,.36,1);
            will-change: transform;
        }

        .front, .back {
            position: absolute;
            inset: 0;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            background: #fdf5f5;
            border-radius: 24px;
            padding: 44px 40px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .back { transform: rotateY(180deg); }

        .logo {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: #8B0000;
            margin-bottom: 4px;
        }

        .card-desc {
            color: #bbb;
            font-size: 0.82rem;
            margin-bottom: 28px;
        }

        .back-desc {
            color: #999;
            font-size: 0.82rem;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .input-box {
            position: relative;
            margin-bottom: 12px;
            width: 100%;
        }

        .input-box input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #e8e0e0;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            color: #333;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-box input:focus {
            border-color: #8B0000;
            box-shadow: 0 0 0 3px rgba(139,0,0,0.08);
        }

        .input-box input::placeholder { color: #bbb; }

        .forgot {
            text-align: right;
            margin-bottom: 16px;
            margin-top: -4px;
            width: 100%;
        }

        .forgot button {
            background: none;
            border: none;
            font-size: 0.78rem;
            color: #8B0000;
            cursor: pointer;
            opacity: 0.7;
            font-family: 'DM Sans', sans-serif;
            transition: opacity 0.15s;
            padding: 0;
        }

        .forgot button:hover { opacity: 1; }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: #8B0000;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-submit:hover { background: #6d0000; }
        .btn-submit:active { transform: scale(0.98); }

        .btn-back {
            background: none;
            border: none;
            font-size: 0.82rem;
            color: #8B0000;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            margin-top: 16px;
            opacity: 0.7;
            transition: opacity 0.15s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back:hover { opacity: 1; }

        .alert-error {
            background: #fff0f0;
            border-left: 3px solid #dc3545;
            color: #721c24;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 16px;
            text-align: left;
            width: 100%;
        }

        .alert-success {
            background: #f0fff4;
            border-left: 3px solid #28a745;
            color: #155724;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 16px;
            text-align: left;
            width: 100%;
        }

        .card-footer {
            margin-top: 20px;
            font-size: 0.75rem;
            color: #ccc;
        }

        .card-footer strong { color: #8B0000; }
    </style>
</head>
<body>

<div class="circle circle-1"></div>
<div class="circle circle-2"></div>
<div class="circle circle-3"></div>

<div class="flip-container" id="flipContainer">
    <div class="flipper">

        {{-- FRENTE: Login --}}
        <div class="front">
            <span class="logo">🍬</span>
            <div class="card-title">Dulcería POS</div>
            <div class="card-desc">Accede a tu sistema de ventas</div>

            @if($errors->any() && !session('status'))
            <div class="alert-error">❌ {{ $errors->first() }}</div>
            @endif

            <x-auth-session-status :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" style="width:100%;">
                @csrf

                <div class="input-box">
                    <input type="text" name="email"
                           value="{{ old('email') }}"
                           placeholder="Usuario o email"
                           required autocomplete="username">
                </div>

                <div class="input-box">
                    <input type="password" name="password"
                           placeholder="Contraseña"
                           required autocomplete="current-password">
                </div>

                <div class="forgot">
                    <button type="button" onclick="flip()">
                        ¿Olvidaste tu contraseña?
                    </button>
                </div>

                <button type="submit" class="btn-submit">Iniciar sesión</button>

            </form>

            <div class="card-footer">
                Sistema protegido · <strong>Dulcería POS</strong>
            </div>
        </div>

        {{-- REVERSO: Recuperar contraseña --}}
        <div class="back">
            <span style="font-size:36px; margin-bottom:12px;">🔑</span>
            <div class="card-title" style="font-size:1.4rem;">Recuperar acceso</div>
            <div class="back-desc">
                Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.
            </div>

            @if(session('status'))
            <div class="alert-success">✅ Te hemos enviado el enlace para restablecer tu contraseña.</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" style="width:100%;">
                @csrf

                <div class="input-box">
                    <input type="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="correo@email.com"
                           required>
                </div>

                @error('email')
                <div class="alert-error">❌ {{ $message }}</div>
                @enderror

                <button type="submit" class="btn-submit" style="margin-top:8px;">
                    Enviar enlace
                </button>

            </form>

            <button class="btn-back" onclick="unflip()">
                ← Volver al inicio de sesión
            </button>
        </div>

    </div>
</div>

<script>
    function flip() {
        document.getElementById('flipContainer').classList.add('flipped');
    }
    function unflip() {
        document.getElementById('flipContainer').classList.remove('flipped');
    }

    @if(session('status') || ($errors->has('email') && !request()->is('login')))
    document.addEventListener('DOMContentLoaded', () => flip());
    @endif
</script>

</body>
</html>