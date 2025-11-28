<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multirrubro Blass</title>
  <link rel="icon" href="<?php echo base_url('./assets/img/iconMB2.png');?>">
  <link rel="stylesheet" href="<?php echo base_url('./assets/css/navbar.css');?>">
  <link rel="stylesheet" href="<?php echo base_url('./assets/css/clock.css');?>">
  <link rel="stylesheet" href="<?php echo base_url('./assets/css/mensajesTemporales.css');?>">

  <script src="<?php echo base_url('./assets/js/a25933befb.js');?>" crossorigin="anonymous"></script>
  
</head>
<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');
          $estado =$session->get('estado'); 
          ?>
<style>
.cart-container {
    position: relative;
    display: inline-block;
}

.cart-dropdown {
    display: none;
    position: absolute;
    right: 0;
    background: white;
    border: 3px solid #00f0ff; /* azul marino fluor */
    padding: 10px;
    min-width: 200px;
    box-shadow: 0 2px 17px rgba(57, 120, 139, 0.7);
    z-index: 1000;
    border-radius: 8px; /* opcional, para suavizar el borde */
}

.cart-container:hover .cart-dropdown {
    display: block;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

.cart-item:last-child {
    border-bottom: none;
}

</style>

<style>
/* Indicador círculo pequeño */
.chat-indicator {
    width: 10px;
    height: 10px;
    display: inline-block;
    background: gray;
    border-radius: 50%;
    margin-left: 6px;
    transition: 0.3s;
}

/* Indicador activo */
.chat-indicator.new {
    background: #00ff00;
    box-shadow: 0 0 10px #00ff00, 0 0 20px #00ff00;
}

/* Burbuja de aviso */
.chat-burbuja {
    position: absolute;
    top: -25px;
    right: -10px;
    background: #ff4444;
    color: white;
    padding: 4px 10px;
    font-size: 12px;
    border-radius: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    animation: burbujaIn 0.3s ease-out;
    white-space: nowrap;
    z-index: 99999;
}

/* Animación de aparición */
@keyframes burbujaIn {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

/* Fondo del modal */
.modal-chat-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

/* Caja del modal */
.modal-chat {
    background: white;
    padding: 20px;
    width: 400px;
    border-radius: 10px;
    border: 3px solid #00f0ff;
    box-shadow: 0 0 10px #00f0ff;
    transform-origin: center;
}

/* Chat */
.chat-mensajes {
    border: 1px solid #ccc;
    height: 250px;
    overflow-y: auto;
    padding: 10px;
    margin-bottom: 10px;
    background: #f8f8f8;
}

.chat-input {
    width: 100%;
    height: 60px;
    margin-bottom: 10px;
    padding: 3px;
}

/* Botones alineados a la derecha */
.chat-botones {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
}

.btn {
    padding: 6px 12px;
    border-radius: 5px;
    cursor: pointer;
    border: none;
    font-weight: bold;
    color: white;
}

/* Animaciones modal */
@keyframes zoomIn {
    from { transform: scale(0.5); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

@keyframes zoomOut {
    from { transform: scale(1); opacity: 1; }
    to   { transform: scale(0.5); opacity: 0; }
}

</style>

<div id="modalChat" class="modal-chat-overlay">
    <div class="modal-chat">
        <h3 style="margin-bottom:10px;">Blass Chat 💬</h3>

        <div id="chatMensajes" class="chat-mensajes"></div>

        <textarea id="chatTexto" class="chat-input" placeholder="Escribir mensaje..."></textarea>

        <div class="chat-botones">
            <button onclick="cerrarModalChat()" class="btn" style="background: gray;">Cerrar</button>
            <button onclick="enviarMensajeChat()" class="btn">Enviar</button>
        </div>
    </div>
</div>
<script>
    const usuarioLogueado = "<?= $session->get('nombre') ?>";
</script>
<script>
let ultimoID = 0; // Guarda el id del último mensaje cargado

// ---------- ABRIR MODAL ----------
// Muestra el chat y marca mensajes como leídos
function abrirModalChat() {
    const modal = document.getElementById('modalChat');
    const caja = document.querySelector('.modal-chat');

    modal.style.display = 'flex';
    caja.style.animation = "zoomIn 0.25s ease forwards";

    cargarMensajes().then(() => {
        limpiarIndicador();

        // MARCAR MENSAJES COMO LEÍDOS EN LA DB
        fetch("<?= base_url('chat/marcarLeido') ?>", {
            method: "POST",
            body: new URLSearchParams({ ultimoID: ultimoID })
        });
    });

    setTimeout(() => {
        document.getElementById("chatTexto").focus();
    }, 80);
}

// ---------- CERRAR MODAL ----------
function cerrarModalChat() {
    const modal = document.getElementById('modalChat');
    const caja = document.querySelector('.modal-chat');

    caja.style.animation = "zoomOut 0.25s ease forwards";

    setTimeout(() => {
        modal.style.display = 'none';
    }, 250);
}

// ---------- ENTER para enviar - ESC para cerrar ----------
document.addEventListener("keydown", function(e) {
    const modal = document.getElementById("modalChat");
    if (modal.style.display !== "flex") return;

    if (e.key === "Escape") cerrarModalChat();
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        enviarMensajeChat();
    }
});

// ---------- RESTAURAR INDICADOR AL CARGAR LA PÁGINA ----------
window.addEventListener("DOMContentLoaded", () => {
    // se puede mantener luz si había mensajes nuevos
    if (document.getElementById("chatNotification") && document.getElementById("chatBurbuja")) {
        document.getElementById("chatNotification").classList.remove("new");
        document.getElementById("chatBurbuja").style.display = "none";
    }
});

// ---------- LIMPIAR INDICADOR ----------
function limpiarIndicador() {
    document.getElementById("chatNotification").classList.remove("new");
    document.getElementById("chatBurbuja").style.display = "none";
}

// ---------- CARGAR MENSAJES ----------
async function cargarMensajes() {
    const response = await fetch("<?= base_url('chat/listar') ?>");
    const data = await response.json();

    let contenedor = document.getElementById("chatMensajes");
    contenedor.innerHTML = "";

    // ----------------------------
    // 1) MENSAJES LEÍDOS DEL DÍA
    // ----------------------------
    data.leidosHoy.forEach(msg => {
        let f = new Date(msg.fecha);
        let hora = String(f.getHours()).padStart(2, '0');
        let min  = String(f.getMinutes()).padStart(2, '0');
        let fechaFormateada = `${hora}:${min}`;

        contenedor.innerHTML += `
            <div style="margin-bottom:8px;">
                <strong>${msg.usuario}</strong>: ${msg.mensaje}
                <div style="font-size:12px;color:#669;">${fechaFormateada}</div>
            </div>
        `;

        ultimoID = msg.id; // el último leído
    });

    // ----------------------------------------
// 2) MENSAJES NUEVOS (REMARK EN VERDE)
// ----------------------------------------
data.nuevosHoy.forEach(msg => {
    let f = new Date(msg.fecha);
    let hora = String(f.getHours()).padStart(2, '0');
    let min  = String(f.getMinutes()).padStart(2, '0');
    let fechaFormateada = `${hora}:${min}`;

    // Es mensaje propio → no marcar como nuevo
    let esPropio = (msg.usuario === usuarioLogueado);

    // Estilo: si es propio, NO color verde
    let estiloFondo = esPropio ? "" : "background:#d7ffd7;";
    let etiquetaNuevo = esPropio ? "" : `<span style="color:green;font-weight:bold;font-size:8px;">Nuevo</span>`;

    contenedor.innerHTML += `
        <div style="margin-bottom:5px; ${estiloFondo} padding:3px; border-radius:4px;">
            <strong>${msg.usuario}</strong>: ${msg.mensaje}
            <div style="font-size:12px;color:#335;">${fechaFormateada} ${etiquetaNuevo}</div>
        </div>
    `;

    ultimoID = msg.id;
});


    contenedor.scrollTop = contenedor.scrollHeight;
}


// ---------- ENVIAR MENSAJE ----------
function enviarMensajeChat() {
    let texto = document.getElementById("chatTexto").value;
    if (texto.trim() === "") return;

    fetch("<?= base_url('chat/enviar') ?>", {
        method: "POST",
        body: new URLSearchParams({ mensaje: texto })
    })
    .then(() => {
        document.getElementById("chatTexto").value = "";
        cargarMensajes();
    });
}

// ---------- NOTIFICACIÓN DE MENSAJES NUEVOS ----------
setInterval(() => {
    fetch("<?= base_url('chat/nuevos') ?>")
        .then(r => r.json())
        .then(data => {
            const burbuja = document.getElementById("chatBurbuja");
            if (data.hayNuevos) {
                document.getElementById("chatNotification")?.classList.add("new");

                if (burbuja) {
                    burbuja.textContent = `Mensaje de "${data.usuario}"`;
                    burbuja.style.display = "block";
                }
            }
        });
}, 5000);
</script>


<body>
<section class="navBarSection">
    <div class="headernav">
        <div class="logoDiv">
            <div class="clock">
                <div id="day" class="day"></div>
                <div id="hours"></div>
                <span class="colon" id="colon">:</span>
                <div id="minutes"></div>
            </div>
        </div>
        <style>
            .recuadro {
                font-weight:900;
                border-radius:10px;
                margin: 20px;
                padding: 10px;
                border: 3px solid green;
            }
        </style>
      <?php if($nombre){?>
      <div class="btn">
        <p style="color:;">User: <?php echo $nombre; ?> </p>
      </div>
      <?php } ?>
        <!-- Botón de hamburguesa -->
        <button class="toggleNavBar" id="toggleNavBar">
            &#9776; <!-- Icono de hamburguesa -->
        </button>        
        <div id="navBar" class="navBar">
            <ul class="navList flex">
        <?php if($perfil) { ?> 
        <li class="nnavItem">
          <button id="btnChatInterno" class="btn" onclick="abrirModalChat()" style="position: relative;">
              Chat
              <!-- Indicador -->
              <span id="chatNotification" class="chat-indicator"></span>

              <!-- Burbuja de aviso -->
              <span id="chatBurbuja" class="chat-burbuja" style="display:none;">
                  Hay mensajes nuevos
              </span>
          </button>
        </li>
        <?php } ?> 
        <?php if( ($perfil =='1')) { ?>          
          
          <li class="nnavItem">
            <a href="<?= base_url('pedidos')?>" class="btn">PEDIDOS</a>
          </li>
       
          <li class="nnavItem">
            <a class="btn signUp" href="<?php echo base_url('compras');?>">VENTAS</a>
          </li>
          <li class="nnavItem">
            <a href="<?= base_url('usuarios-list')?>" class="btn signUp">US/Empleado</a>
          </li>
          <li class="nnavItem">
            <a class="btn signUp" href="<?php echo base_url('clientes');?>">CLIENTES</a>
          </li>
          <li class="nnavItem">
            <a href="<?= base_url('Lista_Productos')?>" class="btn">ABM_PRODUCTOS</a>
          </li>
          <li class="nnavItem">
            <a href="<?= base_url('ListaCategorias')?>" class="btn">P_Categorias</a>
          </li>
          <li class="nnavItem">
          <a href="<?= base_url('/logout')?>" class="btn" onclick="return confirmarAccionSalir(event);">Salir</a>
          </li>

          <?php } else if( (($perfil == 2 || $perfil == 3)) ) { ?>
          <li class="navItem">
          

        <?php if ($estado): ?>
        <?php 
        $mensaje = "ATENCIÓN! Se está Procesando una Venta o Pedido";
        $color = "orange"; // Color por defecto
        $link = ""; // Variable para el enlace

        switch ($estado) {
            case 'Modificando':
                $mensaje = "ATENCIÓN! Se está Modificando una Venta o Pedido";
                $color = "#FF6700"; // Naranja neón
                $link = base_url('CarritoList'); // Ruta del enlace
                break;
            case 'Modificando_SF':
                $mensaje = "ATENCIÓN! Se está Modificando una Venta o Pedido";
                $color = "#FF6700"; // Naranja neón
                $link = base_url('CarritoList'); // Ruta del enlace
                break;
            case 'Cobrando':
                $mensaje = "ATENCIÓN! Se está Cobrando una Venta o Pedido";
                $color = "#00FF00"; // Verde neón
                $link = base_url('casiListo'); // Ruta del enlace
                break;
        }
        ?>

        <h5 class="resaltado" style="
        color: white; 
        font-weight: bold; 
        border: 1px solid <?php echo $color; ?>; 
        padding: 7px; 
        display: inline-block; 
        border-radius: 5px; 
        text-align: center;
        text-transform: uppercase;
        box-shadow: 0 0 3px <?php echo $color; ?>, 0 0 5px <?php echo $color; ?>;">
        
        
            <a href="<?php echo $link; ?>" style="color: white; text-decoration: none;">
                <?php echo $mensaje; ?>
            </a>
        

        </h5>
        <?php endif; ?>


          
          </li>
          <?php if($perfil == 3) { ?>
          <li class="nnavItem">
            <a class="btn" href="<?php echo base_url('caja');?>">CAJA</a>            
          </li>          
          <li class="nnavItem">
            <a class="btn signUp" href="<?php echo base_url('compras');?>">VENTAS</a>
          </li>          
          <li class="nnavItem">
            <a class="btn signUp" href="<?php echo base_url('clientes');?>">CLIENTES</a>
          </li>
            <?php } ?>
          <li class="nnavItem">
            <a href="<?= base_url('/catalogo')?>" class="btn">Productos</a>
          </li>

          <li class="navItem cart-container">
              <a href="<?= base_url('CarritoList') ?>">
                  <img class="navImg" src="<?= base_url('assets/img/icons/iconMB2.png') ?>">
              </a>
              <div class="cart-dropdown">
                  <?php 
                  $cart = \Config\Services::cart();
                  $items = $cart->contents(); // Obtiene los items del carrito
                  ?>
                  
                  <?php if (empty($items)): ?>
                      <p>El carrito está vacío</p>
                  <?php else: ?>
                      <?php foreach ($items as $item): ?>
                          <div class="cart-item">
                              <span class="item-name"><?= esc($item['name']) ?></span>
                              <span class="item-quantity"><?= esc($item['qty']) ?></span>
                          </div>
                          <hr>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </div>
          </li>

          <li class="nnavItem">
            <a class="btn" href="<?php echo base_url('pedidos');?>">Pedidos</a>
            <li class="navItem">            
          </li>
          <li class="nnavItem">            
          <a href="<?= base_url('/logout')?>" class="btn" onclick="return confirmarAccionSalir(event);">Salir</a>            
          </li>
          <?php } else { ?>
          
          <li class="navItem">
            <button class="btn loginBtn">
              <a href="<?= base_url('/login')?>" class="login">Ir al Login</a>
            </button>
          </li>
          
         <?php } ?> 
         </ul>
        </div>
    </div>
</section>

<style>
  .resaltado {
    color: orange;
    border: 2px solid orange;
    padding: 10px;
    display: inline-block;
    border-radius: 5px;
    text-align: center;
}
</style>

<script>
  // Obtén el botón de hamburguesa y la barra de navegación
const toggleButton = document.querySelector('.toggleNavBar');
const navBar = document.querySelector('.navBar');
const body = document.querySelector('body');

// Función para activar la barra de navegación y desplazar el contenido
toggleButton.addEventListener('click', function() {
    navBar.classList.toggle('active'); // Abre o cierra la barra de navegación
    body.classList.toggle('navbar-active'); // Desplaza el contenido hacia abajo
});

</script>



  <script>
    // Obtener elementos del DOM
    const toggleNavBar = document.getElementById('toggleNavBar');
    const navBar = document.getElementById('navBar');

    // Función para alternar la visibilidad del menú
    toggleNavBar.addEventListener('click', () => {
        navBar.classList.toggle('active');
    });

    // Cerrar el menú si se hace clic fuera de él
    document.addEventListener('click', (event) => {
        if (!navBar.contains(event.target) && !toggleNavBar.contains(event.target)) {
            navBar.classList.remove('active');
        }
    });
  </script>


  <script>

    function handleScroll() {
      var headernav = document.querySelector('.headernav');
      var scrollPosition = window.scrollY;

      if (scrollPosition > 0) {
          headernav.classList.add('scrolled');
      } else {
          headernav.classList.remove('scrolled');
      }
    }

    window.addEventListener('scroll', handleScroll);
  </script>

<script>
    //Funciones del Reloj
    let showColon = true;

function updateClock() {
    const hoursElement = document.getElementById('hours');
    const minutesElement = document.getElementById('minutes');
    const colonElement = document.getElementById('colon');
    const dayElement = document.getElementById('day');

    // Obtener la fecha y hora actuales
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const days = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];

    // Alternar visibilidad del colon
    colonElement.textContent = showColon ? ':' : ' '; // Espacio no separable
    showColon = !showColon;

    // Actualizar horas, minutos y día
    hoursElement.textContent = hours;
    minutesElement.textContent = minutes;
    dayElement.textContent = days[now.getDay()];
}

// Actualizar el reloj cada medio segundo
setInterval(updateClock, 500);
updateClock(); // Llamar inicialmente
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function confirmarAccionSalir(event) {
      event.preventDefault(); // Detiene la navegación automática

      Swal.fire({
          title: "¿Desea Cerrar Sesión y Salir?",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Sí, Salir",
          cancelButtonText: "Cancelar",
          customClass: {
              popup: 'small-swal' // Clases personalizadas
          }
      }).then((result) => {
          if (result.isConfirmed) {
              window.location.href = "<?= base_url('/logout') ?>";
          }
      });

      return false; // Evita la navegación si no se confirma
  }
</script>

<style>
  /* Reducir tamaño del cuadro de diálogo */
  .small-swal {
      width: 300px !important; /* Ancho más pequeño */
      font-size: 14px !important; /* Texto más pequeño */
      padding: 10px !important;
  }
</style>
</body>
</html>
