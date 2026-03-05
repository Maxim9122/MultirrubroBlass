<?php $session = session();
$nombre = $session->get('nombre');
$perfil = $session->get('perfil_id');
$id = $session->get('id');
?>

<br>

<style>
.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.form-row .mb-2 {
    flex: 1;
    min-width: 250px;
}
@media (max-width: 768px) {
    .form-row { flex-direction: column; }
}

.button-container {
    display:flex;
    gap:10px;
    justify-content:flex-end;
}
.button-container .btn {
    padding:8px 12px;
    border-radius:6px;
    text-decoration:none;
    border:1px solid #ccc;
    background:#f5f5f5;
    cursor:pointer;
}
</style>

<?php if ($perfil == 1) { ?>

<?php $validation = \Config\Services::validation(); ?>

<!-- Mensaje flash de éxito -->
<?php if (session()->getFlashdata('msg')): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({
            title: '¡Éxito!',
            text: '<?= session()->getFlashdata('msg') ?>',
            icon: 'success',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#50fa7b',
        });
    });
</script>
<?php endif; ?>

<div class="nuevoTurno">
<h2>Registrar Nuevo Producto</h2>
<br>

<form id="productoForm" method="post" enctype="multipart/form-data" action="<?= base_url('ProductoValidation') ?>">
<?= csrf_field(); ?>

<!-- Este campo se maneja desde el popup -->
<input type="hidden" name="local_independencia" id="local_independencia" value="0">

<!-- FILA 1 -->
<div class="form-row">
    <div class="mb-2">
        <label>Código de Barra</label>
        <input name="codigo_barra" type="text" maxlength="15" required
            value="<?= old('codigo_barra') ?>"
            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        <?= $validation->getError('codigo_barra') ? "<div class='alert alert-danger mt-2'>{$validation->getError('codigo_barra')}</div>" : "" ?>
    </div>

    <div class="mb-2">
        <label>Nombre</label>
        <input name="nombre" type="text" minlength="5" maxlength="70" required
            value="<?= old('nombre') ?>">
        <?= $validation->getError('nombre') ? "<div class='alert alert-danger mt-2'>{$validation->getError('nombre')}</div>" : "" ?>
    </div>
</div>

<!-- FILA 2 -->
<div class="form-row">
    <div class="mb-2">
        <label>Descripción</label>
        <input name="descripcion" type="text" maxlength="100"
            value="<?= old('descripcion') ?>">
    </div>

    <div class="mb-2">
        <label>Imagen</label>
        <input name="imagen" type="file" required>
        <?= $validation->getError('imagen') ? "<div class='alert alert-danger mt-2'>{$validation->getError('imagen')}</div>" : "" ?>
    </div>
</div>

<!-- FILA 3 -->
<div class="form-row">
    <div class="mb-2">
        <label>Categoría</label>
        <select name="categoria_id" class="form-control" required>
            <option value="">Seleccione Categoría</option>
            <?php foreach ($categorias as $categoria) : ?>
                <option value="<?= $categoria['categoria_id']; ?>"
                    <?= old('categoria_id') == $categoria['categoria_id'] ? 'selected' : '' ?>>
                    <?= $categoria['descripcion']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-2">
        <label>Precio de Costo</label>
        <input name="precio" type="text" required maxlength="20"
            value="<?= old('precio') ?>"
            oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1');">
        <?= $validation->getError('precio') ? "<div class='alert alert-danger mt-2'>{$validation->getError('precio')}</div>" : "" ?>
    </div>
</div>

<!-- FILA 4 -->
<div class="form-row">
    <div class="mb-2">
        <label>Precio de Venta</label>
        <input name="precio_vta" type="text" required maxlength="20"
            value="<?= old('precio_vta') ?>"
            oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1');">
        <?= $validation->getError('precio_vta') ? "<div class='alert alert-danger mt-2'>{$validation->getError('precio_vta')}</div>" : "" ?>
    </div>

    <div class="mb-2">
        <label>Precio (3 o más / CM)</label>
        <input name="precio_promo1" type="text" maxlength="20"
            value="<?= old('precio_promo1') ?>"
            oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1');">
        <?= $validation->getError('precio_promo1') ? "<div class='alert alert-danger mt-2'>{$validation->getError('precio_promo1')}</div>" : "" ?>
    </div>

    <div class="mb-2">
        <label>Precio (10 o más / CM300)</label>
        <input name="precio_promo2" type="text" maxlength="20"
            value="<?= old('precio_promo2') ?>"
            oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1');">
        <?= $validation->getError('precio_promo2') ? "<div class='alert alert-danger mt-2'>{$validation->getError('precio_promo2')}</div>" : "" ?>
    </div>
</div>

<!-- FILA 5 -->
<div class="form-row">
    <div class="mb-2">
        <label>Stock Belgrano</label>
        <input name="stock" type="text" required maxlength="11"
            value="<?= old('stock') ?>"
            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        <?= $validation->getError('stock') ? "<div class='alert alert-danger mt-2'>{$validation->getError('stock')}</div>" : "" ?>
    </div>

    <div class="mb-2">
        <label>Stock Independencia</label>
        <input name="stock_mb2" type="text" required maxlength="11"
            value="<?= old('stock_mb2') ?>"
            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        <?= $validation->getError('stock_mb2') ? "<div class='alert alert-danger mt-2'>{$validation->getError('stock_mb2')}</div>" : "" ?>
    </div>

    <div class="mb-2">
        <label>Stock Mínimo (Ambos)</label>
        <input name="stock_min" type="text" required maxlength="11"
            value="<?= old('stock_min') ?>"
            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        <?= $validation->getError('stock_min') ? "<div class='alert alert-danger mt-2'>{$validation->getError('stock_min')}</div>" : "" ?>
    </div>
</div>

<br>

<div align="end">
    <a href="<?= base_url('Lista_Productos'); ?>" class="btn">Cancelar</a>
    <button type="button" class="btn" onclick="abrirPopupGuardar()">Guardar</button>
</div>

</form>
</div>

<?php } else { ?>
<h2>Su perfil no tiene acceso a esta parte.</h2>
<?php } ?>

<!-- SWEETALERT2 Y SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function abrirPopupGuardar() {

    // Validar el formulario nativo antes de abrir el popup
    const form = document.getElementById('productoForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    Swal.fire({
        title: '¿Guardar en Independencia también?',
        text: 'El producto se guardará en Belgrano. ¿Desea guardarlo también en el local Independencia?',
        icon: 'question',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: 'Sí, en ambos',
        denyButtonText: 'Solo Belgrano',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#50fa7b',
        denyButtonColor: '#8be9fd',
        cancelButtonColor: '#ff5555',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('local_independencia').value = 1;
            form.submit();
        } else if (result.isDenied) {
            document.getElementById('local_independencia').value = 0;
            form.submit();
        }
    });
}
</script>