<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dulcería POS — Restablecer contraseña</title>
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

        .card {
            width: 400px;
            background: #fdf5f5;
            border-radius: 24px;
            padding: 44px 40px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

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

        .input-box {
            position: relative;
            margin-bottom: 12px;
            width: 100%;
            text-align: left;
        }

        .input-box label {
            display: block;
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 4px;
            font-weight: 500;
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
            margin-top: 8px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-submit:hover { background: #6d0000; }
        .btn-submit:active { transform: scale(0.98); }

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

        .field-error {
            color: #dc3545;
            font-size: 0.78rem;
            margin-top: 4px;
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

<div class="card">
    <span class="logo">🔑</span>
    <div class="card-title">Restablecer contraseña</div>
    <div class="card-desc">Ingresa tu nueva contraseña a continuación</div>

    @if($errors->any())
    <div class="alert-error">❌ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" style="width:100%;">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="input-box">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email', $request->email) }}"
                   placeholder="correo@email.com"
                   required autofocus autocomplete="username">
            @error('email')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-box">
            <label for="password">Nueva contraseña</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••"
                   required autocomplete="new-password">
            @error('password')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-box">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   placeholder="••••••••"
                   required autocomplete="new-password">
            @error('password_confirmation')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-submit">Restablecer contraseña</button>
    </form>

    <div class="card-footer">
        Sistema protegido · <strong>Dulcería POS</strong>
    </div>
</div>

</body>
</html>
