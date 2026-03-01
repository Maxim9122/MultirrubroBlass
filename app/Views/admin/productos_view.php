<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>  
 <?php if($perfil == 1){  ?>

<!-- Mensajes temporales -->
<?php if(session()->getFlashdata('mensaje_stock')): ?>
    <div id="msg_stock">
        <?= session()->getFlashdata('mensaje_stock'); ?>
    </div>
<?php endif; ?>

<style>
    #msg_stock {
        position: fixed;
        top: 100px;
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

    @media (max-width: 768px) { /* Aplica cambios en pantallas pequeñas */
    table td:last-child {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1px; /* Espaciado entre los botones */
        min-height: 50px; /* Ajusta la altura mínima según necesites */
    }
    
    table td:last-child a {
        width: 100%; /* Hace que los botones ocupen todo el ancho */
        text-align: center;
    }
}
      /* Hacer el campo de búsqueda más largo y ancho */
      .dataTables_filter input {
        width: 300px; /* Ajusta el tamaño según sea necesario */
        height: 55px; /* Ajusta la altura si lo deseas */
        font-size: 20px; /* Tamaño de la fuente */
        padding: 5px 10px; /* Añadir espacio dentro del campo */
        border-radius: 5px; /* Bordes redondeados */
        border: 1px solid #ccc; /* Borde gris claro */
    }

    /* Cambiar el color y hacer más nítida la letra del placeholder */
    .dataTables_filter input::placeholder {
        color: white; /* Cambiar a blanco */
        opacity: 1; /* Asegura que el color del placeholder no sea opaco */
        font-weight: bold; /* Hacer el texto más nítido */
    }


    .botones-acciones {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    justify-content: center;
}

.botones-acciones .btn {
    flex-shrink: 0;
}


.botones-acciones .btn {
    flex-shrink: 0;
}

 .paginacion-productos .pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
}

.paginacion-productos .pagination li {
    margin: 10px 5px;
}

.paginacion-productos .pagination li a,
.paginacion-productos .pagination li span {
    display: inline-block;
    padding: 8px 12px;
    background-color: #000;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    border: 1px solid #ff073a;
}

/* Página actual seleccionada */
.paginacion-productos .pagination li.active a,
.paginacion-productos .pagination li.active span {
    background-color: #ff073a;
    color: white;
    font-weight: bold;
    border-bottom: 4px solid white;
}

.busqueda-form-derecha {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-bottom: 20px;
    margin-right: 5px;
    margin-top:10px;
    flex-wrap: wrap;
}

.busqueda-input {
    padding: 10px 15px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    flex: 1 1 250px;
    max-width: 400px;
    font-family: 'Segoe UI', sans-serif;
}

.busqueda-btn {
    padding: 10px 20px;
    background-color:rgb(88, 87, 87);
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-family: 'Segoe UI', sans-serif;
    transition: background-color 0.3s ease;
}

.busqueda-btn:hover {
    background-color:rgb(78, 117, 83);
}

@media (max-width: 600px) {
    .busqueda-form-derecha {
        flex-direction: column;
        align-items: flex-end;
    }

    .busqueda-input {
        width: auto;
        min-width: 200px;
        max-width: 100%;
        flex: none;
    }

    .busqueda-btn {
        width: auto;
    }
}

.btn-tipos-precio {
    width: 18px;
    height: 20px;
    border: 1px solid #28a745;   /* borde verde */
    background-color: #ffffff;   /* fondo blanco */
    color: #28a745;              /* letra verde */
    font-weight: bold;
    font-size: 13px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Hover */
.btn-tipos-precio:hover {
    background-color: #28a745;   /* fondo verde */
    color: #ffffff;              /* letra blanca */
    transform: scale(1.12);      /* pequeño zoom */
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

/* Click */
.btn-tipos-precio:active {
    transform: scale(1.05);
}
</style>

<script>
    setTimeout(function() {
        let msg = document.getElementById('msg_stock');
        if (msg) {
            msg.style.display = 'none';
        }
    }, 1500); // Se oculta después de 1.5 segundos
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
 
<section class="contenedor-titulo">
  <strong class="titulo-vidrio">ABM de Productos</strong>
  </section>
  
  <section style="width: 100%; text-align: center; margin-top:50px; font-weigth:900;">
    <a href="<?= base_url('Lista_Productos')?>" class="btn">MOSTRAR TODOS</a>
  </section>

<div style="width: 100%; text-align: end;">
<div style="position: relative; width: 100%;">
    <!-- Tu contenido actual aquí -->
     <?php if($perfil == 1 || $perfil == 3){?>
     <br><br><br><br>                   
    <!-- Botón Descontar Defectuosos -->
    <a class="btn" href="<?php echo base_url('descontarDefectuosos');?>" style="position: absolute; bottom: 0; right: 0; margin: 20px; color:red; font-weight: 900;">
        Descontar Defectuosos
    </a>    
    <?php  } ?>
</div>
<div style="position: relative; width: 100%;">
    <!-- Tu contenido actual aquí -->
     <?php if($perfil == 1 || $perfil == 3){?>
     <br><br>                  
    <!-- Botón Descontar Defectuosos -->
    <a class="btn" href="<?php echo base_url('filtrarHistorial');?>" style="position: absolute; bottom: 0; right: 0; margin: 20px; color:Green; font-weight: 900;">
        Historial de Modificaciones
    </a>   
    <?php  } ?>
</div>
  <div class="dropdown2" style="margin-right: 45px;">
        <span class="dropdown-toggle2 btn">Mas Opciones▼</span>
        <ul class="dropdown-menu2">
            <li>
            <a class="btn" href="<?php echo base_url('StockBajo');?>">
                    📄 Productos Stock Bajo
                </a>
            </li>
            <li>
                <a class="btn" href="<?php echo base_url('nuevoProducto');?>">
                    📄 Crear Producto
                </a>
            </li>
            <li>
                <a class="btn" href="<?php echo base_url('eliminadosProd');?>">
                    ❌ Eliminados
                </a>
            </li>
                </ul>
    </div>

        <form method="get" action="<?= base_url('Lista_Productos') ?>" class="busqueda-form-derecha">
        <?php $request = \Config\Services::request(); ?>
        <input type="text" name="search" value="<?= esc($request->getGet('search')) ?>" placeholder="Buscar productos..." class="busqueda-input" autofocus>
        <button type="submit" class="busqueda-btn">Buscar</button>
        </form>
        <script>
        window.addEventListener('DOMContentLoaded', function() {
            const input = document.querySelector('.busqueda-input');
            if (input) {
                input.focus();
                input.setSelectionRange(input.value.length, input.value.length); // Opcional: pone el cursor al final
            }
        });
        </script>



  <div class="mt-3 text">
      <!-- Variables para calcular cuanto hay en $ en mercaderia total -->
  <?php $TotalArticulos= 0; 
        $totalCU = 0;
  ?>

  <!-- Si no se busco nada aun no muestra nada -->
        <?php if (isset($pager)): ?>

  <br>
  <table class="table table-responsive table-hover" id="users-list">
       <thead>
          <tr class="colorTexto2">
             <th>Nombre</th>
             <th>Precio Costo</th>
             <th>Precio Venta</th>
             <th>Categoría</th>
             <th>Imagen</th>
             <th>Stock</th>
             <th>Acciones</th>
          </tr>
       </thead>
       <tbody style="color:white;">
          <?php if($productos): ?>
          <?php foreach($productos as $prod): ?>
            <tr>
             <td><?php echo $prod['nombre']; ?></td>
             <td>
                    <form method="post" action="<?php echo base_url('/EdicionRapidaProd') ?>">
                    <input type="hidden" name="search" value="<?= esc($request->getGet('search')) ?>">
                    <?php echo form_hidden('page', $page ?? 1); ?>  <!-- Página actual enviada aquí -->
                    <input type="number" step="0.01" name="precio" value="<?php echo $prod['precio']; ?>" 
                    class="form-control form-control-sm d-inline" style="width: 110px; text-align:center;">
             </td>
             <td>
                    <input type="number" step="0.01" name="precio_vta" value="<?php echo $prod['precio_vta']; ?>" 
                        class="form-control form-control-sm d-inline" style="width: 110px; text-align:center;">
                    <input type="hidden" name="id_prod" value="<?php echo $prod['id']; ?>">
                    
                       <button type="button"
                            class="btn-tipos-precio"
                            data-id="<?= $prod['id']; ?>">
                        P
                    </button>
                   
            </td>
  
             <?php 
             $categoria_nombre = 'Desconocida';
             foreach ($categorias as $categoria) {
                 if ($categoria['categoria_id'] == $prod['categoria_id']) {
                     $categoria_nombre = $categoria['descripcion'];
                     break;
                 }
             }
             ?>
             <td><?php echo $categoria_nombre; ?></td>
             
             <td><img class="frmImg" src="<?php echo base_url('assets/uploads/'.$prod['imagen']);?>"></td>
             
             <td class="text-center">
                <?php if($prod['stock'] <= $prod['stock_min']){ ?>
                    <span class="low-stock-ring">
                        <input type="number" name="stock" value="<?php echo $prod['stock']; ?>" 
                            class="form-control form-control-sm d-inline" style="width: 60px;">
                    </span>
                <?php } else { ?>
                    <input type="number" name="stock" value="<?php echo $prod['stock']; ?>" 
                        class="form-control form-control-sm d-inline" style="width: 60px;">
                <?php } ?>
                
            </td>
             
            <td>
            <div class="botones-acciones">
                <form action="" method="post" style="display:inline;">
                    <button type="submit" class="btn btn-primary">
                        💾 Edit Rápido
                    </button>
                </form>

                <a class="btn btn-outline-warning" href="<?php echo base_url('ProductoEdit/'.$prod['id']); ?>">
                    ✏️ Editar
                </a>

                <a class="btn btn-outline-danger" href="<?php echo base_url('deleteProd/'.$prod['id']); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                        <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"/>
                    </svg> Eliminar
                </a>
            </div>
        </td>

             <?php $totalCU = $prod['precio_vta'] * $prod['stock']; ?>
             <?php $TotalArticulos = $TotalArticulos + $totalCU; ?>
            </tr>
         <?php endforeach; ?>
         <?php endif; ?>       
         
        <div class="paginacion-productos" style="text-align: end; margin-top: 20px;">
            
         <?= $pager->links() ?>

        <?php endif; ?>

        </div>

     </table>
     <h2 class="estiloTurno textColor day">Total en articulos: $ <?php echo $TotalArticulos ?></h2>
     <br>
  </div>
</div>

<div class="paginacion-productos">
    <?= $pager->links() ?>
</div>

<!-- ================= MODAL TIPOS DE PRECIO ================= -->
<div id="modalTiposPrecio" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:99999;">

    <div style="
        position:absolute;
        top:50%;
        left:50%;
        transform:translate(-50%,-50%);
        background:#fff;
        width:650px;
        border-radius:10px;
        padding:20px;
        box-shadow:0 10px 30px rgba(0,0,0,0.3);
    ">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="margin:0;">Editar Tipos de Precio</h3>
            <span id="cerrarModal" style="cursor:pointer; font-size:22px; font-weight:bold;">&times;</span>
        </div>

        <form id="formTiposPrecio">

            <input type="hidden" id="producto_id_hidden" name="producto_id">

            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="border:1px solid #ddd; padding:8px;">Nombre</th>
                        <th style="border:1px solid #ddd; padding:8px;">Precio</th>
                        <th style="border:1px solid #ddd; padding:8px;">Cantidad</th>
                    </tr>
                </thead>
                <tbody id="contenidoTiposPrecio"></tbody>
            </table>

            <div style="text-align:right; margin-top:15px;">
                <button type="submit" style="
                    padding:8px 15px;
                    background:#28a745;
                    color:white;
                    border:none;
                    border-radius:5px;
                    cursor:pointer;
                ">
                    Guardar Cambios
                </button>
            </div>

        </form>

    </div>
</div>
<!-- ========================================================== -->
 <script>
document.addEventListener("DOMContentLoaded", function(){

    const modal = document.getElementById("modalTiposPrecio");
    const cerrar = document.getElementById("cerrarModal");
    const tbody = document.getElementById("contenidoTiposPrecio");
    const form = document.getElementById("formTiposPrecio");
    const productoHidden = document.getElementById("producto_id_hidden");

    // ABRIR MODAL
    document.querySelectorAll(".btn-tipos-precio").forEach(function(boton){

        boton.addEventListener("click", function(e){

            e.preventDefault();

            let idProd = this.getAttribute("data-id");
            productoHidden.value = idProd;

            fetch("<?= base_url('producto/getTiposPrecio'); ?>", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "id_prod=" + idProd
            })
            .then(response => response.json())
            .then(data => {

                tbody.innerHTML = "";

                if(data.length > 0){

                    data.forEach(function(item){

                        tbody.innerHTML += `
                        <tr>
                            <td style="border:1px solid #ddd; padding:8px;">
                                ${item.nom_precio}
                                <input type="hidden" name="ids[]" value="${item.id ?? ''}">
                                <input type="hidden" name="nombres[]" value="${item.nom_precio}">
                            </td>

                            <td style="border:1px solid #ddd; padding:8px;">
                                <input type="number"
                                       step="0.01"
                                       name="precios[]"
                                       value="${item.precio ?? ''}"
                                       style="width:100%;">
                            </td>

                            <td style="border:1px solid #ddd; padding:8px;">
                                <input type="number"
                                       name="cantidades[]"
                                       value="${item.cantidad ?? ''}"
                                       style="width:100%;">
                            </td>
                        </tr>
                        `;
                    });

                } else {

                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" style="padding:10px;">
                                No hay tipos de precio
                            </td>
                        </tr>
                    `;
                }

                modal.style.display = "block";

            });

        });

    });

    // GUARDAR CAMBIOS
    form.addEventListener("submit", function(e){

        e.preventDefault();

        const formData = new FormData(form);

        fetch("<?= base_url('producto/updateTiposPrecio'); ?>", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {

            if(data.status === "ok"){
                alert("Precios actualizados correctamente");
                modal.style.display = "none";
            } else {
                alert("Error al actualizar");
            }

        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error en la petición");
        });

    });

    // CERRAR CON X
    cerrar.addEventListener("click", function(){
        modal.style.display = "none";
    });

    // CERRAR HACIENDO CLICK AFUERA
    modal.addEventListener("click", function(e){
        if(e.target === modal){
            modal.style.display = "none";
        }
    });

});
</script>

<script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
<script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>

<script>
  
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
</script>

<?php }else{ ?>
  <h2>Su perfil no tiene acceso a esta parte,
    Vuelva a alguna seccion de Empleado!
  </h2>
<?php }?>
<br><br>
