<?php
namespace App\Controllers;
Use App\Models\Productos_model;
Use App\Models\Tipos_precio_model;
Use App\Models\categoria_model;
use CodeIgniter\Controller; 

class Producto_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);

	}

    public function verHistorialProductos() {
    $historialModel = new \App\Models\modif_productos();

    // Obtener fechas desde GET o POST
    $desde = $this->request->getVar('desde');
    $hasta = $this->request->getVar('hasta');

    // Convertir a d-m-Y solo si no están vacías
    $filtros = [
        'desde' => !empty($desde) ? date('d-m-Y', strtotime($desde)) : null,
        'hasta' => !empty($hasta) ? date('d-m-Y', strtotime($hasta)) : null,
    ];

    // Obtener historial desde el modelo
    $historial = $historialModel->obtenerHistorialPorFechas($filtros);

    // Preparar datos para inputs (en formato Y-m-d para el value del input)
    $fechaDesde = $desde ?: '';
    $fechaHasta = $hasta ?: '';

    $data = [
        'titulo' => 'Historial Modificaciones',
        'historial' => $historial,
        'fechaDesde' => $fechaDesde,
        'fechaHasta' => $fechaHasta
    ];

    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('admin/historial_prods_view', $data);
    echo view('footer/footer');
    }


    public function EdicionRapidaProd() {
    $model = new Productos_model();
    $historialModel = new \App\Models\modif_productos(); // Modelo del historial

    $id = $this->request->getPost('id_prod');

    // Obtener el producto actual
    $productoActual = $model->find($id);

    if (!$productoActual) {
        session()->setFlashdata('msgEr', 'Producto no encontrado');
        return redirect()->to(base_url('Lista_Productos'));
    }

    $data = [];
    $hayCambios = false;

    // Variables para historial
    $nuevoPrecioVta = $productoActual['precio_vta'];
    $nuevoStock = $productoActual['stock'];

    // Validar y actualizar precio
    if ($this->request->getPost('precio') !== null && $this->request->getPost('precio') !== '') {
        $nuevoPrecio = (float)$this->request->getPost('precio');
        if ($nuevoPrecio != $productoActual['precio']) {
            $data['precio'] = $nuevoPrecio;
            $hayCambios = true;
        }
    }

    // Validar y actualizar precio_vta
    if ($this->request->getPost('precio_vta') !== null && $this->request->getPost('precio_vta') !== '') {
        $nuevoPrecioVtaPost = (float)$this->request->getPost('precio_vta');
        if ($nuevoPrecioVtaPost != $productoActual['precio_vta']) {
            $data['precio_vta'] = $nuevoPrecioVtaPost;
            $nuevoPrecioVta = $nuevoPrecioVtaPost;
            $hayCambios = true;
        }
    }

    // Validar y actualizar stock
    if ($this->request->getPost('stock') !== null && $this->request->getPost('stock') !== '') {
        $nuevoStockPost = (int)$this->request->getPost('stock');
        if ($nuevoStockPost != $productoActual['stock']) {
            $data['stock'] = $nuevoStockPost;
            $nuevoStock = $nuevoStockPost;
            $hayCambios = true;
        }
    }

    if ($hayCambios) {
        try {
            // Guardar cambios en producto
            $model->updateDatosProd($id, $data);

            // Guardar historial
            $registroHistorial = [
                'id_prod' => $id,
                'dia_modif' => date('d-m-Y'),
                'hora_modif' => date('H:i:s'),
                'usuario_id' => session()->get('id'),
                'stock_anterior' => $productoActual['stock'],
                'precio_vta' => $productoActual['precio_vta'],
                'nvo_stock' => $nuevoStock,
                'nvo_precio_vta' => $nuevoPrecioVta
            ];
            $historialModel->insert($registroHistorial);

            session()->setFlashdata('msg', 'Producto actualizado correctamente');
        } catch (\Exception $e) {
            session()->setFlashdata('msgEr', 'Error al actualizar: ' . $e->getMessage());
        }
    } else {
        session()->setFlashdata('msg', 'No se realizaron cambios');
    }

    // ✅ Mantener búsqueda y paginación al volver
    $search = $this->request->getPost('search') ?? '';
    $page = (int) $this->request->getPost('page') ?: 1;

    return redirect()->to(base_url('Lista_Productos?page=' . $page . '&search=' . urlencode($search)));
    }



	public function nuevoProducto(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
        $eliminado = 'NO';
        $data['categorias']= $Model->getProdBaja($eliminado);//trae la categoria del db
        
		$data['titulo']='Nuevo Producto';
                echo view('navbar/navbar');
                echo view('header/header',$data);
                echo view('admin/nuevoProducto_view',$data);
                echo view('footer/footer');
	}

    // funcion para agregar nueva categoria
    public function nuevoCategoria(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }    
		$data['titulo']='Nuevo Categoria';
                echo view('navbar/navbar');
                echo view('header/header',$data);
                echo view('admin/nuevoCategoria_view');
                echo view('footer/footer');
	}

    

public function ProductoValidation()
{
    $session = session();

    if (!$session->has('id')) { 
        return redirect()->to(base_url('login'));
    }
    
    $reglas = [
        'nombre'       => 'required|min_length[3]|is_unique[productos.nombre]',
        'categoria_id' => 'required|min_length[1]|max_length[20]',
        'precio'       => 'required|min_length[2]|max_length[10]',
        'precio_vta'   => 'required|min_length[2]',
        'stock'        => 'required|min_length[1]|max_length[10]',
        'stock_mb2'    => 'required|min_length[1]|max_length[10]',
        'stock_min'    => 'required|min_length[1]|max_length[10]',
    ];

    $codigoBarra = $this->request->getVar('codigo_barra');

    if (strlen($codigoBarra) >= 7) {
        $reglas['codigo_barra'] = 'is_unique[productos.codigo_barra]';
    }

    if (!$this->validate($reglas)) {
        return redirect()->back()->withInput();
    }

    // ---------------------------------------------
    // SUBIR IMAGEN LOCAL
    // ---------------------------------------------
    $img = $this->request->getFile('imagen');
    $nombre_aleatorio = $img->getRandomName();
    $img->move(ROOTPATH . 'assets/uploads', $nombre_aleatorio);

    // ---------------------------------------------
    // GUARDAR PRODUCTO LOCAL
    // ---------------------------------------------
    $ProductoModel = new Productos_model();

    $ProductoModel->save([
        'nombre'        => $this->request->getVar('nombre'),
        'descripcion'   => $this->request->getVar('descripcion'),
        'imagen'        => $nombre_aleatorio,
        'categoria_id'  => $this->request->getVar('categoria_id'),
        'precio'        => $this->request->getVar('precio'),
        'precio_vta'    => $this->request->getVar('precio_vta'),
        'stock'         => $this->request->getVar('stock'),
        'stock_min'     => $this->request->getVar('stock_min'),
        'codigo_barra'  => $codigoBarra,
        'eliminado'     => 'NO',
    ]);

    $idProductoNuevo = $ProductoModel->getInsertID();

    // ---------------------------------------------
    // GUARDAR TIPOS DE PRECIO LOCAL
    // ---------------------------------------------
    $TiposPrecioModel = new Tipos_precio_model();

    $precioPromo1   = $this->request->getVar('precio_promo1');
    $cantidadPromo1 = 1;

    $precioPromo2   = $this->request->getVar('precio_promo2');
    $cantidadPromo2 = 1;

    if (!empty($precioPromo1)) {
        $TiposPrecioModel->save([
            'id_prod'    => $idProductoNuevo,
            'nom_precio' => 'PROMO1',
            'precio'     => $precioPromo1,
            'cantidad'   => $cantidadPromo1,
        ]);
    }

    if (!empty($precioPromo2)) {
        $TiposPrecioModel->save([
            'id_prod'    => $idProductoNuevo,
            'nom_precio' => 'PROMO2',
            'precio'     => $precioPromo2,
            'cantidad'   => $cantidadPromo2,
        ]);
    }

    // ---------------------------------------------
    // GUARDAR EN MB2 SI CORRESPONDE
    // ---------------------------------------------
    $localIndependencia = $this->request->getPost('local_independencia');

    if ($localIndependencia == 1) {

        $dbExt = \Config\Database::connect('mb2');

        $ProductoExt    = new \App\Models\Productos_model($dbExt);
        $TiposPrecioMB2 = new \App\Models\Tipos_precio_model($dbExt);

        $nombreProd = $this->request->getVar('nombre');

        // Buscar si ya existe en MB2 por código de barra O por nombre
        // Se usan consultas separadas para evitar problemas con orWhere
        $existeExt = null;

        //if (strlen($codigoBarra) > 6) {
        //    $existeExt = $ProductoExt
        //                    ->where('codigo_barra', $codigoBarra)
         //                   ->first();
        //}

        if (!$existeExt) {
            $existeExt = $ProductoExt
                            ->where('nombre', $nombreProd)
                            ->first();
        }

        if (!$existeExt) {

            $ProductoExt->save([
                'nombre'        => $this->request->getVar('nombre'),
                'descripcion'   => $this->request->getVar('descripcion'),
                'imagen'        => $nombre_aleatorio,
                'categoria_id'  => $this->request->getVar('categoria_id'),
                'precio'        => $this->request->getVar('precio'),
                'precio_vta'    => $this->request->getVar('precio_vta'),
                'stock'         => $this->request->getVar('stock_mb2'),
                'stock_min'     => $this->request->getVar('stock_min'),
                'codigo_barra'  => $codigoBarra,
                'eliminado'     => 'NO'
            ]);

            $idProductoMB2 = $ProductoExt->getInsertID();

            if (!empty($precioPromo1)) {
                $TiposPrecioMB2->save([
                    'id_prod'    => $idProductoMB2,
                    'nom_precio' => 'PROMO1',
                    'precio'     => $precioPromo1,
                    'cantidad'   => $cantidadPromo1,
                ]);
            }

            if (!empty($precioPromo2)) {
                $TiposPrecioMB2->save([
                    'id_prod'    => $idProductoMB2,
                    'nom_precio' => 'PROMO2',
                    'precio'     => $precioPromo2,
                    'cantidad'   => $cantidadPromo2,
                ]);
            }
        }
    }

    // ---------------------------------------------
    // FINAL
    // ---------------------------------------------
    session()->setFlashdata('msg', 'Producto creado con éxito.');
    return redirect()->to(base_url('nuevoProducto'));
}


    // verifica los datos de la categoria nueva
    public function categoriaValidation() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $input = $this->validate([
            'descripcion'   => 'required'
        ]);
        $categoriaModel = new categoria_model();
        
        if (!$input) {
               $data['titulo']='Nuevo Categoria';
               echo view('navbar/navbar');
               echo view('header/header',$data);
                echo view('admin/nuevoCategoria_view',['validation' => $this->validator]);
                echo view('footer/footer');
        } else {

        	

            $categoriaModel->save([
                'descripcion' => $this->request->getVar('descripcion'),
                'eliminado' => "No" 
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

        // Capturamos la página actual de paginación (por defecto 1 si no existe)
        $page = $this->request->getGet('page') ?? 1;

        $busqueda = $this->request->getGet('search');
        // Pasamos la página actual para que paginate sepa cuál devolver
        $productos = $ProductosModel->getProductosPaginadosTodos($eliminado, $busqueda, $page);

        $pager = $ProductosModel->getPager();
    
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
        $dato1['titulo'] = 'Productos Disponibles';
        $data['productos'] = $productos;
        $data['pager'] = $pager;
        $data['page'] = $page;  // <-- enviar la página actual a la vista

        echo view('navbar/navbar');
        echo view('header/header',$dato1);
         echo view('admin/productos_view', $data + $dato);
          echo view('footer/footer');
       
    } 

public function getPrecioPromo()
{
    $id_prod = $this->request->getPost('id_prod');
    $tipo    = $this->request->getPost('tipo');

    // Validación básica
    if (empty($id_prod) || empty($tipo)) {
        return $this->response->setJSON([
            'precio'   => null,
            'cantidad' => null
        ]);
    }

    $model = new \App\Models\Tipos_precio_model();

    $promo = $model->where('id_prod', $id_prod)
                   ->where('nom_precio', $tipo)
                   ->first();

    if ($promo) {

        return $this->response->setJSON([
            'precio'   => $promo['precio'],
            'cantidad' => $promo['cantidad'] // 👈 ahora también mandamos la cantidad
        ]);

    }

    return $this->response->setJSON([
        'precio'   => null,
        'cantidad' => null
    ]);
}

public function getTiposPrecio()
{
    $id_prod = $this->request->getPost('id_prod');

    $model = new \App\Models\Tipos_precio_model();
    $existentes = $model->where('id_prod', $id_prod)->findAll();

    // Tipos FIJOS del sistema
    $tiposFijos = ['PROMO1', 'PROMO2', 'OUTLET'];

    $resultado = [];

    foreach ($tiposFijos as $nombre) {

        $encontrado = null;

        foreach ($existentes as $row) {
            if ($row['nom_precio'] === $nombre) {
                $encontrado = $row;
                break;
            }
        }

        if ($encontrado) {
            $resultado[] = $encontrado;
        } else {
            $resultado[] = [
                'id' => '',
                'nom_precio' => $nombre,
                'precio' => '',
                'cantidad' => ''
            ];
        }
    }

    return $this->response->setJSON($resultado);
}

public function updateTiposPrecio()
{
    $producto_id = $this->request->getPost('producto_id');
    $ids = $this->request->getPost('ids');
    $nombres = $this->request->getPost('nombres');
    $precios = $this->request->getPost('precios');
    $cantidades = $this->request->getPost('cantidades');

    $model = new \App\Models\Tipos_precio_model();

    for ($i = 0; $i < count($nombres); $i++) {

        $precio = isset($precios[$i]) ? floatval($precios[$i]) : 0;
        $cantidad = isset($cantidades[$i]) ? intval($cantidades[$i]) : 1;
        $id = $ids[$i] ?? null;
        $nombre = $nombres[$i];

        // 🔴 SI EL PRECIO ES 0 → ELIMINAR REGISTRO
        if ($precio <= 0) {

            if (!empty($id)) {
                $model->delete($id);
            } else {
                // Por seguridad, eliminar si existe por nombre
                $model->where('id_prod', $producto_id)
                      ->where('nom_precio', $nombre)
                      ->delete();
            }

            continue;
        }

        // 🟢 SI EXISTE ID → ACTUALIZA
        if (!empty($id)) {

            $model->update($id, [
                'precio' => $precio,
                'cantidad' => $cantidad
            ]);

        } else {

            // 🔎 VERIFICAR SI YA EXISTE
            $existe = $model->where('id_prod', $producto_id)
                            ->where('nom_precio', $nombre)
                            ->first();

            if ($existe) {

                // Si existe pero no tenía ID en el form
                $model->update($existe['id'], [
                    'precio' => $precio,
                    'cantidad' => $cantidad
                ]);

            } else {

                $model->insert([
                    'id_prod' => $producto_id,
                    'nom_precio' => $nombre,
                    'precio' => $precio,
                    'cantidad' => $cantidad
                ]);
            }
        }
    }

    return $this->response->setJSON(['status' => 'ok']);
}

    // muestra las categorias 
    public function ListaCategorias(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
        $eliminado = 'NO';
        $productos = $Model->getProdBaja($eliminado);
        //print_r($dato);
        //exit;
        $dato1['titulo']='Lista de Categorias'; 
        $data['productos'] = $productos;
        echo view('navbar/navbar');
        echo view('header/header',$dato1);
         echo view('admin/categorias_view.php', $data);
          echo view('footer/footer');
       
    }

	public function ProductosDisp()
{
    $session = session();
    $cart = \Config\Services::cart();

    if (!$session->has('id')) {
        return redirect()->to(base_url('login'));
    }

    // ===============================
    // CATEGORÍAS
    // ===============================
    $Model = new categoria_model();
    $dato['categorias'] = $Model->getCategoria();

    // ===============================
    // PRODUCTOS PAGINADOS
    // ===============================
    $ProductosModel = new Productos_model();
    $eliminado = 'NO';

    $page = $this->request->getGet('page') ?? 1;
    $busqueda = $this->request->getGet('search');

    $productos = $ProductosModel->getProductosPaginados($eliminado, $busqueda, $page);
    $pager = $ProductosModel->getPager();

    // ===============================
    // TRAER TIPOS DE PRECIO
    // ===============================
    $tiposModel = new \App\Models\Tipos_precio_model();
    $tipos = [];

    // 🔹 1) IDs de productos listados
    $idsProductos = !empty($productos) ? array_column($productos, 'id') : [];

    // 🔹 2) IDs de productos en carrito
    $carrito = $cart->contents();
    $idsCarrito = [];

    if (!empty($carrito)) {
        foreach ($carrito as $item) {
            $idsCarrito[] = $item['id'];
        }
    }

    // 🔹 3) Unificamos todos los IDs
    $idsTotales = array_unique(array_merge($idsProductos, $idsCarrito));

    if (!empty($idsTotales)) {

        // 🔥 TRAER PRODUCTOS (para precio NORMAL)
        $productosTotales = $ProductosModel
            ->whereIn('id', $idsTotales)
            ->findAll();

        $productosIndexados = [];
        foreach ($productosTotales as $prod) {
            $productosIndexados[$prod['id']] = $prod;
        }

        // 🔥 TRAER PROMOS
        $tiposData = $tiposModel
            ->whereIn('id_prod', $idsTotales)
            ->findAll();

        foreach ($tiposData as $tipo) {
            $tipos[$tipo['id_prod']][] = $tipo;
        }

        // 🔥 AGREGAR NORMAL MANUALMENTE (SIEMPRE)
        foreach ($idsTotales as $idProd) {

            if (!isset($productosIndexados[$idProd])) continue;

            $tipos[$idProd] = array_merge(
                [[
                    'id'         => 0,
                    'id_prod'    => $idProd,
                    'precio'     => $productosIndexados[$idProd]['precio_vta'],
                    'cantidad'   => 1,
                    'nom_precio' => 'NORMAL'
                ]],
                $tipos[$idProd] ?? []
            );
        }
    }

    // ===============================
    // STOCK BAJO
    // ===============================
    $productos_bajo_stock = array_filter($productos, function ($producto) {
        return $producto['stock'] <= $producto['stock_min'];
    });

    if (!empty($productos_bajo_stock)) {
        $session->setFlashdata(
            'mensaje_stock',
            '¡Atención! Algunos productos tienen stock bajo o nulo.'
        );
    }

    // ===============================
    // DATOS A LA VISTA
    // ===============================
    $dato1['titulo'] = 'Productos Disponibles';

    $data = [
        'productos' => $productos,
        'tipos'     => $tipos,
        'pager'     => $pager,
        'page'      => $page
    ];

    $carritoData = [
        'carrito' => $carrito,
        'tipos'   => $tipos
    ];
    //print_r($carritoData);exit;
    // ===============================
    // VISTAS
    // ===============================
    echo view('navbar/navbar');
    echo view('header/header', $dato1);
    echo view('productos/listar', $data + $dato);
    echo view('carrito/ProductosEnCarrito', $carritoData);
    echo view('footer/footer');
}

    public function ProductosStockBajo(){
        $ProductosModel = new Productos_model();
        $data['productos'] = $ProductosModel->getPorStockBajo();
        $Model = new categoria_model();
    	$dato1['categorias']=$Model->getCategoria();//trae la categoria del db
        $dato['titulo']='Productos Disponibles';
        echo view('navbar/navbar');
        echo view('header/header',$dato);        
         echo view('admin/productos_view', $data + $dato1);
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
        $eliminado = 'NO';
        $dato1['categorias']= $Model->getProdBaja($eliminado);//trae la categoria del db
    	$Model = new Productos_model();
    	$data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
                echo view('navbar/navbar');
                echo view('header/header',$dato);
                echo view('admin/editarProducto_view',compact('data')+ $dato1);
                echo view('footer/footer');
    }
    //editar categoria
    public function getCategoriaEdit($categoria_id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    	$Model = new categoria_model();
    	$data=$Model->getEdit($categoria_id);
            $dato['titulo']='Editar Producto'; 
                echo view('navbar/navbar');
                echo view('header/header',$dato);
                echo view('admin/editarCategoria_view',compact('data'));
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
            echo view('navbar/navbar');
            echo view('header/header',$dato);   
                echo view('back/carrito/DetalleProducto_view',compact('data'));
                echo view('footer/footer');
    }

    public function ProdValidationEdit() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        //print_r($_POST);exit;
        
        $input = $this->validate([
            'codigo_barra' => "required|is_unique[productos.codigo_barra,id,{$_POST['id']}]", // Ignora el ID actual
            'nombre'   => 'required|min_length[3]',            
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
            $ModelCat = new categoria_model();
    	    $dato1['categorias']=$ModelCat->getCategoria();//trae la categoria del db
            $data=$Model->getProducto($id);
            $dato['titulo']='Editar Producto'; 
            echo view('navbar/navbar');
            echo view('header/header',$dato);   
                echo view('admin/editarProducto_view',compact('data') + $dato1);
                echo view('footer/footer');
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
                'codigo_barra' => $_POST['codigo_barra'],
                
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
                'codigo_barra' => $_POST['codigo_barra'],
            ];
            }
         
         $Model -> updateDatosProd($id,$datos);

         session()->setFlashdata('msg','Producto Editado');

         return redirect()->to(base_url('Lista_Productos'));
        }
    }
    
    //valida la edicion de categoria para cargar al db
    public function CategValidationEdit() {
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        //print_r($_POST);exit;
        
        $input = $this->validate([
            'descripcion'   => 'required|max_length[200]'
        ]);
        $Model = new categoria_model();
        $categoria_id=$_POST['categoria_id'];
        if (!$input) {
            $data=$Model->getEdit($categoria_id);
            $dato['titulo']='Editar Categoria'; 
                echo view('header',$dato);
                echo view('nav_view');
                echo view('back/Admin/editarCategoria_view',compact('data'));
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
                'categiria_id' => $_POST['categoria_id'],
                'descripcion' => $_POST['descripcion'],
                'eliminado' => $_POST['eliminado'],
            ];  
         	}else{
         	$datos=[
                'categiria_id' => $_POST['categoria_id'],
                'descripcion' => $_POST['descripcion'],
                'eliminado' => $_POST['eliminado'],
                
            ];
            }
         
         $Model -> updateDatosCateg($categoria_id,$datos);

         session()->setFlashdata('msg','Categoria Editado');

         return redirect()->to(base_url('ListaCategorias'));
        }
    }

    public function deleteProd($id)
{
    $session = session();

    // Verifica si el usuario está logueado
    if (!$session->has('id')) { 
        return redirect()->to(base_url('login'));
    }

    $request = \Config\Services::request();

    // Capturamos parámetros actuales
    $page   = $request->getGet('page') ?? 1;
    $search = $request->getGet('search');

    $Model = new Productos_model();

    $datos = [
        'eliminado' => 'SI'
    ];

    $Model->update($id, $datos);

    session()->setFlashdata('msg','Producto Eliminado');

    // Redirige manteniendo filtros
    return redirect()->to(base_url('Lista_Productos?page='.$page.'&search='.$search));
}

    //elimina la categoria
    public function deleteCateg($categoria_id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model=new categoria_model();
        $data=$Model->getEliminar($categoria_id);
        $datos=[
                'categoria_id' => 'id',
                'eliminado'  => 'SI',
                
            ];
        $Model->update($categoria_id,$datos);

        session()->setFlashdata('msg','Categoria Eliminado');

        return redirect()->to(base_url('ListaCategorias'));
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
    // lista de categorias eliminados
    public function ListaCategElim(){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model = new categoria_model();
        $userModel = new Productos_model();
        $eliminado='SI';
        $data['productos'] = $Model->getProdBaja($eliminado);
        $dato1['titulo']='Productos Eliminados'; 
        echo view('navbar/navbar');
        echo view('header/header',$dato1);        
         echo view('admin/listCateg_Eliminados_view',$data);
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
    //cambia el estado de categoria eliminado
    public function habilitarCateg($categoria_id){
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        $Model=new categoria_model();
        $data=$Model->getCateg($categoria_id);
        $datos=[
                'categoria_id' => 'categoria_id',
                'eliminado'  => 'NO',
                
            ];
        $Model->update($categoria_id,$datos);

        session()->setFlashdata('msg','Categoria Habilitado');

        return redirect()->to(base_url('eliminadosCateg'));
    }
}