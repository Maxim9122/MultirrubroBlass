<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>
          
<!-- Mensajes temporales -->
<?php if(session()->getFlashdata('mensaje_stock')): ?>
    <div id="msg_stock">
        <?= session()->getFlashdata('mensaje_stock'); ?>
    </div>
<?php endif; ?>

<style>
    #msg_stock {
        position: fixed;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        background-color: black; /* Fondo oscuro para destacar el mensaje */
        color: white;
        font-weight: bold;
        padding: 10px 20px;
        border: 3px solid #ff073a; /* Rojo flúor */
        border-radius: 5px;
        text-align: center;
        z-index: 1000;
        box-shadow: 0px 0px 10px #ff073a; /* Efecto neón */
    }
</style>

<script>
    setTimeout(function() {
        let msg = document.getElementById('msg_stock');
        if (msg) {
            msg.style.display = 'none';
        }
    }, 3000); // Se oculta después de 3 segundos
</script>
<?php if (session()->getFlashdata('msg')): ?>
        <div id="flash-message" class="flash-message success">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>
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
<br>
<div class="" style="width: 100%;">
  <div class="">
  <h2 class="textoColor" align="center">Listado de Productos</h2>
  <br>

  <section class="buscador">
  
  <form id="product_form" action="<?php echo base_url('Carrito_agrega'); ?>" method="post">
  <button type="submit" class="success">Buscar y Agregar</button>
  <br>
    <div style="position: relative; display: inline-block;">
        <input type="text" id="product_input" placeholder="Buscar producto..." autocomplete="off" required onfocus="this.value=''" />
        <select id="product_select" name="product_id" required size="3">
            <option class="separador">Seleccione un Producto!</option>
            <?php if ($productos): ?>
                
                <?php foreach ($productos as $prod): ?>
                  <?php if($prod['stock'] != 0) {?>
                    <option class="product-option" value="<?php echo $prod['id']; ?>" data-nombre="<?php echo $prod['nombre']; ?>" data-precio="<?php echo $prod['precio_vta']; ?>">
                        <?php echo $prod['nombre']; ?> <h5> ---- Precio -- $</h5> <?php echo $prod['precio_vta']; ?>
                    </option>
                    <?php  } ?>
                <?php endforeach; ?>
                
            <?php endif; ?>
        </select>
        <input type="hidden" name="nombre" id="nombre">
        <input type="hidden" name="precio_vta" id="precio_vta">
        <input type="hidden" name="id" id="product_id">
    </div>
</form>

    </section>
  
  <table class="" id="users-list">
       <thead>
          <tr class="colorTexto2">
             <th>Nombre</th>
             <th>Precio Venta</th>
             <th>Categoría</th>
             <th>Imagen</th>
             <th>Stock</th>
             <th>Acciones</th>
          </tr>
       </thead>
       <tbody>
          <?php if($productos): ?>
          <?php foreach($productos as $prod): ?>
          <tr>
             <td><?php echo $prod['nombre']; ?></td>
             <td><?php echo $prod['precio_vta']; ?></td>
             <?php  switch ($prod['categoria_id']) {
                case 1:
                    $categoria = 'Maquinas';
                    break;
                case 2:
                    $categoria = 'Perfumes';
                    break;
                case 3:
                    $categoria = 'Ropa';
                    break;
                case 4:
                    $categoria = 'Otros';
                    break;
                
            }?>
             <td><?php echo $categoria  ?></td>
             <td><img class="frmImg" src="<?php echo base_url('assets/uploads/'.$prod['imagen']);?>"></td>
             <td class="text-center"><?php echo $prod['stock']; ?></td>
             <td>
             <?php
                                     if($prod['stock'] <= 0){
                                         $btn = array(
                                         'class' => 'danger',
                                              'value' => 'Sin Stock',
                                             'disabled' => '',
                                             'name' => 'action'
                                             );
                                      echo form_submit($btn);
                                       echo form_close();

                                           ?>
                                          <?php
                                          
                                      } else if ($session){
                                        if ($perfil==2) {
                                            
                                           // Envia los datos en forma de formulario para agregar al carrito
                                   echo form_open('Carrito_agrega');
                                         echo form_hidden('id', $prod['id']);
                                         echo form_hidden('nombre', $prod['nombre']);
                                         echo form_hidden('precio_vta', $prod['precio_vta']);
                                         echo form_hidden('stock', $prod['stock']);
                                                       ?>
                                                      <?php
                                                      
                                                                        $btn = array(
                                                                         'onclick'=> 'comprar()',
                                                 'class' => 'btn',
                                                   'value' => 'Agregar',
                                                   'name' => 'action'
                                                           );
                                            echo form_submit($btn);
                                           echo form_close();
   
   
                                           }else{
                                           ?>
                                           <input class="margen" id="btnAdvertencia" type="button" onclick="alert('¡Debe registrarse o Logearse para Comprar!')" value="Desea Comprar?" />
                                           <?php  }
                                           ?>
                                           <?php
                                           } 
                                           ?>
             </td>
             
            </tr>
         <?php endforeach; ?>
         
         <?php endif; ?>
       
     </table>
     <br>
  </div>
</div>

<script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
<script src="<?php echo base_url('./assets/js/jquery-ui.js');?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
<script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>

<script>
    
    $(document).ready( function () {
      $('#users-list').DataTable( {
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página.",
            "zeroRecords": "Lo sentimos! No hay resultados.",
            "info": "Mostrando la página e _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles.",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar: ",
            "paginate": {
              "next": "Siguiente",
              "previous": "Anterior"
            }
        }
    } );
  } );

</script>

<script>

const input = document.getElementById('product_input');
const select = document.getElementById('product_select');
const form = document.getElementById('product_form'); // Obtener el formulario

// Filtrar opciones al escribir en el input
input.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const options = select.options;

    let hasOptions = false; // Para mostrar/ocultar el select
    let firstMatchIndex = -1; // Para recordar la primera coincidencia

    for (let i = 1; i < options.length; i++) { // Comenzar desde 1 para omitir la opción por defecto
        const optionText = options[i].text.toLowerCase();
        options[i].style.display = optionText.includes(searchTerm) ? 'block' : 'none';
        if (options[i].style.display === 'block') {
            hasOptions = true; // Hay opciones que coinciden
            if (firstMatchIndex === -1) {
                firstMatchIndex = i; // Guarda la primera coincidencia
            }
        }
    }

    // Mostrar el select solo si hay opciones y se ha ingresado al menos una letra
    select.style.display = hasOptions && searchTerm.length > 0 ? 'block' : 'none';

    // Si hay opciones que coinciden, selecciona la primera
    if (firstMatchIndex !== -1) {
        select.selectedIndex = firstMatchIndex; // Selecciona la primera opción que coincide
    } else {
        select.selectedIndex = 0; // Reinicia la selección si no hay coincidencias
    }
});

// Manejar la selección al cambiar el select
select.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const nombre = selectedOption.getAttribute('data-nombre');
    const precio = selectedOption.getAttribute('data-precio');

    // Actualizar los campos ocultos
    document.getElementById('nombre').value = nombre;
    document.getElementById('precio_vta').value = precio;
    document.getElementById('product_id').value = selectedOption.value;

    // Reiniciar el campo de búsqueda
    input.value = nombre; // Para que el input muestre el nombre del producto
    select.style.display = 'none'; // Ocultar el select
    highlightedIndex = -1; // Reiniciar el índice
});

// Navegación con flechas y selección con Enter
let highlightedIndex = -1;
input.addEventListener('keydown', function(event) {
    const options = Array.from(select.options).filter(option => option.style.display === 'block');

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (highlightedIndex < options.length - 1) {
            highlightedIndex++;
        }
        updateHighlight(options);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (highlightedIndex > 0) {
            highlightedIndex--;
        }
        updateHighlight(options);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        if (highlightedIndex >= 0 && highlightedIndex < options.length) {
            select.value = options[highlightedIndex].value; // Asignar el valor al select
            select.dispatchEvent(new Event('change')); // Despachar evento de cambio
            select.style.display = 'none'; // Ocultar el select
            form.submit(); // Enviar el formulario
        }
    }
});

// Función para actualizar el resaltado de las opciones
function updateHighlight(options) {
    for (let i = 0; i < options.length; i++) {
        options[i].style.backgroundColor = i === highlightedIndex ? '#5bb852' : ''; // Color de resaltado
    }
}

// Ocultar el select si se hace clic fuera de él
document.addEventListener('click', function(event) {
    if (!input.contains(event.target) && !select.contains(event.target)) {
        select.style.display = 'none';
    }
});

// Enviar el formulario cuando se presiona Enter después de seleccionar un producto
form.addEventListener('submit', function(event) {
    const productId = document.getElementById('product_id').value;
    if (!productId) {
        event.preventDefault(); // Prevenir el envío si no hay un ID de producto
        alert('Por favor, selecciona un producto antes de agregar al carrito.'); // Mensaje de error
    } else {
        // Reiniciar el estado después del envío
        input.value = '';
        select.value = ''; // Reiniciar el select
        highlightedIndex = -1; // Reiniciar el índice destacado
        Array.from(select.options).forEach(option => option.style.display = 'block'); // Mostrar todas las opciones
        select.style.display = 'none'; // Asegurarse de que el select esté oculto
    }
});

// Al cargar el documento
document.addEventListener("DOMContentLoaded", function() {
    const productInput = document.getElementById('product_input');
    productInput.focus();  // Enfoca el input
    productInput.select(); // Selecciona el texto en el input
});




</script>
<br><br>