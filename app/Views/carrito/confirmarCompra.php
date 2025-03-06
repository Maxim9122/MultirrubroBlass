<?php if (session("msgEr")): ?>
        <div id="flash-message" class="flash-message danger">
            <?php echo session("msgEr"); ?>
        </div>
    <?php endif; ?>
    <script>
        setTimeout(function() {
            document.getElementById('flash-message').style.display = 'none';
        }, 3000); // 3000 milisegundos = 3 segundos
    </script>
<!-- Fin de los mensajes temporales -->

<?php
$cart = \Config\Services::cart(); 
$session = session();
$nombre = $session->get('nombre');
$perfil = $session->get('perfil_id');

//print_r($session->get());
//exit;
// Inicializar las variables con una cadena vacía
$id_vendedor = '';
$nombre_vendedor = '';
$id_cliente = '';
$fecha_pedido = '';
$tipo_compra = '';
$tipo_pago = '';
$id_pedido = '';
$total_venta = '';

$id_cliente_cobro = '';
// Asignar valores desde la sesión solo si existen
if ($session->has('id_cliente_pedido')) {
    $id_cliente = $session->get('id_cliente_pedido');
}
if ($session->has('id_cliente')) {
    $id_cliente = $session->get('id_cliente');    
}
if ($session->has('fecha_pedido')) {
    $fecha_pedido = $session->get('fecha_pedido');
}
if ($session->has('tipo_compra')) {
    $tipo_compra = $session->get('tipo_compra');
}
if ($session->has('tipo_pago')) {
    $tipo_pago = $session->get('tipo_pago');
}
if ($session->has('id_pedido')) {
    $id_pedido = $session->get('id_pedido');
}
if ($session->has('id_vendedor')) {
    $id_vendedor = $session->get('id_vendedor');
}
if ($session->has('nombre_vendedor')) {
    $nombre_vendedor = $session->get('nombre_vendedor');
}
//print_r($nombre_vendedor);
//exit;
if ($session->has('total_venta')) {
    $total_venta = $session->get('total_venta');
}
//print_r($id_pedido);
//exit;
?>
<style>
    .resaltado {
    color: orange;
    border: 2px solid orange;
    padding: 10px;
    display: inline-block;
    border-radius: 5px;
    text-align: center;
}

.contenedor {
    text-align: center;
}
</style>
<?php
$gran_total = 0;

// Calcula gran total si el carrito tiene elementos
if ($cart):
    foreach ($cart->contents() as $item):
        $gran_total = $gran_total + $item['subtotal'];
    endforeach;
endif;
?>

<div style="width:100%;"align="center">
    <div id="">
        <?php 
        // Crea formulario para guardar los datos de la venta
        echo form_open("confirma_compra", ['class' => 'form-signin', 'role' => 'form']);
        ?>
        <br>
        <div align="center">
            <u><i><h2 align="center">Resumen de la Compra</h2></i></u>
                <br>
        <?php if (!empty($id_pedido) && $total_venta == ''): ?>
            <h3 class="resaltado">
                Modificando Pedido Numero: <?php echo htmlspecialchars($id_pedido, ENT_QUOTES, 'UTF-8'); ?>
            </h3>
            <br>
        <?php endif; ?>
            <table style="font-weight: 900;" class="tableResponsive">
            <tr>
            <td style="color:rgb(192, 250, 214);"><strong>Total General:</strong></td>
            <td style="color: #ffff;">
                <strong id="totalCompra">
                    $<?php echo number_format(($gran_total > 0 ? $gran_total : $total_venta), 2); ?>
                </strong>
            </td>
            </tr>
            <tr>
            <td style="color:rgb(192, 250, 214);"><strong>Vendedor:</strong></td>
            <td style="color: #ffff;">
                <?php echo (!empty($nombre_vendedor) ? $nombre_vendedor : $nombre); ?>
            </td>
            </tr>
                <tr>
                <td style="color:rgb(192, 250, 214);"><strong>Cliente:</strong></td>
                <td>
                    <?php if ($clientes): ?>
                        <select name="cliente_id" class="selector">
                            <option value="Anonimo">Consumidor Final</option>
                            <?php foreach ($clientes as $cl): ?>
                                <option value="<?php echo $cl['id_cliente']; ?>" <?php echo $cl['id_cliente'] == $id_cliente ? 'selected' : ''; ?>>
                                    <?php echo $cl['nombre']; ?>
                                    <?php echo "Cuil:" . $cl['cuil']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <span>No hay clientes disponibles</span>
                    <?php endif; ?>
                </td>
                 </tr>
                     
                 <?php if ($perfil == 3): ?>
                 <tr>
                <td style="color: rgb(192, 250, 214);"><strong>Monto en Transferencia:</strong></td>
                <td>
                    <input class="selector" type="text" id="pagoTransferencia" name="pagoTransferencia" placeholder="Monto en $" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, ''); formatearMiles();" onkeyup="calcularMontoEfectivo()">
                </td>
                </tr>
                <tr>
                    <td style="color: rgb(192, 250, 214);"><strong>Monto en Efectivo (-5%):</strong></td>
                    <td>
                        <input class="selector" type="text" id="pagoEfectivo" name="pagoEfectivo" placeholder="Monto en $" maxlength="15" readonly>
                    </td>
                </tr>
                <?php endif; ?>

                <tr>
                <td style="color: rgb(192, 250, 214);"><strong>Tipo de Compra o Pedido:</strong></td>
                <td>
                <select name="tipo_compra" id="tipoCompra" class="selector">
                    <?php if ($tipo_compra == 'Compra_Normal') {  ?>
                        <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>Compra Normal</option>  
            
                    <?php } else if ($tipo_compra == 'Pedido') {  ?>
                        <option value="Pedido" <?php echo $tipo_compra == 'Pedido' ? 'selected' : ''; ?>>Reservar Pedido</option>
                    
                    <?php } else {  ?>                    
                        <option value="Compra_Normal" <?php echo $tipo_compra == 'Compra_Normal' ? 'selected' : ''; ?>>Compra Normal</option>
                        <option value="Pedido" <?php echo $tipo_compra == 'Pedido' ? 'selected' : ''; ?>>Reservar Pedido</option>
                    
                    <?php } ?>
                </select>
                <?php echo form_hidden('tipo_compra_input', $tipo_compra); ?>
                </td>

                </tr>
                <tr id="fechaPedidoFila" style="display: <?php echo !empty($fecha_pedido) ? 'table-row' : 'none'; ?>;">
                <td style="color: rgb(192, 250, 214);"><strong>Fecha de entrega del Pedido:</strong></td>
                <td>
                    <input class="selector" type="date" name="fecha_pedido" id="fechaPedido" 
                           value="<?php echo !empty($fecha_pedido) ? date('Y-m-d', strtotime($fecha_pedido)) : date('Y-m-d'); ?>" 
                           min="<?php echo date('Y-m-d'); ?>">
                    <?php echo form_hidden('fecha_pedido_input', ''); ?>
                </td>
                </tr>
                <?php echo form_hidden('total_venta', ($gran_total > 0 ? $gran_total : $total_venta)); ?>
                <?php echo form_hidden('total_con_descuento', ''); // Campo para el descuento ?>
                
                <br>
            </table>
            <section class="botones-container" style="width:65%;">

            <?php if ($total_venta == '') { ?>               
            <a class="btn" href="<?php echo base_url('CarritoList') ?>">Volver</a>
            <?php } ?>

            <?php if ($total_venta != '') { ?>
                <a href="<?php echo base_url('cancelarCobro/'.$id_pedido);?>" class="btn danger">
                    Cancelar Cobro
                </a>
            <?php } else if ($id_cliente) { ?>
                <a href="<?php echo base_url('cancelar_edicion/'.$id_pedido);?>" class="btn danger" onclick="return confirmarAccionPedido();">
                    Cancelar Modificación Pedido
                </a>
            <?php } else { ?>
                <a href="<?php echo base_url('carrito_elimina/all');?>" class="btn danger" onclick="return confirmarAccionCompra();">
                    Cancelar Todo
                </a>
            <?php } ?>
            
            <?php echo form_hidden('id_pedido', $id_pedido); ?>
            <?php echo form_hidden('tipo_proceso', ''); ?>
            <?php echo form_submit('confirmar', 'Confirmar', "class='btn'"); ?>
            </section>

        </div>
    </div>
    <?php echo form_close(); ?>
</div>
            <!-- Esto es para cancelar todo, edicion de pedido o compra normal-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmarAccionCompra() {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Esto eliminará todos los productos del carrito.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Eliminar Todo",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('carrito_elimina/all'); ?>";
            }
        });
        return false; // Evita que el enlace siga su curso normal
    }

    
    function confirmarAccionPedido() {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se cancelara la modificacion del pedido y quedara como estaba.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Cancelar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelar_edicion/'.$id_pedido); ?>";
            }
        });
        return false; // Evita que el enlace siga su curso normal
    }

</script>

<!-- Modal (Cartel de confirmacion y opciones de tipo de compra)-->
<div id="confirmationModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <?php if($perfil == 3):?>
        <p>Desea Facturar (Factura tipo C) o solo imprimir ticket.?</p>
        <button id="invoiceArca" class="btn">Factura C (Arca)</button>
        <button id="printTicket" class="btn">Imprimir Ticket</button> 
        <?php endif; if($perfil == 2):?>
            <p>Registrar Compra?</p>
            <button id="printTicket" class="btn">Si, Registrar</button>
        <?php endif; ?>       
    </div>
</div>

<style>
.tableResponsive{
    width: 50%;
    text-align: center;
}
@media screen and (max-width: 768px) {
.tableResponsive{
    width: 100%;
}
}
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
    font-weight: 750;
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
    box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.3);
}

/*Estilos para los selectores de fecha, cliente y tipo compra*/
.selector {
    width: 85%;
    padding: 8px;
    border: 2px solid #50fa7b;
    background-color: #282a36;
    color: #f8f8f2;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
}

.selector:focus {
    outline: none;
    border-color: #8be9fd;
    box-shadow: 0 0 5px #8be9fd;
}

/*Estilos para los botones de confirmar, cancelar modif o volver*/
.botones-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center; /* Centra los botones horizontalmente */
    padding: 15px;
}

.btn {
    padding: 10px 20px;
    background-color: #50fa7b;
    color: #282a36;
    text-decoration: none;
    font-size: 16px;
    border-radius: 5px;
    transition: background 0.3s;
    text-align: center;
    display: inline-block;
}

.btn:hover {
    background-color: #8be9fd;
}

.danger {
    background-color: #ff5555;
    color: white;
}

.danger:hover {
    background-color: #ff4444;
}

/* Responsive */
@media (max-width: 600px) {
    .botones-container {
        flex-direction: column;
        align-items: center;
    }

    .btn {
        width: 100%;
        text-align: center;
    }
}

</style>

<?php
// Determina el valor correcto del total de la venta
$totalVenta = ($gran_total > 0) ? $gran_total : $total_venta;
?>

<script>
    // Pasa el valor de PHP a JavaScript
    const granTotal = <?php echo json_encode($totalVenta); ?>;

    document.addEventListener("DOMContentLoaded", function () {
        // Calcula el monto en efectivo con descuento al cargar la página
        calcularMontoEfectivo();
    });

    function calcularMontoEfectivo() {
        const pagoTransferencia = parseFloat(document.getElementById('pagoTransferencia').value.replace(/\./g, '')) || 0;
        const totalVenta = granTotal;

        // Calcula cuánto falta pagar después del pago en transferencia
        const faltaPagar = totalVenta - pagoTransferencia;

        // Aplica el descuento del 5% al monto en efectivo
        const montoEfectivoConDescuento = faltaPagar / 1.05; // Aplica el 5% de descuento

        // Muestra el monto en efectivo con descuento
        document.getElementById('pagoEfectivo').value = montoEfectivoConDescuento.toLocaleString('de-DE', { minimumFractionDigits: 2 });

        // Si no se ingresa monto en transferencia, el monto en efectivo es el total con descuento
        if (pagoTransferencia === 0) {
            const totalConDescuento = totalVenta * 0.95;
            document.getElementById('pagoEfectivo').value = totalConDescuento.toLocaleString('de-DE', { minimumFractionDigits: 2 });
        }
    }
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("confirmationModal");
    const btnConfirmar = document.querySelector("input[name='confirmar']");
    const spanClose = document.getElementsByClassName("close")[0];
    const btnPrintTicket = document.getElementById("printTicket");
    const btnInvoiceArca = document.getElementById("invoiceArca");
    const tipoProcesoInput = document.querySelector("input[name='tipo_proceso']");

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
        tipoProcesoInput.value = "factura"; // Establece que es una factura tipo C
        document.querySelector("form").submit();
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
    document.addEventListener("DOMContentLoaded", function () {
        const tipoCompra = document.getElementById("tipoCompra");
        const fechaPedidoFila = document.getElementById("fechaPedidoFila");

        // Función para mostrar/ocultar el campo de fecha
        function actualizarFechaPedido() {
            if (tipoCompra.value === "Pedido") {
                fechaPedidoFila.style.display = "table-row"; // Muestra el campo de fecha
            } else {
                fechaPedidoFila.style.display = "none"; // Oculta el campo de fecha
            }
        }

        // Ejecuta la función al cargar la página para verificar el valor inicial
        actualizarFechaPedido();

        // Agrega el evento change al select
        tipoCompra.addEventListener("change", function () {
            actualizarFechaPedido();
        });
    });
</script>