<br>

<!-- Mensajes temporales -->
<?php if (session()->getFlashdata('msg')): ?>
    <div id="flash-message-success" class="flash-message success">
        <?= session()->getFlashdata('msg') ?>
    </div>
<?php endif; ?>
<?php if (session("msgEr")): ?>
    <div id="flash-message-danger" class="flash-message danger">
        <?= session("msgEr"); ?>
    </div>
<?php endif; ?>

<script>
    // Ocultar mensaje de éxito después de 3 segundos
    setTimeout(function() {
        const successMessage = document.getElementById('flash-message-success');
        if (successMessage) {
            successMessage.style.display = 'none';
        }
    }, 3000);

    // Ocultar mensaje de error después de 3 segundos
    setTimeout(function() {
        const errorMessage = document.getElementById('flash-message-danger');
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }, 3000);
</script>

<!-- Fin de los mensajes temporales -->
<br>
 <?php $cart = \Config\Services::cart(); ?>
 <?php $session = session();
          $nombre= $session->get('nombre');
          $apellido = $session->get('apellido');
          $perfil=$session->get('perfil_id');
          $email=$session->get('email');
          $telefono=$session->get('telefono');
          $direccion=$session->get('direccion');
          ?>
 <?php
 //print_r($clientes);
 //exit;
    $gran_total = 0;

    // Calcula gran total si el carrito tiene elementos
    if ($cart):
        foreach ($cart->contents() as $item):
            $gran_total = $gran_total + $item['subtotal'];
        endforeach;
    endif;
 ?>

<div class="comprados" style="width:40%;">
    <div id="">

        <?php // Crea formulario para guarda los datos de la venta
            echo form_open("confirma_compra", ['class' => 'form-signin', 'role' => 'form']);
        ?>
        <div align="center">
            <u><i><h2 align="center">Resumen de la Compra</h2></i></u>

            <table>
                <tr>
                    <td style="color: #2BD5C3;">
                        Total de la Compra:
                    </td>
                    <td>
                        <strong>$<?php echo number_format($gran_total, 2); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td style="color: #2BD5C3;">
                        Vendedor:
                    </td>
                    <td>
                        <?php echo($nombre) ?>
                    </td>
                </tr>
                <tr>
                    <td style="color: #2BD5C3;">
                                Cliente:
                         </td>
                            <td>
                                <?php if ($clientes): ?>
                                    <select name="cliente_id">
                                        <option value="Anonimo">Seleccione un cliente</option>
                                            <?php foreach ($clientes as $cl): ?>
                                            <option value="<?php echo $cl['id_cliente']; ?>">
                                                <?php echo $cl['nombre'] . ' - ' . $cl['id_cliente']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <span>No hay clientes disponibles</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                <tr>
                    <td style="color: #2BD5C3;">
                        Dirección:
                    </td>
                    <td>
                        <?php echo($direccion) ?>
                    </td>
                </tr>
                <tr>
                    <td style="color: #2BD5C3;">
                        Seleccione Tipo de Pago:
                    </td>
                    <td>
                    <select name="tipo_pago">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>

                    </select>
                    </td>
                </tr>
                <?php echo form_hidden('total_venta', $gran_total); ?>
            </table>
            <br> <br>
            <a class='btn' href="<?php echo base_url('CarritoList') ?>">Volver</a>
            <?php echo form_submit('confirmar', 'Confirmar',"class='btn'"); ?>
            <br> <br>
        </div>
    </div>
        <?php echo form_close(); ?>

    </div>
    <br>