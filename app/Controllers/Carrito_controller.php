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
    
    //Identifico si es una compra para facturar si este cambio viene con el dato "Factura"
    //$facturacion = $this->request->getPost('tipo_proceso');
    //print_r($facturacion);
    //exit;

    //id del vendedor
    $id_usuario = $session->get('id');

    //id del cliente seleccionado o se selecciona Consumidor final por defecto.
    $id_cliente = $this->request->getPost('cliente_id');
    if ($id_cliente == "Anonimo") {
        $id_cliente = 1; // Valor por defecto si no se envía cliente_id
    }


    //Tipo de pago enciado del formulario (Transferencia o Efectivo)
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
            'estado' => 'Realizada'
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
    $ancho_papel = 40; // Ajusta el ancho del ticket según la impresora
    $espacios_izq = ($ancho_papel - strlen($titulo)) / 2;

    // Construir el ticket con el título centrado
    $ticket = "";
    $ticket .= str_repeat(" ", max(0, $espacios_izq)) . $titulo . "\n\n";

    $ticket .= "Calle Belgrano 2077, casi Brasil\n";
    $ticket .= "Cel: 3794-095020\n";
    $ticket .= "Instagram: @Blass.Multirubro\n";
    $ticket .= "Facebook: Blass Multirubro\n\n";

    // Información de la venta
    $ticket .= "Numero de Ticket: " . $cabecera['id'] . "\n";
    $ticket .= "Cliente: " . $cliente['nombre'] . "\n";
    $ticket .= "Fecha: " . $cabecera['fecha'] . " Hora: " . $cabecera['hora'] . "\n\n";

    // Detalle de la compra
    $ticket .= "Detalle de Compra:\n";
    foreach ($detalles as $detalle) {
        $producto = $productos[$detalle['producto_id']];
        $ticket .= $producto['nombre'] . " x " . $detalle['cantidad'] . " - $" . $detalle['precio'] . "\n";
    }

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

    return redirect()->to(base_url('catalogo'));
}

private function imprimirTicket($content)
{
    // Configuración de la impresora térmica
    $printerName = "\\\\LAPTOP-U96GGQHJ\\EPSONTMT20"; // Reemplaza con el nombre de tu impresora térmica

    // Abrir la impresora
    $printer = fopen($printerName, 'w');

    if ($printer) {
        // Escribir el contenido en la impresora
        fwrite($printer, $content);

        // Avanzar el papel varias líneas antes de cortar
        fwrite($printer, "\n\n\n\n"); // Avanza 4 líneas

        // Enviar el comando de corte de papel
        fwrite($printer, "\x1D\x56\x00"); // Comando de corte de papel

        // Cerrar la impresora
        fclose($printer);
    } else {
        // Manejar el error si no se puede abrir la impresora
        die("No se pudo abrir la impresora.");
    }
}

public function verificarTA() {
    $session = session();
    // Ruta del archivo TA.xml
    $taPath = ROOTPATH . 'writable/facturacionARCA/TA.xml';

    // Zona horaria de Argentina
    $zonaHorariaArgentina = new \DateTimeZone('America/Argentina/Buenos_Aires');

   // Verificar si el archivo TA.xml existe
   if (!file_exists($taPath)) {
    //echo "No pasa nada kapo";
    //exit;    
    session()->setFlashdata('msgEr', 'Problemas con el TA, comunicarse con el admin!');
    return redirect()->to(base_url('catalogo'));
    }
    // Cargar el XML    
    $xml = simplexml_load_file($taPath);
    if (!$xml) {
        session()->setFlashdata('msgER', 'Problemas con el TA, comunicarse con el admin!');
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
        $this->facturar($TA);
    } else {
        // El ticket ha expirado, eliminar el archivo y generar uno nuevo
        //unlink($taPath);
        rename($taPath, $taPath . ".bak");
        //echo "El ticket ha expirado y se eliminó TA.xml. Generando uno nuevo...<br>";
        $this->generarTA();

        // Verificar si se generó correctamente antes de continuar
        if (!file_exists($taPath)) {
            session()->setFlashdata('msgER', 'Problemas con el TA, comunicarse con el admin!');
            return redirect()->to(base_url('casiListo'));
        }
    }
}

//Genera un nuevo TA.xml si es necesario.
public function generarTA() {
    // Ruta al script wsaa-client.php
    $path = APPPATH . 'Libraries/afip/wsaa-client.php';

    // Ejecutar el script PHP mediante shell_exec()
    $output = shell_exec("php " . escapeshellarg($path) . " wsfe");
    //print_r($output);
    //exit;

    $this->verificarTA();
}

//Aqui va el xml de factura para enviar a ARCA
public function facturar($TA) {
    echo "Token para crear la factura xml para ARCA.\n";
    //print_r($TA['token']);
    $token = $TA['token'];
    print_r($token);
    echo "\nSign para crear la factura xml para ARCA.\n";
    //print_r($TA['sign']);
    $sign = $TA['sign'];
    print_r($sign);

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx',
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
            <ar:PtoVta>1</ar:PtoVta>
            <ar:CbteTipo>11</ar:CbteTipo> <!-- FACTURA C -->
        </ar:FeCabReq>
        <ar:FeDetReq>
            <ar:FECAEDetRequest>
                <ar:Concepto>1</ar:Concepto> <!-- Productos -->
                <ar:DocTipo>99</ar:DocTipo> <!-- 80 CUIT, 99 C_Final-->
                <ar:DocNro>0</ar:DocNro> <!-- 0 para C_final-->
                <ar:CbteDesde>9</ar:CbteDesde> <!-- Nuevo comprobante: debe ser mayor al anterior -->
                <ar:CbteHasta>9</ar:CbteHasta> <!-- Debe ser igual al número de <CbteDesde> -->
                <ar:CbteFch>20250210</ar:CbteFch> <!-- Fecha dentro del rango N-5 a N+5 -->
                <ar:ImpTotal>150.0</ar:ImpTotal> <!-- Suma de ImpNeto + ImpTrib -->
                <ar:ImpTotConc>0</ar:ImpTotConc>
                <ar:ImpNeto>150</ar:ImpNeto>
                <ar:ImpOpEx>0</ar:ImpOpEx>
                <ar:FchServDesde></ar:FchServDesde>
                <ar:FchServHasta></ar:FchServHasta>
                <ar:FchVtoPago></ar:FchVtoPago>
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
        'Cookie: f5avraaaaaaaaaaaaaaaa_session_=DOGCDGKIDKOJLKNJBJIMIMJADLNDFJNFBJOOLLPLBOKCOOBAEMBIKIIKPOOMABAILBHDMMAPGHOGFONOFLPAJBJMGJKFNHCCPJHLEHGFFAHDPJFGKKNFDGACFPOGAOHP; TS010b76f1=01439f1ddf5dc5e1806b91ede532ed9a15e0cd86a7982bb7b04e6db483767d60862c168a2c6638853d4693b02dbc2fb4e2e36deb11f589fb959a6bf821b4c5affd2086fb9a'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    print_r($response);
    

}


}