<br>
<div class="comprados">
  
  <?php $session = session();
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');
          ?>
  <?php if($perfil==1){?>
  <a class="btn btn-primary float-end" href="<?php echo base_url('compras');?>">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-back" viewBox="0 0 16 16">
  <path d="M0 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2H2a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2z"/>
  </svg> Volver</a>
  <?php }else{?>
    <a class="btn btn-primary float-end" href="<?php echo base_url('misCompras/'.$id);?>">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-back" viewBox="0 0 16 16">
  <path d="M0 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2H2a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2z"/>
  </svg> Volver</a>
  <?php }?>
  <br><br>

  <div class="comprados">
  <h2 class="">Detalle de la Compra</h2>
  
  <table class="" id="users-list">
       <thead>
          <tr class="">
             <th>ID Producto</th>
             <th >Nombre</th>
             <th class="text-center">Cantidad Comprada</th>
             <th class="text-center">Precio Unitario</th>
             <th class="text-center">Total x Producto</th>
          </tr>
       </thead>
       <tbody>
          <?php if($ventas): ?>
          <?php foreach($ventas as $vta): ?>
          <tr>
             <td class="bg-light"><?php echo $vta['id']; ?></td>
             <td class="bg-light"><?php echo $vta['nombre']; ?></td>
             <td class="text-center bg-light"><?php echo $vta['cantidad']; ?></td>
             <td class="text-center bg-light"><?php echo $vta['precio']; ?></td>
             <td class="text-center bg-light"><?php echo $vta['total']; ?></td>
            </tr>
           
         <?php endforeach; ?>
         <?php endif; ?>
         
     </table>
     <br>
  </div>
</div>

<br><br>