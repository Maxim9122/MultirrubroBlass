<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url();?>./assets/css/registro_sesion.css">
    <title>Sesiones</title>
    
</head>
<body>
    <h1>Lista Sessiones</h1>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID Sesión</th>
                    <th>Inicio de Sesión</th>
                    <th>Fin de Sesión</th>
                    <th>Estado</th>
                    <th>Nombre del Empleado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sesiones as $sesion): ?>
                    <tr>
                        <td><?= $sesion->id_sesion ?></td>
                        <td><?= $sesion->inicio_sesion ?></td>
                        <td><?= $sesion->fin_sesion ?></td>
                        <td><?= $sesion->estado ?></td>
                      <td><?= $sesion->nombre . ' ' . $sesion->apellido?></td><!--concatena nombre y apellido del empleado -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
