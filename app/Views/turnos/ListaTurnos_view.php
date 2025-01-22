<?php $session = session();
          $nombre= $session->get('nombre');
          $perfil=$session->get('perfil_id');
          $id=$session->get('id');?>
<section>
<!-- Mensajes temporales -->
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


<div style="width: 100%;">
        <section class="contenedor-titulo">
        <strong class="nombreLogo">Barber King</strong>
        
        <!-- Formulario para turno para Clientes Registrados -->
        <form class="estiloTurno" action="<?php echo base_url('turnoClienteRegistrado'); ?>" method="POST">
        <!--Selector/Buscador de clientes -->
        <select class="form-control" name="id_cliente" id="id_cliente" required>
            <option value="">Seleccione un cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id_cliente']; ?>">
                <?= $cliente['nombre']; ?>
                </option>
            <?php endforeach; ?>
            </select>
            
            <select name="tipo_servicio" class="form-control" required>
            <option value="">Seleccione un servicio</option>
            <?php foreach($servicios as $servicio): ?>
                <option value="<?= $servicio['id_servi']; ?>"><?= $servicio['descripcion']; ?> - $<?= $servicio['precio']; ?></option>
            <?php endforeach; ?>
            </select>
            
            <label for="fecha" class="label-inline">Fecha:</label>
            <input type="date" class="form-control" id="fecha" name="fecha_turno">
            
            <label for="hora" class="label-inline">Hora:</label>
            <input type="time" class="form-control" id="hora" name="hora_turno">
            
            <button type="submit" class="btn btn-submit">Agendar</button>
        </form>
        </section>
  <div style="text-align: end;">
  
  <br>
  <a class="button" href="<?php echo base_url('turnosTodos');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
    </svg> Agendados Todos</a>
   <a class="button" href="<?php echo base_url('turnosCompletados');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
    </svg> Turnos Completados</a>
    <a class="button" href="<?php echo base_url('nuevoTurno');?>">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
    </svg> Turno Cliente Nuevo</a>
  <br><br>
  <?php $Recaudacion = 0; ?>
  <table class="table table-responsive table-hover" id="users-list">
       <thead>
          <tr class="colorTexto2">
             <th>Nro Turno</th>
             <th>Cliente</th>
             <th>Teléfono</th>
             <th>Barber</th>
             <th>Hora Turno</th>
             <th>Servicio</th>
             <th>Precio</th>             
             <th>Acciones</th>
          </tr>
       </thead>
       <tbody>
          <?php if($turnos): ?>
            <?php foreach($turnos as $trn): ?>
    <tr>
        <td><?php echo $trn['id']; ?></td>
        <td><?php echo $trn['cliente_nombre']; ?></td>
        <td><?php echo $trn['cliente_telefono']; ?></td>

        <!-- Formulario por cada turno -->
        <form id="turnoForm" action="<?php echo base_url('turno_actualizar/'.$trn['id']); ?>" method="POST">
            <!-- Dropdown para el barbero -->
            <td>
                <select class="form-control btn" name="id_barber">
                    <?php foreach ($barbers as $barber): ?>
                        <option value="<?= $barber['id']; ?>" <?= $barber['id'] == $trn['id_barber'] ? 'selected' : ''; ?>>
                            <?= $barber['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <!-- Campo editable para la hora del turno -->
            <td>
                <input type="time" class="form-control btn" name="hora_turno" value="<?= $trn['hora_turno']; ?>">
            </td>

            <!-- Dropdown para el servicio -->
            <td>
                <select class="form-control btn" name="id_servi">
                    <?php foreach ($servicios as $servicio): ?>
                        <option value="<?= $servicio['id_servi']; ?>" <?= $servicio['id_servi'] == $trn['id_servi'] ? 'selected' : ''; ?>>
                            <?= $servicio['descripcion']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <!-- Campo solo de visualización del precio -->
            <td>$ <?php echo $trn['precio']; ?></td>

            
            <td>
                <!-- Botón para enviar la actualización -->
                <button type="submit" class="btn btn-actualizar">Editar</button>
                
                <!-- Botón para eliminar o cancelar un turno -->
                <a class="btn" href="<?php echo base_url('cancelar/'.$trn['id']);?>" onclick="mostrarConfirmacion(event, 'Cancelar turno.?', this.href);">
                Cancelar
                </a>
                        
                <!-- Botón para terminar un turno (Concretado o Completado) -->
                <button 
                    type="button" 
                    class="btn btn-completar" 
                    onclick="confirmarYEnviar('<?php echo base_url('clienteListo/'.$trn['id']); ?>')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                        <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
                    </svg>
                    Listo
                </button>

            </td>
            
         </form>
         
         </tr>
         <?php endforeach; ?>

         <?php endif; ?>
       
     </table>

<!-- Cuadro de confirmación -->
<div id="confirm-dialog" class="confirm-dialog" style="display: none;">
    <div class="confirm-content btn2">
        <p id="confirm-message">¿Estás seguro?</p>
        <div class="confirm-buttons">
            <button id="confirm-yes" class="btn btn-yes" autofocus>Sí</button>
            <button id="confirm-no" class="btn btn-no">No</button>
        </div>
    </div>
</div>
     
  </div>
</div>

</section>
          <script src="<?php echo base_url('./assets/js/jquery-3.5.1.slim.min.js');?>"></script>
          <link rel="stylesheet" type="text/css" href="<?php echo base_url('./assets/css/jquery.dataTables.min.css');?>">
          <script type="text/javascript" src="<?php echo base_url('./assets/js/jquery.dataTables.min.js');?>"></script>
<!-- Para la tabla de turnos-->
<script>
    $(document).ready( function () {
      $('#users-list').DataTable( {
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página.",
            "zeroRecords": "Sin Resultados! No hay turnos agendados para Hoy.",
            "info": "Mostrando la página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles.",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar: ",
            "paginate": {
              "next": "Siguiente",
              "previous": "Anterior"
            }
        }
    } );
    });


    // Crear un objeto Date en UTC
  const today = new Date();

// Ajustar la hora a la zona horaria de Argentina (UTC-3)
const options = { timeZone: 'America/Argentina/Buenos_Aires', hour12: false };
const formatter = new Intl.DateTimeFormat('es-AR', {
    ...options,
    year: 'numeric', month: '2-digit', day: '2-digit'
});

const formattedDate = formatter.format(today).split('/').reverse().join('-'); // Formato YYYY-MM-DD

// Formatear la hora en formato HH:MM
const formattedTime = new Intl.DateTimeFormat('es-AR', {
    ...options,
    hour: '2-digit',
    minute: '2-digit'
}).format(today);

// Establecer la fecha y hora actuales en los campos correspondientes
// Establecer la fecha mínima y el valor predeterminado
const fechaInput = document.getElementById('fecha');
fechaInput.setAttribute('min', formattedDate);
fechaInput.setAttribute('value', formattedDate);
//Rescata la hora del input por medio del id y asigna la hora actual (Lo mismo con la fecha)
document.getElementById('hora').value = formattedTime;

</script>

<!-- Esta parte es del cartel de confirmacion de Cancelar Turno o Turno Listo-->
<script>
    function mostrarConfirmacion(event, mensaje, href) {
        event.preventDefault(); // Detener la acción predeterminada del enlace

        // Mostrar el cuadro de diálogo
        const dialog = document.getElementById('confirm-dialog');
        const messageElement = document.getElementById('confirm-message');
        const yesButton = document.getElementById('confirm-yes');
        const noButton = document.getElementById('confirm-no');

        messageElement.textContent = mensaje;
        dialog.style.display = 'flex';

        // Acción para cancelar
        noButton.onclick = cerrarConfirmacion;

        // Detectar clics fuera del cuadro de diálogo
        window.onclick = function (e) {
            if (e.target === dialog) {
                cerrarConfirmacion();
            }
        };

        // Detectar las teclas Enter y Escape
        window.onkeydown = function (e) {
            if (e.key === "Escape") {
                cerrarConfirmacion();
            } else if (e.key === "Enter") {
                confirmarAccion(href);
            }
        };
    }

    function confirmarAccion(href) {
        cerrarConfirmacion(); // Cerrar el cuadro
        window.location.href = href; // Redirigir al enlace
    }

    function cerrarConfirmacion() {
        const dialog = document.getElementById('confirm-dialog');
        dialog.style.display = 'none';

        // Eliminar los eventos para evitar interferencias en el futuro
        window.onclick = null;
        window.onkeydown = null;
    }
</script>



<!-- Buscador de clientes -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const buscarInput = document.getElementById("buscar_cliente");
    const clienteSelect = document.getElementById("id_cliente");

    // Mostrar opciones al escribir en el buscador
    buscarInput.addEventListener("input", function() {
        const filter = buscarInput.value.toLowerCase();
        const options = clienteSelect.querySelectorAll("option:not(.search-box)");

        let hasMatches = false; // Bandera para saber si hay coincidencias

        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(filter)) {
                option.style.display = "block"; // Mostrar coincidencias
                hasMatches = true;
            } else {
                option.style.display = "none"; // Ocultar otras
            }
        });

        // Mostrar solo si hay coincidencias y el filtro no está vacío
        if (filter.length > 0 && hasMatches) {
            clienteSelect.classList.add("show-options");
        } else {
            clienteSelect.classList.remove("show-options");
        }
    });

    // Ocultar opciones al borrar todo el buscador
    buscarInput.addEventListener("keydown", function(event) {
        if (event.key === "Escape" || buscarInput.value === "") {
            buscarInput.value = "";
            clienteSelect.classList.remove("show-options");
            const options = clienteSelect.querySelectorAll("option:not(.search-box)");
            options.forEach(option => {
                option.style.display = "none";
            });
        }
    });

    // Actualizar selección al hacer clic en una opción
    clienteSelect.addEventListener("change", function() {
        const selectedOption = clienteSelect.options[clienteSelect.selectedIndex];
        if (!selectedOption.classList.contains("search-box")) {
            buscarInput.value = selectedOption.textContent; // Mostrar el texto seleccionado en el buscador
            clienteSelect.classList.remove("show-options"); // Ocultar opciones
        }
    });

    // Enfocar el buscador automáticamente
    clienteSelect.addEventListener("focus", function() {
        buscarInput.focus();
    });
});
</script>

<!-- Cartel de la funcion que actualiza los campos de Barber Hora y Servicio 
 si se modificaron antes de guardar el turno Completado-->
<script>

function confirmarYEnviar(url) {
    // Detener la acción predeterminada del enlace (si es necesario, en un evento de tipo 'click')
    event.preventDefault();

    // Mostrar el cuadro de diálogo
    const dialog = document.getElementById('confirm-dialog');
    const messageElement = document.getElementById('confirm-message');
    const yesButton = document.getElementById('confirm-yes');
    const noButton = document.getElementById('confirm-no');

    messageElement.textContent = 'Marcar Turno como completado?';
    dialog.style.display = 'flex';

    // Acción para confirmar
    yesButton.onclick = function () {
        enviarFormulario(url);
    };

    // Acción para cancelar
    noButton.onclick = cerrarConfirmacion;

    // Detectar clics fuera del cuadro de diálogo
    window.onclick = function (e) {
        if (e.target === dialog) {
            cerrarConfirmacion();
        }
    };

    // Detectar las teclas Enter y Escape
    window.onkeydown = function (e) {
        if (e.key === "Escape") {
            cerrarConfirmacion();
        } else if (e.key === "Enter") {
            enviarFormulario(url);
        }
    };
}

function enviarFormulario(url) {
    // Enviar el formulario al hacer clic en "Sí"
    const formulario = document.getElementById('turnoForm');
    formulario.action = url; // Cambiar la acción del formulario
    formulario.submit(); // Enviar el formulario
    cerrarConfirmacion(); // Cerrar el cuadro de confirmación
}

function cerrarConfirmacion() {
    const dialog = document.getElementById('confirm-dialog');
    dialog.style.display = 'none';

    // Eliminar los eventos para evitar interferencias en el futuro
    window.onclick = null;
    window.onkeydown = null;
}

</script>
<br><br>