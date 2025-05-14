<?php 
namespace App\Controllers;
use App\Models\Usuarios_model;
use CodeIgniter\Controller;

class Datatable_controller extends Controller
{
        //cacheamo la funcion para no ejecutar la db  todo el tiempo
    public function index(){
        $cache = \Config\Services::cache();
        $baja = 'NO';
        $cacheKey = 'usuarios_activos_' . $baja;
    
        if (!$usuarios = $cache->get($cacheKey)) {
            $userModel = new Usuarios_model();
            $usuarios = $userModel->getUsBaja($baja);
            // Guardamos en caché por 5 minutos (300 segundos)
            $cache->save($cacheKey, $usuarios, 300);
        }
    
        $data['usuarios'] = $usuarios;
        $dato['titulo'] = 'Lista de Usuarios';
    
        echo view('navbar/navbar');
        echo view('header/header', $dato);
        echo view('usuarios/usuarios_view', $data);
        echo view('footer/footer');
    }
    

    public function editar($id){

        $userModel=new Usuarios_model();
        $data=$userModel->getUsuario($id);
        $dato['titulo']='Editar Usuario';
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('admin/editarUsuarios_view',compact('data'));
          echo view('footer/footer');
       
   }

   public function editoMisDatos($id){

        $userModel=new Usuarios_model();
        $data=$userModel->getUsuario($id);
        $dato['titulo']='Editar Usuario';
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('usuarios/editoMisDatos_view',compact('data'));
          echo view('footer/footer');
       
   }

}