<?php
$session = session();
$nombre = $session->get('nombre');
$perfil = $session->get('perfil_id');
$id = $session->get('id');
?>  
<style>
  /* Mover buscador al extremo derecho dentro del contenedor de la tabla */
  div#historial-table_filter {
    text-align: right;
    padding-right: 4rem;
    margin-bottom:5px;
  }

  /* Cambiar color y grosor del texto en buscador y selector */
  div.dataTables_filter label,
  div.dataTables_length label {
    color: white;
    font-weight: bold;
  }

  /* Input de búsqueda */
  div.dataTables_filter input {
    background-color: #222;
    color: white;
    border: 1px solid #555;
    border-radius: 4px;
    padding: 4px 8px;
  }

  /* Selector de cantidad */
  div.dataTables_length select {
    background-color: #222;
    color: white;
    border: 1px solid #555;
    border-radius: 4px;
    padding: 4px 6px;
  }

  /* Estilo del texto dentro de los selectores */
  div.dataTables_length option {
    background-color: #222;
    color: white;
  }
</style>

<?php if ($perfil == 1): ?>

<?php if (session()->getFlashdata('msg')): ?>
    <div id="flash-message" class="flash-message success">
        <?= session()->getFlashdata('msg') ?>
    </div>
<?php endif; ?>

<script>
    setTimeout(function() {
        document.getElementById('flash-message').style.display = 'none';
    }, 3000);
</script>
<div class="" style="width: 100%;" align="center">
<section class="contenedor-titulo">
    <strong class="titulo-vidrio">Historial de Modificaciones de Productos</strong>
</section>
<br>

<!-- Filtro por fechas -->
<div class="estiloTurno" style="width: 50%;">
      <form method="GET" action="<?= base_url('filtrarHistorial') ?>">
        <label for="fecha_desde" style="color:#ffff;">Desde:</label>
        <input type="date" name="desde" id="fecha_desde" value="<?= esc($fechaDesde) ?>">

        <label for="fecha_hasta" style="color:#ffff;">Hasta:</label>
        <input type="date" name="hasta" id="fecha_hasta" value="<?= esc($fechaHasta) ?>">

          <button type="submit" class="btn">Filtrar</button>
       </form>
        <a class="button" href="<?php echo base_url('filtrarHistorial');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
        </svg>TODOS</a>
        </div>
    <br>
<table class="table table-responsive table-hover" id="historial-table" style="width: 90%;">
    <thead>
        <tr class="colorTexto2">
            <th>Nro</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Usuario</th>
            <th>Producto</th>
            <th style="color:orange;">Stock Anterior</th>
            <th style="color:green;">Nuevo Stock</th>
            <th style="color:orange;">Precio Venta Ant.</th>
            <th style="color:green;">Precio Venta Act.</th>
            <th>Imagen</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($historial): ?>
            <?php foreach ($historial as $index => $item): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= esc($item['dia_modif']) ?></td>
                    <td><?= esc($item['hora_modif']) ?></td>
                    <td><?= esc($item['nombre_usuario']) ?></td>
                    <td><?= esc($item['nombre_producto']) ?></td>
                    <td style="text-align:center;"><?= esc($item['stock_anterior']) ?></td>
                    <td style="text-align:center;"><?= esc($item['stock']) ?></td>
                    <td style="text-align:center;">$ <?= esc(number_format($item['precio_anterior'], 2, ',', '.')) ?></td>   
                    <td style="text-align:center;">$ <?= esc(number_format($item['precio_actual'], 2, ',', '.')) ?></td>                  
                    <td><img class="frmImg" src="<?php echo base_url('assets/uploads/'.$item['imagen']);?>"></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="10" style="text-align:center;">No se encontraron registros en el rango indicado.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
        </div>
<!-- Responsive para botones si luego agregás acciones -->
<style>
  @media (max-width: 768px) {
    table td {
        font-size: 14px;
    }
  }
</style>

<!-- DataTables -->
<script src="<?= base_url('./assets/js/jquery-3.5.1.slim.min.js'); ?>"></script>
<link rel="stylesheet" href="<?= base_url('./assets/css/jquery.dataTables.min.css'); ?>">
<script src="<?= base_url('./assets/js/jquery.dataTables.min.js'); ?>"></script>

<script>
  $(document).ready(function () {
    $('#historial-table').DataTable({
      "stateSave": true,
      "language": {
        "lengthMenu": "Mostrar _MENU_ registros.",
        "zeroRecords": "No hay resultados.",
        "info": "Mostrando _PAGE_ de _PAGES_",
        "infoEmpty": "No hay registros disponibles.",
        "infoFiltered": "(filtrado de _MAX_ registros totales)",
        "search": "Buscar: ",
        "paginate": {
          "next": "Siguiente",
          "previous": "Anterior"
        }
      },
      initComplete: function () {
        $('#historial-table_filter input').attr('placeholder', 'Filtrar...');
      }
    });
  });
</script>

<?php else: ?>
    <h2>Su perfil no tiene acceso a esta sección. Vuelva a Empleado.</h2>
<?php endif; ?>
<br><br>
