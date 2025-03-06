<?php 
namespace App\Controllers;
use App\Models\FormModel;
use CodeIgniter\Controller;
use App\Models\Usuarios_model;
use App\Models\Productos_model;
use App\Models\Clientes_model;
use App\Models\Cabecera_model;
use App\Models\VentaDetalle_model;

class cajero_controller extends Controller
{
    public function __construct(){
        helper(['form', 'url']);
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
            return redirect()->to(base_url('CarritoList_vta'));


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
            return redirect()->to(base_url('CarritoList_vta'));
            }
            // Redirige a la página de confirmacion de compra si los calculos de stock estan bien.
            return redirect()->to(base_url('casiListo_vta'));


        } else {
            log_message('error', 'Acción no reconocida: ' . $accion);
        }
    }

     //Muestra los detalles de la venta y confirma(función guarda_compra())
	function muestra_compra_vta()
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
		echo view('carrito/confirmarCompra_vta',$datos);
		echo view('footer/footer');
    }

    //guarda la venta modificada
    public function guarda_compra()
    {    
        $cart = \Config\Services::cart();
        $session = session();
        
        //print_r($id_pedido);
        //exit;
        
        if(!$cart){
        return redirect()->to(base_url('catalogo'));
        }
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
        
        $id_pedido = $this->request->getPost('id_pedido');
        
        $Producto_model = new Productos_model();
    
        // **CONTROL DE STOCK ANTES DE PROCESAR LA COMPRA**
        foreach ($cart->contents() as $item) {
            $producto = $Producto_model->find($item['id']); 
    
            if (!$producto || $producto['stock'] < $item['qty']) {
                session()->setFlashdata('msgEr', "Stock insuficiente para {$item['name']} (Stock disponible: {$producto['stock']}).");
                return redirect()->to('CarritoList_vta');
            }
        }
         
        // Si se encontró un id de pedido, actualizar el pedido existente con los nuevos datos
     if ($id_pedido) {
        // Cargar los modelos necesarios para trabajar con los detalles y la cabecera
       $VentaDetalle_model = new VentaDetalle_model();
        $Producto_model = new Productos_model();
        $Cabecera_model = new Cabecera_model();
        
        // Actualizar la cabecera de la venta con los nuevos datos
        $cabecera_model = new Cabecera_model();
        $cabecera_model->update($id_pedido, [
            'fecha' => $fecha, // Actualizamos la fecha del pedido
            'hora' => $hora, // Actualizamos la hora
            'id_cliente' => $id_cliente, // Actualizamos el id del cliente
            'id_usuario' => $id_usuario, // Actualizamos el id del usuario (vendedor)
            'total_venta' => $total, // Actualizamos el total de la venta
            'tipo_pago' => $tipo_pago, // Actualizamos el tipo de pago
            'total_bonificado' => $total_conDescuento, // Actualizamos el total con descuento (si aplica)
           'tipo_compra' => 'Compra_Normal', // Actualizamos el tipo de compra (Pedido o Compra_Normal)
            'estado' => 'Sin_Facturar', // Mantenemos el estado como "Sin_Facturar" (puede cambiar según el flujo)
            'fecha_pedido' => $fecha_pedido_formateada // Actualizamos la fecha de pedido
       ]);
        
        // Eliminar los detalles de la venta anterior para luego agregar los nuevos detalles del carrito
        $VentaDetalle_model->where('venta_id', $id_pedido)->delete();
    
        // Insertar los nuevos detalles del carrito en la base de datos
        if ($cart) {
            foreach ($cart->contents() as $item) {
                // Guardar cada producto del carrito como un nuevo detalle de la venta
                $VentaDetalle_model->save([
                    'venta_id' => $id_pedido,  // Usamos el id del pedido existente para vincular el detalle
                   'producto_id' => $item['id'], // Producto id desde el carrito
                   'cantidad' => $item['qty'], // Cantidad del producto en el carrito
                    'precio' => $item['price'], // Precio del producto
                    'total' => $item['subtotal'], // Total por ese producto (precio * cantidad)
                ]);
    
                // Actualizar el stock de cada producto después de la venta
                $producto = $Producto_model->find($item['id']); // Obtener el producto desde la base de datos
                if ($producto && isset($producto['stock'])) {
                    // Restar la cantidad vendida del stock del producto
                   $stock_edit = $producto['stock'] - $item['qty'];
                   $Producto_model->update($item['id'], ['stock' => $stock_edit]); // Actualizamos el stock en la base de datos
               }
            }
        }
        $session->remove(['id_cliente_pedido', 'id_pedido', 'fecha_pedido', 'tipo_compra', 'tipo_pago','estado']);
        // Limpiar el carrito después de guardar los datos
        $cart->destroy();
        
        // Redirigir al usuario con un mensaje de éxito según el tipo de compra
            session()->setFlashdata('msg', 'Ventas Actualizado con Éxito!');
            return redirect()->to('compras');
        
      
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
        if ($tipo_compra == 'Compra_Normal') {
            session()->setFlashdata('msg', 'Pedido Actualizado con Éxito!');
            return redirect()->to('compras');
        }
    
        session()->setFlashdata('msg', 'Venta Modificado con Éxito!');
        // Redirige a la vista de la factura
        return redirect()->to('Carrito_controller/generarTicket/' . $id_cabecera);
    }
    //cargar la venta al carrito para modificar la venta que ya se realizo
    public function cargar_venta_en_carrito($id_pedido)
{
    $session = session();
    $cart = \Config\Services::cart();
    $detalle_model = new VentaDetalle_model();
    $cabecera_model = new Cabecera_model(); 
    $producto_model = new Productos_model();

    // ✅ Verificar si ya hay una venta en estado "Modificando"
    $ventaEnModificacion = session('estado') === 'Modificando';

    if ($ventaEnModificacion) {
        session()->setFlashdata('msg', 'Primero debe terminar de modificar la Venta anterior.');
        return redirect()->to('CarritoList_vta'); // Bloquea la modificación
    }

    // Obtener los datos de la cabecera de la venta
    $cabecera = $cabecera_model->find($id_pedido);
    if ($cabecera['estado'] == 'Sin_Facturar') {
        $id_cliente = $cabecera['id_cliente'];
        $id_pedido = $cabecera['id'];
        $fecha_pedido = $cabecera['fecha_pedido'];
        $tipo_compra = $cabecera['tipo_compra'];
        $tipo_pago = $cabecera['tipo_pago'];
        $estado = $cabecera['estado'];

        // Guardar en sesión
        $session->set([
            'id_pedido' => $id_pedido,
            'id_cliente_pedido' => $id_cliente,
            'fecha_pedido' => $fecha_pedido,
            'tipo_compra' => $tipo_compra,
            'tipo_pago' => $tipo_pago,
            'estado' => 'Modificando' // Guardamos en la sesión
        ]);

        // Obtener los productos
        $detalles = $detalle_model->where('venta_id', $id_pedido)->findAll();
        
        if (!$detalles) {
            session()->setFlashdata('error', 'No se encontraron productos en el pedido.');
            return redirect()->to('compras');
        } 

        // Restaurar stock
        foreach ($detalles as $detalle) {
            $producto = $producto_model->find($detalle['producto_id']);
            if ($producto) {
                $nuevo_stock = $producto['stock'] + $detalle['cantidad'];
                $producto_model->update($detalle['producto_id'], ['stock' => $nuevo_stock]);
            }
        }

        // ✅ Actualizar el estado del pedido a "Modificando"
        $cabecera_model->update($id_pedido, ['estado' => 'Modificando']);

        // Insertar productos en el carrito
        foreach ($detalles as $detalle) {
            $producto = $producto_model->find($detalle['producto_id']);
            if ($producto) {
                $cart->insert([
                    'id'    => $producto['id'],
                    'qty'   => $detalle['cantidad'],
                    'price' => $detalle['precio'],
                    'name'  => $producto['nombre'],
                    'options' => array(
                        'stock' => $producto['stock'],                   
                    )
                ]);
            }
        }

        // Redirigir a la vista de edición
        return redirect()->to('CarritoList_vta');
    }

    session()->setFlashdata('msg', 'Este pedido ya está siendo Modificado por otro usuario!');
    return redirect()->to('compras');
}
 //Cancelamos la edicion de la Venta.
 public function cancelar_edicion_vta($id_pedido){
    //print_r($id_pedido);
    //exit;
    $cart = \Config\Services::cart();
    $Cabecera_model = new Cabecera_model();
    $VentaDetalle_model = new VentaDetalle_model();
    $Producto_model = new Productos_model();

    // Obtener detalles de los productos de la venta anterior
    $detalles_venta_anterior = $VentaDetalle_model->where('venta_id', $id_pedido)->findAll();
    
    foreach ($detalles_venta_anterior as $detalle) {
        // Restaurar el stock de los productos
        $producto = $Producto_model->find($detalle['producto_id']);
        if ($producto) {
            $stock_edit = $producto['stock'] - $detalle['cantidad'];
            $Producto_model->update($detalle['producto_id'], ['stock' => $stock_edit]);
        }
    }        
    // Después de guardar el pedido (cuando ya no se necesiten los datos de la sesión)
    $session = session();
    $session->remove(['id_cliente_pedido', 'id_pedido', 'fecha_pedido', 'tipo_compra', 'tipo_pago','estado']);
    // Actualizar el estado del pedido a "Pendiente"
    $Cabecera_model->update($id_pedido, ['estado' => 'Sin_Facturar']);
    $cart->destroy();
    return redirect()->to(base_url('compras'));
}
}