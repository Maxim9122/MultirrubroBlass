<?php
namespace App\Controllers;
use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\Cabecera_model;
Use App\Models\VentaDetalle_model;
use App\Models\Turnos_model;
use App\Models\Usuarios_model;
use App\Models\Clientes_model;
//use Dompdf\Dompdf;

class Clientes_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}

    function ListarClientes()
	{
		$ClientesModel = new Clientes_model();
        $datos['clientes'] = $ClientesModel->getClientes();
		$data['titulo'] = 'Confirmar compra';
		echo view('navbar/navbar');
		echo view('header/header',$data);		
		echo view('clientes/ListaClientes',$datos);
		echo view('footer/footer');
    }

    public function editarCliente($id){
    	$ClientesModel = new Clientes_model();
    	$data=$ClientesModel->getCliente($id);
            $dato['titulo']='Editar Cliente'; 
                echo view('navbar/navbar');
                echo view('header/header',$dato);                
                echo view('clientes/editoCliente',compact('data'));
                echo view('footer/footer');
    }

    //Verifica y edita el cliente
    public function EdicionOk()
    {
        $input = $this->validate([
            'nombre' => 'required|min_length[3]',
            'telefono' => 'required|min_length[10]|max_length[10]'
        ]);
    
        $id = $this->request->getVar('id');
        $clienteModel = new Clientes_model();
    
        if (!$input) {
            $data = $clienteModel->getCliente($id);
            $data['titulo'] = 'Editar Cliente'; 
            echo view('navbar/navbar');
            echo view('header/header', $data);                
            echo view('clientes/editoCliente', compact('data'));
            echo view('footer/footer');
        } else {
            // Manejo de archivo subido
            $foto = $this->request->getFile('foto');
    
            if ($foto && $foto->isValid() && !$foto->hasMoved()) {
                $nombre_aleatorio = $foto->getRandomName();
                $foto->move(ROOTPATH . 'assets/uploads', $nombre_aleatorio);
    
                $datos = [
                    'nombre' => $this->request->getVar('nombre'),
                    'telefono' => $this->request->getVar('telefono'),
                    'foto' => $nombre_aleatorio,
                ];
            } else {
                $datos = [
                    'nombre' => $this->request->getVar('nombre'),
                    'telefono' => $this->request->getVar('telefono'),
                ];
            }
    
            $clienteModel->update($id, $datos);
    
            session()->setFlashdata('msg', 'Cliente Actualizado!');
            return redirect()->to(base_url('clientes'));
        }
    }
    

}