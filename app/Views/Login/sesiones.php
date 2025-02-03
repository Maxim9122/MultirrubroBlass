<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url();?>./assets/css/sesiones.css">
    <title>Sesiones</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        /* Título */
        .titulo-vidrio {
            font-size: 24px;
            margin: 20px 0;
        }

        /* Contenedor principal */
        .sesiones-container {
            max-width: 900px;
            margin: auto;
            background: rgba(88, 87, 87, 0.7);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        /* Formulario de filtro */
        .sesiones-formulario {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 20px;
            gap: 10px;
        }

        .sesiones-volver {
            background: #dc3545;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
        }

        .sesiones-volver:hover {
            background: #c82333;
        }

        .sesiones-select {
            flex-grow: 1;
            text-align: center;
            max-width: 200px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .sesiones-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .sesiones-btn:hover {
            background: #0056b3;
        }

        /* Tabla */
        .sesiones-tabla-container {
            width: 100%;
            overflow-x: hidden;
        }

        .sesiones-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
            color:#ffff;
            font-size: 10px;
        }

        .sesiones-tabla th, .sesiones-tabla td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            word-wrap: break-word;
            white-space: normal;
        }

        .sesiones-tabla th {
            background: #343a40;
            color: white;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .sesiones-container {
                width: 100%;
                padding: 15px;
            }

            .sesiones-formulario {
                flex-direction: row;
                justify-content: space-between;
                gap: 5px;
            }

            .sesiones-select {
                max-width: 180px;
            }
        }
    </style>
</head>
<body>

    <h1 class="titulo-vidrio">Lista De Ingresos al Sistema</h1>

    <div class="sesiones-container">
        <!-- Formulario de búsqueda y filtro -->
        <form class="sesiones-formulario" method="post" action="<?php echo base_url('filtrarSesiones'); ?>">
            <a class="sesiones-volver btn" href="javascript:history.back()">⬅ Volver</a>
            <select name="filter" class="sesiones-select">
                <option value="">Todos los estados</option>
                <option value="activa">Activa</option>
                <option value="cerrada">Cerrada</option>
            </select>
            <button class="sesiones-btn btn" type="submit">Filtrar</button>
        </form>

        <!-- Tabla de sesiones -->
        <div class="sesiones-tabla-container">
            <table class="sesiones-tabla" id="users-list">
                <thead>
                    <tr>
                        <th>Nro Sesión</th>
                        <th>Inicio de Sesión</th>
                        <th>Fin de Sesión</th>
                        <th>Estado</th>
                        <th>Usuarios</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sesiones as $sesion): ?>
                        <tr>
                            <td><?= $sesion->id_sesion ?></td>
                            <td><?= $sesion->inicio_sesion ?></td>
                            <td><?= $sesion->fin_sesion ?></td>
                            <td><?= $sesion->estado ?></td>
                            <td><?= $sesion->nombre . ' ' . $sesion->apellido?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
    <script src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>
    <script>
        $('#users-list').DataTable({
            "order": [[0, "desc"]],
            "language": {
                "lengthMenu": "Mostrar _MENU_ registros por página.",
                "zeroRecords": "Lo sentimos! No hay resultados.",
                "info": "Mostrando la página _PAGE_ de _PAGES_",
                "infoEmpty": "No hay registros disponibles.",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar: ",
                "paginate": {
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
    </script>

</body>
</html>
