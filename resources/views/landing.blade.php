<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dulcería POS — Punto de venta para dulcerías y abarrotes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; background: #f4f4f5; font-family: 'DM Sans', system-ui, sans-serif; }
        img, svg { display: block; max-width: 100%; }
        a { color: #8B0000; }
        a:hover { color: #6d0000; }

        /* ---- hovers ---- */
        .lp-nav:hover  { color: #fff !important; }
        .lp-nav2:hover { color: #ffcccc !important; }
        .lp-btn:hover  { background: #ff8a63 !important; }
        .lp-ghost:hover { background: rgba(255,255,255,0.12) !important; }

        /* ---- animación hero ---- */
        @keyframes flotar {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-6px); }
        }

        /* ============================================================
           RESPONSIVE
           Base: escritorio. Los breakpoints colapsan layouts progresivamente.
        ============================================================ */

        /* 900 px — tabletas y abajo */
        @media (max-width: 900px) {
            /* Colapsa todos los grids de 2 y 3 columnas */
            .lp-grid-2, .lp-grid-3 { grid-template-columns: 1fr !important; }

            /* Hero: una sola columna con menos padding vertical */
            .lp-hero-grid {
                grid-template-columns: 1fr !important;
                padding: 52px 24px 60px !important;
                gap: 36px !important;
            }
            .lp-hero-h1 { font-size: 40px !important; }
            .lp-hero-p  { font-size: 16px !important; }

            /* Las maquetas del dashboard y POS se esconden: son decorativas
               y resultan ilegibles e inmanejables en pantallas pequeñas. */
            .lp-mock { display: none !important; }

            /* Las secciones de 2-col donde una columna ERA la maqueta
               dejan de ser grid y el texto ocupa todo el ancho */
            .lp-pos-grid, .lp-preview-grid {
                display: block !important;
            }

            /* Padding de sección reducido */
            .lp-section-pad { padding: 60px 24px !important; }
            .lp-h2 { font-size: 30px !important; }

            /* Franja de 3 beneficios */
            .lp-benefits-grid { padding: 36px 24px !important; }

            /* Roles: las dos tarjetas en columna */
            .lp-roles-grid { gap: 16px !important; }
        }

        /* 600 px — móviles */
        @media (max-width: 600px) {
            .lp-hero-h1  { font-size: 32px !important; }
            .lp-hero-p   { font-size: 15px !important; }
            .lp-h2       { font-size: 26px !important; }
            .lp-cta-h2   { font-size: 30px !important; }

            /* Nav: oculta los anclas de sección, deja solo login/register */
            .lp-nav-links { display: none !important; }

            /* Botones hero en columna */
            .lp-hero-buttons { flex-direction: column !important; }

            /* Badges y bullets en el hero */
            .lp-hero-bullets { gap: 12px !important; flex-direction: column !important; }

            /* Footer en columna */
            .lp-footer-inner { flex-direction: column !important; text-align: center !important; }

            /* Tarjetas de roles: padding reducido en móvil */
            .lp-rol-card { padding: 28px 22px !important; }

            /* Sección de módulos: reducir padding interno */
            .lp-modulo-card { padding: 22px 18px !important; }
        }

        /* 400 px — móviles muy pequeños */
        @media (max-width: 400px) {
            .lp-hero-h1 { font-size: 28px !important; }
        }
    </style>
</head>
<body>
<div style="font-family:'DM Sans',system-ui,sans-serif;color:#2b2b2b;background:#f4f4f5;overflow-x:hidden">

  {{-- ================================================================ HEADER --}}
  <header style="position:sticky;top:0;z-index:50;background:#8B0000;color:#fff">
    <div style="max-width:1180px;margin:0 auto;padding:14px 28px;display:flex;align-items:center;justify-content:space-between;gap:16px">
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        <span style="font-size:24px">🍬</span>
        <span style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;white-space:nowrap">Dulcería POS</span>
      </div>
      <nav style="display:flex;align-items:center;gap:20px;font-size:14px;font-weight:500;flex-wrap:wrap;justify-content:flex-end">
        <a href="#modulos"        class="lp-nav lp-nav-links" style="color:#ffcccc;text-decoration:none;white-space:nowrap">Módulos</a>
        <a href="#punto-de-venta" class="lp-nav lp-nav-links" style="color:#ffcccc;text-decoration:none;white-space:nowrap">Punto de venta</a>
        <a href="#roles"          class="lp-nav lp-nav-links" style="color:#ffcccc;text-decoration:none;white-space:nowrap">Roles</a>
        <a href="{{ route('login') }}"    class="lp-nav2" style="color:#fff;text-decoration:none;padding:10px 6px;font-weight:600;white-space:nowrap">Iniciar sesión</a>
        <a href="{{ route('register') }}" class="lp-btn"  style="background:#ff7043;color:#fff;text-decoration:none;padding:10px 18px;border-radius:10px;font-weight:600;white-space:nowrap">Registrarse</a>
      </nav>
    </div>
  </header>

  {{-- ================================================================ HERO --}}
  <section style="background:#8B0000;color:#fff;position:relative;overflow:hidden">
    {{-- decoración de círculos (mismo recurso que login.blade.php) --}}
    <div style="position:absolute;width:500px;height:500px;border-radius:50%;border:50px solid rgba(255,255,255,0.06);top:-180px;left:-160px;pointer-events:none"></div>
    <div style="position:absolute;width:400px;height:400px;border-radius:50%;border:50px solid rgba(255,255,255,0.06);bottom:-160px;right:-120px;pointer-events:none"></div>

    <div class="lp-hero-grid" style="max-width:1180px;margin:0 auto;padding:76px 28px 96px;display:grid;grid-template-columns:minmax(0,0.9fr) minmax(0,1.1fr);gap:56px;align-items:center;position:relative">

      {{-- Columna texto --}}
      <div data-reveal="1">
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);border-radius:999px;padding:7px 15px;font-size:13px;font-weight:500;color:#ffcccc;margin-bottom:22px">
          Hecho en México 🇲🇽 para negocios de barrio
        </div>
        <h1 class="lp-hero-h1" style="font-family:'Playfair Display',serif;font-size:56px;line-height:1.08;margin:0 0 20px;text-wrap:pretty">
          Tu dulcería, cuadrada al centavo, todos los días.
        </h1>
        <p class="lp-hero-p" style="font-size:19px;line-height:1.6;color:#ffcccc;margin:0 0 32px;max-width:30em">
          Cobra rápido, controla tu inventario y cierra el turno sin sacar la calculadora. Un sistema de punto de venta pensado para dulcerías y tiendas de abarrotes.
        </p>
        <div class="lp-hero-buttons" style="display:flex;flex-wrap:wrap;gap:14px;margin-bottom:34px">
          <a href="{{ route('register') }}" class="lp-btn"   style="background:#ff7043;color:#fff;text-decoration:none;padding:15px 28px;border-radius:12px;font-weight:600;font-size:16px;white-space:nowrap">Registrarse gratis</a>
          <a href="{{ route('login') }}"    class="lp-ghost" style="border:1.5px solid rgba(255,255,255,0.35);color:#fff;text-decoration:none;padding:15px 28px;border-radius:12px;font-weight:600;font-size:16px;white-space:nowrap">Iniciar sesión</a>
        </div>
        <div class="lp-hero-bullets" style="display:flex;gap:34px;flex-wrap:wrap;font-size:14px;color:#ffcccc">
          <div style="display:flex;align-items:center;gap:8px">✅ Ventas en efectivo y tarjeta</div>
          <div style="display:flex;align-items:center;gap:8px">✅ Cortes de turno automáticos</div>
        </div>
      </div>

      {{-- Columna maqueta dashboard --}}
      <div class="lp-mock" data-reveal="1" style="border-radius:16px;overflow:hidden;box-shadow:0 30px 70px rgba(0,0,0,0.35)">
        @include('landing.mock-dashboard')
      </div>

    </div>
  </section>

  {{-- ================================================================ FRANJA 3 BENEFICIOS --}}
  <section style="background:#fff;border-bottom:1px solid #ececec">
    <div class="lp-grid-3 lp-benefits-grid" style="max-width:1180px;margin:0 auto;padding:44px 28px;display:grid;grid-template-columns:repeat(3,1fr);gap:40px">
      <div data-reveal="1">
        <div style="font-family:'Playfair Display',serif;font-size:19px;color:#8B0000;margin-bottom:6px">Sin sobreventas</div>
        <div style="font-size:14.5px;line-height:1.6;color:#6c757d">El stock se descuenta al momento de cobrar, aunque dos cajas vendan el mismo producto a la vez.</div>
      </div>
      <div data-reveal="1">
        <div style="font-family:'Playfair Display',serif;font-size:19px;color:#8B0000;margin-bottom:6px">Cambio calculado solo</div>
        <div style="font-size:14.5px;line-height:1.6;color:#6c757d">Capturas el monto recibido y el sistema te dice el cambio exacto. Menos errores en el mostrador.</div>
      </div>
      <div data-reveal="1">
        <div style="font-family:'Playfair Display',serif;font-size:19px;color:#8B0000;margin-bottom:6px">Cierre de turno en un clic</div>
        <div style="font-size:14.5px;line-height:1.6;color:#6c757d">Efectivo, tarjeta, ingresos y retiros sumados por turno, con el monto real contado.</div>
      </div>
    </div>
  </section>

  {{-- ================================================================ MÓDULOS --}}
  <section id="modulos" class="lp-section-pad" style="max-width:1180px;margin:0 auto;padding:86px 28px">
    <div data-reveal="1" style="max-width:620px;margin-bottom:44px">
      <h2 class="lp-h2" style="font-family:'Playfair Display',serif;font-size:40px;color:#8B0000;margin:0 0 14px;line-height:1.15">
        Todo lo que hace tu negocio, en un solo lugar
      </h2>
      <p style="font-size:17px;line-height:1.65;color:#6c757d;margin:0">
        Siete módulos que cubren el día completo: desde la primera venta de la mañana hasta el corte de la noche.
      </p>
    </div>

    <div class="lp-grid-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px">
      @foreach ([
        ['🛒','Punto de venta','Búsqueda de productos en tiempo real, carrito dinámico y cálculo de cambio automático. Efectivo o tarjeta.'],
        ['💵','Caja','Registro de ingresos y retiros de efectivo, con validación para que nunca se retire más de lo disponible.'],
        ['🧾','Cortes de turno','Cierre por turno con resumen de efectivo, tarjeta, ingresos, retiros y el monto real contado.'],
        ['📦','Inventario','Alta y edición de productos con alerta visual cuando el stock queda por debajo del mínimo.'],
        ['📊','Reportes','Gráficas de ventas por día, semana o mes, con tabla detallada por fecha y método de pago.'],
        ['🏷️','Catálogos y usuarios','Categorías, marcas y proveedores. Alta, edición y desactivación de cajeros por el administrador.'],
      ] as [$icon, $titulo, $texto])
      <div class="lp-modulo-card" data-reveal="1" style="background:#fff;border-radius:14px;padding:28px 26px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #8B0000">
        <div style="font-size:26px;margin-bottom:12px">{{ $icon }}</div>
        <div style="font-family:'Playfair Display',serif;font-size:20px;color:#8B0000;font-weight:700;margin-bottom:8px">{{ $titulo }}</div>
        <div style="font-size:14.5px;line-height:1.65;color:#6c757d">{{ $texto }}</div>
      </div>
      @endforeach
    </div>
  </section>

  {{-- ================================================================ PUNTO DE VENTA --}}
  <section id="punto-de-venta" style="background:#fff;border-top:1px solid #ececec;border-bottom:1px solid #ececec">
    <div class="lp-pos-grid lp-section-pad" style="max-width:1180px;margin:0 auto;padding:86px 28px;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.25fr);gap:56px;align-items:center">
      <div data-reveal="1">
        <div style="font-size:13px;font-weight:600;letter-spacing:0.4px;text-transform:uppercase;color:#ff7043;margin-bottom:14px">Punto de venta</div>
        <h2 class="lp-h2" style="font-family:'Playfair Display',serif;font-size:38px;color:#8B0000;margin:0 0 18px;line-height:1.15">
          Cobrar es escribir el nombre y dar clic
        </h2>
        <p style="font-size:17px;line-height:1.65;color:#6c757d;margin:0 0 26px">
          La búsqueda responde mientras escribes. Agregas el producto al carrito, eliges efectivo o tarjeta y cobras. El carrito no se pierde si se recarga la pantalla.
        </p>
        <div style="display:flex;flex-direction:column;gap:14px">
          <div style="display:flex;gap:12px;align-items:flex-start"><span style="color:#28a745;font-weight:700">✓</span><span style="font-size:15px;line-height:1.55;color:#3d3d3d">Subtotal, IVA del 16% y total calculados al instante</span></div>
          <div style="display:flex;gap:12px;align-items:flex-start"><span style="color:#28a745;font-weight:700">✓</span><span style="font-size:15px;line-height:1.55;color:#3d3d3d">Aviso cuando pides más piezas de las que hay en existencia</span></div>
          <div style="display:flex;gap:12px;align-items:flex-start"><span style="color:#28a745;font-weight:700">✓</span><span style="font-size:15px;line-height:1.55;color:#3d3d3d">Ticket imprimible con folio, cajero y método de pago</span></div>
        </div>
      </div>
      <div class="lp-mock" data-reveal="1" style="border-radius:16px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,0.14)">
        @include('landing.mock-pos')
      </div>
    </div>
  </section>

  {{-- ================================================================ ROLES --}}
  <section id="roles" class="lp-section-pad" style="max-width:1180px;margin:0 auto;padding:86px 28px">
    <div data-reveal="1" style="max-width:620px;margin-bottom:44px">
      <h2 class="lp-h2" style="font-family:'Playfair Display',serif;font-size:40px;color:#8B0000;margin:0 0 14px;line-height:1.15">
        Cada quien ve lo que le toca
      </h2>
      <p style="font-size:17px;line-height:1.65;color:#6c757d;margin:0">
        Dos perfiles con accesos distintos. El cajero cobra y cierra su turno; usted ve el negocio completo.
      </p>
    </div>
    <div class="lp-grid-2 lp-roles-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
      <div class="lp-rol-card" data-reveal="1" style="background:#8B0000;color:#fff;border-radius:16px;padding:36px 34px">
        <div style="font-size:28px;margin-bottom:12px">👑</div>
        <div style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;margin-bottom:8px">Administrador</div>
        <p style="font-size:15px;line-height:1.6;color:#ffcccc;margin:0 0 22px">Acceso total al sistema y a la información del negocio.</p>
        <div style="display:flex;flex-direction:column;gap:11px;font-size:15px">
          <div style="display:flex;gap:10px">🍬 <span>Productos, precios y stock mínimo</span></div>
          <div style="display:flex;gap:10px">📊 <span>Reportes y gráficas por día, semana o mes</span></div>
          <div style="display:flex;gap:10px">📋 <span>Historial completo de cortes de caja</span></div>
          <div style="display:flex;gap:10px">👥 <span>Alta y baja de cajeros</span></div>
          <div style="display:flex;gap:10px">📝 <span>Categorías, marcas y proveedores</span></div>
        </div>
      </div>
      <div class="lp-rol-card" data-reveal="1" style="background:#fff;border:2px solid #8B0000;border-radius:16px;padding:36px 34px">
        <div style="font-size:28px;margin-bottom:12px">🧑‍💼</div>
        <div style="font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:#8B0000;margin-bottom:8px">Cajero</div>
        <p style="font-size:15px;line-height:1.6;color:#6c757d;margin:0 0 22px">Solo lo necesario para atender al cliente y cuadrar su turno.</p>
        <div style="display:flex;flex-direction:column;gap:11px;font-size:15px;color:#3d3d3d">
          <div style="display:flex;gap:10px">🛒 <span>Punto de venta y cobro</span></div>
          <div style="display:flex;gap:10px">💰 <span>Ingresos y retiros de su caja</span></div>
          <div style="display:flex;gap:10px">🧾 <span>Corte de su propio turno</span></div>
          <div style="display:flex;gap:10px;color:#adb5bd">🔒 <span>Sin acceso a precios, usuarios ni reportes</span></div>
        </div>
      </div>
    </div>
  </section>

  {{-- ================================================================ ASÍ SE VE POR DENTRO --}}
  <section style="background:#fff;border-top:1px solid #ececec">
    <div class="lp-section-pad" style="max-width:1180px;margin:0 auto;padding:86px 28px">
      <div data-reveal="1" style="max-width:620px;margin-bottom:40px">
        <h2 class="lp-h2" style="font-family:'Playfair Display',serif;font-size:40px;color:#8B0000;margin:0 0 14px;line-height:1.15">
          Así se ve por dentro
        </h2>
        <p style="font-size:17px;line-height:1.65;color:#6c757d;margin:0">
          Pantallas reales del sistema: el tablero del día, la caja y el corte de turno.
        </p>
      </div>

      {{-- Maqueta dashboard completa --}}
      <div class="lp-mock" data-reveal="1" style="border-radius:16px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,0.12);margin-bottom:24px">
        @include('landing.mock-dashboard')
      </div>

      {{-- Fallback para móvil: texto descriptivo en lugar de la maqueta --}}
      <div class="lp-mock-fallback" style="display:none;margin-bottom:24px;padding:28px;background:#fff8f8;border-radius:14px;border-left:4px solid #8B0000;text-align:center">
        <div style="font-size:32px;margin-bottom:10px">📊</div>
        <div style="font-family:'Playfair Display',serif;font-size:20px;color:#8B0000;margin-bottom:8px">Panel de control completo</div>
        <div style="font-size:15px;color:#6c757d;line-height:1.6">Tablero con ventas del día, productos más vendidos, gráfica semanal y últimas transacciones — todo en una sola pantalla.</div>
      </div>

      {{-- Dos tarjetas de preview: Caja + Corte --}}
      <div class="lp-grid-2 lp-preview-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
        {{-- Caja del turno --}}
        <div data-reveal="1" style="border-radius:16px;overflow:hidden;box-shadow:0 12px 34px rgba(0,0,0,0.1);background:#f4f4f5;padding:22px">
          <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid #f0f0f0;font-family:'Playfair Display',serif;color:#8B0000;font-size:16.8px;font-weight:700">💵 Caja del turno</div>
            <div style="padding:18px">
              <div class="lp-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div style="border-left:4px solid #28a745;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:12px 14px">
                  <div style="font-size:14px;color:#6c757d;margin-bottom:2px">Efectivo esperado</div>
                  <div style="font-size:26px;font-weight:700;color:#28a745;line-height:1.1">$3,420.50</div>
                </div>
                <div style="border-left:4px solid #6f42c1;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:12px 14px">
                  <div style="font-size:14px;color:#6c757d;margin-bottom:2px">Ventas con tarjeta</div>
                  <div style="font-size:26px;font-weight:700;color:#6f42c1;line-height:1.1">$1,180.00</div>
                </div>
              </div>
              <div style="display:flex;gap:10px">
                <div style="flex:1;background:#8B0000;color:#fff;border-radius:10px;padding:11px;text-align:center;font-weight:600;font-size:14.4px">＋ Registrar ingreso</div>
                <div style="flex:1;border:1.5px solid #8B0000;color:#8B0000;border-radius:10px;padding:11px;text-align:center;font-weight:600;font-size:14.4px">－ Retiro de efectivo</div>
              </div>
            </div>
          </div>
        </div>

        {{-- Corte de turno --}}
        <div data-reveal="1" style="border-radius:16px;overflow:hidden;box-shadow:0 12px 34px rgba(0,0,0,0.1);background:#f4f4f5;padding:22px">
          <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden">
            <div style="padding:14px 18px;border-bottom:1px solid #f0f0f0;font-family:'Playfair Display',serif;color:#8B0000;font-size:16.8px;font-weight:700">🧾 Corte de turno</div>
            <table style="width:100%;border-collapse:collapse">
              <thead>
                <tr>
                  <th style="text-align:left;font-size:12.5px;text-transform:uppercase;letter-spacing:0.4px;color:#999;font-weight:600;background:#f9f9f9;padding:10px 18px">Concepto</th>
                  <th style="text-align:right;font-size:12.5px;text-transform:uppercase;letter-spacing:0.4px;color:#999;font-weight:600;background:#f9f9f9;padding:10px 18px">Monto</th>
                </tr>
              </thead>
              <tbody>
                @foreach ([
                  ['💵 Ventas en efectivo','$3,150.50'],
                  ['💳 Ventas con tarjeta','$1,180.00'],
                  ['➕ Ingresos de caja','$500.00'],
                  ['➖ Retiros','−$230.00'],
                ] as [$concepto, $monto])
                <tr>
                  <td style="padding:11px 18px;font-size:14.4px;border-bottom:1px solid #f2f2f2;color:#3d3d3d">{{ $concepto }}</td>
                  <td style="padding:11px 18px;font-size:14.4px;border-bottom:1px solid #f2f2f2;text-align:right;font-weight:600;color:#3d3d3d">{{ $monto }}</td>
                </tr>
                @endforeach
                <tr>
                  <td style="padding:12px 18px;font-size:15px;font-weight:700;color:#8B0000">Total del turno</td>
                  <td style="padding:12px 18px;font-size:18px;font-weight:700;text-align:right;color:#8B0000">$4,600.50</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ================================================================ CTA FINAL --}}
  <section id="acceso" style="background:#8B0000;color:#fff;position:relative;overflow:hidden">
    <div style="position:absolute;width:400px;height:400px;border-radius:50%;border:50px solid rgba(255,255,255,0.06);bottom:-180px;left:-100px;pointer-events:none"></div>
    <div style="max-width:760px;margin:0 auto;padding:88px 28px;text-align:center;position:relative">
      <div data-reveal="1">
        <h2 class="lp-cta-h2" style="font-family:'Playfair Display',serif;font-size:42px;margin:0 0 16px;line-height:1.12">
          Empiece a cobrar hoy mismo
        </h2>
        <p style="font-size:18px;line-height:1.6;color:#ffcccc;margin:0 auto 32px;max-width:30em">
          Cree su cuenta y cargue sus productos en unos minutos. Si ya tiene cuenta, entre directo al mostrador.
        </p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
          <a href="{{ route('register') }}" class="lp-btn"   style="background:#ff7043;color:#fff;text-decoration:none;padding:16px 30px;border-radius:12px;font-weight:600;font-size:16px;white-space:nowrap">Registrarse gratis</a>
          <a href="{{ route('login') }}"    class="lp-ghost" style="border:1.5px solid rgba(255,255,255,0.35);color:#fff;text-decoration:none;padding:16px 30px;border-radius:12px;font-weight:600;font-size:16px;white-space:nowrap">Ya tengo cuenta</a>
        </div>
      </div>
    </div>
  </section>

  {{-- ================================================================ FOOTER --}}
  <footer style="background:#580000;color:#ffcccc">
    <div class="lp-footer-inner" style="max-width:1180px;margin:0 auto;padding:28px;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;font-size:14px">
      <div style="display:flex;align-items:center;gap:9px">
        <span style="font-size:18px">🍬</span>
        <span style="font-family:'Playfair Display',serif;color:#fff;font-size:17px">Dulcería POS</span>
      </div>
      <div>Hecho con ❤️ en México 🇲🇽 · Victor Ramirez Mendoza</div>
    </div>
  </footer>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Mostrar fallback de maqueta en móvil
    if (window.matchMedia('(max-width: 900px)').matches) {
        document.querySelectorAll('.lp-mock-fallback').forEach(function (el) {
            el.style.display = 'block';
        });
    }

    // Scroll reveal con degradación sin JS: si IntersectionObserver no está
    // disponible, los elementos ya son visibles (no se ocultan previamente).
    if (!('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            e.target.style.transition = 'opacity .6s ease, transform .6s cubic-bezier(.22,1,.36,1)';
            e.target.style.opacity   = '1';
            e.target.style.transform = 'none';
            io.unobserve(e.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px' });

    document.querySelectorAll('[data-reveal]').forEach(function (n) {
        // Elementos ya visibles al cargar no se ocultan (no rompe sin JS)
        if (n.getBoundingClientRect().top < window.innerHeight) return;
        n.style.opacity   = '0';
        n.style.transform = 'translateY(18px)';
        io.observe(n);
    });
});
</script>
</body>
</html>
