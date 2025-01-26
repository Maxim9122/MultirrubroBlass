<?php $cart = \Config\Services::cart(); ?>
<?php 
$session = session();
$nombre = $session->get('nombre');
$apellido = $session->get('apellido');
$perfil = $session->get('perfil_id');
$email = $session->get('email');
$telefono = $session->get('telefono');
$direccion = $session->get('direccion');
?>

<?php
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
        <?php 
        // Crea formulario para guardar los datos de la venta
        echo form_open("confirma_compra", ['class' => 'form-signin', 'role' => 'form']);
        ?>
        <div align="center">
            <u><i><h2 align="center">Resumen de la Compra</h2></i></u>

            <table>
                <tr>
                    <td style="color: #2BD5C3;">Total de la Compra:</td>
                    <td><strong id="totalCompra">$<?php echo number_format($gran_total, 2); ?></strong></td>
                </tr>
                <tr>
                    <td style="color: #2BD5C3;">Vendedor:</td>
                    <td><?php echo($nombre) ?></td>
                </tr>
                <tr>
                    <td style="color: #2BD5C3;">Cliente:</td>
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
                    <td style="color: #2BD5C3;">Dirección:</td>
                    <td><?php echo($direccion) ?></td>
                </tr>
                <tr>
                    <td style="color: #2BD5C3;">Seleccione Tipo de Pago:</td>
                    <td>
                        <select name="tipo_pago" id="tipoPago">
                            <option value="Transferencia">Transferencia</option>
                            <option value="Efectivo">Efectivo (-10%)</option>                            
                        </select>
                    </td>
                </tr>
                <tr name="total_conDescuento" id="totalConDescuentoFila" style="display: none;">
                    <td style="color: #2BD5C3;">Total con Descuento:</td>
                    <td><strong id="totalConDescuento">-</strong></td>
                </tr>
                <?php echo form_hidden('total_venta', $gran_total); ?>
                <?php echo form_hidden('total_con_descuento', ''); // Campo para el descuento ?>
            </table>
            <br> <br>
            <a class='btn' href="<?php echo base_url('CarritoList') ?>">Volver</a>
            <?php echo form_submit('confirmar', 'Confirmar',"class='btn'"); ?>
            <br> <br>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tipoPago = document.getElementById("tipoPago");
        const totalConDescuentoFila = document.getElementById("totalConDescuentoFila");
        const totalConDescuento = document.getElementById("totalConDescuento");
        const granTotalOriginal = <?php echo $gran_total; ?>;
        const totalConDescuentoInput = document.querySelector('input[name="total_con_descuento"]');

        tipoPago.addEventListener("change", function () {
            const seleccion = tipoPago.value;
            let descuento = 0;

            if (seleccion === "Efectivo") {
                descuento = granTotalOriginal * 0.10;
                const totalConDescuentoCalculado = granTotalOriginal - descuento;
                totalConDescuentoFila.style.display = "table-row";
                totalConDescuento.textContent = `$${totalConDescuentoCalculado.toFixed(2)}`;
                totalConDescuentoInput.value = totalConDescuentoCalculado.toFixed(2); // Actualiza el campo oculto
            } else {
                totalConDescuentoFila.style.display = "none";
                totalConDescuento.textContent = "-";
                totalConDescuentoInput.value = ""; // Limpia el campo oculto
            }
        });
    });
</script>

