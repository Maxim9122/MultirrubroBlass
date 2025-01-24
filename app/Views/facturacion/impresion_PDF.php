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
            padding: auto;
            background-color: #f4f4f4;
            margin: auto; /* Margen general */
            
        }
        .reporte-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .reporte-header h1 {
            font-size: 18px;
            margin: 0;
        }
        .venta-detalle p {
            margin: 5px 0;
            font-size: 12px; /* Tamaño reducido */
        }
        .table-wrapper {
            width: 100%;
            margin: 10px 0;
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
            font-size: 14px;
            text-align: right;
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

    <div class="venta-detalle">
        <p><strong>Numero de Ticket:</strong> <?= $cabecera['id']; ?></p>
        <p><strong>Cliente:</strong> <?= $usuario['nombre']; ?></p>
        <p><strong>Fecha:</strong> <?= $cabecera['fecha']; ?> <strong>Hora:</strong> <?= $cabecera['hora']; ?></p>
    </div>

    <div class="table-wrapper">
        <h4>Detalles de la Compra</h4>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $key => $detalle): ?>
                    <tr>
                        <td><?= $productos[$key]['nombre']; ?></td>
                        <td><?= $detalle['cantidad']; ?></td>
                        <td><?= $detalle['precio']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="total-pagar">
        <p><strong>Total a Pagar:</strong> <?= $cabecera['total_venta']; ?></p>
    </div>
</body>
</html>
