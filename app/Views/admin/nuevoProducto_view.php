<br>
<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>  
 <?php if($perfil == 1){  ?>
<div class="">
  <div class="nuevoTurno" >
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
<div class="mb-2">
  <label for="exampleFormControlTextarea1" class="">Codigo de Barra</label>
<<<<<<< Updated upstream
   <input name="codigo_barra" type="text" maxlength="15" required="required" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
=======
   <input name="codigo_barra" type="text">
>>>>>>> Stashed changes
   
     <!-- Error -->
        <?php if($validation->getError('codigo_barra')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('codigo_barra'); ?>
            </div>
        <?php }?>
  </div>
  <br> 
<div class="mb-2">
  <label for="exampleFormControlTextarea1" class="">Nombre</label>
   <input name="nombre" type="text" minlength="5" maxlength="20"required="required" >
   
     <!-- Error -->
        <?php if($validation->getError('nombre')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('nombre'); ?>
            </div>
        <?php }?>
  </div>
  <br>
  <div class="mb-2">
  <label for="exampleFormControlTextarea1" class="form-label">Descripcion</label>
   <input name="descripcion" type="text" required="required" minlength="5" maxlength="20">
   
    <!-- Error -->
        <?php if($validation->getError('descripcion')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('descripcion'); ?>
            </div>
        <?php }?>
    </div>
    <br>
  <div class="mb-2">
  <label for="exampleFormControlTextarea1" class="form-label">Imagen</label>
  <input name="imagen" type="file" required="required" >
  
  <?php if($validation->getError('imagen')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('imagen'); ?>
            </div>
        <?php }?>
  </div>
  <br>

  <div class="mb-2">
  <label for="exampleFormControlTextarea1" class="form-label">Categoria</label>
    <select name="categoria_id" class="form-control">
        <option value="">Seleccione Categoria</option>
        <?php foreach ($categorias as $categoria) : ?>
            <option value="<?= $categoria['categoria_id']; ?>">
                <?= $categoria['descripcion']; ?>
            </option>
        <?php endforeach; ?>
    </select>
    <!-- Error -->
    <?php if ($validation->getError('categoria_id')) : ?>
        <div class='alert alert-danger mt-2'>
            <?= $validation->getError('categoria_id'); ?>
        </div>
    <?php endif; ?>
</div>


    <br>
    <div class="mb-2">
    <label for="exampleFormControlTextarea1" class="form-label">Precio de Costo</label>
   <input name="precio"  type="text" step="0.01" min="0"  maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')"required="required" >
   
    <!-- Error -->
        <?php if($validation->getError('precio')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('precio'); ?>
            </div>
        <?php }?>
        <br>
  </div>
  <br>
  <div class="mb-2">
  <label for="exampleFormControlTextarea1" class="form-label">Precio Venta</label>
   <input  type="text" name="precio_vta" required="required" step="0.01" min="0"  maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
   
   <!-- Error -->
        <?php if($validation->getError('precio_vta')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('precio_vta'); ?>
            </div>
        <?php }?>
  </div>
  <br>
  <div class="mb-2">
  <label for="exampleFormControlTextarea1" class="form-label">Stock</label>
   <input name="stock" type="text" required="required" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
   
   <!-- Error -->
        <?php if($validation->getError('stock')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('stock'); ?>
            </div>
        <?php }?>
  </div>
  <br>
  <div class="mb-2">
  <label for="exampleFormControlTextarea1" class="textColor2">Stock Minimo</label>
   <input name="stock_min" type="text" required="required" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
   
   <!-- Error -->
        <?php if($validation->getError('stock_min')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('stock_min'); ?>
            </div>
        <?php }?>
  </div>
  <br>
  <div class="button-container">
  <a href="<?php echo base_url('Lista_Productos');?>" class="btn">Cancelar</a>   
  <button type="submit" class="btn">Guardar</button>
  </div>
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