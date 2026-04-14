<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 560px; margin: 40px auto; background: #fff;
                     border-radius: 10px; overflow: hidden;
                     box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #8B0000; padding: 28px 32px; }
        .header h1 { color: #fff; font-size: 1.3rem; margin: 0; }
        .header p  { color: #ffcccc; font-size: 0.85rem; margin: 6px 0 0; }
        .body { padding: 32px; color: #333; line-height: 1.6; }
        .body h2 { font-size: 1rem; color: #8B0000; margin: 0 0 16px; }
        .product-box { background: #fff5f5; border-left: 4px solid #8B0000;
                       border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .product-box p { margin: 4px 0; font-size: 0.9rem; }
        .product-box .label { font-weight: 600; color: #555; }
        .footer { background: #f9f9f9; padding: 20px 32px;
                  font-size: 0.8rem; color: #999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🍬 Dulcería POS — Alerta de stock bajo</h1>
        <p>Notificación automática del sistema</p>
    </div>

    <div class="body">
        <h2>Estimado/a {{ $proveedor->nombre }},</h2>

        <p>
            Le informamos que el siguiente producto ha alcanzado su nivel mínimo de stock
            y requiere reabastecimiento:
        </p>

        <div class="product-box">
            <p><span class="label">Producto:</span> {{ $producto->nombre }}</p>
            <p><span class="label">Stock actual:</span> {{ $producto->stock }} {{ $producto->unidad_medida }}</p>
            <p><span class="label">Stock mínimo:</span> {{ $producto->stock_minimo }} {{ $producto->unidad_medida }}</p>
        </div>

        <p>
            Por favor, comuníquese con nosotros para coordinar el siguiente pedido.
        </p>

        <p>Gracias,<br><strong>Equipo Dulcería POS</strong></p>
    </div>

    <div class="footer">
        Este mensaje fue generado automáticamente. Por favor no responda directamente a este correo.
    </div>
</div>
</body>
</html>
