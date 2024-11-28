<?php
namespace App\Controllers;
Use App\Models\Productos_model;
use CodeIgniter\Controller; 

class Producto_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);

	}



	public function nuevoProducto(){

		$data['titulo']='Nuevo Producto'; 
                echo view('navbar/navbar');
                echo view('header/header',$data);
                echo view('admin/nuevoProducto_view');
                echo view('footer/footer');
	}

	public function ProductoValidation() {
        
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
        $ProductosModel = new Productos_model();
        $eliminado='NO';
        $data['productos'] = $ProductosModel->getProdBaja($eliminado);
        $dato['titulo']='Lista de Productos'; 
        echo view('navbar/navbar');
        echo view('header/header',$dato);
        
         echo view('admin/Productos_view', $data);
          echo view('footer/footer');
       
    } 

	public function ProductosDisp(){
        $ProductosModel = new Productos_model();
        $eliminado='NO';
        $data['productos'] = $ProductosModel->getProdBaja($eliminado);
        $dato['titulo']='Productos Disponibles'; 
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
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
    	$Model = new Productos_model();
    	$data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
                echo view('navbar/navbar');
                echo view('header/header',$dato);                
                echo view('admin/editarProducto_view',compact('data'));
                echo view('footer/footer');
    }

    public function ProductoDetalle($id){
    	$Model = new Productos_model();
    	$data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
                echo view('header',$dato);
                echo view('nav_view');
                echo view('back/carrito/DetalleProducto_view',compact('data'));
                echo view('footer');
    }

    public function ProdValidationEdit() {
        
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
        $userModel = new Productos_model();
        $eliminado='SI';
        $data['productos'] = $userModel->getProdBaja($eliminado);
        $dato['titulo']='Productos Eliminados'; 
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('admin/listProd_Eliminados_view',$data);
          echo view('footer/footer');
    }

    public function habilitarProd($id){
    
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