<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url();?>./assets/css/registro_sesion.css">
    <title>Sesiones</title>
    
</head>
<body>
    <h1>Lista De Ingresos al Sistema</h1>

    <!-- Formulario de búsqueda y filtro -->
    <form style="margin-left: 50px;" method="post" action="<?php echo base_url('filtrarSesiones'); ?>">
            <select name="filter">
            <option value="">Todos los estados</option>
            <option value="activa">Activa</option>
            <option value="cerrada">Cerrada</option>
        </select>
        <button class="btn" type="submit">Buscar</button>
    </form>
    <br>
    <div class="table-container container">
        <table id="users-list">
            <thead>
                <tr>
                    <th>ID Sesión</th>
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
                      <td><?= $sesion->nombre . ' ' . $sesion->apellido?></td><!--concatena nombre y apellido del Usuario -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

<script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
 <link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
 <script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>
<script>
     $('#users-list').DataTable({
    "order": [[0, "desc"]], // Ordena por la primera columna (ID Sesión) en orden descendente
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
</html>
