<?php $cart = \Config\Services::cart(); ?>

<?php if (session()->getFlashdata('msg')): ?>
    <div id="flash-message" class="success" style="width: 30%;">
        <?= session()->getFlashdata('msg') ?>
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('flash-message').style.display = 'none';
        }, 2000); // 2000 milisegundos = 2 segundos
    </script>
<?php endif; ?>
  <?php if(session("msgEr")):?>
   <div class="secondary" style="width: 30%;">
      <?php echo session("msgEr"); ?>
      </div>
  <?php endif?>
<br>

<div class="comprados" style="width:100%;">

<div class="cart" >
        <div class = "heading">
            <u><i><h2>Productos Seleccionados</h2></i></u>
        </div>
        <div class="texto-negrita" align="center" >

            <?php  
            // Si el carrito está vacio, mostrar el siguiente mensaje
            if (empty($carrito))
            {
                echo 'Para agregar productos al carrito, click en "Comprar"';
                
            }
            ?>
        </div>
       <!-- Formulario para agregar un Gastos de productos no registrados-->
    <form action="<?php echo base_url('Otros_gastos');?>" method="post" class="alineacion_Y_texto">
    <input type="hidden" name="id" value="10000">
    <label for="nombre_producto">Otros Gastos:</label>
    <input type="text" name="nombre" id="nombre_producto" class="no-border-input" placeholder="Nombre" required>
    <label for="precio_producto">Monto/Precio:</label>
    <input type="text" name="precio_vta" id="precio_producto" class="no-border-input" placeholder="Importe en $" required>    
    <button class="success margenArriba" type="submit" >Agregar y Sumar</button>
    </form>
<br>

<?php
// Asegúrate de definir $gran_total antes de este script
$gran_total = isset($gran_total) ? $gran_total : 0; // Si $gran_total no está definido, usa 0 como valor
?>

        <table class="texto-negrita">

            <?php // Todos los items de carrito en "$cart".
            if ($carrito):
            ?>
                <tr class=" colorTexto2"  >
                    <td>ID</td>
                    <td>Nombre</td>
                    <td>Precio</td>
                    <td>Cantidad</td>
                    <td>Subtotal</td>
                    <td>Eliminar?</td>
                </tr>
                
            <?php // Crea un formulario php y manda los valores a carrito_controller/actualiza carrito
            echo form_open('carrito_actualiza');
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
                        
                        <td  class="separador">
                            <?php echo $i++; ?>
                        </td>
                        <td class="separador">
                            <?php echo $item['name']; ?>
                        </td>
                        <td class="separador">
                        $ARS <?php  echo number_format($item['price'], 2);?>
                        </td>
                        <td class="separador">
                        <?php 
                                if($item['id'] < 10000){
                                echo form_input('cart[' . $item['id'] . '][qty]', $item['qty'],
                                'maxlength="3" size="1" style="text-align: right"');
                            }else{
                                echo number_format($item['qty']);
                            }
                        ?>
                        </td>
                            <?php $gran_total = $gran_total + $item['subtotal']; ?>
                        <td class="separador">
                        $ARS <?php echo number_format($item['subtotal'], 2) ?>
                        </td>
                        <td class="imagenCarrito separador">
                            <?php // Imagen para Eliminar Item
                                $path = '<img src= '. base_url('assets/img/icons/basura3.png') . ' width="10px" height="10px">';
                                echo anchor('carrito_elimina/'. $item['rowid'], $path);
                            ?>
                            
                        </td>
                        
                    </tr>
                    
                <?php
                endforeach;
                ?>
                
                <tr>
                    <td>
                        
                        
                    </td>
                    
                    <td colspan="5" align="right">
                        <br>
                        <h4 class="totalVenta">Total: $
                            
                            <?php //Gran Total
                            echo number_format($gran_total, 2);
                            ?>
                            
                        </h4>
                        <br>
                        <label for="pago" class="cambio">Paga con: $</label>
                        <input class="no-border-input" type="text" id="pago" placeholder="Monto en $" oninput="formatearMiles()" onkeyup="calcularCambio()">

                        <h4 class="cambio">Cambio: $ <span id="cambio">0.00</span></h4>
                        <br>


                        <!-- Borrar carrito usa mensaje de confirmacion javascript implementado en partes/head_view -->
                        <a href="<?php echo base_url('carrito_elimina/all');?>" type="submit" class="success"  >
                        Borrar Todo</a>

                        <!-- Submit boton. Actualiza los datos en el carrito -->
                        <button type="submit" class="success" onclick="actualizar()">                        
                        Actualizar Importes</button>
                        <br><br>
                        <!-- " Confirmar orden envia a carrito_controller/muestra_compra  -->
                        <a href="<?php echo base_url('comprar');?>" class ="success">Confirmar Compra</a>
                    </td>
                </tr>
                <?php echo form_close();
            endif; ?>
        </table>
    </div>
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
        const cambio = pago - granTotal;
        document.getElementById('cambio').textContent = cambio >= 0 ? cambio.toLocaleString('de-DE', { minimumFractionDigits: 2 }) : "0.00";
    }
</script>

<br>