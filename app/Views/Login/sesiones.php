<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesiones</title>
</head>
<body>
    <h1>Lista de Sesiones</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID Sesión</th>
                <th>Inicio de Sesión</th>
                <th>Fin de Sesión</th>
                <th>Estado</th>
                <th>Nombre de Usuario</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sesiones as $sesion): ?>
                <tr>
                    <td><?= $sesion->id_sesion ?></td>
                    <td><?= $sesion->inicio_sesion ?></td>
                    <td><?= $sesion->fin_sesion ?></td>
                    <td><?= $sesion->estado ?></td>
                    <td><?= $sesion->nombre ?></td>
                    <td><?= $sesion->email ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
