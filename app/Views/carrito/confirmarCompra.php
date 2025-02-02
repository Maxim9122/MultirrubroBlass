<?php
$cart = \Config\Services::cart(); 
$session = session();
$nombre = $session->get('nombre');
$perfil = $session->get('perfil_id');


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
//print_r($tipo_compra);
//exit;
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
                    <td style="color:rgb(192, 250, 214);"><strong>Total General:</strong></td>
                    <td style="color: #ffff;"><strong id="totalCompra">$<?php echo number_format($gran_total, 2); ?></strong></td>
                </tr>
                <tr>
                    <td style="color:rgb(192, 250, 214);"><strong>Vendedor:</strong></td>
                    <td style="color: #ffff;"><?php echo($nombre) ?></td>
                </tr>
                <tr>
                    <td style="color:rgb(192, 250, 214);"><strong>Cliente:</strong></td>
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
                    <td style="color: rgb(192, 250, 214);"><strong>Seleccione Tipo de Pago:</strong></td>
                    <td>
                        <select name="tipo_pago" id="tipoPago">
                            <option value="Transferencia">Transferencia</option>
                            <option value="Efectivo">Efectivo (-5%)</option>                            
                        </select>
                    </td>
                </tr>
                <tr name="total_conDescuento" id="totalConDescuentoFila" style="display: none;">
                    <td style="color: rgb(192, 250, 214);"><strong>Total con Descuento:</strong></td>
                    <td style="color: #ffff;"><strong id="totalConDescuento">-</strong></td>
                </tr>
                <tr>
                    <td style="color: rgb(192, 250, 214);"><strong>Tipo de Compra o Pedido:</strong></td>
                    <td>
                    <?php if($id_cliente){ ?>
                        <select name="tipo_compra" id="tipoCompra">
                            <option value="Pedido" <?php echo $tipo_compra == 'Pedido' ? 'selected' : ''; ?>>Reservar Pedido</option>
                            <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>Compra Normal</option>                           
                        </select>
                    <?php  } else {  ?>
                        <select name="tipo_compra" id="tipoCompra">
                            <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>Compra Normal</option>
                            <option value="Pedido" <?php echo $tipo_compra == 'Pedido' ? 'selected' : ''; ?>>Reservar Pedido</option>
                        </select>
                        <?php  } ?>
                        <?php echo form_hidden('tipo_compra_input', $tipo_compra); ?>
                    </td>
                </tr>
                <tr id="fechaPedidoFila" style="display: <?php echo $fecha_pedido ? 'table-row' : 'none'; ?>;">
                    <td style="color: rgb(192, 250, 214);"><strong>Fecha de entrega del Pedido:</strong></td>
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
            <?php if ($id_cliente) { ?>
                <a href="<?php echo base_url('carrito_elimina/all');?>" class="danger" onclick="return confirmarAccionPedido();">
                    Cancelar Modificación de Pedido
                </a>
            <?php } ?>

            <?php echo form_submit('confirmar', 'Confirmar',"class='btn'"); ?>
            <br> <br>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script>
    function confirmarAccionPedido() {
        return confirm("¿Desea cancelar la Modificacion del Pedido?");
    }
</script>

<!-- Modal -->
<div id="confirmationModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Elija el tipo de Ticket para imprimir.!</p>
        <button id="invoiceArca" class="btn">Factura C (Arca)</button>
        <button id="printTicket" class="btn">Imprimir Ticket</button>        
    </div>
</div>

<style>
    /* Estilos para el modal */
.modal {
    display: none; /* Oculto por defecto */
    position: fixed; /* Posición fija */
    z-index: 1; /* Encima de todo */
    left: 0;
    top: 0;
    width: 100%; /* Ancho completo */
    height: 100%; /* Alto completo */
    overflow: auto; /* Habilitar scroll si es necesario */
    background-color: rgb(0,0,0); /* Color de fondo */
    background-color: rgba(0,0,0,0.4); /* Negro con opacidad */
    padding-top: 60px;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto; /* 5% desde la parte superior y centrado */
    padding: 20px;
    border: 7px solid #888;
    width: 70%; /* Ancho del contenido */
    max-width: 400px; /* Ancho máximo */
    text-align: center;
}

.modal-content p{
    font-weight: 800;
    background-color: #fefefe;
    margin: 5% auto; /* 5% desde la parte superior y centrado */
    padding: 20px;
    border: 7px solid #888;
    width: 70%; /* Ancho del contenido */
    max-width: 400px; /* Ancho máximo */
    text-align: center;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    font-weight: 700;
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>


<script>
    document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("confirmationModal");
    const btnConfirmar = document.querySelector("input[name='confirmar']");
    const spanClose = document.getElementsByClassName("close")[0];
    const btnPrintTicket = document.getElementById("printTicket");
    const btnInvoiceArca = document.getElementById("invoiceArca");

    btnConfirmar.addEventListener("click", function (event) {
    event.preventDefault(); // Evita el envío inmediato del formulario
    
    const tipoCompra = document.getElementById("tipoCompra").value;

    if (tipoCompra === "Pedido") {
        // Si es "Reservar Pedido", enviar directamente el formulario sin abrir el modal
        document.querySelector("form").submit();
    } else {
        // Si es una compra normal, abrir el modal
        modal.style.display = "block";
    }
    });

    // Cuando el usuario hace clic en <span> (x), cierra el modal
    spanClose.addEventListener("click", function () {
        modal.style.display = "none";
    });

    // Cuando el usuario hace clic en "Imprimir Ticket", envía el formulario
    btnPrintTicket.addEventListener("click", function () {
        document.querySelector("form").submit();
    });

    // Cuando el usuario hace clic en "Facturar Arca", puedes agregar la lógica necesaria
    btnInvoiceArca.addEventListener("click", function () {
        alert("Facturar Arca no está implementado aún.");
    });

    // Cuando el usuario hace clic fuera del modal, ciérralo
    window.addEventListener("click", function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });

    // Cuando el usuario presiona la tecla Escape, ciérralo
    window.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            modal.style.display = "none";
        }
    });
});
</script>

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