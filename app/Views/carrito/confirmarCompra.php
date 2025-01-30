<?php
$cart = \Config\Services::cart(); 
$session = session();
$nombre = $session->get('nombre');
$apellido = $session->get('apellido');
$perfil = $session->get('perfil_id');
$email = $session->get('email');
$telefono = $session->get('telefono');
$direccion = $session->get('direccion');

// Obtener los datos del carrito
$id_cliente = '';
$fecha_pedido = '';
$tipo_compra = ''; // Añadido para el tipo de compra

$cart_items = $cart->contents(); // Obtener los artículos del carrito
if (!empty($cart_items)) {
    // Tomamos los datos de las opciones del primer artículo del carrito
    $first_item = reset($cart_items); // Obtener el primer ítem del carrito
    $id_cliente = isset($first_item['options']['id_cliente']) ? $first_item['options']['id_cliente'] : '';
    $fecha_pedido = isset($first_item['options']['fecha_pedido']) ? $first_item['options']['fecha_pedido'] : '';
    $tipo_compra = isset($first_item['options']['tipo_compra']) ? $first_item['options']['tipo_compra'] : ''; // Establecemos el tipo de compra por defecto
}
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
                    <td style="color: #2BD5C3;">Total General:</td>
                    <td style="color: #ffff;"><strong id="totalCompra">$<?php echo number_format($gran_total, 2); ?></strong></td>
                </tr>
                <tr>
                    <td style="color: #2BD5C3;">Vendedor:</td>
                    <td style="color: #ffff;"><?php echo($nombre) ?></td>
                </tr>
                <tr>
                    <td style="color: #2BD5C3;">Cliente:</td>
                    <td>
                        <?php if ($clientes): ?>
                            <select name="cliente_id">
                                <option value="Anonimo">Seleccione un cliente</option>
                                <?php foreach ($clientes as $cl): ?>
                                    <option value="<?php echo $cl['id_cliente']; ?>" <?php echo $cl['id_cliente'] == $id_cliente ? 'selected' : ''; ?>>
                                        <?php echo $cl['nombre']; ?>
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
                    <td style="color: #ffff;"><?php echo($direccion) ?></td>
                </tr>
                
                <tr>
                    <td style="color: #2BD5C3;">Seleccione Tipo de Pago:</td>
                    <td>
                        <select name="tipo_pago" id="tipoPago">
                            <option value="Transferencia">Transferencia</option>
                            <option value="Efectivo">Efectivo (-5%)</option>                            
                        </select>
                    </td>
                </tr>
                <tr name="total_conDescuento" id="totalConDescuentoFila" style="display: none;">
                    <td style="color: #2BD5C3;">Total con Descuento:</td>
                    <td style="color: #ffff;"><strong id="totalConDescuento">-</strong></td>
                </tr>
                <tr>
                    <td style="color: #2BD5C3;">Tipo de Compra o Pedido:</td>
                    <td>
                        <select name="tipo_compra" id="tipoCompra">
                            <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>Compra Normal</option>
                            <option value="Pedido" <?php echo $tipo_compra == 'Pedido' ? 'selected' : ''; ?>>Compra Pedido</option>
                        </select>
                        <?php echo form_hidden('tipo_compra_input', $tipo_compra); ?>
                    </td>
                </tr>
                <tr id="fechaPedidoFila" style="display: <?php echo $fecha_pedido ? 'table-row' : 'none'; ?>;">
                    <td style="color: #2BD5C3;">Fecha de entrega del Pedido:</td>
                    <td>
                        <input type="date" name="fecha_pedido" id="fechaPedido" value="<?php echo $fecha_pedido; ?>" min="<?php echo date('Y-m-d'); ?>">
                        <?php echo form_hidden('fecha_pedido_input', $fecha_pedido); ?>
                    </td>
                </tr>
                <?php echo form_hidden('total_venta', $gran_total); ?>
                <?php echo form_hidden('total_con_descuento', ''); // Campo para el descuento ?>
                <br>
                        <label for="pago" class="cambio">Paga con: $</label>
                        <input class="no-border-input" type="text" id="pago" placeholder="Monto en $" oninput="formatearMiles()" onkeyup="calcularCambio()">

                        <h4 class="cambio" style="color: #ffff;">Cambio: $ <span id="cambio">0.00</span></h4>
                <br>
            </table>
            <br> <br>
            <a class='btn' href="<?php echo base_url('CarritoList') ?>">Volver</a>
            <!-- Cancelar edicion de pedido -->
            <?php if($id_cliente){ ?>

            <a href="<?php echo base_url('carrito_elimina/all');?>" type="submit" class="danger"  >
            Cancelar Edicion de Pedido</a>
            <?php  } ?>

            <?php echo form_submit('confirmar', 'Confirmar',"class='btn'"); ?>
            <br> <br>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script>
    // Define el total de PHP en JavaScript
    const granTotal = <?= $gran_total ?>;

    function formatearMiles() {
        const input = document.getElementById('pago');
        let valor = input.value.replace(/\./g, ''); // Quita los puntos
        if (valor === '') {
            input.value = '';
            return;
        }
        valor = parseFloat(valor).toLocaleString('de-DE'); // Agrega el formato de miles con puntos
        input.value = valor;
    }

    function calcularCambio() {
        const pago = parseFloat(document.getElementById('pago').value.replace(/\./g, '')) || 0;
        const tipoPago = document.getElementById("tipoPago").value;
        let totalAPagar = granTotal;

        if (tipoPago === "Efectivo") {
         totalAPagar = granTotal * 0.95; // Aplica el 5% de descuento
        }


        const cambio = pago - totalAPagar;
        document.getElementById('cambio').textContent = cambio >= 0 ? cambio.toLocaleString('de-DE', { minimumFractionDigits: 2 }) : "0.00";
    }
</script>

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
                descuento = granTotalOriginal * 0.05;
                const totalConDescuentoCalculado = granTotalOriginal - descuento;
                totalConDescuentoFila.style.display = "table-row";
                totalConDescuento.textContent = `$${totalConDescuentoCalculado.toFixed(2)}`;
                totalConDescuentoInput.value = totalConDescuentoCalculado.toFixed(2); // Actualiza el campo oculto
            } else {
                totalConDescuentoFila.style.display = "none";
                totalConDescuento.textContent = "-";
                totalConDescuentoInput.value = ""; // Limpia el campo oculto
            }

            // Recalcula el cambio cuando cambia el tipo de pago
            calcularCambio();
        });

        const tipoCompra = document.getElementById("tipoCompra");
        const fechaPedidoFila = document.getElementById("fechaPedidoFila");
        const fechaPedido = document.getElementById("fechaPedido");
        const tipoCompraInput = document.querySelector('input[name="tipo_compra_input"]');
        const fechaPedidoInput = document.querySelector('input[name="fecha_pedido_input"]');

        tipoCompra.addEventListener("change", function () {
            if (tipoCompra.value === "Pedido") {
                fechaPedidoFila.style.display = "table-row";
                tipoCompraInput.value = "Pedido"; // Actualiza el campo oculto
            } else {
                fechaPedidoFila.style.display = "none";
                tipoCompraInput.value = "Compra Normal"; // Actualiza el campo oculto
                fechaPedidoInput.value = ""; // Limpia el campo oculto de fecha
            }
        });

        fechaPedido.addEventListener("change", function () {
            fechaPedidoInput.value = fechaPedido.value; // Actualiza el campo oculto con la fecha seleccionada
        });

        // Establece la fecha mínima como hoy
        fechaPedido.min = new Date().toISOString().split("T")[0];
    });
</script>