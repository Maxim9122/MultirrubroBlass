<?php
namespace App\Controllers;
Use App\Models\Productos_model;
Use App\Models\categoria_model;
use CodeIgniter\Controller; 

class Producto_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);

	}



	public function nuevoProducto(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
    	$dato['categorias']=$Model->getCategoria();//trae la categoria del db
        
		$data['titulo']='Nuevo Producto';
                echo view('navbar/navbar');
                echo view('header/header',$data);
                echo view('admin/nuevoProducto_view',$dato);
                echo view('footer/footer');
	}

	public function ProductoValidation() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $input = $this->validate([
            'nombre'   => 'required|min_length[3]',
            'descripcion'   => 'required',
            'categoria_id' => 'required|min_length[1]|max_length[20]',
            'precio'    => 'required|min_length[2]|max_length[10]',
            'precio_vta'  => 'required|min_length[2]',
            'stock'     => 'required|min_length[1]|max_length[10]',
            'stock_min'     => 'required|min_length[1]|max_length[10]',
            
            
        ]);
        $ProductoModel = new Productos_model();
        
        if (!$input) {
               $data['titulo']='Nuevo Producto'; 
               echo view('navbar/navbar');
               echo view('header/header',$data);
                echo view('admin/nuevoProducto_view',['validation' => $this->validator]);
                echo view('footer/footer');
        } else {

        	$img = $this->request->getFile('imagen');
        	$nombre_aleatorio= $img->getRandomName();
        	$img->move(ROOTPATH.'assets/uploads',$nombre_aleatorio);

            $ProductoModel->save([
                'nombre' => $this->request->getVar('nombre'),
                'descripcion' => $this->request->getVar('descripcion'),
                'imagen' => $img->getName(),
                'categoria_id' => $this->request->getVar('categoria_id'),
                'precio' => $this->request->getVar('precio'),
                'precio_vta'  => $this->request->getVar('precio_vta'),
                'stock' => $this->request->getVar('stock'),
                'stock_min' => $this->request->getVar('stock_min'),
                'eliminado' => 'NO',
                
            ]);  
            session()->setFlashdata('msg','Producto Creado con Éxito!');
             return redirect()->to(base_url('Lista_Productos'));
        }
    }

    public function ListaProductos(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
    	$dato['categorias']=$Model->getCategoria();//trae la categoria del db
        $ProductosModel = new Productos_model();
        $eliminado = 'NO';
        $productos = $ProductosModel->getProdBaja($eliminado);
    
        // Verificar si algún producto tiene stock bajo
        $productos_bajo_stock = array_filter($productos, function($producto) {
            return $producto['stock'] <= $producto['stock_min'];
        });
    
        // Si hay productos con stock bajo, guardamos un mensaje en sesión
        if (!empty($productos_bajo_stock)) {
            $session->setFlashdata('mensaje_stock', '¡Atención! Algunos productos tienen stock bajo o nulo.');
        }
        //print_r($dato);
        //exit;
        $dato1['titulo']='Lista de Productos'; 
        $data['productos'] = $productos;
        echo view('navbar/navbar');
        echo view('header/header',$dato1);
         echo view('admin/Productos_view', $data + $dato);
          echo view('footer/footer');
       
    } 

	public function ProductosDisp(){
        $session = session();
        
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login'));
        }
    
        $ProductosModel = new Productos_model();
        $eliminado = 'NO';
        $productos = $ProductosModel->getProdBaja($eliminado);
    
        // Verificar si algún producto tiene stock bajo
        $productos_bajo_stock = array_filter($productos, function($producto) {
            return $producto['stock'] <= $producto['stock_min'];
        });
    
        // Si hay productos con stock bajo, guardamos un mensaje en sesión
        if (!empty($productos_bajo_stock)) {
            $session->setFlashdata('mensaje_stock', '¡Atención! Algunos productos tienen stock bajo o nulo.');
        }
    
        $dato['titulo'] = 'Productos Disponibles'; 
        $data['productos'] = $productos;
    
        echo view('navbar/navbar');
        echo view('header/header', $dato);        
        echo view('productos/listar', $data);
        echo view('footer/footer');
    }
    

    public function Indumentaria(){
        $ProductosModel = new Productos_model();
        $tipo='1';
        $data['productos'] = $ProductosModel->getTipo($tipo);
        $dato['titulo']='Productos Disponibles';
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('productos/listar', $data);
          echo view('footer/footer');
       
    }

    public function Calzado(){
        $ProductosModel = new Productos_model();
        $tipo='2';
        $data['productos'] = $ProductosModel->getTipo($tipo);
        $dato['titulo']='Productos Disponibles';
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('productos/listar', $data);
          echo view('footer/footer');
       
    }

    public function Accesorios(){
        $ProductosModel = new Productos_model();
        $tipo='3';
        $data['productos'] = $ProductosModel->getTipo($tipo);
        $dato['titulo']='Productos Disponibles';
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('productos/listar', $data);
          echo view('footer/footer');
       
    }

    public function getProductoEdit($id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
    	$dato1['categorias']=$Model->getCategoria();//trae la categoria del db
    	$Model = new Productos_model();
    	$data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
                echo view('navbar/navbar');
                echo view('header/header',$dato);
                echo view('admin/editarProducto_view',compact('data')+ $dato1);
                echo view('footer/footer');
    }

    public function ProductoDetalle($id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    	$Model = new Productos_model();
    	$data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
                echo view('header',$dato);
                echo view('nav_view');
                echo view('back/carrito/DetalleProducto_view',compact('data'));
                echo view('footer');
    }

    public function ProdValidationEdit() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        //print_r($_POST);exit;
        
        $input = $this->validate([
            'nombre'   => 'required|min_length[3]',
            'descripcion'   => 'required|max_length[200]',
            'categoria_id' => 'required|min_length[1]|max_length[2]',
            'precio'    => 'required|min_length[2]|max_length[10]',
            'precio_vta'  => 'required|min_length[2]',
            'stock'     => 'required|min_length[1]|max_length[10]',
            'stock_min'     => 'required|min_length[1]|max_length[10]',
            'eliminado' => 'required|min_length[2]|max_length[2]',
        ]);
        $Model = new Productos_model();
        $id=$_POST['id'];
        if (!$input) {
            $data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
                echo view('header',$dato);
                echo view('nav_view');
                echo view('back/Admin/editarProducto_view',compact('data'));
                echo view('footer');
        } else {
        	$validation= $this->validate([
        		'image' => ['uploaded[imagen]',
        		'mime_in[imagen,image/jpg,image/jpeg,image/png]',
        	]
        	]);
        	if($validation){
        	$img = $this->request->getFile('imagen');
        	$nombre_aleatorio= $img->getRandomName();
        	$img->move(ROOTPATH.'assets/uploads',$nombre_aleatorio);
            $datos=[
                'id' => $_POST['id'],
                'nombre' =>$_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'imagen' => $img->getName(),
                'precio' => $_POST['precio'],
                'precio_vta'  => $_POST['precio_vta'],
                'categoria_id'  => $_POST['categoria_id'],
                'stock'  => $_POST['stock'],
                'stock_min'  => $_POST['stock_min'],
                'eliminado' => $_POST['eliminado'],
                
            ];  
         	}else{
         	$datos=[
                'id' => $_POST['id'],
                'nombre' =>$_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'precio' => $_POST['precio'],
                'precio_vta'  => $_POST['precio_vta'],
                'categoria_id'  => $_POST['categoria_id'],
                'stock'  => $_POST['stock'],
                'stock_min'  => $_POST['stock_min'],
                'eliminado' => $_POST['eliminado'],
                
            ];
            }
         
         $Model -> updateDatosProd($id,$datos);

         session()->setFlashdata('msg','Producto Editado');

         return redirect()->to(base_url('Lista_Productos'));
        }
    }

    public function deleteProd($id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model=new Productos_model();
        $data=$Model->getProducto($id);
        $datos=[
                'id' => 'id',
                'eliminado'  => 'SI',
                
            ];
        $Model->update($id,$datos);

        session()->setFlashdata('msg','Producto Eliminado');

        return redirect()->to(base_url('Lista_Productos'));
    }

    public function ListaProductosElim(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
    	$dato['categorias']=$Model->getCategoria();//trae la categoria del db
        $userModel = new Productos_model();
        $eliminado='SI';
        $data['productos'] = $userModel->getProdBaja($eliminado);
        $dato1['titulo']='Productos Eliminados'; 
        echo view('navbar/navbar');
        echo view('header/header',$dato1);        
         echo view('admin/listProd_Eliminados_view',$data + $dato);
          echo view('footer/footer');
    }

    public function habilitarProd($id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model=new Productos_model();
        $data=$Model->getProducto($id);
        $datos=[
                'id' => 'id',
                'eliminado'  => 'NO',
                
            ];
        $Model->update($id,$datos);

        session()->setFlashdata('msg','Producto Habilitado');

        return redirect()->to(base_url('eliminadosProd'));
    }
}