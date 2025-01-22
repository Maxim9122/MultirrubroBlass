<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>  
 <?php if($perfil == 1){  ?>
   <div class="nuevoTurno">   
      <h2>Editar Producto</h2>
 <?php $validation = \Config\Services::validation(); ?>
     <form method="post" enctype="multipart/form-data" action="<?php echo base_url('/enviarEdicionProd') ?>">
      <?=csrf_field();?>
      <?php if(!empty (session()->getFlashdata('fail'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('fail');?></div>
 <?php endif?>
           <?php if(!empty (session()->getFlashdata('success'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('success');?></div>
  <?php endif?>     
<div class ="card-body" media="(max-width:768px)">
  <div class="mb-2">
   <label for="exampleFormControlInput1" class="form-label">Nombre</label>
   <input name="nombre" type="text"  class="form-control" placeholder="nombre" 
   value="<?php echo $data['nombre']?>">
     <!-- Error -->
        <?php if($validation->getError('nombre')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('nombre'); ?>
            </div>
        <?php }?>
  </div>
  <div class="mb-3">
   <label for="exampleFormControlTextarea1" class="form-label">Descripción</label>
    <input type="text" name="descripcion" class="form-control" value="<?php echo $data['descripcion'] ?>">
    <!-- Error -->
        <?php if($validation->getError('descripcion')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('descripcion'); ?>
            </div>
        <?php }?>
    </div>
    <label for="exampleFormControlInput1" class="form-label">Imagen Actual: </label>
    <div class="mb-3">
      <img class="imagenForm" src="<?php echo base_url('assets/uploads/'.$data['imagen']);?>">
      <br><br>
       <input name="imagen"  type="file" class="form-control">
    <!-- Error -->
        <?php if($validation->getError('imagen')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('imagen'); ?>
            </div>
        <?php }?>
  </div>
    <div class="mb-3">
  <label for="exampleFormControlInput1" class="form-label">Precio</label>
   <input  type="text" name="precio" class="form-control" value="<?php echo $data['precio']?>">
   <!-- Error -->
        <?php if($validation->getError('precio')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('precio'); ?>
            </div>
        <?php }?>
  </div>
  
  <div class="mb-3">
   <label for="exampleFormControlInput1" class="form-label">Precio Venta</label>
   <input name="precio_vta" type="text" class="form-control" value="<?php echo $data['precio_vta']?>">
   <!-- Error -->
        <?php if($validation->getError('precio_vta')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('precio_vta'); ?>
            </div>
        <?php }?>
  </div>

  <div class="mb-3">
   <label for="exampleFormControlInput1" class="form-label">Stock</label>
   <input name="stock" type="text" class="form-control" value="<?php echo $data['stock']?>">
   <!-- Error -->
        <?php if($validation->getError('stock')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('stock'); ?>
            </div>
        <?php }?>
  </div>

  <div class="mb-3">
   <label for="exampleFormControlInput1" class="form-label">Stock Minimo</label>
   <input name="stock_min" type="text" class="form-control" value="<?php echo $data['stock_min']?>">
   <!-- Error -->
        <?php if($validation->getError('stock_min')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('stock_min'); ?>
            </div>
        <?php }?>
  </div>
<br>
  <div class="mb-3">
<?php  
  $categoria='';
  switch ($data['categoria_id']) {
    case 1:
      $categoria = 'Makinas';
      break;
  case 2:
      $categoria = 'Perfumes';
      break;
  case 3:
      $categoria = 'Ropa';
      break;
  case 4:
      $categoria = 'Otros';
      break;
}?>
   <label for="exampleFormControlInput1" class="form-label">Categoria</label>
   <select name="categoria_id">
    <option value="<?php echo $data['categoria_id']?>"><?php echo $categoria ?></option>
    <option value="1">Maquinas</option>
    <option value="2">Perfumes</option>
    <option value="3">Ropa</option>
    <option value="4">Otros</option>
    
    </select>
   <!-- Error -->
        <?php if($validation->getError('categoria_id')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('categoria_id'); ?>
            </div>
        <?php }?>
  </div>
<br>
  <div class="mb-3">
   <label for="exampleFormControlInput1" class="form-label">Eliminado</label>
   <input name="eliminado" type="text" readonly="true" class="form-control" value="<?php echo $data['eliminado']?>">
   <!-- Error -->
        <?php if($validation->getError('eliminado')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('eliminado'); ?>
            </div>
        <?php }?>
  </div>

  <input type="hidden" name="id" value="<?php echo $data['id']?>">

  <br>
  <div class="button-container">
           
            <a type="reset" href="<?php echo base_url('Lista_Productos');?>" class="btn">Cancelar</a>
            <input type="submit" value="Modificar" class="btn">
            <br><br>
        </div>
 </div>
</form>

<?php }else{ ?>
  <h2>Su perfil no tiene acceso a esta parte,
    Vuelva a alguna seccion de Empleado!
  </h2>
<?php }?>
        </div>
        <br>
        