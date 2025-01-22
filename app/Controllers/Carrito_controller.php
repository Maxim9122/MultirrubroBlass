<?php
namespace App\Controllers;
use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\Cabecera_model;
Use App\Models\VentaDetalle_model;
Use App\Models\Clientes_model;
//use Dompdf\Dompdf;

class Carrito_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}

	public function ListVentasCabecera()
{
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
            
         ));
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
		return redirect()->to(base_url('catalogo'));
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

    //Actualiza el carrito que se muestra
	function actualiza_carrito()
    {
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
	}

    //Muestra los detalles de la venta y confirma(función guarda_compra())
	function muestra_compra()
	{
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
	
    // Recuperar datos del formulario usando $this->request->getPost()
    $id_cliente = $this->request->getPost('cliente_id');
	//print_r($id_cliente);
		//exit;
    if ($id_cliente == "Anonimo") {
        $id_cliente = 1; // Valor por defecto si no se envía cliente_id
    }
    $tipo_pago = $this->request->getPost('tipo_pago');
    $total = $this->request->getPost('total_venta');

    // Establecer zona horaria y obtener fecha/hora en formato correcto
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $hora = date('H:i:s'); // Formato TIME
    $fecha = date('d-m-Y'); // Formato DATE

    // Guardar cabecera de la venta
    $cabecera_model = new Cabecera_model();
    $ventas_id = $cabecera_model->save([
        'fecha'        => $fecha,
        'hora'         => $hora,
        'id_cliente'   => $id_cliente,
        'total_venta'  => $total,
        'tipo_pago'    => $tipo_pago,
    ]);

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
    session()->setFlashdata('msg', 'Compra Guardada con Éxito!');
    return redirect()->to(base_url('/catalogo'));
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

		//$html = view('back/Admin/facturacion_view',$datos2+$datos);
		//$dompdf->loadHtml('Hola loco');
		//$dompdf->setPaper('A4', 'landscape');
		//$dompdf->render();
		//$dompdf->stream('demoFactura.pdf',['attachment' => false]);
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

		//$html = view('back/Admin/facturacion_view',$datos2+$datos);
		//$dompdf->loadHtml('Hola loco');
		//$dompdf->setPaper('A4', 'landscape');
		//$dompdf->render();
		//$dompdf->stream('demoFactura.pdf',['attachment' => false]);
	}
}