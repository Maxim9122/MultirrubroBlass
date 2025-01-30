<?php
namespace App\Controllers;

require_once APPPATH . 'libraries/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\Cabecera_model;
Use App\Models\VentaDetalle_model;
Use App\Models\Clientes_model;
use App\Models\Usuarios_model;


class Carrito_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}

	public function ListVentasCabecera()
{
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    // Instanciar el modelo
    $cabeceraModel = new Cabecera_model();
    
    // Llamar al método del modelo para obtener las ventas con clientes
    $datos['ventas'] = $cabeceraModel->getVentasConClientes();
    
    // Pasar el título y los datos a las vistas
    $data['titulo'] = 'Listado de Compras';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('comprasXcliente/ListaVentas_view', $datos);
    echo view('footer/footer');
}

public function ListaComprasCabeceraCliente($id)
{
    // Obtener la fecha de hoy
    $fechaHoy = date('d-m-Y');

    // Instanciar el modelo
    $cabeceraModel = new Cabecera_model();

    // Obtener las ventas del cliente para la fecha de hoy
    $datos['ventas'] = $cabeceraModel->getVentasPorClienteYFecha($id, $fechaHoy);

    // Preparar el título y cargar las vistas
    $data['titulo'] = 'Listado de Compras';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('comprasXcliente/ListaTurnos_view', $datos);
    echo view('footer/footer');
}

public function ListCompraDetalle($id)
{
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
    // Instanciar el modelo
    $cabeceraModel = new Cabecera_model();

    // Obtener los detalles de la venta
    $datos['ventas'] = $cabeceraModel->getDetallesVenta($id);

    // Preparar el título y cargar las vistas
    $data['titulo'] = 'Listado de Compras';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('comprasXcliente/CompraDetalle_view', $datos);
    echo view('footer/footer');
}

    public function productosAgregados(){
        $cart = \Config\Services::cart();
		$carrito['carrito']=$cart->contents();
        $data['titulo']='Productos en el Carrito'; 
		echo view('navbar/navbar');
        echo view('header/header',$data);        
        echo view('carrito/ProductosEnCarrito',$carrito);
        echo view('footer/footer');
    }

    //Agrega elemento al carrito
	function add()
	{
        $cart = \Config\Services::cart();
        // Genera array para insertar en el carrito
		$cart->insert(array(
            'id'      => $_POST['id'],
            'qty'     => 1,
            'price'   => $_POST['precio_vta'],
            'name'    => $_POST['nombre'],
            'options' => array('stock' => $_POST['stock']))
        );           // Almacena el stock disponible
         
		 session()->setFlashdata('msg','Producto Agregado!');
        // Redirige a la misma página que se encuentra
		return redirect()->to(base_url('catalogo'));
	}

	//Agrega elemento al carrito desde confirmar
	function agregar()
	{
        $cart = \Config\Services::cart();
        // Genera array para insertar en el carrito
		$id_producto = uniqid('prod_') . random_int(100000, 999900);
		$cart->insert(array(
            'id'      => $id_producto,
            'qty'     => 1,
            'price'   => $_POST['precio_vta'],
            'name'    => $_POST['nombre'],
            
         ));
		 session()->setFlashdata('msg','Producto Agregado!');
        // Redirige a la misma página que se encuentra
		return redirect()->to(base_url('CarritoList'));
	}

	//Agrega elemento al carrito desde confirmar
	function agregarDesdeListaProd()
	{
        $cart = \Config\Services::cart();
        // Genera array para insertar en el carrito
		$id_producto = uniqid('prod_') . random_int(100000, 999900);
		$cart->insert(array(
            'id'      => $id_producto,
            'qty'     => 1,
            'price'   => $_POST['precio_vta'],
            'name'    => $_POST['nombre'],
            
         ));
		 session()->setFlashdata('msg','Producto Agregado!');
        // Redirige a la misma página que se encuentra
		return redirect()->to($this->request->getHeader('referer')->getValue());
	}

    //Elimina elemento del carrito o el carrito entero
	function remove($rowid){
        $cart = \Config\Services::cart();
        //Si $rowid es "all" destruye el carrito
		if ($rowid==="all")
		{
			$cart->destroy();
		}
		else //Sino destruye sola fila seleccionada
		{
			session()->setFlashdata('msg','Producto Eliminado');
            // Actualiza los datos
			$cart->remove($rowid);
		}
		
        // Redirige a la misma página que se encuentra
		return redirect()->to(base_url('CarritoList'));
	}

    public function procesarCarrito()
    {
        $accion = $this->request->getPost('accion');
    
        if ($accion == 'actualizar') {
            
            $cart = \Config\Services::cart();
            // Recibe los datos del carrito, calcula y actualiza
               $cart_info = $this->request->getPost('cart');
            
            foreach( $cart_info as $id => $carrito)
            {   
                $prod = new Productos_model();
                $idprod = $prod->getProducto($carrito['id']);
                if($carrito['id'] < 100000){
                $stock = $idprod['stock'];
                }
                 $rowid = $carrito['rowid'];
                $price = $carrito['price'];
                $amount = $price * $carrito['qty'];
                $qty = $carrito['qty'];
    
                if($carrito['id'] < 100000){
                if($qty <= $stock && $qty >= 1){ 
                $cart->update(array(
                    'rowid'   => $rowid,
                    'price'   => $price,
                    'amount' =>  $amount,
                    'qty'     => $qty
                    ));	    	
                }else{
                    session()->setFlashdata('msgEr','La Cantidad Solicitada de algunos productos no estan disponibles o SELECCIONASTE 0!');
                }
                }
                
            }
    
            session()->setFlashdata('msg','Carrito Actualizado!');
            // Redirige a la misma página que se encuentra
            return redirect()->to(base_url('CarritoList'));


        } elseif ($accion == 'confirmar') {
            
            $cart = \Config\Services::cart();
            // Recibe los datos del carrito, calcula y actualiza
               $cart_info = $this->request->getPost('cart');
               $errores_stock = false; // Variable para controlar si hay errores de stock

            foreach( $cart_info as $id => $carrito)
            {   
                $prod = new Productos_model();
                $idprod = $prod->getProducto($carrito['id']);
                if($carrito['id'] < 100000){
                $stock = $idprod['stock'];
                }
                 $rowid = $carrito['rowid'];
                $price = $carrito['price'];
                $amount = $price * $carrito['qty'];
                $qty = $carrito['qty'];
    
                if($carrito['id'] < 100000){
                if($qty <= $stock && $qty >= 1){ 
                $cart->update(array(
                    'rowid'   => $rowid,
                    'price'   => $price,
                    'amount' =>  $amount,
                    'qty'     => $qty
                    ));	    	
                }else{
                    // Si hay un error de stock, marca la variable de error y guarda el mensaje
                    $errores_stock = true;
                    session()->setFlashdata('msgEr','La Cantidad Solicitada de algunos productos no estan disponibles o SELECCIONASTE 0!');
                }
                }
                
            }
            
            // Si hubo errores de stock, redirige a la página de carrito
            if ($errores_stock) {
            return redirect()->to(base_url('CarritoList'));
            }
            // Redirige a la página de confirmacion de compra si los calculos de stock estan bien.
            return redirect()->to(base_url('casiListo'));


        } else {
            log_message('error', 'Acción no reconocida: ' . $accion);
        }
    }


    //Muestra los detalles de la venta y confirma(función guarda_compra())
	function muestra_compra()
	{
        $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
		$ClientesModel = new Clientes_model();
        $datos['clientes'] = $ClientesModel->getClientes();
		$data['titulo'] = 'Confirmar compra';
		echo view('navbar/navbar');
		echo view('header/header',$data);		
		echo view('carrito/confirmarCompra',$datos);
		echo view('footer/footer');
    }

    public function guarda_compra()
{
    
    $cart = \Config\Services::cart();
    $session = session();   
    
    $id_ususario = $session->get('id');
    //print_r($id_ususario);
    //exit;
    // Recuperar datos del formulario usando $this->request->getPost()
    $id_cliente = $this->request->getPost('cliente_id');
	
    if ($id_cliente == "Anonimo") {
        $id_cliente = 1; // Valor por defecto si no se envía cliente_id
    }
	
    $tipo_pago = $this->request->getPost('tipo_pago');
    $total = $this->request->getPost('total_venta');
    $total_conDescuento = $this->request->getPost('total_con_descuento');
    if(!$total_conDescuento){
        $total_conDescuento = $total;
    }
    // Establecer zona horaria y obtener fecha/hora en formato correcto
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $hora = date('H:i:s'); // Formato TIME
    $fecha = date('d-m-Y'); // Formato DATE

    $tipo_compra = $this->request->getPost('tipo_compra_input');
    $fecha_pedido = $this->request->getPost('fecha_pedido_input');
    $fecha_pedido_formateada = date('d-m-Y', strtotime($fecha_pedido));
    //print_r($fecha_formateada);
	//exit;
if(!$fecha_pedido){ 
    // Guardar cabecera de la venta tipo compra normal
    $cabecera_model = new Cabecera_model();
    $ventas_id = $cabecera_model->save([
        'fecha'        => $fecha,
        'hora'         => $hora,
        'id_cliente'   => $id_cliente,
        'id_usuario'   => $id_ususario,
        'total_venta'  => $total,
        'tipo_pago'    => $tipo_pago,
        'total_bonificado' => $total_conDescuento,
        'tipo_compra' => $tipo_compra,
        'estado' => 'Realizada'
    ]);
}else{
    // Guardar cabecera de la venta tipo pedido
    $cabecera_model = new Cabecera_model();
    $ventas_id = $cabecera_model->save([
        'fecha'        => $fecha,
        'hora'         => $hora,
        'id_cliente'   => $id_cliente,
        'id_usuario'   => $id_ususario,
        'total_venta'  => $total,
        'tipo_pago'    => $tipo_pago,
        'total_bonificado' => $total_conDescuento,
        'tipo_compra' => $tipo_compra,
        'fecha_pedido' => $fecha_pedido_formateada,
        'estado' => 'Pendiente'
    ]);
}
    // Obtener ID de la cabecera guardada
    $id_cabecera = $cabecera_model->getInsertID();

    // Guardar detalles de la venta si el carrito no está vacío
    if ($cart):
        foreach ($cart->contents() as $item):
            $VentaDetalle_model = new VentaDetalle_model();
            $VentaDetalle_model->save([
                'venta_id'    => $id_cabecera,
                'producto_id' => $item['id'],
                'cantidad'    => $item['qty'],
                'precio'      => $item['price'],
                'total'       => $item['subtotal'],
            ]);

            // Actualizar stock del producto
            $Producto_model = new Productos_model();
            $producto = $Producto_model->find($item['id']); // Asegúrate de usar el método correcto para obtener datos

            if ($producto && isset($producto['stock'])) {
                $stock_edit = $producto['stock'] - $item['qty'];
                $Producto_model->update($item['id'], ['stock' => $stock_edit]);
            }
        endforeach;
    endif;

    // Limpiar el carrito y redirigir con mensaje
    $cart->destroy();
    if($tipo_compra == 'Pedido'){
        session()->setFlashdata('msg', 'Pedido Guardado con Éxito!');
        
        return redirect()->to('catalogo');
    }
    session()->setFlashdata('msg', 'Compra Guardada con Éxito!');
    // Redirige a la vista de la factura
    return redirect()->to('Carrito_controller/verFactura/' . $id_cabecera);
}



	function FacturaAdmin($id)
	{
		//$dompdf = new Dompdf();

		$db = db_connect();
		$builder2 = $db->table('ventas_cabecera a');
		$builder2->where('a.id',$id);
		$builder2->select('a.id , c.nombre , c.apellido, c.telefono , c.direccion , a.total_venta , a.fecha , a.tipo_pago');
		$builder2->join('usuarios c','a.usuario_id = c.id');
		$ventas2= $builder2->get();
		$datos2['datos']=$ventas2->getResultArray();
		//print_r($datos2);
		//exit;

		$builder = $db->table('ventas_detalle u');
		$builder->where('venta_id',$id);
		$builder->select('d.id , d.nombre , u.cantidad , u.precio , u.total ,');
		$builder->join('productos d','u.producto_id = d.id');
		$ventas= $builder->get();
		$datos['ventas']=$ventas->getResultArray();
		//print_r($datos);
		//exit;
		
		$data['titulo'] ='Factura';
		echo view('navbar/navbar');
		echo view('header/header',$data);		
		echo view('comprasXcliente/facturacion_view',$datos2+$datos);
		echo view('footer/footer');

		
	}

	function FacturaCliente($id)
	{
		//$dompdf = new Dompdf();

		$db = db_connect();
		$builder2 = $db->table('ventas_cabecera a');
		$builder2->where('a.id',$id);
		$builder2->select('a.id , c.nombre , c.apellido, c.telefono , c.direccion , a.total_venta , a.fecha , a.tipo_pago');
		$builder2->join('usuarios c','a.usuario_id = c.id');
		$ventas2= $builder2->get();
		$datos2['datos']=$ventas2->getResultArray();
		//print_r($datos2);
		//exit;

		$builder = $db->table('ventas_detalle u');
		$builder->where('venta_id',$id);
		$builder->select('d.id , d.nombre , u.cantidad , u.precio , u.total ,');
		$builder->join('productos d','u.producto_id = d.id');
		$ventas= $builder->get();
		$datos['ventas']=$ventas->getResultArray();
		//print_r($datos);
		//exit;
		
		$data['titulo'] ='Factura';
		echo view('navbar/navbar');
		echo view('header/header',$data);		
		echo view('comprasXcliente/facturacion_view',$datos2+$datos);
		echo view('footer/footer');

		
	}

	//Genero factura de la compra
public function verfactura($id)
{
    $facturaModel = new Cabecera_model();
    $detalleModel = new VentaDetalle_model();
    $productoModel = new Productos_model();
    $clienteModel = new Clientes_model(); // Suponiendo que tienes un modelo de usuarios
    
    // Obtener la cabecera de la venta
    $cabecera = $facturaModel->find($id);
    
    // Obtener los detalles de la venta
    $detalles = $detalleModel->where('venta_id', $id)->findAll();
    
    // Obtener detalles del usuario
    $cliente = $clienteModel->find($cabecera['id_cliente']);
    //print_r($cliente);
	//exit;
    // Obtener detalles de los productos
    $productos = [];
    foreach ($detalles as $detalle) {
        $producto = $productoModel->find($detalle['producto_id']);
        $productos[] = $producto;
    }
    
    // Pasar los datos a la vista
    return view('facturacion/factura_compra', [
        'cabecera' => $cabecera,
        'detalles' => $detalles,
        'usuario' => $cliente, // Detalles del usuario
        'productos' => $productos, // Detalles de los productos
    ]);
}


//Genero el pdf a partir de la vista
public function generarPDF($id_venta)
{
    // Cargar el modelo para obtener la información de la venta
    $ventaModel = new \App\Models\Cabecera_model();
    $detalleModel = new \App\Models\VentaDetalle_model();
    $productoModel = new \App\Models\Productos_model();
    $clienteModel = new \App\Models\Clientes_model();

    // Obtener la cabecera de la venta
    $cabecera = $ventaModel->find($id_venta);

    // Obtener los detalles de la venta
    $detalles = $detalleModel->where('venta_id', $id_venta)->findAll();

    // Obtener los productos relacionados
    $productos = [];
    foreach ($detalles as $detalle) {
        $productos[] = $productoModel->find($detalle['producto_id']);
    }

    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);

    // Cargar la vista HTML para generar el contenido del PDF
    $html = view('facturacion/impresion_PDF', [
        'cabecera' => $cabecera,
        'detalles' => $detalles,
        'productos' => $productos,
        'usuario' => $cliente
    ]);

    // Configurar Dompdf
    //$options = new \Dompdf\Options();
    //$options->set('isHtml5ParserEnabled', true);
    //$options->set('isPhpEnabled', true);
    $dompdf = new Dompdf();
	
    // Cargar el HTML y renderizar el PDF
    $dompdf->loadHtml($html);
    //$dompdf->render();

	$height = 150; // Altura en mm
	$width = 78;  // Ancho en mm (típico para tickets térmicos)
	$paper_format = array(0, 0, ($width / 25.4) * 72, ($height / 25.4) * 72);
	$type = "portrait"; // Orientación vertical
	$dompdf->setPaper($paper_format, $type);
	$dompdf->render();
	// Ruta donde se guardará el PDF en la carpeta Descargas del usuario
    $downloadDirectory = getenv('USERPROFILE') . '\\Downloads';  // Directorio Descargas en Windows
    $pdf_path = $downloadDirectory . '\\factura_venta_' . $cabecera['id'] . '.pdf';

    // Guardar el PDF en la carpeta Descargas
    file_put_contents($pdf_path, $dompdf->output());

    // Forzar la descarga del PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="factura_venta_' . $cabecera['id'] . '.pdf"');
    echo $dompdf->output();
	
    // Redirigir al carrito después de la descarga
    return redirect()->to(base_url('catalogo'));
}

}