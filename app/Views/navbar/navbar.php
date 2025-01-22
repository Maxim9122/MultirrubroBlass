<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barberia King</title>
  <link rel="icon" href="<?php echo base_url('./assets/img/iconoBK.png');?>">
  <link rel="stylesheet" href="<?php echo base_url();?>./assets/css/navbar.css">
  <link rel="stylesheet" href="<?php echo base_url();?>./assets/css/clock.css">
  <link rel="stylesheet" href="<?php echo base_url();?>./assets/css/mensajesTemporales.css">

  <script src="<?php echo base_url();?>./assets/js/a25933befb.js" crossorigin="anonymous"></script>
  
</head>

<body>

<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>

  <section class="navBarSection">
    <div class="headernav">
      <div class="logoDiv">
        <a href="<?= base_url('turnos')?>" class="logo">
        <div class="clock">
        <div id="day" class="day"></div>
        <div id="hours"></div>
        <span class="colon" id="colon">:</span>
        <div id="minutes"></div>
        </div>

        </a>

      </div>
      
      <div id="navBar" class="navBar">
        <ul class="navList flex">
        <?php if( ($perfil =='1')) { ?>
          <li class="navItem">
          <h5 class="colorTexto2"><?php echo "Bienvenido ".$nombre?> </h5>
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
            <a href="<?= base_url('Lista_Productos')?>" class="btn">PRODUCTOS</a>
          </li>
          <li class="nnavItem">
            <a href="<?= base_url('Lista_servicios')?>" class="button">SERVICIOS</a>
          </li>
          <li class="nnavItem">
            <a href="<?= base_url('turnos')?>" class="button">TURNOS</a>
          </li>
          <li class="navItem">
            <button class="btn signUp">
              <a href="<?= base_url('/logout')?>" class="signUp">Salir</a>
            </button>
          </li>
          <?php } else if( (($perfil =='2')) ) { ?>
          <li class="navItem">
            <h5 class="colorTexto2"><?php echo "Bienvenido ".$nombre?></h5>
          </li>
          <li class="nnavItem">
            <a href="<?= base_url('/catalogo')?>" class="btn">Productos</a>
          </li>
          <li class="navItem">
          <a href="<?php echo base_url('CarritoList') ?>"> <img src=" <?php echo base_url('assets/img/icons/carrito2.png')?>"> </a>
          </li>
          <li class="nnavItem">
            <a class="button" href="<?php echo base_url('turnos');?>">Turnos</a>
            <li class="navItem">
            <button class="btn signUp">
              <a href="<?= base_url('/logout')?>" class="signUp">Salir</a>
            </button>
          </li>
          <?php } else { ?>
          
          <li class="navItem">
            <button class="btn loginBtn">
              <a href="<?= base_url('/login')?>" class="login">Ingresar</a>
            </button>
          </li>
          
         <?php } ?> 
        </ul>
      </div>
    </div>
  </section>

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


</body>
</html>
