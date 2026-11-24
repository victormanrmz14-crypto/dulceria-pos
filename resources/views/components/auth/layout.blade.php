@props(['title', 'wide' => false])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f8fa">
    <meta name="color-scheme" content="light">
    <title>{{ $title }} · Dulcería POS</title>
    <x-platform-favicons />
    <link rel="preload" href="{{ asset('brand/fonts/dm-sans-latin-variable.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('brand/tokens.css') }}?v=figma-2132">
    @vite(['resources/css/auth.css', 'resources/js/auth.js'])
</head>
<body class="platform-brand auth-page">
    <a href="#auth-content" class="auth-skip">Saltar al formulario</a>
    <div class="auth-frame">
        <aside class="auth-identity" aria-label="Acerca de Dulcería POS">
            <x-platform-logo />
            <div class="auth-story">
                <h2>El orden que tu<br>dulcería necesita.</h2>
                <p>Ventas, inventario y caja.<br>Un mismo lugar para llevar tu negocio.</p>
                <div class="auth-composition" aria-hidden="true">
                    <div class="auth-composition-label"><span class="auth-dot"></span> Tu negocio, conectado</div>
                    <div class="auth-diagram">
                        <div class="auth-diagram-rail"><span></span><span></span><span></span><span></span></div>
                        <div class="auth-diagram-content">
                            <div class="auth-diagram-heading"><span>Mi dulcería</span><span class="auth-diagram-avatar"></span></div>
                            <div class="auth-diagram-modules">
                                <div><x-auth.icon name="store" /><span>Ventas</span><i></i></div>
                                <div><x-auth.icon name="box" /><span>Inventario</span><i></i></div>
                                <div><x-auth.icon name="wallet" /><span>Caja</span><i></i></div>
                            </div>
                            <div class="auth-diagram-line"></div><div class="auth-diagram-line short"></div>
                        </div>
                    </div>
                    <div class="auth-brand-seal"><img src="{{ asset('brand/symbol-color.svg') }}" width="168" height="99" alt=""></div>
                    <span class="auth-composition-caption">Una vista de todo lo que importa.</span>
                </div>
            </div>
            <p class="auth-identity-footer">Pensado para el día a día de tu dulcería.</p>
        </aside>
        <div class="auth-workspace">
            <header class="auth-header">
                <x-platform-logo compact class="auth-mobile-brand" />
                <a href="{{ url('/') }}" class="auth-home"><x-auth.icon name="arrow-left" /> Volver al sitio</a>
            </header>
            <main id="auth-content" class="auth-main" tabindex="-1">
                <div @class(['auth-form-wrap', 'auth-form-wrap-wide' => $wide])>
                    {{ $slot }}
                </div>
            </main>
            <footer class="auth-footer"><span translate="no">Dulcería POS</span><span>Tu negocio, en orden.</span></footer>
        </div>
    </div>
</body>
</html>
