<?php
namespace App\Controllers;
use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\Cabecera_model;
Use App\Models\VentaDetalle_model;
use App\Models\Turnos_model;
use App\Models\Usuarios_model;
use App\Models\Clientes_model;
use App\Models\Servicios_model;
//use Dompdf\Dompdf;

class Turnos_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}

    public function ListarTurnos()
    {
        $turnosModel = new Turnos_model();
        $filtros = [
            'estado' => 'Pendiente',
            'fecha_turno' => date('d-m-Y'),
        ];
        $datos['turnos'] = $turnosModel->obtenerTurnos($filtros);

        $datos2['barbers'] = (new Usuarios_model())->getUsBaja('NO');
        $datos3['servicios'] = (new Servicios_model())->getServicio();
        $datos4['clientes'] = (new Clientes_model())->getClientes();

        $data['titulo'] = 'Listado de Turnos';
        echo view('navbar/navbar');
        echo view('header/header', $data);
        echo view('turnos/ListaTurnos_view', $datos + $datos2 + $datos3 + $datos4);
        echo view('footer/footer');
    }

    public function TurnosTodos()
    {
        $turnosModel = new Turnos_model();
        $filtros = [
            'estado' => 'Pendiente'
        ];
        $datos['turnos'] = $turnosModel->obtenerTurnos($filtros);

        $datos2['barbers'] = (new Usuarios_model())->getUsBaja('NO');
        $datos3['servicios'] = (new Servicios_model())->getServicio();
        $datos4['clientes'] = (new Clientes_model())->getClientes();

        $data['titulo'] = 'Listado de Turnos';
        echo view('navbar/navbar');
        echo view('header/header', $data);
        echo view('turnos/turnosDeDiaDistinto_alActual', $datos + $datos2 + $datos3 + $datos4);
        echo view('footer/footer');
    }


    public function nuevoTurno()
    {
            // Cargar el modelo de servicios
            $serviciosModel = new Servicios_model();
    
            // Obtener todos los servicios desde la base de datos
            $servicios = $serviciosModel->getServicio();
    
            // Preparar los datos para la vista
            $data = [
                'titulo' => 'Crear Nuevo Turno',
                'servicios' => $servicios // Pasamos los servicios a la vista
            ];
    
            // Cargar las vistas
            echo view('navbar/navbar');
            echo view('header/header', $data);
            echo view('turnos/nuevoTurno_view', $data); // Pasamos los datos a la vista
            echo view('footer/footer');
        }


   //Verifica y guarda los turnos
   public function RegistrarTurno() {
    $input = $this->validate([
        'nombre_cliente' => 'required|min_length[3]',
        'telefono' => 'required|min_length[10]|max_length[10]|is_unique[cliente.telefono]',
        'tipo_servicio' => 'required|max_length[13]'
    ]);

    $turnosModel = new Turnos_model();
    $clienteModel = new Clientes_model();

        if (!$input) {
        $data['titulo'] = 'Registro Turno'; 
        echo view('navbar/navbar');
        echo view('header/header', $data);                
        echo view('turnos/nuevoTurno_view', ['validation' => $this->validator]);
        echo view('footer/footer');
        } else {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha = date('d-m-Y');

        // Validación y carga de la imagen
        $validation = $this->validate([
            'foto' => ['uploaded[foto]', 'mime_in[foto,image/jpg,image/jpeg,image/png]']
        ]);

        if ($validation) {
            $img = $this->request->getFile('foto');
            $nombre_aleatorio = $img->getRandomName();
            $img->move(ROOTPATH . 'assets/uploads', $nombre_aleatorio);

            $clienteModel->save([
                'nombre' => $this->request->getVar('nombre_cliente'), 
                'telefono' => $this->request->getVar('telefono'),
                'foto' => $img->getName()
            ]);
        } else {
            $clienteModel->save([
                'nombre' => $this->request->getVar('nombre_cliente'), 
                'telefono' => $this->request->getVar('telefono')
            ]);
        }

        // Rescato el ID del cliente nuevo que se guardó para asignarle al turno
        $id_cliente = $clienteModel->getInsertID();

        // Convertir la fecha al formato dd-mm-yyyy
        $fecha_turno = $this->request->getVar('fecha_turno');
        $fecha_turno_formateada = date('d-m-Y', strtotime($fecha_turno));

        // Guardar el turno en la base de datos
        $turnosModel->save([
            'id_cliente' => $id_cliente,
            'id_barber' => 1,
            'fecha_registro' => $fecha,
            'fecha_turno' => $fecha_turno_formateada,
            'hora_turno' => $this->request->getVar('hora_turno'),
            'id_servi' => $this->request->getVar('tipo_servicio'),
            'estado' => 'Pendiente',
        ]);

        session()->setFlashdata('msg', 'Turno Registrado!');
        return redirect()->to(base_url('turnos'));
        }
        }


    //Verifica y guarda los turnos de clientes ya registrados
    public function turnoClienteRegistrado() {

        $turnosModel = new Turnos_model();
        $clienteModel = new Clientes_model();

        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha = date('d-m-Y');

         // Rescato el ID del cliente.
         $id_cliente = $this->request->getVar('id_cliente');

         // Convertir la fecha al formato dd-mm-yyyy
         $fecha_turno = $this->request->getVar('fecha_turno');
         $fecha_turno_formateada = date('d-m-Y', strtotime($fecha_turno));
 
         // Guardar el turno en la base de datos
         $turnosModel->save([
             'id_cliente' => $id_cliente,
             'id_barber' => 1,
             'fecha_registro' => $fecha,
             'fecha_turno' => $fecha_turno_formateada,
             'hora_turno' => $this->request->getVar('hora_turno'),
             'id_servi' => $this->request->getVar('tipo_servicio'),
             'estado' => 'Pendiente',
         ]);
 
         session()->setFlashdata('msg', 'Turno Registrado!');
         return redirect()->to($this->request->getHeader('referer')->getValue());
    }

    //Actualiza el turno
    public function turno_actualizar($id_turno){ 
     // Cargar el modelo
     $Turnos_model = new Turnos_model();

     // Capturar los datos enviados desde el formulario
     $id_barber = $this->request->getPost('id_barber');
     $hora_turno = $this->request->getPost('hora_turno');
     $id_servi = $this->request->getPost('id_servi');

     // Preparar los datos para actualizar
     $data = array(
         'id_barber' => $id_barber,
         'hora_turno' => $hora_turno,
         'id_servi' => $id_servi
     );

     // Actualizar en la base de datos
     $Turnos_model->actualizar_turno($id_turno, $data);

     // Redirigir a la lista de turnos
     session()->setFlashdata('msg', 'Turno Actualizado!');
     return redirect()->to($this->request->getHeader('referer')->getValue());
    }
        
    
    //Guarda el turno Completado
    public function Turno_completado($id_turno)
    {
        $turnosModel = new Turnos_model();

        // Capturar los datos enviados desde el formulario
     $id_barber = $this->request->getPost('id_barber');
     $hora_turno = $this->request->getPost('hora_turno');
     $id_servi = $this->request->getPost('id_servi');

     // Preparar los datos para actualizar
     $data = array(
         'id_barber' => $id_barber,
         'hora_turno' => $hora_turno,
         'id_servi' => $id_servi
     );

     // Actualiza el turno en la base de datos antes de cambiar el estado a Listo
     $turnosModel->actualizar_turno($id_turno, $data);

        $turnosModel->cambiarEstado($id_turno, 'Listo');
        session()->setFlashdata('msg', 'Turno Completado!');
        return redirect()->to($this->request->getHeader('referer')->getValue());
    }


    //Guarda el turno Cancelado
    public function Turno_cancelado($id_turno)
    {
    $turnosModel = new Turnos_model();
    
    // Eliminar el turno de la base de datos por completo, no de forma logica.
    if ($turnosModel->eliminarTurno($id_turno)) {
        session()->setFlashdata('msgEr', 'Turno Eliminado!');
    } else {
        session()->setFlashdata('msgEr', 'Error al eliminar el turno.');
    }
    
    return redirect()->to($this->request->getHeader('referer')->getValue());
    }
    
    //Muestra todos los turnos realizados    
    public function turnosCompletados()
    {
        $TurnosModel = new Turnos_model();
        $UsuariosModel = new Usuarios_model();
        $ServiciosModel = new Servicios_model();
        $ClientesModel = new Clientes_model();

        // Obtener turnos completados desde el modelo
        $datos['turnos'] = $TurnosModel->obtenerTurnosCompletados();

        // Obtener barberos, servicios y clientes
        $datos['barbers'] = $UsuariosModel->getUsBaja('NO');
        $datos['servicios'] = $ServiciosModel->getServicio();
        $datos['clientes'] = $ClientesModel->getClientes();

        // Preparar datos para la vista
        $data['titulo'] = 'Listado de Turnos Completados';

        // Cargar las vistas
        echo view('navbar/navbar');
        echo view('header/header', $data);
        echo view('turnos/turnosCompletados', $datos);
        echo view('footer/footer');
    }

//Filtrado de turnos por fecha y barber
public function filtrarTurnos()
{
    $turnosModel = new Turnos_model();
    $filtros = [
        'estado' => 'Listo',
        'fecha_desde' => $this->request->getVar('fecha_desde'),
        'fecha_hasta' => $this->request->getVar('fecha_hasta'),
        'id_barber' => $this->request->getVar('id_barber'),
    ];
    $datos['turnos'] = $turnosModel->obtenerTurnos($filtros);
    //Creo un objeto del tipo modelo y en la misma linea ejecuto una funcion de ese modelo.
    $datos2['barbers'] = (new Usuarios_model())->getUsBaja('NO');
    $datos3['servicios'] = (new Servicios_model())->getServicio();
    $datos4['clientes'] = (new Clientes_model())->getClientes();

    $data['titulo'] = 'Listado de Turnos Filtrados';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('turnos/turnosCompletados', $datos + $datos2 + $datos3 + $datos4);
    echo view('footer/footer');
}


}