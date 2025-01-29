<?php 
namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\Usuarios_model;
use App\Models\Sesion_model;
  
class Login_controller extends Controller
{
    public function index()
    {
        helper(['form','url']);

         $dato['titulo']='login'; 
        
        echo view('navbar/navbar');
        echo view('header/header',$dato);
        echo view('login/login');
        echo view('footer/footer');
    } 
  
    public function auth()
    {
        $session = session();
        $model = new Usuarios_model();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('pass');
        $data = $model->where('email', $email)->first();
        if($data){
            $pass = $data['pass'];
            $verify_pass = password_verify($password, $pass);
            if($verify_pass){
                if($data['baja']=='SI'){
                    $session->setFlashdata('msg', 'Usted fue dado de Baja');
                    return redirect()->to('login');
                }else{
                    //registrando inicio de sesion
                $registro_sesion = new Sesion_model();
                date_default_timezone_set('America/Argentina/Buenos_Aires');
                $registro_sesion ->save([
                    'id_usuario' => $data['id'],
                    'inicio_sesion' => date('Y-m-d H:i:s'), // Fecha y hora actual de Argentina
                    'estado' => 'activa'
                ]); 
                
                $id_regSesion = $registro_sesion->getInsertID(); 

                $ses_data = [
                    'id' => $data['id'],
                    'nombre' => $data['nombre'],
                    'apellido'=> $data['apellido'],
                    'email' =>  $data['email'],
                    'telefono' => $data['telefono'],
                    'direccion' => $data['direccion'],
                    'perfil_id'=> $data['perfil_id'],
                    'id_sesion'=> $id_regSesion,
                    'logged_in'     => TRUE
                ];
                
                //print_r($ses_data['id_session']);
                //exit;

                $session->set($ses_data);
                if($ses_data['perfil_id'] == 2){
                return redirect()->to('catalogo');
                }else{
                return redirect()->to('/Lista_Productos');
                }
            }}else{
                $session->setFlashdata('msg', 'Password Incorrecta');
                return redirect()->to('login');
            }
        }else{
            $session->setFlashdata('msg', 'Email Incorrecto');
            return redirect()->to('login');
        }
    }
    public function logout()
    {
        $session = session();
         $registro_sesion = new Sesion_model();
         $id_sesion = $session->get('id_sesion'); 
                date_default_timezone_set('America/Argentina/Buenos_Aires');
                $data =[
                    'fin_sesion' => date('Y-m-d H:i:s'), // Fecha y hora actual de Argentina
                    'estado' => 'cerrada'
                ];

                //print_r($id_sesion);
                //print_r($data);
               // exit;
                $registro_sesion->actualizar_sesion($id_sesion,$data);
            $session->destroy();
            return redirect()->to('/');
    }
    //muestra las sesiones de los usuarios
    public function mostrarSesiones()
    {
        $usuarioModel = new Sesion_model();
        // Obtener parámetros de búsqueda y filtro
        $filter = $this->request->getVar('filter'); // Para filtrar por estado
       // $search = $this->request->getVar('search'); // Para filtrar por busqueda
        //print_r($search);
        //exit;
        // Pasar los parámetros al modelo
        if($filter){
            $data['sesiones'] = $usuarioModel->buscarYFiltrar($filter);
        }else{
            $data['sesiones'] = $usuarioModel->getSesionesConUsuarios();
        }
        
       // $sesiones = $usuarioModel->getSesionesConUsuarios();
       
        $dato['titulo']='Sesiones';
        echo view('navbar/navbar'); 
        echo view('header/header',$dato);
         echo view('Login/sesiones',$data);
       echo view('footer/footer');
       // return view('sesiones', ['sesiones' => $sesiones]);
    }
} 
