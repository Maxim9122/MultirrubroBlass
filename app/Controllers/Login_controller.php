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
    $registro_sesion = new Sesion_model();
    
    $email = $this->request->getVar('email');
    $password = $this->request->getVar('pass');
    
    $data = $model->where('email', $email)->first();

    if ($data) {
        $pass = $data['pass'];
        $verify_pass = password_verify($password, $pass);

        if ($verify_pass) {
            if ($data['baja'] == 'SI') {
                $session->setFlashdata('msg', 'Usted fue dado de Baja');
                return redirect()->to('login');
            }

            // ✅ 1️⃣ Verificar si el usuario ya tiene una sesión activa
            $sesion_activa = $registro_sesion->where([
                'id_usuario' => $data['id'],
                'estado' => 'activa'
            ])->first();

            if ($sesion_activa) {
                $session->setFlashdata('msg', 'Este usuario ya tiene una sesión activa.');
                return redirect()->to('login');
            }

            // ✅ 2️⃣ Registrar nueva sesión
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $registro_sesion->save([
                'id_usuario' => $data['id'],
                'inicio_sesion' => date('Y-m-d H:i:s'), // Formato correcto de timestamp
                'estado' => 'activa'
            ]);

            $id_regSesion = $registro_sesion->getInsertID();

            // ✅ 3️⃣ Guardar datos en sesión
            $ses_data = [
                'id' => $data['id'],
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'email' => $data['email'],
                'telefono' => $data['telefono'],
                'direccion' => $data['direccion'],
                'perfil_id' => $data['perfil_id'],
                'id_sesion' => $id_regSesion,
                'logged_in' => TRUE
            ];

            $session->set($ses_data);

            // Redirigir según el perfil
            return ($ses_data['perfil_id'] == 2) ? redirect()->to('catalogo') : redirect()->to('/Lista_Productos');
        } else {
            $session->setFlashdata('msg', 'Password Incorrecta');
            return redirect()->to('login');
        }
    } else {
        $session->setFlashdata('msg', 'Email Incorrecto');
        return redirect()->to('login');
    }
}



    public function logout()
    {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }

         $registro_sesion = new Sesion_model();
         $id_sesion = $session->get('id_sesion'); 
         //print_r();
         //exit;
                date_default_timezone_set('America/Argentina/Buenos_Aires');
                $data =[
                    'fin_sesion' => date('d-m-Y H:i'), // Fecha y hora actual de Argentina
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
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }

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
        //print_r($data['sesiones']); 
        //exit;
       // $sesiones = $usuarioModel->getSesionesConUsuarios();
       
        $dato['titulo']='Sesiones';
        echo view('navbar/navbar'); 
        echo view('header/header',$dato);
         echo view('Login/sesiones',$data);
       echo view('footer/footer');
       // return view('sesiones', ['sesiones' => $sesiones]);
    }
} 
