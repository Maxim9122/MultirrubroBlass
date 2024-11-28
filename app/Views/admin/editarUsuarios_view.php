<br>
<div class="container mt-1 mb-0 comprados">
  <div class="comprados" style="width: 50%;" >
    <div class= "card-header text-center">
      <h2>Editar Usuarios</h2>
    </div>
 <?php $validation = \Config\Services::validation(); ?>
     <form method="post" action="<?php echo base_url('/enviarEdicion') ?>">
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
   <label for="exampleFormControlTextarea1" class="form-label">Apellido</label>
    <input type="text" name="apellido"class="form-control" placeholder="apellido" value="<?php echo $data['apellido'] ?>">
    <!-- Error -->
        <?php if($validation->getError('apellido')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('apellido'); ?>
            </div>
        <?php }?>
    </div>
    <div class="mb-3">
       <label for="exampleFormControlInput1" class="form-label">email</label>
   <input name="email"  type="femail" class="form-control"  placeholder="correo@algo.com" value="<?php echo $data['email']?>" >
    <!-- Error -->
        <?php if($validation->getError('email')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('email'); ?>
            </div>
        <?php }?>
  </div>
    <div class="mb-3">
  <label for="exampleFormControlInput1" class="form-label">Usuario</label>
   <input  type="text" name="usuario" class="form-control" placeholder="usuario" value="<?php echo $data['usuario']?>">
   <!-- Error -->
        <?php if($validation->getError('usuario')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('usuario'); ?>
            </div>
        <?php }?>
  </div>
  
  <div class="mb-3">
       <label for="exampleFormControlInput1" class="form-label">Teléfono</label>
   <input name="telefono"  type="text" class="form-control"  placeholder="Telefono" value="<?php echo $data['telefono']?>" >
    <!-- Error -->
        <?php if($validation->getError('telefono')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('telefono'); ?>
            </div>
        <?php }?>
  </div>

  <div class="mb-3">
       <label for="exampleFormControlInput1" class="form-label">Direccion</label>
   <input name="direccion"  type="text" class="form-control"  placeholder="Direccion" value="<?php echo $data['direccion']?>" >
    <!-- Error -->
        <?php if($validation->getError('direccion')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('direccion'); ?>
            </div>
        <?php }?>
  </div>
  
  <div class="mb-3">
   <label for="exampleFormControlInput1" class="form-label">Password</label>
   <input name="pass" type="txt" class="form-control"  placeholder="password" value="<?php echo $data['pass']?>">
   <!-- Error -->
        <?php if($validation->getError('pass')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('pass'); ?>
            </div>
        <?php }?>
  </div>
  <br>
  <div class="mb-3">
   <?php  
  $perfil='';
  switch ($data['perfil_id']) {
    case 1:
        $perfil = 'Admin';
        break;
    case 2:
        $perfil = 'Cliente';
        break;
}?>
   <label for="exampleFormControlInput1" class="form-label">Categoria</label>
   <select name="perfil_id">
    <option value="<?php echo $data['perfil_id']?>"><?php echo $perfil ?></option>
    <option value="1">Admin</option>
    <option value="2">Cliente</option>
    </select>
   <!-- Error -->
        <?php if($validation->getError('perfil_id')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('perfil_id'); ?>
            </div>
        <?php }?>
  </div>

  <div class="mb-3">
   <label for="exampleFormControlInput1" class="form-label">Eliminado</label>
   <input name="baja" type="text" readonly="true" class="form-control"  placeholder="baja" value="<?php echo $data['baja']?>">
   <!-- Error -->
        <?php if($validation->getError('baja')) {?>
            <div class='alert alert-danger mt-2'>
              <?= $error = $validation->getError('baja'); ?>
            </div>
        <?php }?>
  </div>

  <input type="hidden" name="id" value="<?php echo $data['id']?>">

  <br>
           <input type="submit" value="Editar" class="btn btn-outline-success float-end">
            <a type="reset" href="<?php echo base_url('usuarios-list');?>" class="btn btn-outline-danger float-end">Cancelar</a>
      <br><br>
 </div>
</form>
</div>
</div>
<br>