<?php $cart = \Config\Services::cart(); ?>

<!-- Mensajes temporales -->
<?php if (session()->getFlashdata('msg')): ?>
    <div id="flash-message-success" class="flash-message success">
        <?= session()->getFlashdata('msg') ?>
    </div>
<?php endif; ?>

<?php if (session("msgEr")): ?>
    <div id="flash-message-Error" class="flash-message danger">
        <?php echo nl2br(session("msgEr")); ?>
        <button class="close-btn" onclick="cerrarMensaje()">×</button>
    </div>
<?php endif; ?> 

<script>
    function cerrarMensaje() {
        document.getElementById("flash-message-Error").style.display = "none";
    }
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

<style>
@media (max-width: 768px) { /* Para dispositivos con ancho menor o igual a 768px (tablets y teléfonos) */
    .ocultar-en-movil {
        display: none;
    }
}

    /* Estilos generales de la tabla del carrito */
.tabla-carrito {
    width: 100%;
    border-collapse: collapse;
    font-size: 16px;
}

.tabla-carrito th,
.tabla-carrito td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
}

.tabla-carrito thead {
    background-color: #f4f4f4;
    font-weight: bold;
}

/* Ajustar tabla en pantallas pequeñas */
@media screen and (max-width: 600px) {
    .tabla-carrito {
        font-size: 14px; /* Reducir tamaño de fuente */
    }
    
    .ocultar-en-movil {
        display: none; /* Ocultar columnas innecesarias */
    }

    .tabla-carrito th, 
    .tabla-carrito td {
        padding: 6px; /* Reducir espacio interno */
    }
}

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

/*Estilos para el input de motivo*/
.motivo {
    width: 100%;
    max-width: 750px;
    padding: 8px;
    border: 2px solid #50fa7b;
    background-color: #282a36;
    color: #f8f8f2;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 800px;
    color:#ffff;
}

.motivo:focus {
    outline: none;
    border-color: #8be9fd;
    box-shadow: 0 0 5px #8be9fd;
}
.total_ant {
    width: 100%;
    max-width: 300px;
    padding: 8px;
    border: 2px solid #50fa7b;
    background-color: #282a36;
    color: #f8f8f2;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 800px;
    color:#ffff;
}

.total_ant:focus {
    outline: none;
    border-color: #8be9fd;
    box-shadow: 0 0 5px #8be9fd;
}

.diferencia_result {
    width: 100%;
    max-width: 450px;
    padding: 8px;
    border: 2px solid #50fa7b;
    background-color: #282a36;
    color: red;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 800px;
    color:#ffff;
}

.diferencia_result:focus {
    outline: none;
    border-color: #8be9fd;
    box-shadow: 0 0 5px #8be9fd;
}
</style>




<?php

$id_pedido = '';
// Añadido para el tipo de compra
$session = session();
$perfil = $session->get('perfil_id');
if (!empty($session)) {
    $id_pedido = $session->get('id_pedido');
    $tipo_compra = $session->get('tipo_compra');
    $estado = $session->get('estado');
    $total_anterior = $session->get('total_bonificado');
}
//print_r($perfil);
//exit;
?>

<div class="compados" style="width:100%;">

<div class="" >
<div class="contenedor">
        <u><i><h2>Productos En Carrito</h2></i></u>
        <br>
        <?php if ($estado == 'Modificando'): ?>
            <h3 class="resaltado">
                Modificando Venta/Pedido Numero: <?php echo htmlspecialchars($id_pedido, ENT_QUOTES, 'UTF-8'); ?>
            </h3>
        <?php endif; ?>
        <?php if ($estado == 'Modificando_SF'): ?>
            <h4 class="resaltado">
                "Importante!" Si se cambia un producto defectuosos por otro del mismo, ir al "Panel de descuento de Stock."
            </h4>
        <?php endif; ?>
        </div>
        <br>
        <div class="sinProductos" style="color:#ffff; " align="center" >
            <h2>
            <?php  
                if (empty($carrito)) {
                    echo 'No hay productos agregados todavía.!<br><br>';
                    
                    if ($id_pedido > 0 && $tipo_compra == 'Pedido' && $estado == 'Modificando') { ?>
                        <a href="<?php echo base_url('cancelar_edicion/' . $id_pedido); ?>" class="danger" onclick="return confirmarAccionPedido();">
                            Cancelar Modificación Pedido
                        </a>
                        <br><br>
                    <?php 
                    } elseif ($perfil == 3 && $tipo_compra == 'Compra_Normal' && $estado == 'Modificando') { ?>
                        <a href="<?php echo base_url('cancelar_edicion_Venta/' . $id_pedido); ?>" class="danger" onclick="return confirmarAccionVenta();">
                            Cancelar Modificación Venta
                        </a>
                        <br><br>
                    <?php  
                    } elseif ($perfil == 3 && $estado == 'Modificando_SF') { ?>
                        <a href="<?php echo base_url('cancelar_edicion_Venta_SF/' . $id_pedido); ?>" class="danger" onclick="return confirmarAccionVenta_SF();">
                            Cancelar Cambios en Venta
                        </a>
                        <br><br>
                    <?php 
                    } 
                } 
                ?>
            </h2>
        </div>
   

<?php
// Asegúrate de definir $gran_total antes de este script
$gran_total = isset($gran_total) ? $gran_total : 0; // Si $gran_total no está definido, usa 0 como valor
?>

        <table class="texto-negrita">

            <?php // Todos los items de carrito en "$cart".
            if ($carrito):
            ?>
                <tr class=" colorTexto2"  >
                    <td class="ocultar-en-movil">ID</td>
                    <td>Nombre</td>
                    <td>Precio</td>
                    <td>Precio Efectivo(-10%)</td>
                    <td>Cantidad</td>
                    <td>Subtotal</td>
                    <td>Sub.Tot. Efectivo(-10%)</td>
                    <td>Eliminar?</td>
                </tr>
                
            <?php // Crea un formulario php y manda los valores a carrito_controller/actualiza carrito
            echo form_open('carrito/procesarCarrito', ['id' => 'carrito_form']); // Deja vacío para enviar al mismo controlador
                $gastos = 0;
                $i = 1;

                foreach ($carrito as $item):
                    echo form_hidden('cart[' . $item['id'] . '][id]', $item['id']);
                    echo form_hidden('cart[' . $item['id'] . '][rowid]', $item['rowid']);
                    echo form_hidden('cart[' . $item['id'] . '][name]', $item['name']);
                    echo form_hidden('cart[' . $item['id'] . '][price]', $item['price']);
                    echo form_hidden('cart[' . $item['id'] . '][qty]', $item['qty']);
            ?>
                    <tr style="color: black;" >
                        
                        <td  class="separador ocultar-en-movil" style="color: #ffff;">
                            <?php echo $i++; ?>
                        </td>
                        <td class="separador" style="color: #ffff;">
                            <?php echo $item['name']; ?>
                        </td>

                        <td class="separador"  style="color: #ffff;">
                        $ <?php  echo number_format($item['price'], 2, ',', '.');?>
                        </td>

                        <td class="separador"  style="color: #ffff;">
                        $ <?php  echo number_format($item['price'] / 1.1, 2, ',', '.');?>
                        </td>     

                        <td class="separador" style="color: #ffff;">
                        <?php 
                            if ($item['id'] < 10000) {
                                echo form_input([
                                    'name' => 'cart[' . $item['id'] . '][qty]',
                                    'value' => $item['qty'],
                                    'type' => 'number',
                                    'min' => '1',
                                    'maxlength' => '3',
                                    'size' => '1',
                                    'style' => 'text-align: right; width: 50px;',
                                    'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"
                                ]);?>
                                <span class="stock-disponible"> (Mas <?php echo  $item['options']['stock']; ?> Disponibles ) </span>
                            <?php } else {
                                echo number_format($item['qty']);
                            }
                            ?>
                        </td>
                        
                            <?php $gran_total = $gran_total + $item['subtotal']; ?>

                        <td class="separador" style="color: #ffff;">
                        $ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?>
                        </td>

                        <td class="separador" style="color: #ffff;">
                        $ <?php echo number_format($item['subtotal'] / 1.1, 2, ',', '.'); ?>
                        </td>

                        <td class="imagenCarrito separador" style="color: #ffff;">
                            <?php // Imagen para Eliminar Item
                                $path = '<img src= '. base_url('assets/img/icons/basura3.png') . ' width="10px" height="10px">';
                                echo anchor('carrito_elimina/'. $item['rowid'], $path);
                            ?>
                            
                        </td>
                        
                    </tr>
                    
                <?php
                endforeach;
                ?>

                    <?php if ($estado == 'Modificando_SF'): ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td colspan="6" align="right">
                                <label style="color:orange;" for="motivo_cambio">Motivo de los cambios de la Venta:</label>
                                <input class="motivo" type="text" id="motivo_cambio" name="motivo_modif" placeholder="Ingrese el motivo de los cambios">
                                <h4 class="total_ant">Total Anterior: $
                                    <?php //Gran Total
                                    echo number_format($total_anterior, 2);
                                    ?>                    
                                </h4>
                                <h4 class="total_ant" id="total_actual">Total Actual: $ <?php echo number_format($gran_total, 2); ?></h4>
                                <label style="color:orange;" for="tipo_pago">Paga la Diferencia Con:</label>
                                <select class="total_ant" id="tipo_pago" name="tipo_pago_dif" onchange="calcularDiferencia()">
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Efectivo">Efectivo</option>
                                </select>                                
                                <h4 class="total_ant" id="diferencia">Diferencia: $ <?php echo number_format($gran_total - $total_anterior, 2); ?></h4>
                                <?php if ($gran_total - $total_anterior < 0): ?>
                                    <h3 style="color:#f42632;" class="diferencia_result">Atencion! La diferencia Resultó Plata a favor del Cliente.</h3>
                                <?php endif; ?>
                                                                
                                <br>
                                <h4 style="color:orange;">Si la Diferencia es Negativa, significa que es dinero a favor del Cliente.</h4>
                            </td>       
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="5" align="right">
                        <?php if ($estado != 'Modificando_SF'): ?>                        
                            <br>
                            <h4 class="totalVenta">Total Actual: $
                                <?php //Gran Total
                                echo number_format($gran_total, 2);
                                ?>
                            </h4>
                        <?php endif; ?>

                        <h4></h4>
                        <br>
                        <input type="hidden" id="accion" name="accion" value=""> <!-- Este campo controlará a qué función se envía -->

                        <!-- Cancelar edicion de pedido -->
                        <?php if ($id_pedido > 0 && $tipo_compra == 'Pedido' && $estado == 'Modificando') { ?>
                            <a href="<?php echo base_url('cancelar_edicion/'.$id_pedido);?>" class="danger" onclick="return confirmarAccionPedido();">
                                Cancelar Modificación Pedido
                            </a>
                            <?php } else if ($perfil == 3 && $tipo_compra == 'Compra_Normal' && $estado == 'Modificando'){?>
                                <a href="<?php echo base_url('cancelar_edicion_Venta/'.$id_pedido);?>" class="danger" onclick="return confirmarAccionVenta();">
                                Cancelar Modificación Venta
                                </a>
                            <?php  } else if ($perfil == 3 && $estado == 'Modificando_SF'){?>
                                <a href="<?php echo base_url('cancelar_edicion_Venta_SF/'.$id_pedido);?>" class="danger" onclick="return confirmarAccionVenta_SF();">
                                Cancelar Cambios en Venta
                                </a>
                                <br><br>
                            <?php  } else {  ?>
                                <!-- Borrar carrito usa mensaje de confirmacion -->
                            <a href="<?php echo base_url('carrito_elimina/all');?>" class="danger" onclick="return confirmarAccionCompra();">
                                        Borrar Todo
                            </a>
                            <?php  } ?>
                        <!-- Submit boton. Actualiza los datos en el carrito -->
                        <button type="submit" class="success" onclick="setAccion('actualizar')">
                            Actualizar Importes
                        </button>                        
                                
                            <br><br>
                            <?php if(($tipo_compra == 'Pedido' || $perfil == 2) && ($estado == '' || $estado == 'Modificando')) { ?>
                        <!-- " Confirmar orden envia a carrito_controller/muestra_compra  -->
                        <a href="javascript:void(0);" class="success" onclick="setAccion('confirmar')">Continuar Compra</a>
                                
                        <?php }else if ($perfil == 3 && $tipo_compra == 'Compra_Normal' && $estado == 'Modificando'){ ?>            
                        <!-- Envia los cambios y Modifica e impacta los cambios de la venta modificada -->
                        <a href="javascript:void(0);" class="success" onclick="setAccion('modificar')">Modificar Venta</a>

                        <?php } else if($perfil == 3 && $estado == 'Modificando_SF') {?>
                        <!-- Envia los cambios y Modifica e impacta los cambios de la venta modificada -->
                        <a href="javascript:void(0);" class="success" onclick="setAccion('GuardarCambios')">Guardar Cambios</a>      
                            <?php } ?>
                    </td>
                </tr>
                <?php echo form_close();
            endif; ?>
        </table>
    </div>
</div>

<script>
    function setAccion(accion) {
    // Asignamos la acción al campo oculto
    document.getElementById('accion').value = accion;

    // Enviamos el formulario
    document.getElementById('carrito_form').submit();
}

</script>


<script>
    function calcularDiferencia() {
        const tipoPago = document.getElementById('tipo_pago').value;
        const granTotal = <?php echo $gran_total; ?>;
        const totalAnterior = <?php echo $total_anterior; ?>;
        let diferencia = granTotal - totalAnterior;

        if (tipoPago === 'Efectivo') {
            diferencia = diferencia / 1.1; // Aplicar descuento del 10% solo a la diferencia
        }

        // Mostrar la diferencia con el descuento aplicado (si corresponde)
        document.getElementById('diferencia').innerText = `Diferencia: $${diferencia.toFixed(2)}`;
    }
</script>




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

    function confirmarAccionVenta() {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se cancelara la modificacion de la Venta y quedara como estaba.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Cancelar Edicion",
            cancelButtonText: "Volver"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelar_edicion_Venta/'.$id_pedido); ?>";
            }
        });
        return false; // Evita que el enlace siga su curso normal
    }

    function confirmarAccionVenta_SF() {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se cancelara la modificacion de la Venta y quedara como estaba.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, Cancelar Cambios",
            cancelButtonText: "Volver"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelar_edicion_Venta_SF/'.$id_pedido); ?>";
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
            cancelButtonText: "Volver"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?php echo base_url('cancelar_edicion/'.$id_pedido); ?>";
            }
        });
        return false; // Evita que el enlace siga su curso normal
    }

</script>

<br>