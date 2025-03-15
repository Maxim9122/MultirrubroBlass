<?php 
$session = session();
$perfil = $session->get('perfil_id');

$VTO_CAE = '';
$motivo = '';
$total_anterior = '';
?>
<style>
  /* Estilos para las celdas de la tabla */
.detalle-compra-tabla td {
    color: white; /* Letra blanca */
    font-weight: bold; /* Negrita */
}

</style>
<div style="width: 100%;">
  <div style="text-align:center;">
<br><br>
<a class="detalle-compra-titulo btn" align="center" href="javascript:history.back()">⬅ Volver</a>
</div>
<div style="clear: both;"></div>
<br>

<h2 class="detalle-compra-titulo">Detalle de la Compra</h2>
<br>

<?php if (!empty($ventas)): ?>
  <?php foreach ($ventas as $vta): ?>
    <?php 
    if ($vta['vto_cae'] != null) {
        $VTO_CAE = date('d-m-Y', strtotime($vta['vto_cae']));
    }
    if (!empty($vta['motivo'])) {
        $motivo = $vta['motivo'];
    }
    if (!empty($vta['total_anterior'])) {
        $total_anterior = $vta['total_anterior'];
    }
    if (!empty($vta['total_bonificado'])) {
      $total_actual = $vta['total_bonificado'];
    }
    ?>
  <?php endforeach; ?>
<?php endif; ?>

<!-- Mostrar solo si existe un CAE -->
<?php if ($VTO_CAE != ''): ?>
  <table class="comprados detalle-compra-tabla">
    <thead>
      <tr>
          <th>Nro Factura</th>
          <th>CAE</th>
          <th>Vencimiento CAE</th>
      </tr>
    </thead>
    <tbody>
      <tr>
          <td><?php echo $vta['id_cae']; ?></td>
          <td><?php echo $vta['cae']; ?></td>                   
          <td><?php echo $VTO_CAE; ?></td>
      </tr>
    </tbody>
  </table>
<?php endif; ?>

<!-- Mostrar motivo si existe -->
<?php if ($motivo != ''): ?>
  <table class="comprados detalle-compra-tabla">
    <thead>
      <tr>
          <th>Venta Modificada sin Facturar (Modificada_SF) Motivo:</th>
      </tr>
    </thead>
    <tbody>
      <tr>
          <td class="color"><?php echo $motivo; ?></td>
      </tr>
    </tbody>
  </table>
<?php endif; ?>

<!-- Mostrar total anterior si existe -->
<?php if ($total_anterior != ''): ?>
  <table class="comprados detalle-compra-tabla">
    <thead>
      <tr>
          <th>Total Anterior</th>
          <th>Total Actual</th>
          <th style="color:orange;">Diferencia</th>
      </tr>
      <br>
    </thead>
    <tbody>
      <tr>
          <td>$ <?php echo $total_anterior; ?></td>
          <td>$ <?php echo $total_actual; ?></td>
          <td style="color:orange;">$ <?php echo $total_actual - $total_anterior; ?></td>
      </tr>
    </tbody>
  </table>
<?php endif; ?>

<br>
<div class="detalle-compra-tabla-container">
  <table class="detalle-compra-tabla comprados">
    <thead>
      <tr>
        <th>ID Producto</th>
        <th>Nombre</th>
        <th>Cantidad Comprada</th>
        <th>Precio Unitario</th>
        <th>Total x Producto</th>          
      </tr>
    </thead>
    <tbody>
      <?php if ($ventas): ?>
        <?php foreach ($ventas as $vta): ?>
          <tr>
            <td><?php echo $vta['id']; ?></td>
            <td><?php echo $vta['nombre']; ?></td>
            <td><?php echo $vta['cantidad']; ?></td>
            <td>$ <?php echo $vta['precio']; ?></td>
            <td>$ <?php echo $vta['total']; ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<br>
