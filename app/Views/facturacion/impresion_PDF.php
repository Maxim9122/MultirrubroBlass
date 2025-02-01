<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Venta</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            /* Margen general */
        }
        .reporte-header {
            text-align: center;
            
        }
        .reporte-header h1 {
            font-size: 18px;
            margin: 0;
        }
        .venta-detalle p {
            margin: 3px 0;
            font-size: 12px; /* Tamaño reducido */
        }
        .table-wrapper {
            width: 100%;
            margin: 3px 0;
        }
        .table-wrapper table {
            width: 100%;
            margin: auto;
            border: 1px solid #ddd;
            border-collapse: collapse;
            font-size: 12px; /* Tamaño reducido para impresión */
        }
        .table-wrapper th, .table-wrapper td {
            padding: 5px; /* Reducir espacio en celdas */
            text-align: center;
            border: 1px solid #ddd;
        }
        .total-pagar p {
            font-size: 12px;
            text-align: right;
            margin: 0;
            padding: 0;
        }

        /* Estilos para el celular y redes sociales */
        .informacion-footer {
            font-size: 10px; /* Letras pequeñas */
            text-align: center;
            white-space: nowrap; /* Para que todo esté en una sola línea */
        }
        .informacion-footer p {
            margin: 0;
            display: inline-block; /* Hacer que los elementos estén en una sola fila */
            margin-right: 10px; /* Espacio entre los elementos */
        }

        /* Estilos específicos para impresión */
        @media print {
            body {
                margin: 0; /* Sin margen al imprimir */
                padding: 0;
            }
            .reporte-header, .venta-detalle, .table-wrapper, .total-pagar {
                margin: 0;
                padding: 0;
            }
            .table-wrapper table {
                width: 100%; /* Usar el 100% del ancho del papel */
                margin: auto;
            }
            .boton-descargar {
                display: none; /* Ocultar botones no necesarios en impresión */
            }
        }
    </style>
</head>
<body>
    <div class="reporte-header">
        <h1>Multirubro Blass</h1>
    </div>

    <div class="informacion-footer">
        <p>Calle Belgrano 2077, casi Brasil</p>
        <p>Cel: 3794-095020</p>
        <p>Ig: @Blass.Multirubro</p>
        <p>Facebook: Blass Multirubro</p>
    </div>

    <div class="venta-detalle">
        <p><strong>Numero de Ticket:</strong> <?= $cabecera['id']; ?></p>
        <p><strong>Cliente:</strong> <?= $usuario['nombre']; ?></p>
        <p><strong>Fecha:</strong> <?= $cabecera['fecha']; ?> <strong>Hora:</strong> <?= $cabecera['hora']; ?></p>
    </div>
    <div class="table-wrapper">
        <h5>Detalles de la Compra</h5>
        <table>
            <thead>
                <tr>
                    <th>Pruduc.</th>
                    <th>Cant.</th>
                    <th>Precio</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $key => $detalle): ?>
                    <tr>
                        <td><?= $productos[$key]['nombre']; ?></td>
                        <td><?= $detalle['cantidad']; ?></td>
                        <td><?= $detalle['precio']; ?></td>
                        <td>$<?= $detalle['cantidad'] * $detalle['precio']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Campo Total a Pagar -->
    <div class="total-pagar">
        <?php if($cabecera['tipo_pago'] == 'Efectivo'):?>
        <p><strong>Descuento Efectivo:</strong> $<?= $descuento = $cabecera['total_venta'] * 0.10; ?>
        <?php endif; ?>
        <p><strong>Total a Pagar:</strong> $<?= $cabecera['total_bonificado']; ?></p>
    </div>

    <div class="informacion-footer">
        <p>La mercaderia viaja por cuenta y riesgo del comprador.</p>
        <p>Es responsabilidad del cliente controlar su compra antes</p>
        <p>de salir del local.</p>
        <p>Su compra tiene 48hs para cambio ante fallas previas</p>
        <p>del producto.</p>
        
    </div>
</body>
</html>
