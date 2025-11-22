<?php $session = session();
$nombre = $session->get('nombre');
$perfil = $session->get('perfil_id');
$id = $session->get('id');
?>
<br>
<style>
  .nuevoTurno form .btn {
      color: black !important;
      font-weight: 900 !important;
  }
</style>
<style>
  .form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px; /* Espacio entre los campos */
  }

  .form-row .mb-2 {
    flex: 1; /* Para que ambos campos ocupen la misma proporción */
    min-width: 250px; /* Evita que los campos sean demasiado pequeños */
  }

  /* En pantallas pequeñas, los campos se apilan en una columna */
  @media (max-width: 768px) {
    .form-row {
      flex-direction: column;
    }
  }

  /* Estilos modal */
  .modalOverlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 9999;
  }

  .modalBox {
      background: #fff;
      width: 380px;
      max-width: 95%;
      padding: 18px;
      border-radius: 10px;
      text-align: left;
      box-shadow: 0 6px 18px rgba(0,0,0,0.2);
  }

  .modalBox h3 { margin: 0 0 10px 0; font-size: 18px; }

  .switchContainer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 12px 0;
      font-size: 15px;
  }

  /* Toggle switch */
  .switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
  }
  .switch input { display:none; }

  .slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0;
    right: 0; bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 26px;
  }
  .slider:before {
    position: absolute;
    content: "";
    height: 20px; width: 20px;
    left: 3px; bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
  }
  input:checked + .slider {
    background-color: #4CAF50;
  } 
  input:checked + .slider:before {
    transform: translateX(24px);
  }

  .modalBtns {
      margin-top: 18px;
      display: flex;
      justify-content: flex-end;
      gap: 8px;      
  }
  .modalBtns button {
      padding: 8px 14px;
      border-radius: 6px;
      cursor: pointer;
      border: none;
      font-size: 14px;
  }
  .btn-cancel { background:#ddd; color:#333; }
  .btn-confirm { background:#28a745; color:#fff; }

  /* small helper for the page buttons to match your style */
  .button-container { display:flex; gap:10px; justify-content:flex-end; }
  .button-container .btn { padding:8px 12px; border-radius:6px; text-decoration:none; border:1px solid #ccc; background:#f5f5f5; cursor:pointer; }
</style>

<?php if ($perfil == 1) { ?>
    <?php if (session()->getFlashdata('msg')): ?>
        <div id="flash-message" class="flash-message success">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>
    <?php if (session("msgEr")): ?>
        <div id="flash-message" class="flash-message danger">
            <?= session("msgEr"); ?>
        </div>
    <?php endif; ?>

    <script>
        setTimeout(function () {
            const fm = document.getElementById('flash-message');
            if (fm) fm.style.display = 'none';
        }, 3000);
    </script>

    <div class="nuevoTurno">
        <h2>Registrar Nuevo Producto</h2>
        <br>

        <?php $validation = \Config\Services::validation(); ?>
        <form id="productoForm" method="post" enctype="multipart/form-data" action="<?= base_url('ProductoValidation') ?>">
            <?= csrf_field(); ?>

            <!-- Hidden fields para recibir en el controlador. Inicialmente 1 porque en el modal vienen activados por defecto -->
            <input type="hidden" name="local_independencia" id="local_independencia" value="1">
            <input type="hidden" name="local_tercero" id="local_tercero" value="1">

            <!-- Primera fila -->
            <div class="form-row">
                <div class="mb-2">
                    <label>Código de Barra</label>
                    <input name="codigo_barra" type="text" maxlength="15" required
                        value="<?= old('codigo_barra') ?>"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?= $validation->getError('codigo_barra') ? "<div class='alert alert-danger mt-2'>{$validation->getError('codigo_barra')}</div>" : "" ?>
                </div>

                <div class="mb-2">
                    <label>Nombre</label>
                    <input name="nombre" type="text" minlength="5" maxlength="70" required
                        value="<?= old('nombre') ?>">
                    <?= $validation->getError('nombre') ? "<div class='alert alert-danger mt-2'>{$validation->getError('nombre')}</div>" : "" ?>
                </div>
            </div>

            <!-- Segunda fila -->
            <div class="form-row">
                <div class="mb-2">
                    <label>Descripción</label>
                    <input name="descripcion" type="text" maxlength="100"
                        value="<?= old('descripcion') ?>">
                    <?= $validation->getError('descripcion') ? "<div class='alert alert-danger mt-2'>{$validation->getError('descripcion')}</div>" : "" ?>
                </div>

                <div class="mb-2">
                    <label>Imagen</label>
                    <input name="imagen" type="file" required>
                    <?= $validation->getError('imagen') ? "<div class='alert alert-danger mt-2'>{$validation->getError('imagen')}</div>" : "" ?>
                </div>
            </div>

            <!-- Tercera fila -->
            <div class="form-row">
                <div class="mb-2">
                    <label>Categoría</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">Seleccione Categoría</option>
                        <?php foreach ($categorias as $categoria) : ?>
                            <option value="<?= $categoria['categoria_id']; ?>"
                                <?= old('categoria_id') == $categoria['categoria_id'] ? 'selected' : '' ?>>
                                <?= $categoria['descripcion']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= $validation->getError('categoria_id') ? "<div class='alert alert-danger mt-2'>{$validation->getError('categoria_id')}</div>" : "" ?>
                </div>

                <div class="mb-2">
                    <label>Precio de Costo</label>
                    <input name="precio" type="text" required maxlength="20"
                        value="<?= old('precio') ?>"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                    <?= $validation->getError('precio') ? "<div class='alert alert-danger mt-2'>{$validation->getError('precio')}</div>" : "" ?>
                </div>

                <div class="form-row">
                    <div class="mb-2">
                        <label>Precio de Venta</label>
                        <input name="precio_vta" type="text" required maxlength="20"
                            value="<?= old('precio_vta') ?>"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                        <?= $validation->getError('precio_vta') ? "<div class='alert alert-danger mt-2'>{$validation->getError('precio_vta')}</div>" : "" ?>
                    </div>
                </div>

                <div class="mb-2">
                    <label>Stock Belgrano</label>
                    <input name="stock" type="text" required maxlength="11"
                        value="<?= old('stock') ?>"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?= $validation->getError('stock') ? "<div class='alert alert-danger mt-2'>{$validation->getError('stock')}</div>" : "" ?>
                </div>

                <div class="mb-2">
                    <label>Stock Independencia</label>
                    <input name="stock_mb2" type="text" required maxlength="11"
                        value="<?= old('stock_mb2') ?>"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?= $validation->getError('stock_Ind') ? "<div class='alert alert-danger mt-2'>{$validation->getError('stock_hostinger')}</div>" : "" ?>
                </div>

               <!-- <div class="mb-2">
                    <label>Stock 3erLocal</label>
                    <input name="stock_mb2" type="text" required maxlength="11"
                        value="<?= old('stock_mb3') ?>"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?= $validation->getError('stock_Ter') ? "<div class='alert alert-danger mt-2'>{$validation->getError('stock_hostinger')}</div>" : "" ?>
                </div> -->
            </div> 

            <!-- Quinta fila -->
            <div class="form-row">
                <div class="mb-2">
                    <label>Stock Mínimo (Ambos)</label>
                    <input name="stock_min" type="text" required maxlength="11"
                        value="<?= old('stock_min') ?>"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <?= $validation->getError('stock_min') ? "<div class='alert alert-danger mt-2'>{$validation->getError('stock_min')}</div>" : "" ?>
                </div>
            </div>

            <br>
            <div class="button-container">
                <a href="<?= base_url('Lista_Productos'); ?>" class="btn">Cancelar</a>
                <!-- Abre modal en vez de enviar -->
                <button type="button" class="btn" onclick="abrirModal()">Guardar</button>
            </div>
            <br>
        </form>
    </div>

    <!-- MODAL -->
    <div class="modalOverlay" id="modalLocal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modalBox" role="document">
            <h3>Locales donde también se guardará.</h3>           
            <div class="switchContainer">
                <div><h4>Local Independencia</h4></div>
                <label class="switch" title="Local Independencia">
                    <input type="checkbox" id="checkIndependencia" checked>
                    <span class="slider"></span>
                </label>
            </div>

           <!-- <div class="switchContainer">
                <div>3er Local</div>
                <label class="switch" title="3er Local">
                    <input type="checkbox" id="checkTercer" checked>
                    <span class="slider"></span>
                </label>
            </div> -->

            <div class="modalBtns">
                <button type="button" class="btn-cancel" style="color:;" onclick="cerrarModal()">Cancelar</button>
                <button type="button" class="btn-confirm btn" onclick="confirmarYEnviar()">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
    function abrirModal() {
        const modal = document.getElementById('modalLocal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        // Asegurar que hidden reflejen los defaults (por si el usuario re-abre el modal)
        // Si querés que los hidden se sincronicen en cada apertura, se puede descomentar:
        // document.getElementById('local_independencia').value = document.getElementById('checkIndependencia').checked ? '1' : '0';
        // document.getElementById('local_tercero').value = document.getElementById('checkTercer').checked ? '1' : '0';
    }

    function cerrarModal() {
        const modal = document.getElementById('modalLocal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    function confirmarYEnviar() {
        // Tomar los valores de los switches
        const ind = document.getElementById('checkIndependencia').checked ? '1' : '0';
        //const ter = document.getElementById('checkTercer').checked ? '1' : '0';

        // Actualizar los hidden fields del formulario
        document.getElementById('local_independencia').value = ind;
        //document.getElementById('local_tercero').value = ter;

        // Cerrar modal y enviar form
        cerrarModal();

        // Enviar
        document.getElementById('productoForm').submit();
    }

    // cerrar modal si clickean afuera (opcional)
    window.addEventListener('click', function(e){
        const modal = document.getElementById('modalLocal');
        if (modal.style.display === 'flex' && e.target === modal) {
            cerrarModal();
        }
    });

    // soporte teclado: Esc para cerrar modal
    window.addEventListener('keydown', function(e){
        if (e.key === 'Escape') {
            const modal = document.getElementById('modalLocal');
            if (modal.style.display === 'flex') cerrarModal();
        }
    });
    </script>

<?php } else { ?>
    <h2>Su perfil no tiene acceso a esta parte, vuelva a alguna sección de Empleado.</h2>
<?php } ?>
