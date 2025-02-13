<?php
namespace App\Controllers;

use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\Cabecera_model;
Use App\Models\VentaDetalle_model;
Use App\Models\Clientes_model;
use App\Models\Usuarios_model;
use App\Models\Cae_model;


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
    $USmodel = new Usuarios_model();
    $cabeceraModel = new Cabecera_model();
    
    // Llamar al método del modelo para obtener las ventas con clientes
    $datos['ventas'] = $cabeceraModel->getVentasConClientes();
    $datos2['usuarios'] = $USmodel->getUsBaja('NO');
    // Pasar el título y los datos a las vistas
    $data['titulo'] = 'Listado de Compras';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('comprasXcliente/ListaVentas_view', $datos + $datos2);
    echo view('footer/footer');
}

//Filtrado de ventas por fechas y vendedor.
public function filtrarVentas()
{
    $session = session();

    // Verifica si el usuario está logueado
    if (!$session->get('id')) { 
        return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
    }

    // Cargar modelos
    $cabeceraModel = new Cabecera_model();
    $usuariosModel = new Usuarios_model();

    // Obtener y limpiar filtros
    $filtros = [
        'fecha_hoy' => '',
        'fecha_desde' => trim($this->request->getVar('fecha_desde') ?? ''),
        'fecha_hasta' => trim($this->request->getVar('fecha_hasta') ?? ''),
        'estado' => trim($this->request->getVar('estado') ?? ''),
    ];

    // Obtener datos
    $datos['ventas'] = $cabeceraModel->getVentasConClientes($filtros);
    $datos['usuarios'] = $usuariosModel->getUsBaja('NO');

    // Pasar filtros a la vista para mantener los valores seleccionados
    $datos['filtros'] = $filtros;

    // Definir título
    $data['titulo'] = 'Listado de Pedidos Filtrados';

    // Cargar vistas
    return view('navbar/navbar')
        . view('header/header', $data)
        . view('comprasXcliente/ListaVentas_view', $datos)
        . view('footer/footer');
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
    $data['titulo'] = 'Detalle de Compras';
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
    $producto_id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio_vta'];
    $stock = $_POST['stock'];

    // Obtener todos los productos en el carrito
    $cart_items = $cart->contents();
    $producto_encontrado = false;

    foreach ($cart_items as $item) {
        if ($item['id'] == $producto_id) {
            // Si el producto ya está en el carrito, incrementar cantidad
            $nueva_cantidad = $item['qty'] + 1;

            // Verificar si supera el stock disponible
            if ($nueva_cantidad > $stock) {
                session()->setFlashdata('msgEr', 'No puedes agregar más productos de los disponibles en stock.');
                return redirect()->to(base_url('catalogo'));
            }

            // Actualizar cantidad en el carrito
            $cart->update([
                'rowid' => $item['rowid'],
                'qty'   => $nueva_cantidad
            ]);

            $producto_encontrado = true;
            break;
        }
    }

    // Si el producto no está en el carrito, agregarlo
    if (!$producto_encontrado) {
        $cart->insert([
            'id'      => $producto_id,
            'qty'     => 1,
            'price'   => $precio,
            'name'    => $nombre,
            'options' => ['stock' => $stock] // Almacenar stock disponible
        ]);
    }

    session()->setFlashdata('msg', 'Producto Agregado!');
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
            'options' => array('stock' => $_POST['stock'])
            
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
            'options' => array('stock' => $_POST['stock'])
            
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


//GUARDA LA COMPRA
    public function guarda_compra()
{    
    $cart = \Config\Services::cart();
    $session = session();
    

    //id del vendedor
    $id_usuario = $session->get('id');

    //id del cliente seleccionado o se selecciona Consumidor final por defecto.
    $id_cliente = $this->request->getPost('cliente_id');
    if ($id_cliente == "Anonimo") {
        $id_cliente = 1; // Valor por defecto si no se envía cliente_id
    }


    //Tipo de pago enviado del formulario (Transferencia o Efectivo)
    $tipo_pago = $this->request->getPost('tipo_pago');
    //Total de la venta
    $total = $this->request->getPost('total_venta');
    //Total menos el descuento si se pago en efectivo.
    $total_conDescuento = $this->request->getPost('total_con_descuento');
    //Si no trajo el descuento y esa variable quedo vacia se asigna el mismo valor de la venta total.
    if (!$total_conDescuento) {
        $total_conDescuento = $total;
    }
    
    // Establecer zona horaria y obtener fecha/hora en formato correcto
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $hora = date('H:i:s'); // Formato TIME
    $fecha = date('d-m-Y'); // Formato DATE
    //Rescato el tipo de compra (Pedido o Compra_Normal)
    $tipo_compra = $this->request->getVar('tipo_compra');
    //$tipo_compra = $this->request->getPost('tipo_compra_input');
    
    //Si no se selecciono una fecha se asigna la fecha de hoy por defecto para el pedido.
    $fecha_pedido = $this->request->getPost('fecha_pedido_input');
    if (!$fecha_pedido){
        $fecha_pedido = date('d-m-Y');
    }
    //print_r($tipo_compra);
    //exit;
    //Formateamos la fecha del pedido al formato dia-mes-año
    $fecha_pedido_formateada = date('d-m-Y', strtotime($fecha_pedido));
    
    // Obtener el id_venta del carrito si es un pedido modificado
    $id_venta_anterior = null;
    //recorremos el carrito porque si fue un pedido modificado guarde el id en una variable del carrito
    foreach ($cart->contents() as $item) {
        if (isset($item['options']['id_venta'])) {
            $id_venta_anterior = $item['options']['id_venta'];
            break; // recorre solo el 1ero, No es necesario seguir recorriendo si ya encontramos el id_venta
        }
    }
    

    // Si se encontro un id, eliminar el pedido anterior porque se va crear uno nuevo modificado y restaura el stock.
    if ($id_venta_anterior) {
        
        $VentaDetalle_model = new VentaDetalle_model();
        $Producto_model = new Productos_model();

        // Obtener detalles de los productos de la venta anterior
        $detalles_venta_anterior = $VentaDetalle_model->where('venta_id', $id_venta_anterior)->findAll();
        
        foreach ($detalles_venta_anterior as $detalle) {
            // Restaurar el stock de los productos
            $producto = $Producto_model->find($detalle['producto_id']);
            if ($producto) {
                $stock_edit = $producto['stock'] + $detalle['cantidad'];
                $Producto_model->update($detalle['producto_id'], ['stock' => $stock_edit]);
            }
        }

        // Eliminar los detalles y la cabecera de la venta anterior
        $VentaDetalle_model->where('venta_id', $id_venta_anterior)->delete();
        $Cabecera_model = new Cabecera_model();
        $Cabecera_model->delete($id_venta_anterior);
    }
    

    //Identifico si es una compra para facturar si este campo viene con el dato "Factura"
    $facturacion = $this->request->getPost('tipo_proceso');
    //Si el tipo de proceso es para facturar se manda a otra funcion.
    if($facturacion == "factura"){
        //print_r($facturacion);
        //exit;
        // Guardar cabecera de la venta para Facturar, mientras el estado esta para Verificar.
        $cabecera_model = new Cabecera_model();
        $ventas_id = $cabecera_model->save([
            'fecha'        => $fecha,
            'hora'         => $hora,
            'id_cliente'   => $id_cliente,
            'id_usuario'   => $id_usuario,
            'total_venta'  => $total,
            'tipo_pago'    => $tipo_pago,
            'total_bonificado' => $total_conDescuento,
            'tipo_compra' => $tipo_compra,
            'estado' => 'Sin_Facturar'
        ]);

        // Obtener ID de la nueva cabecera guardada
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

        // Limpiar el carrito
        $cart->destroy();
        //Una vez guardada la compra manda a verificar la factura en ARCA.
        return redirect()->to('Carrito_controller/verificarTA/' . $id_cabecera);
    }


    // Guardar la nueva cabecera del Pedido (Nuevo o Modidicado segun sea) utiliza el mismo carrito.
    if ($tipo_compra == 'Pedido') { 
        // Guardar cabecera de la venta tipo pedido
        $cabecera_model = new Cabecera_model();
        $ventas_id = $cabecera_model->save([
            'fecha'        => $fecha,
            'hora'         => $hora,
            'id_cliente'   => $id_cliente,
            'id_usuario'   => $id_usuario,
            'total_venta'  => $total,
            'tipo_pago'    => $tipo_pago,
            'total_bonificado' => $total_conDescuento,
            'tipo_compra' => $tipo_compra,
            'fecha_pedido' => $fecha_pedido_formateada,
            'estado' => 'Pendiente'
        ]);
        
    } else {
        
        // Guardar cabecera de la venta tipo compra normal
        $cabecera_model = new Cabecera_model();
        $ventas_id = $cabecera_model->save([
            'fecha'        => $fecha,
            'hora'         => $hora,
            'id_cliente'   => $id_cliente,
            'id_usuario'   => $id_usuario,
            'total_venta'  => $total,
            'tipo_pago'    => $tipo_pago,
            'total_bonificado' => $total_conDescuento,
            'tipo_compra' => $tipo_compra,
            'estado' => 'Sin_Facturar'
        ]);
    }

    // Obtener ID de la nueva cabecera guardada
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
    if ($tipo_compra == 'Pedido') {
        session()->setFlashdata('msg', 'Pedido Guardado con Éxito!');
        return redirect()->to('catalogo');
    }

    session()->setFlashdata('msg', 'Compra Guardada con Éxito!');
    // Redirige a la vista de la factura
    return redirect()->to('Carrito_controller/generarTicket/' . $id_cabecera);
}


//Genera ticket venta normal
public function generarTicket($id_venta)
{
    // Cargar los modelos necesarios
    $ventaModel = new \App\Models\Cabecera_model();
    $detalleModel = new \App\Models\VentaDetalle_model();
    $productoModel = new \App\Models\Productos_model();
    $clienteModel = new \App\Models\Clientes_model();

    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_venta);
    $detalles = $detalleModel->where('venta_id', $id_venta)->findAll();

    // Obtener los productos relacionados
    $productos = [];
    foreach ($detalles as $detalle) {
        $productos[$detalle['producto_id']] = $productoModel->find($detalle['producto_id']);
    }

    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);

    // Obtener el nombre del vendedor desde la sesión
    $session = session();
    $nombreVendedor = $session->get('nombre');

    // Generar el contenido del ticket en texto
    $ticket = "";

    // Cabecera del ticket
    // Definir ancho del papel en caracteres
    $titulo = "MULTIRUBRO BLASS";
    $ancho_papel = 25; // Ajusta el ancho del ticket según la impresora
    $espacios_izq = intval(($ancho_papel - strlen($titulo)) / 2);

    // Agregar la advertencia "REMITO no válido como factura"
    $remito = "REMITO";
    $remito2 = "No válido como factura.";

    $espacios_remito = intval(($ancho_papel - strlen($remito)) / 2);
    $espacios_remito2 = intval(($ancho_papel - strlen($remito2)) / 2);

    $ticket .= str_repeat(" ", max(0, $espacios_remito)) . $remito . "\n";
    $ticket .= str_repeat(" ", max(0, $espacios_remito2)) . $remito2 . "\n\n";

    // **Activar negrita y ancho doble sin altura doble**
    $ticket .= "\x1B\x45\x01"; // Activar negrita
    $ticket .= "\x1D\x21\x10"; // Doble ancho, altura normal
    $ticket .= str_repeat(" ", max(0, $espacios_izq)) . $titulo . "\n";
    $ticket .= "\x1D\x21\x00"; // Restaurar tamaño normal
    $ticket .= "\x1B\x45\x00\n\n"; // Desactivar negrita

    $ticket .= "Calle Belgrano 2077, casi Brasil\n";
    $ticket .= "Cel: 3794-095020\n";
    $ticket .= "Instagram: @Blass.Multirubro\n";
    $ticket .= "Facebook: Blass Multirubro\n\n";


    // Información de la venta
    $ticket .= "Numero de Ticket: " . $cabecera['id'] . "\n";
    //Si tiene un cuil lo muestra si no solo muestra el nombre del cliente.
    if($cliente['cuil'] > 0){
        $ticket .= "Cliente: " . $cliente['nombre'] . " Cuil: " . $cliente['cuil'] ."\n";
    }else{
        $ticket .= "Cliente: " . $cliente['nombre'] ."\n";
    }
    $ticket .= "Vendedor: " . $nombreVendedor . "\n";
    $ticket .= "Fecha: " . $cabecera['fecha'] . " Hora: " . $cabecera['hora'] . "\n";

    $ticket .= "=====================================\n";
    // Detalle de la compra
    $ticket .= "Detalle de Compra:\n";
    foreach ($detalles as $detalle) {
        $producto = $productos[$detalle['producto_id']];
        $ticket .= $producto['nombre'] . " x " . $detalle['cantidad'] . " - $" . $detalle['precio'] . "\n";
    }
    $ticket .= "=====================================\n";
    // Total a pagar
    if ($cabecera['tipo_pago'] == 'Efectivo') {
        $ticket .= "\nDescuento Efectivo: $" . ($cabecera['total_venta'] * 0.05) . "\n";
    }
    $ticket .= "\nTotal a Pagar: $" . $cabecera['total_bonificado'] . "\n";
    

    // Footer con notas
    $ticket .= "\nImportante:.\n";
    $ticket .= "\nLa mercadería viaja por cuenta y riesgo del comprador.\n";
    $ticket .= "Es responsabilidad del cliente controlar su compra antes de salir del local.\n";
    $ticket .= "Su compra tiene 48hs para cambio ante fallas previas del producto.\n";
    $ticket .= "\nMuchas Gracias por su Compra!.\n";

    // Imprimir el ticket
    $this->imprimirTicket($ticket);

    if($cabecera['tipo_compra'] == 'Pedido'){

        $ventaModel->cambiarEstado($id_venta, 'Sin_Facturar');
    }

    session()->setFlashdata('msg', 'Gracias por su compra.!');
    return redirect()->to(base_url('catalogo'));
}

private function imprimirTicket($content)
{
    $printerName = "\\\\LAPTOP-U96GGQHJ\\EPSONTMT20"; // Nombre de la impresora

    $printer = fopen($printerName, 'w');

    if ($printer) {
        // Enviar el contenido línea por línea con una pequeña pausa
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            fwrite($printer, $line . "\n");
            usleep(50000); // 50ms de pausa entre líneas
        }

        // Avanzar el papel varias líneas antes de cortar
        fwrite($printer, "\n\n\n\n");
        usleep(100000); // 100ms de pausa antes del corte

        // Comando de corte de papel
        fwrite($printer, "\x1D\x56\x00");

        fclose($printer);
    } else {
        die("No se pudo abrir la impresora.");
    }
}



//Verifica que todo este bien para Facturar
public function verificarTA($id_cabecera = null) {
    $ventaModel = new \App\Models\Cabecera_model();
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    //print_r($cabecera);
    //exit;
    if ($cabecera['estado'] == 'Facturado' || $cabecera['id_cae'] > 0) {
        session()->setFlashdata('msgEr', 'No se puede facturar una misma venta dos veces, solo puede volver a imprimir la factura.');
        return redirect()->to(base_url('catalogo'));
    }
    //$id_cabecera = 252;
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        } 
    if ($id_cabecera === null) {
        //session()->setFlashdata('msgEr', 'No se puede facturar sin enviar una Venta.');
        return redirect()->to(base_url('catalogo'));
    }
    //$id_cabecera = 24;
    // Ruta del archivo TA.xml
    $taPath = ROOTPATH . 'writable/facturacionARCA/TA.xml';

    // Zona horaria de Argentina
    $zonaHorariaArgentina = new \DateTimeZone('America/Argentina/Buenos_Aires');

   // Verificar si el archivo TA.xml existe
   if (!file_exists($taPath)) {
    $ventaModel->update($id_cabecera,['estado' => 'Error_factura']);
    session()->setFlashdata('msgEr', 'Problemas con el servidor ARCA, se guardo la compra sin Facturar, intente mas tarde');
    return redirect()->to(base_url('catalogo'));
    } 
    // Cargar el XML    
    $xml = simplexml_load_file($taPath);
    if (!$xml) {
        $ventaModel->update($id_cabecera,['estado' => 'Error_factura']);
        session()->setFlashdata('msgER', 'Problemas con el servidor ARCA, se guardo la compra sin Facturar, intente mas tarde');
        return redirect()->to($this->request->getHeader('referer')->getValue());
    }
    

    // Obtener la fecha de expiración del XML
    $expirationTime = (string)$xml->header->expirationTime;
    $expirationDateTime = new \DateTime($expirationTime, new \DateTimeZone('UTC')); // AFIP usa UTC
    $expirationDateTime->setTimezone($zonaHorariaArgentina); // Convertir a Argentina

    // Obtener la fecha y hora actuales en la misma zona horaria
    $currentDateTime = new \DateTime('now', $zonaHorariaArgentina);

    // Comparar fechas
    if ($expirationDateTime > $currentDateTime) {
        // El ticket sigue siendo válido, continuar con la facturación
        $TA = [
            'token' => (string)$xml->credentials->token,
            'sign'  => (string)$xml->credentials->sign            
        ];
        //print_r($TA);
        //exit;
        //Manda a facturar con el TA y el id de cabecera, y redireccion con msg si es venta o pedido facturado con exito.
        $this->facturar($TA,$id_cabecera);
        session()->setFlashdata('msg', 'Se realizo la Factura con Exito.!');
        return redirect()->to(base_url('catalogo'));
    } else {
        // El ticket ha expirado, eliminar el archivo y generar uno nuevo
        //unlink($taPath);
        rename($taPath, $taPath . ".bak");
        //echo "El ticket ha expirado y se eliminó TA.xml. Generando uno nuevo...<br>";
        return redirect()->to('Carrito_controller/generarTA/'. $id_cabecera);
        //$this->generarTA($id_cabecera);

        // Verificar si se generó correctamente antes de continuar
        if (!file_exists($taPath)) {

            session()->setFlashdata('msgER', 'Problemas con el Servidor ARCA, intente mas tarde.!');
            return redirect()->to(base_url('casiListo'));
        }
    }
}

//Genera un nuevo TA.xml si es necesario.
public function generarTA($id_cabecera = null) {
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        } 
    if ($id_cabecera === null) {
        //session()->setFlashdata('msgEr', 'No se puede facturar sin enviar una Venta.');
        return redirect()->to(base_url('catalogo'));
    }
    // Ruta al script wsaa-client.php
    $path = APPPATH . 'Libraries/afip/wsaa-client.php';

    // Ejecutar el script PHP mediante shell_exec()
    $output = shell_exec("php " . escapeshellarg($path) . " wsfe");
    print_r($output);
    exit;

    return redirect()->to('Carrito_controller/verificarTA/'. $id_cabecera);
}

//Aqui va el xml de factura para enviar a ARCA
//re copiar abajo $TA,$id_cabecera
public function facturar($TA = null,$id_cabecera = null) {
    $ventaModel = new \App\Models\Cabecera_model();
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    //print_r($cabecera);
    //exit;
    if ($cabecera['estado'] == 'Facturado' || $cabecera['id_cae'] > 0 ) {
        session()->setFlashdata('msgEr', 'No se puede facturar una misma Venta dos Veces, Solo puede volver a imprimir la factura.');
        return redirect()->to(base_url('catalogo'));
    }
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        } 
    if ($id_cabecera === null) {
        //session()->setFlashdata('msgEr', 'No se puede facturar sin enviar una Venta.');
        return redirect()->to(base_url('catalogo'));
    }

    // Cargar los modelos necesarios 
    $clienteModel = new \App\Models\Clientes_model();
    //Obtengo el ultimo id del cae
    $caeModel = new \App\Models\Cae_model();    
    $ultimoRegistro = $caeModel->orderBy('id_cae', 'DESC')->first(); // Trae el último registro de la tabla cae
    $ultimo_id_cae = $ultimoRegistro ? $ultimoRegistro['id_cae'] : 0;
    //echo "Último ID registrado: " . $ultimo_id_cae;
    //exit;
    //sumamos uno al ultimo id_cae para que ARCA lo acepte porque tiene que ser de 1 en 1.
    $id_cae_siguiente = $ultimo_id_cae + 1;
    //print_r($id_cae_siguiente);
    //exit;
    // Obtener los detalles de la venta
    
    //print_r($cabecera);
    //exit;
    //Obtengo el total de la venta, con descuento o sin
    $total_venta = $cabecera['total_bonificado'];
    //Obtengo la fecha
    $fecha_venta = $cabecera['fecha'];
    $fecha_formateadaF = date('Ymd', strtotime($fecha_venta)); // Ajusta y suma 2 dias porque es el rango permitido por AFIP.
    //print_r($fecha_formateadaF);    
    //exit;
    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);
    //Obtener el cuil del cliente
    $cuil_cliente = $cliente['cuil'];
    //print_r($cuil_cliente);
    //Obtener el tipo de Documento.
    $tipoDoc = 80; //Si tiene un cuil real
    if($cuil_cliente == 0){
        $tipoDoc = 99; //Si no tiene Cuil
    }
    //print_r($tipoDoc);
    //exit;

    $new_cae = null;
    //echo "Token para crear la factura xml para ARCA.\n";
    //print_r($TA['token']);
    $token = $TA['token'];
    //print_r($token);
    //echo "\nSign para crear la factura xml para ARCA.\n";
    //print_r($TA['sign']);
    $sign = $TA['sign'];
    //print_r($sign);

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
                      xmlns:ar="http://ar.gov.afip.dif.FEV1/">
        <soapenv:Header/>
        <soapenv:Body>
            <ar:FECAESolicitar>
                <ar:Auth>
                    <ar:Token>' . $token . '</ar:Token>
                    <ar:Sign>' . $sign . '</ar:Sign>
                    <ar:Cuit>20369557263</ar:Cuit>
                </ar:Auth>
                <ar:FeCAEReq>
        <ar:FeCabReq>
            <ar:CantReg>1</ar:CantReg>
            <ar:PtoVta>2</ar:PtoVta>
            <ar:CbteTipo>11</ar:CbteTipo> <!-- 11 para FACTURA C -->
        </ar:FeCabReq>
        <ar:FeDetReq>
            <ar:FECAEDetRequest>
                <ar:Concepto>1</ar:Concepto> <!-- Productos -->
                <ar:DocTipo>' . $tipoDoc . '</ar:DocTipo> <!-- 80 CUIT, 99 C_Final-->
                <ar:DocNro>' . $cuil_cliente . '</ar:DocNro> <!-- 0 para C_final-->
                <ar:CbteDesde>' . $id_cae_siguiente . '</ar:CbteDesde> <!-- Nuevo comprobante: debe ser mayor al anterior -->
                <ar:CbteHasta>' . $id_cae_siguiente . '</ar:CbteHasta> <!-- Debe ser igual al número de <CbteDesde> -->
                <ar:CbteFch>' . $fecha_formateadaF . '</ar:CbteFch> <!-- Fecha dentro del rango N-5 a N+5 -->
                <ar:ImpTotal>' . $total_venta . '</ar:ImpTotal> <!-- Suma de ImpNeto + ImpTrib -->
                <ar:ImpTotConc>0</ar:ImpTotConc>
                <ar:ImpNeto>' . $total_venta . '</ar:ImpNeto>
                <ar:MonId>PES</ar:MonId>
                <ar:MonCotiz>1</ar:MonCotiz>
                <ar:CondicionIVAReceptorId>5</ar:CondicionIVAReceptorId> 
                
            </ar:FECAEDetRequest>
        </ar:FeDetReq>
    </ar:FeCAEReq>
    </ar:FECAESolicitar>
    </soapenv:Body>
    </soapenv:Envelope>
    ',
      CURLOPT_HTTPHEADER => array(
        'SOAPAction: http://ar.gov.afip.dif.FEV1/FECAESolicitar',
        'Content-Type: text/xml; charset=utf-8',        
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    
    
    // **Extraer los datos del XML**
    
        // Cargar el XML y registrar el namespace
        $xml = new \SimpleXMLElement($response);
        $xml->registerXPathNamespace('ns', 'http://ar.gov.afip.dif.FEV1/');

        // Buscar los valores dentro del XML
        $resultado_nodes = $xml->xpath('//ns:FECAEDetResponse/ns:Resultado');
        $cae_nodes = $xml->xpath('//ns:FECAEDetResponse/ns:CAE');
        $cae_vencimiento_nodes = $xml->xpath('//ns:FECAEDetResponse/ns:CAEFchVto');
        $observaciones_nodes = $xml->xpath('//ns:FECAEDetResponse/ns:Observaciones/ns:Obs/ns:Msg');

        // Verificar si los nodos existen antes de acceder a ellos
        $resultado = isset($resultado_nodes[0]) ? (string) $resultado_nodes[0] : 'No encontrado';
        $cae = isset($cae_nodes[0]) ? (string) $cae_nodes[0] : 'No encontrado';
        $cae_vencimiento = isset($cae_vencimiento_nodes[0]) ? (string) $cae_vencimiento_nodes[0] : 'No encontrado';
        // Capturar mensaje de error si la factura fue rechazada
        $mensaje_error = isset($observaciones_nodes[0]) ? (string) $observaciones_nodes[0] : '';
        //Pregunta si fue aprobada la factura guarda si no re direcciona a otra vista.
    if($resultado == 'A'){ 
        $caeModel->save([
            'cae'       => $cae,
            'vto_cae'   => $cae_vencimiento
        ]); // Muestra los errores si la inserción falla
        //Rescato el id del ultimo cae generado y guardado en la DB.
        $new_cae = $caeModel->getInsertID();
        //asignamos el id_cae a la venta y cambiamos el estado a Facturado.
        $ventaModel->facturado($id_cabecera,$new_cae);

    }else{ 
        //print_r($response);
        //exit;
        $ventaModel->update($id_cabecera,['estado' => 'Error_factura']);
        //Si tiene una R en resultado redirecciona por rechazado
        session()->setFlashdata('msgEr', 'No se pudo facturar, Motivo: ' . $mensaje_error . ' La venta se guardó para facturar despues de corregir el error.');
        return redirect()->to(base_url('catalogo'));
    }
        // Mostrar los datos obtenidos
        //echo "Resultado: $resultado\n";
        //echo "CAE: $cae\n";
        //echo "Vencimiento CAE: $cae_vencimiento\n";
        $this->generarTicketFacturaC($id_cabecera);
}


//Genera el ticket factura tipo C
public function generarTicketFacturaC($id_cabecera = null)
{   
    
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        } 
    if ($id_cabecera === null) {
        //session()->setFlashdata('msgEr', 'No se puede facturar sin enviar una Venta.');
        return redirect()->to(base_url('catalogo'));
    }
    // Cargar los modelos necesarios
    $ventaModel = new \App\Models\Cabecera_model();
    $detalleModel = new \App\Models\VentaDetalle_model();
    $productoModel = new \App\Models\Productos_model();
    $clienteModel = new \App\Models\Clientes_model();
    $caeModel = new \App\Models\Cae_model();
    // Obtener los detalles de la venta y el CAE
    $cabecera = $ventaModel->find($id_cabecera);
    //print_r($cabecera);
    //exit;
    $detalle_CAE = $caeModel->find($cabecera['id_cae']);
    //print_r($detalle_CAE);
    //exit;
    $detalles = $detalleModel->where('venta_id', $id_cabecera)->findAll();

    // Obtener los productos relacionados
    $productos = [];
    foreach ($detalles as $detalle) {
        $productos[$detalle['producto_id']] = $productoModel->find($detalle['producto_id']);
    }

    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);

    // Obtener el nombre del vendedor desde la sesión
    $session = session();
    $nombreVendedor = $session->get('nombre');

    // Generar el contenido del ticket en texto
    $ticket = "";
    //$ancho_papel2 = 50; // Ancho del papel para centrar la advertencia
    $ancho_papel = 25;  // Ancho del papel para el título

    // Cabecera del ticket
    $titulo = "MULTIRRUBRO BLASS";
    $espacios_izq = intval(($ancho_papel - strlen($titulo)) / 2);

    // Agregar la advertencia "Factura C" con CAE y Vto.
    //$remito = "Factura C";
    //$remito2 = "CAE: " . $detalle_CAE['cae'] . " Vto Fecha: " . $detalle_CAE['vto_cae'];

    //$espacios_remito = intval(($ancho_papel2 - strlen($remito)) / 2);
    //$espacios_remito2 = intval(($ancho_papel2 - strlen($remito2)) / 2);

    // Agregar la advertencia centrada con "Factura C" en negrita
    //$ticket .= "\x1B\x45\x01"; // Activar negrita
    //$ticket .= str_repeat(" ", max(0, $espacios_remito)) . $remito . "\n";
    //$ticket .= "\x1B\x45\x00"; // Desactivar negrita
    //$ticket .= str_repeat(" ", max(0, $espacios_remito2)) . $remito2 . "\n\n";

    // **Activar negrita y doble ancho sin doble altura para el título**
    $ticket .= "\x1B\x45\x01"; // Activar negrita
    $ticket .= "\x1D\x21\x10"; // Ancho doble, altura normal
    $ticket .= str_repeat(" ", max(0, $espacios_izq)) . $titulo . "\n";
    $ticket .= "\x1D\x21\x00"; // Restaurar tamaño normal
    $ticket .= "\x1B\x45\x00\n\n"; // Desactivar negrita

    // Datos de la empresa
    $ticket .= "GONZALEZ EMMANUEL ALEJANDRO\n";
    $ticket .= "CUIT Nro: 20-36955726-3\n";
    $ticket .= "Domicilio: Belgrano 2077\n"; 
    $ticket .= "Corrientes (3400) - Corrientes\n"; 
    $ticket .= "Cel: 3794-095020\n";
    $ticket .= "Inicio de actividades: 01/02/2023\n";     
    $ticket .= "Ingresos Brutos: 20-36955726-3\n";
    $ticket .= "Resp. Monotributo\n";
    $ticket .= "----------------------------------\n";
    // Información de la venta
    if($cabecera['tipo_compra'] == 'Pedido'){
        // Establecer zona horaria y obtener fecha/hora en formato correcto
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fechaHoy = date('d-m-Y'); // Formato: dd-mm-yyyy
        $horaHoy = date('H:i:s');  // Formato: HH:MM:SS

        $ticket .= "Fecha: " . $fechaHoy . " Hora: " . $horaHoy . "\n";
    }else{
        $ticket .= "Fecha: " . $cabecera['fecha'] . " Hora: " . $cabecera['hora'] . "\n";
    }
    $ticket .= "----------------------------------\n";

    $ticket .= "Factura C (Cod.011) a Consumidor Final\n";
    $ticket .= "Numero de Ticket: " . $cabecera['id'] . "\n";
    //Si tiene un cuil lo muestra si no solo muestra el nombre del cliente.
    if($cliente['cuil'] > 0){
        $ticket .= "Cliente: " . $cliente['nombre'] . " Cuil: " . $cliente['cuil'] ."\n";
    }else{
        $ticket .= "Cliente: " . $cliente['nombre'] ."\n";
    }
    $ticket .= "Vendedor: " . $nombreVendedor . "\n";
    $ticket .= "----------------------------------\n";

    // Detalle de la compra
    $ticket .= "Detalle de Compra:\n";
    foreach ($detalles as $detalle) {
        $producto = $productos[$detalle['producto_id']];
        $ticket .= $producto['nombre'] . "  x  " . $detalle['cantidad'] . "  - $" . $detalle['precio'] . "\n";
    }
    // Total a pagar
    $ticket .= "Subtotal sin descuentos: $" . $cabecera['total_venta'] . "\n";
    if ($cabecera['tipo_pago'] == 'Efectivo') {
        $ticket .= "Descuentos: $" . ($cabecera['total_venta'] * 0.05) . "\n";
    }else{
        $ticket .= "Descuentos: $0.00\n";
    }
    $ticket .= "Total: $" . $cabecera['total_bonificado'] . "\n";
    $ticket .= "----------------------------------\n";


    $ticket .= "Reg. Transparencia fiscal al consumidor\n";
    $ticket .= "IVA CONTENIDO: $0.00\n";
    $ticket .= "Otros Imp. Nac. Indirectos: $0.00\n";        
    $ticket .= "Tipo de Pago: " . $cabecera['tipo_pago'] . "\n";
    $ticket .= "Referencia electronica del comprobante:\n";
    //Transformo la fecha de 20250210 a 10-02-2025
    $fecha_vencimiento = date('d-m-Y', strtotime($detalle_CAE['vto_cae']));
    $ticket .= "CAE: " . $detalle_CAE['cae'] . " - Vto: " . $fecha_vencimiento ."\n";
    // Footer con notas
    $ticket .= "\nImportante:\n";
    $ticket .= "La mercaderia viaja por cuenta y riesgo del comprador.\n";
    $ticket .= "Es responsabilidad del cliente controlar su compra antes de salir del local.\n";
    $ticket .= "Su compra tiene 48hs para cambio ante fallas previas del producto.\n";
    $ticket .= "Instagram: @Blass.Multirrubro\n";
    $ticket .= "Facebook: Blass Multirrubro\n\n";
    $ticket .= "Muchas Gracias por su Compra.!\n";

   
    // Imprimir el ticket
    $this->imprimirTicket($ticket);
    //Mensaje de factura impresa cuando solo quiere volver a imprimir la factura
    session()->setFlashdata('msg', 'Facturar Impresa con Éxito.');
    return redirect()->to(base_url('catalogo'));
}


}