<br>
<div>
  <div class="comprados nuevoTurno" style="width: 50%;" >
    <div>
      <h2>Editar Usuarios</h2>
    </div>
    <br>
 <?php $validation = \Config\Services::validation(); ?>
     <form method="post" action="<?php echo base_url('/edicionOk') ?>" enctype="multipart/form-data">
      <?=csrf_field();?>
      <?php if(!empty (session()->getFlashdata('fail'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('fail');?></div>
 <?php endif?>
           <?php if(!empty (session()->getFlashdata('success'))):?>
      <div class="alert alert-danger"><?=session()->getFlashdata('success');?></div>
  <?php endif?>     
<div media="(max-width:768px)">
  <div>
   <label for="exampleFormControlInput1">Nombre</label>
   <input name="nombre" type="text"  placeholder="nombre" 
   value="<?php echo $data['nombre']?>">
     <!-- Error -->
        <?php if($validation->getError('nombre')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('nombre'); ?>
            </div>
        <?php }?>
  </div>
  
  <div>
       <label for="exampleFormControlInput1">Teléfono</label>
   <input name="telefono"  type="text"  placeholder="Telefono" value="<?php echo $data['telefono']?>" >
    <!-- Error -->
        <?php if($validation->getError('telefono')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('telefono'); ?>
            </div>
        <?php }?>
  </div>  

  <label for="exampleFormControlInput1">Foto Actual: </label>
    <div>
      <img class="imagenForm" src="<?php echo base_url('assets/uploads/'.$data['foto']);?>">
      <br><br>
       <input name="foto"  type="file">
    <!-- Error -->
        <?php if($validation->getError('foto')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('foto'); ?>
            </div>
        <?php }?>
  </div>
    

  <input type="hidden" name="id" value="<?php echo $data['id_cliente']?>">
  <br>
  <br>
  <div class="button-container">
            <a type="reset" href="<?php echo base_url('/');?>" class="btn">
            Volver</a>
           <button type="submit" value="Editar" class="btn">
           Modificar</button>
           
      <br><br>
  </div>
 </div>
</form>
</div>
</div>