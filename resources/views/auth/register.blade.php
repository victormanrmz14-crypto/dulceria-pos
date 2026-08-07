<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dulcería POS — Crear cuenta</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #8B0000;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
        }

        .circle {
            position: fixed;
            border-radius: 50%;
            border: 50px solid rgba(255,255,255,0.06);
            pointer-events: none;
        }
        .circle-1 { width: 500px; height: 500px; top: -150px; left: -150px; }
        .circle-2 { width: 400px; height: 400px; bottom: -120px; right: -120px; }

        .card {
            background: #fdf5f5;
            border-radius: 24px;
            padding: 44px 40px;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 2;
        }

        .logo { font-size: 36px; display: block; text-align: center; margin-bottom: 10px; }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.55rem;
            color: #8B0000;
            text-align: center;
            margin-bottom: 4px;
        }

        .card-desc {
            color: #bbb;
            font-size: 0.82rem;
            text-align: center;
            margin-bottom: 28px;
        }

        .input-group { margin-bottom: 14px; }

        .input-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            letter-spacing: 0.02em;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #e8e0e0;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            color: #333;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-group input:focus {
            border-color: #8B0000;
            box-shadow: 0 0 0 3px rgba(139,0,0,0.08);
        }

        .input-group input::placeholder { color: #bbb; }

        .field-error {
            color: #dc3545;
            font-size: 0.76rem;
            margin-top: 4px;
        }

        .alert-error {
            background: #fff0f0;
            border-left: 3px solid #dc3545;
            color: #721c24;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 16px;
        }

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
            margin-top: 6px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-submit:hover { background: #6d0000; }
        .btn-submit:active { transform: scale(0.98); }

        .card-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.82rem;
            color: #999;
        }

        .card-footer a {
            color: #8B0000;
            font-weight: 600;
            text-decoration: none;
        }

        .card-footer a:hover { text-decoration: underline; }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 6px 0 18px;
            color: #ccc;
            font-size: 0.75rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e8e0e0;
        }
    </style>
</head>
<body>

<div class="circle circle-1"></div>
<div class="circle circle-2"></div>

<div class="card">
    <span class="logo">🍬</span>
    <div class="card-title">Crea tu cuenta</div>
    <div class="card-desc">Empieza a gestionar tu dulcería hoy</div>

    @if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="input-group">
            <label for="negocio">Nombre de tu dulcería</label>
            <input type="text" id="negocio" name="negocio"
                   value="{{ old('negocio') }}"
                   placeholder="Ej. Dulcería La Esperanza"
                   autofocus autocomplete="organization">
            @error('negocio')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label for="nombre">Tu nombre</label>
            <input type="text" id="nombre" name="nombre"
                   value="{{ old('nombre') }}"
                   placeholder="Ej. María García"
                   autocomplete="given-name">
            @error('nombre')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="tu@correo.com"
                   required autocomplete="email">
            @error('email')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="divider">Elige una contraseña</div>

        <div class="input-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password"
                   placeholder="Mínimo 8 caracteres"
                   required autocomplete="new-password">
            @error('password')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   placeholder="Repite tu contraseña"
                   required autocomplete="new-password">
        </div>

        <button type="submit" class="btn-submit">Crear cuenta</button>
    </form>

    <div class="card-footer">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
    </div>
</div>

</body>
</html>
