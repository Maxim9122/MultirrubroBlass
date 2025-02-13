<style>
  /* Contenedor principal de la vista */
  .detalle-compra-container {
    width: 100%;
    padding: 10px;
  }

  /* Botón de volver */
  .detalle-compra-btn-volver {
    display: inline-block;
    padding: 8px 15px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    float: right;
  }

  /* Contenedor para la tabla */
  .detalle-compra-tabla-container {
    overflow-x: auto; /* Permite desplazamiento horizontal solo en este contenedor */
    width: 100%;
  }

  /* Estilos específicos para la tabla */
  .detalle-compra-tabla {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  .detalle-compra-tabla th, .detalle-compra-tabla td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
    color: #ffff;
    
  }

  .detalle-compra-tabla th {
    background-color: #333;
    color: white;
  }

  /* Ajustes para pantallas pequeñas */
  @media screen and (max-width: 600px) {
    .detalle-compra-tabla th, .detalle-compra-tabla td {
      font-size: 18px;
      padding: 1px;
    }
  }
</style>

<div class="detalle-compra-container" align="center">
  
  <?php 
    $session = session();
    $perfil = $session->get('perfil_id');
    $VTO_CAE = '';
  ?>

  <a class="detalle-compra-btn-volver btn" align="center" href="javascript:history.back()">⬅ Volver</a>

  <div style="clear: both;"></div>
  <br>

  <h2 class="detalle-compra-titulo">Detalle de la Compra</h2>
  <table class="comprados detalle-compra-tabla">
    <thead>
      <tr>
          <th>Nro CAE</th>
          <th>CAE</th>
          <th>Vencimiento CAE</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($ventas): ?>
      <?php foreach ($ventas as $vta): ?>
      <tr>      
          <td><?php echo $vta['id_cae']; ?></td>
          <td><?php echo $vta['cae']; ?></td>
          <?php $VTO_CAE = date('d-m-Y', strtotime($vta['vto_cae'])); ?>
          <td><?php echo $VTO_CAE; ?></td>
      </tr>
      <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
  </table>
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
              <td><?php echo $vta['precio']; ?></td>
              <td><?php echo $vta['total']; ?></td>
              
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <br>
</div>
