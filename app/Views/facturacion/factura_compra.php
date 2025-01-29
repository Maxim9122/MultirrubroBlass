<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Venta</title>
    <style>
        /* Tu estilo personalizado para la factura */
        body {
            font-family: Arial, sans-serif;
            margin: 20px 300px;
            padding: 0;
            background-color: #f4f4f4; /* Fondo gris tenue */
        }
        .reporte-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .reporte-header h1 {
            font-size: 24px;
            margin: 0;
        }
        .venta-detalle {
            margin-bottom: 20px;
        }
        .venta-detalle p {
            margin: 5px 0;
        }
        .table-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-wrapper table {
            width: 100%;
            border: 1px solid #ddd;
            border-collapse: collapse;
            background-color: white; /* Fondo blanco para la tabla */
        }
        .table-wrapper th, .table-wrapper td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .table-wrapper th {
            background-color: #f4f4f4;
        }
        .boton-descargar {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        .boton-descargar button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .boton-descargar button:hover {
            background-color: #0056b3;
        }
        /* Ocultar el botón de descarga en el PDF */
        .no-print {
            display: none;
        }
    </style>
</head>
<body>
    <div class="reporte-header">
        <h1>Multirubro Blass</h1>
      
    </div>

    <div class="venta-detalle">
        <p><strong>Numero de Ticket:</strong> <?= $cabecera['id']; ?></p>
        <p><strong>Cliente:</strong> <?= $usuario['nombre']; ?></p>
        <p><strong>Fecha:</strong> <?= $cabecera['fecha']; ?> <strong>Hora:</strong> <?= $cabecera['hora']; ?></p>
        
    </div>

    <div class="table-wrapper">
        <h2>Detalles de los Productos</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $key => $detalle): ?>
                    <tr>
                        <td><?= $productos[$key]['nombre']; ?></td>
                        <td><?= $detalle['cantidad']; ?></td>
                        <td>$<?= $detalle['precio']; ?></td>
                        <td>$<?= $detalle['cantidad'] * $detalle['precio']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Campo Total a Pagar -->
    <div class="total-pagar">
        <?php if($cabecera['tipo_pago'] == 'Efectivo'):?>
        <p><strong>Descuento:</strong> $<?= $descuento = $cabecera['total_venta'] * 0.10;?>
        <?php endif; ?>
        <p><strong>Total a Pagar:</strong> $<?= $cabecera['total_bonificado']; ?></p>
    </div>

    <!-- Botón para descargar, que se oculta en el PDF -->
    <div class="boton-descargar">
        <form action="<?= base_url('Carrito_controller/generarPDF/' . $cabecera['id']); ?>" method="post">
            <button type="submit">Descargar en PDF</button>
        </form>
    </div>

</body>
</html>
