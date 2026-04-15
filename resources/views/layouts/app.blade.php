<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dulcería POS')</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --dark-red:   #8B0000;
            --medium-red: #A52A2A;
            --deep-red:   #580000;
            --accent:     #ff7043;
            --sidebar-w:  240px;
            --white:      #ffffff;
            --text-soft:  #ffcccc;
            --bg:         #f4f4f4;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background: var(--bg);
        }

        aside {
            width: var(--sidebar-w);
            min-width: var(--sidebar-w);
            height: 100vh;
            background: var(--dark-red);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            background: var(--medium-red);
            padding: 28px 0 24px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--white);
        }

        .sidebar-header span {
            display: block;
            font-size: 0.82rem;
            color: var(--text-soft);
            margin-top: 6px;
        }

        nav { flex: 1; padding: 8px 0; overflow-y: auto; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            height: 58px;
            padding: 0 0 0 36px;
            background: transparent;
            border: none;
            color: var(--white);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            position: relative;
            transition: background 0.18s ease, padding-left 0.18s ease;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%) scaleY(0);
            width: 3px; height: 55%;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
            transition: transform 0.18s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.12);
            padding-left: 42px;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            transform: translateY(-50%) scaleY(1);
        }

        .nav-submenu { background: rgba(0,0,0,0.15); }

        .nav-sublink {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            height: 46px;
            padding: 0 0 0 60px;
            background: transparent;
            border: none;
            color: var(--text-soft);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            position: relative;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .nav-sublink:hover,
        .nav-sublink.active {
            background: rgba(255,255,255,0.10);
            color: var(--white);
        }

        .btn-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 58px;
            background: var(--deep-red);
            border: none;
            color: var(--white);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.18s ease;
        }

        .btn-logout:hover { background: #3d0000; }

        main {
            flex: 1;
            overflow-y: auto;
            padding: 40px;
            background: var(--bg);
        }

        main.ventas-layout {
            overflow: hidden;
            padding: 24px 32px;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-error   { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
    </style>
    @livewireStyles
</head>
<body>

<aside>
    <div class="sidebar-header">
        <h1>🍬 Dulcería POS</h1>
        <span>{{ Auth::user()->nombre ?? 'Usuario' }}</span>
    </div>

    <nav>
        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            🏠 &nbsp; Inicio
        </a>

        <a href="{{ route('ventas.index') }}"
           class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
            🛒 &nbsp; Ventas
        </a>

        {{-- Caja (visible para admin y cajero) --}}
        <div x-data="{ open: {{ request()->routeIs('caja.*') || request()->routeIs('cortes.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="nav-link {{ request()->routeIs('caja.*') || request()->routeIs('cortes.*') ? 'active' : '' }}"
                    style="justify-content:space-between; padding-right:20px;">
                <span>🏦 &nbsp; Caja</span>
                <span x-text="open ? '▲' : '▼'" style="font-size:0.7rem;"></span>
            </button>
            <div x-show="open" x-transition class="nav-submenu">
                <a href="{{ route('caja.index') }}"
                   class="nav-sublink {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                    💰 &nbsp; Caja Actual
                </a>
                @if(auth()->user()->rol === 'admin')
                <a href="{{ route('cortes.index') }}"
                   class="nav-sublink {{ request()->routeIs('cortes.*') ? 'active' : '' }}">
                    📋 &nbsp; Cortes Históricos
                </a>
                @endif
            </div>
        </div>

        @if(auth()->user()->rol === 'admin')

            <a href="{{ route('productos.index') }}"
               class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                🍬 &nbsp; Productos
            </a>

            <a href="{{ route('usuarios.index') }}"
               class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                👥 &nbsp; Usuarios
            </a>

            <a href="{{ route('reportes.index') }}"
                class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                📊 &nbsp; Reportes
            </a>

            <div x-data="{ open: {{ request()->routeIs('catalogos.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="nav-link {{ request()->routeIs('catalogos.*') ? 'active' : '' }}"
                        style="justify-content:space-between; padding-right:20px;">
                    <span>📝 &nbsp; Catálogos</span>
                    <span x-text="open ? '▲' : '▼'" style="font-size:0.7rem;"></span>
                </button>

                <div x-show="open" x-transition class="nav-submenu">
                    <a href="{{ route('catalogos.categorias.index') }}"
                       class="nav-sublink {{ request()->routeIs('catalogos.categorias.*') ? 'active' : '' }}">
                        🏷️ &nbsp; Categorías
                    </a>
                    <a href="{{ route('catalogos.marcas.index') }}"
                       class="nav-sublink {{ request()->routeIs('catalogos.marcas.*') ? 'active' : '' }}">
                        🏭 &nbsp; Marcas
                    </a>
                    <a href="{{ route('catalogos.proveedores.index') }}"
                       class="nav-sublink {{ request()->routeIs('catalogos.proveedores.*') ? 'active' : '' }}">
                        🚚 &nbsp; Proveedores
                    </a>
                </div>
            </div>

        @endif
    </nav>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout">
            🚪 &nbsp; Cerrar Sesión
        </button>
    </form>
</aside>

<main class="{{ request()->routeIs('ventas.index') ? 'ventas-layout' : '' }}">
    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">❌ {{ session('error') }}</div>
    @endif

    @yield('content')
</main>

@livewireScripts
</body>
</html>