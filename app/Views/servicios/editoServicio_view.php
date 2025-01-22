<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?> 
<div>
<?php if($perfil == 1){  ?>
  <div class="comprados nuevoTurno" style="width: 50%;">
    <div>
      <h2>Editar Servicio</h2>
    </div>
    <br>
    <?php $validation = session()->get('validation'); ?>
    <form method="post" action="<?php echo base_url('/edicionServiOk') ?>" enctype="multipart/form-data">
      <?= csrf_field(); ?>
      <?php if (session()->getFlashdata('fail')) : ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('fail'); ?></div>
      <?php endif ?>
      <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success'); ?></div>
      <?php endif ?>

      <div>
        <label for="descripcion">Descripción</label>
        <input name="descripcion" type="text" placeholder="Descripción" value="<?= old('descripcion', $data['descripcion'] ?? '') ?>">
        <!-- Error -->
        <?php if ($validation && $validation->getError('descripcion')) : ?>
          <div class='alert alert-danger mt-2'>
            <?= $validation->getError('descripcion'); ?>
          </div>
        <?php endif ?>
      </div>

      <div>
        <label for="precio">Precio</label>
        <input name="precio" type="text" placeholder="Precio" value="<?= old('precio', $data['precio'] ?? '') ?>">
        <!-- Error -->
        <?php if ($validation && $validation->getError('precio')) : ?>
          <div class='alert alert-danger mt-2'>
            <?= $validation->getError('precio'); ?>
          </div>
        <?php endif ?>
      </div>

      <input type="hidden" name="id" value="<?= $data['id_servi'] ?? '' ?>">
      <br><br>
      <div class="button-container">
        <a type="reset" href="<?php echo base_url('Lista_servicios'); ?>" class="btn">Volver</a>
        <button type="submit" value="Editar" class="btn">Modificar</button>
      </div>
    </form>
  </div>
  <?php }else{ ?>
  <h2 class="warning">Su perfil no tiene acceso a esta parte,
    Vuelva a alguna sección de Empleado o comunuquese con el Jefe!
  </h2>
<?php }?>
</div>
