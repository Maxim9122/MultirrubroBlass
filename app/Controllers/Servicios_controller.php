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

class Servicios_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}

    function Servicios(){
    // Cargo las lista de los servicios
    $userModel = new Servicios_model();
    $datos['servicios'] = $userModel->getServicio();
    $data['titulo'] = 'Listado de Servicios';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('servicios/servicios_view', $datos);
    echo view('footer/footer');
    }


    // vista de nuevo servicio
    function new_Servicio(){
        // Cargo las vistas
        $data['titulo'] = 'Formulario agregar Servicio';
        echo view('navbar/navbar');
        echo view('header/header', $data);
        echo view('servicios/new_servicio_view');
        echo view('footer/footer');
        }


    // trae los datos del agregar servicios para mandar al modelo
    function agregar_Servicio()
    {
        $input = $this->validate([
            'descripcion' => 'required|min_length[3]',
            'precio' => 'required|numeric|min_length[1]|max_length[10]'
        ]);
    
        $ServiModel = new Servicios_model();
    
        if (!$input) {
            $data['titulo'] = 'Formulario agregar Servicio';
            session()->setFlashdata('fail', 'Error en los datos del formulario.');
            echo view('navbar/navbar');
            echo view('header/header', $data);
            echo view('servicios/new_servicio_view');
            echo view('footer/footer');
        } else {
            $ServiModel->save([
                'descripcion' => $this->request->getPost('descripcion'),
                'precio' => $this->request->getPost('precio'),
            ]);
    
            session()->setFlashdata('success', 'Servicio Agregado!!');
            return redirect()->to(base_url('Lista_servicios'));
        }
    } 
    
    public function editarServi($id){
    	$serviModel = new Servicios_model();
    	$data=$serviModel->getServi($id);
            $dato['titulo']='Editar Servicio'; 
                echo view('navbar/navbar');
                echo view('header/header',$dato);                
                echo view('servicios/editoServicio_view',compact('data'));
                echo view('footer/footer');
    }

    //Verifica y edito los servicios
    public function edicionServiOk()
    {
        // Reglas de validación
        $validationRules = [
            'descripcion' => 'required|min_length[3]',
            'precio'      => 'required|numeric|min_length[1]'
        ];
    
        if (!$this->validate($validationRules)) {
            // Si la validación falla, redirige con errores
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }
    
        // Si pasa la validación, guarda los datos
        $ServiModel = new Servicios_model();
        $id = $this->request->getPost('id');
        $data = [
            'descripcion' => $this->request->getPost('descripcion'),
            'precio'      => $this->request->getPost('precio')
        ];
    
        $ServiModel->update($id, $data);
    
        session()->setFlashdata('success', 'Servicio modificado correctamente.');
        return redirect()->to(base_url('Lista_servicios'));
    }
    

}