<br>
<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>  
 <?php if($perfil == 1){  ?>
<div class="">
  <div class="nuevoProd" >
    <div class= "">
      <h2>Registrar Nuevo Producto</h2>
    </div>
  <br>
 <?php $validation = \Config\Services::validation(); ?>
     <form method="post" enctype="multipart/form-data" action="<?php echo base_url('ProductoValidation') ?>">
      <?=csrf_field();?>
      <?php if(!empty (session()->getFlashdata('fail'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('fail');?></div>
 <?php endif?>
           <?php if(!empty (session()->getFlashdata('success'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('success');?></div>
  <?php endif?>     
<div class="inputBox">
   <input name="nombre" type="text" required="required">
   <label for="exampleFormControlTextarea1" class="">Nombre</label>
     <!-- Error -->
        <?php if($validation->getError('nombre')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('nombre'); ?>
            </div>
        <?php }?>
  </div>
  <br>
  <div class="inputBox">
   <input name="descripcion" type="text" required="required">
   <label for="exampleFormControlTextarea1" class="form-label">Descripcion</label>
    <!-- Error -->
        <?php if($validation->getError('descripcion')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('descripcion'); ?>
            </div>
        <?php }?>
    </div>
    <br>
  <div class="inputBox">
  <input name="imagen" type="file" required="required">
  <label for="exampleFormControlTextarea1" class="form-label">Imagen</label>
  <?php if($validation->getError('imagen')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('imagen'); ?>
            </div>
        <?php }?>
  </div>
  <br>
  <div class="inputBox">
    <select name="categoria_id">
    <option>Seleccione Categoria</option>
    <option value="1">Maquinas</option>
    <option value="2">Perfumes</option>
    <option value="3">Ropa</option>
    <option value="4">Otros</option>
    </select>
    <label for="exampleFormControlTextarea1" class="form-label">Categoria</label>
    <!-- Error -->
        <?php if($validation->getError('categoria_id')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('categoria_id'); ?>
            </div>
        <?php }?>
    </div>
    <br>
    <div class="inputBox">
   <input name="precio"  type="text" required="required" >
   <label for="exampleFormControlTextarea1" class="form-label">Precio de Costo</label>
    <!-- Error -->
        <?php if($validation->getError('precio')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('precio'); ?>
            </div>
        <?php }?>
        <br>
  </div>
  <br>
  <div class="inputBox">
   <input  type="text" name="precio_vta" required="required">
   <label for="exampleFormControlTextarea1" class="form-label">Precio Venta</label>
   <!-- Error -->
        <?php if($validation->getError('precio_vta')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('precio_vta'); ?>
            </div>
        <?php }?>
  </div>
  <br>
  <div class="inputBox">
   <input name="stock" type="text" required="required">
   <label for="exampleFormControlTextarea1" class="form-label">Stock</label>
   <!-- Error -->
        <?php if($validation->getError('stock')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('stock'); ?>
            </div>
        <?php }?>
  </div>
  <br>
  <div class="inputBox">
   <input name="stock_min" type="text" required="required">
   <label for="exampleFormControlTextarea1" class="textColor2">Stock Minimo</label>
   <!-- Error -->
        <?php if($validation->getError('stock_min')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('stock_min'); ?>
            </div>
        <?php }?>
  </div>
  <br>

  <a href="<?php echo base_url('Lista_Productos');?>" class="btn">Cancelar</a>   
  <button type="submit" class="btn">Guardar</button>
   
  <br>
 </div>
</form>
<?php }else{ ?>
  <h2>Su perfil no tiene acceso a esta parte,
    Vuelva a alguna seccion de Empleado!
  </h2>
<?php }?>
</div>
</div>
<br>