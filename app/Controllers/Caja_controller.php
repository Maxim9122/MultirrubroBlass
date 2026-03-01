<?php
namespace App\Controllers;

use CodeIgniter\Controller;
Use App\Models\Productos_model;
Use App\Models\Cabecera_model;
Use App\Models\VentaDetalle_model;
Use App\Models\Clientes_model;
use App\Models\Usuarios_model;
use App\Models\Cae_model;

class Caja_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}
//verificacion de codigo de acceso
    public function verificarCodigo()
    {
        $codigoCorrecto = "7791293043746"; // Código estático en el backend
        $codigoIngresado = $this->request->getPost('codigo');

        if ($codigoIngresado === $codigoCorrecto) {
            return $this->response->setJSON(['success' => true]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Código incorrecto. Intente de nuevo.']);
        }
    }

    //Vista para el cajero
    public function Caja(){
        $session = session();
        $perfil=$session->get('perfil_id');
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        if($perfil == 2){
            return redirect()->to(base_url('catalogo'));
       }
    //print_r($session->get());
    //exit;
    // Instanciar el modelo
    $USmodel = new Usuarios_model();
    $cabeceraModel = new Cabecera_model();
    // Obtener y limpiar filtros
    $filtros = [
        'tipo_compra' => 'Compra_Normal',
        'estado' => 'Pendiente'    
    ];
    // Llamar al método del modelo para obtener las ventas con clientes
    $datos['ventas'] = $cabeceraModel->getVentasRegistradas($filtros);
    $datos2['usuarios'] = $USmodel->getUsBaja('NO');
    // Pasar el título y los datos a las vistas
    $data['titulo'] = 'Listado de Compras';
    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('comprasXcliente/ListaVentas_Caja', $datos + $datos2);
    echo view('footer/footer');
    }

    //Cargo la venta a Cobrar
    public function CargarVenta($id_vta)
{
    $cart = \Config\Services::cart();
    $cart->destroy();
    $session = session();
        $perfil=$session->get('perfil_id');
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        }
        if($perfil == 2){
            return redirect()->to(base_url('catalogo'));
        }
   
    $cabecera_model = new Cabecera_model(); 
    $US_model = new Usuarios_model();
    // Obtener los datos de la cabecera de la venta para obtener el id_cliente
    $cabecera = $cabecera_model->find($id_vta);
    if($cabecera['estado'] == 'Pendiente' || $cabecera['estado'] == 'Error_factura'){
    // Actualizar el estado del pedido a "Cobrando"
    $cabecera_model->update($id_vta, ['estado' => 'Cobrando']);

    $id_vendedor = $cabecera ? $cabecera['id_usuario'] : null;
    $vendedor = $US_model->find($id_vendedor);
    $nombre_vendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';
    $id_cliente = $cabecera ? $cabecera['id_cliente'] : null;
    $nombre_cli = $cabecera ? $cabecera['nombre_prov_client'] : null;   
    $id_pedido = $cabecera ? $cabecera['id'] : null;
    $fecha_pedido = $cabecera ? $cabecera['fecha_pedido'] : null;
    $tipo_compra = $cabecera ? $cabecera['tipo_compra'] : null;
    $tipo_pago = $cabecera ? $cabecera['tipo_pago'] : null;
    $total_venta = $cabecera ? $cabecera['total_venta'] : null;    
    //print_r($estado);
    //exit;
    $session->set([
        'id_vendedor' => $id_vendedor,
        'nombre_vendedor' => $nombre_vendedor,
        'id_cliente' => $id_cliente,
        'nombre_cli' => $nombre_cli,
        'id_pedido' => $id_pedido,
        'fecha_pedido' => $fecha_pedido,
        'tipo_compra' => $tipo_compra,
        'tipo_pago' => $tipo_pago,
        'total_venta' => $total_venta,
        'estado' => 'Cobrando'
    ]);
    //print_r($fecha_pedido);
    //exit;    
    // Redirigir a la vista de edición del pedido
    return redirect()->to('casiListo');
    }
    
    session()->setFlashdata('msg', 'Otro cajero esta con esta Venta!');
    return redirect()->to('caja');
    }

    //Cancelar Cobro de la venta
    public function CancelarCobro($id_pedido){
        $session = session();
        $tipo_compra = $session->get('tipo_compra');
        $cart = \Config\Services::cart();
        $cart->destroy();
        $Cabecera_model = new Cabecera_model();
        $Cabecera_model->update($id_pedido, ['estado' => 'Pendiente']);           
        $session->remove(['estado','id_vendedor', 'nombre_vendedor', 'id_cliente', 'nombre_cli' , 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
        if($tipo_compra == 'Compra_Normal'){ 
            session()->setFlashdata('msg', 'Se Cancelo el cobro de la Venta!');
        return redirect()->to('caja');
        } else {
            session()->setFlashdata('msg', 'Se Cancelo el cobro del Pedido!');
        return redirect()->to('pedidos');
        }
    }


    //Funcion para modificar la venta
   public function cargar_Venta_en_Carrito($id_pedido)
{
    $session = session();
    $cart = \Config\Services::cart();

    $US_model       = new Usuarios_model();
    $detalle_model  = new VentaDetalle_model();
    $cabecera_model = new Cabecera_model();
    $producto_model = new Productos_model();
    $TiposPrecio_model = new \App\Models\Tipos_precio_model();

    $cabecera = $cabecera_model->find($id_pedido);

    if ($cabecera['estado'] == 'Pendiente') {

        $id_vendedor = $cabecera ? $cabecera['id_usuario'] : null;
        $vendedor = $US_model->find($id_vendedor);
        $nombre_vendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';

        $id_cliente   = $cabecera['id_cliente'] ?? null;
        $id_pedido    = $cabecera['id'] ?? null;
        $fecha_pedido = $cabecera['fecha_pedido'] ?? null;
        $tipo_compra  = $cabecera['tipo_compra'] ?? null;
        $tipo_pago    = $cabecera['tipo_pago'] ?? null;
        $total_venta  = $cabecera['total_venta'] ?? null;

        $session->set([
            'id_vendedor'     => $id_vendedor,
            'nombre_vendedor' => $nombre_vendedor,
            'id_cliente'      => $id_cliente,
            'id_pedido'       => $id_pedido,
            'fecha_pedido'    => $fecha_pedido,
            'tipo_compra'     => $tipo_compra,
            'tipo_pago'       => $tipo_pago,
            'total_venta'     => $total_venta,
            'estado'          => 'Modificando'
        ]);

        $detalles = $detalle_model
            ->where('venta_id', $id_pedido)
            ->findAll();

        $cart->destroy();

        if (!$detalles) {
            session()->setFlashdata('error', 'No se encontraron productos en el pedido.');
            return redirect()->to($this->request->getHeader('referer')->getValue());
        }

        $cabecera_model->update($id_pedido, ['estado' => 'Modificando']);

        foreach ($detalles as $detalle) {

            $producto = $producto_model->find($detalle['producto_id']);
            if (!$producto) continue;

            $tipoPrecioId = (int)$detalle['tipo_precio'];
            $cantidadPromo = 1;

            // 🔥 Reconstruir promo desde tipos_precio
            if ($tipoPrecioId > 0) {

                $promo = $TiposPrecio_model->find($tipoPrecioId);

                if ($promo && $promo['cantidad'] > 0) {
                    $cantidadPromo = (int)$promo['cantidad'];
                }
            }

            $cart->insert([
                'id'    => $producto['id'],
                'qty'   => $detalle['cantidad'], // lo que eligió el cliente
                'price' => $detalle['precio'],
                'name'  => $producto['nombre'],
                'options' => [
                    'stock'           => $producto['stock'],
                    'cantidadXpromo'  => $cantidadPromo,
                    'tipo_precio_id'  => $tipoPrecioId
                ]
            ]);
        }

        return redirect()->to('CarritoList');
    }

    session()->setFlashdata('msg', 'Esta Venta ya esta siendo Modificada por otro Cajero!');
    return redirect()->to('caja');
}

//Cancelar la modificacion de la Venta
public function cancelar_edicion_Venta($id_pedido){
    //print_r($id_pedido);
    //exit;
    $cart = \Config\Services::cart();
    $Cabecera_model = new Cabecera_model();
      
    // Después de guardar el pedido (cuando ya no se necesiten los datos de la sesión)
    $session = session();
    $session->remove(['estado','id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
    // Actualizar el estado del pedido a "Pendiente"
    $Cabecera_model->update($id_pedido, ['estado' => 'Pendiente']);
    $cart->destroy();
    return redirect()->to(base_url('caja'));
}

//Cancela la Venta
public function Venta_cancelar($id_pedido)
{
    $session = session();
    $perfil = $session->get('perfil_id');

    // Verifica si el usuario está logueado
    if (!$session->has('id')) {
        return redirect()->to(base_url('login'));
    }

    if ($perfil == 2) {
        return redirect()->to(base_url('catalogo'));
    }

    $cabecera_model     = new Cabecera_model();
    $detalle_model      = new VentaDetalle_model();
    $producto_model     = new Productos_model();
    $TiposPrecio_model  = new \App\Models\Tipos_precio_model();

    // Obtener los detalles de la venta
    $detalles = $detalle_model->where('venta_id', $id_pedido)->findAll();

    if (!$detalles) {
        // Si no hay detalles, eliminar cabecera (error de venta duplicada)
        $cabecera_model->delete($id_pedido);

        session()->setFlashdata('msg', 'Se Elimino Error de Venta Duplicada.');
        return redirect()->to($this->request->getHeader('referer')->getValue());
    }

    // 🔥 Restaurar stock correctamente considerando promociones
    foreach ($detalles as $detalle) {

        $producto = $producto_model->find($detalle['producto_id']);
        if (!$producto) continue;

        $tipoPrecioId  = (int)$detalle['tipo_precio'];
        $cantidadPromo = 1;

        // Si tiene promo, buscar cantidad en tipos_precio
        if ($tipoPrecioId > 0) {

            $promo = $TiposPrecio_model->find($tipoPrecioId);

            if ($promo && isset($promo['cantidad']) && $promo['cantidad'] > 0) {
                $cantidadPromo = (int)$promo['cantidad'];
            }
        }

        // 🔥 Calcular unidades reales vendidas
        $unidadesReales = (int)$detalle['cantidad'] * $cantidadPromo;

        // 🔥 Devolver stock real
        $nuevo_stock = $producto['stock'] + $unidadesReales;

        $producto_model->update($detalle['producto_id'], [
            'stock' => $nuevo_stock
        ]);
    }

    // Actualizar estado del pedido
    $cabecera_model->update($id_pedido, [
        'estado' => 'Cancelado'
    ]);

    session()->setFlashdata('msg', 'Venta cancelada y stock restaurado correctamente.');

    return redirect()->to($this->request->getHeader('referer')->getValue());
}


//Modificar Venta Sin_Facturar
public function cargar_Venta_Sin_Facturar($id_pedido)
{
    $session = session();
    $cart = \Config\Services::cart();

    $US_model       = new Usuarios_model();
    $detalle_model  = new VentaDetalle_model();
    $cabecera_model = new Cabecera_model();
    $producto_model = new Productos_model();
    $TiposPrecio_model = new \App\Models\Tipos_precio_model();

    $cabecera = $cabecera_model->find($id_pedido);

    if ($cabecera['estado'] == 'Sin_Facturar' || $cabecera['estado'] == 'Modificada_SF') {

        $id_vendedor = $cabecera['id_usuario'] ?? null;
        $vendedor = $US_model->find($id_vendedor);
        $nombre_vendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';

        $id_cliente        = $cabecera['id_cliente'] ?? null;
        $id_pedido         = $cabecera['id'] ?? null;
        $fecha_pedido      = $cabecera['fecha_pedido'] ?? null;
        $tipo_compra       = $cabecera['tipo_compra'] ?? null;
        $tipo_pago         = $cabecera['tipo_pago'] ?? null;
        $total_venta       = $cabecera['total_venta'] ?? null;
        $total_bonificado  = $cabecera['total_bonificado'] ?? null;
        $total_anterior    = $cabecera['total_anterior'] ?? null;

        $pago_efec     = $cabecera['monto_efectivo'] ?? null;
        $pago_transfer = $cabecera['monto_transferencia'] ?? null;
        $pago_tarjeta  = $cabecera['monto_tarjetaC'] ?? null;

        $session->set([
            'id_vendedor'     => $id_vendedor,
            'nombre_vendedor' => $nombre_vendedor,
            'id_cliente'      => $id_cliente,
            'id_pedido'       => $id_pedido,
            'fecha_pedido'    => $fecha_pedido,
            'tipo_compra'     => $tipo_compra,
            'tipo_pago'       => $tipo_pago,
            'total_venta'     => $total_venta,
            'total_bonificado'=> $total_bonificado,
            'total_anterior'  => $total_anterior,
            'pago_efec'       => $pago_efec,
            'pago_transfer'   => $pago_transfer,
            'pago_tarjeta'    => $pago_tarjeta,
            'estado'          => 'Modificando_SF'
        ]);

        // Obtener detalles
        $detalles = $detalle_model
            ->where('venta_id', $id_pedido)
            ->findAll();

        $cart->destroy();

        if (!$detalles) {
            session()->setFlashdata('error', 'No se encontraron productos en el pedido.');
            return redirect()->to($this->request->getHeader('referer')->getValue());
        }

        // Cambiar estado a Modificando_SF
        $cabecera_model->update($id_pedido, ['estado' => 'Modificando_SF']);

        foreach ($detalles as $detalle) {

            $producto = $producto_model->find($detalle['producto_id']);
            if (!$producto) continue;

            $tipoPrecioId = (int)($detalle['tipo_precio'] ?? 0);
            $cantidadPromo = 1;

            // 🔥 Reconstruir promo desde tipos_precio
            if ($tipoPrecioId > 0) {

                $promo = $TiposPrecio_model->find($tipoPrecioId);

                if ($promo && $promo['cantidad'] > 0) {
                    $cantidadPromo = (int)$promo['cantidad'];
                }
            }

            $cart->insert([
                'id'    => $producto['id'],
                'qty'   => $detalle['cantidad'], // cantidad elegida por cliente
                'price' => $detalle['precio'],
                'name'  => $producto['nombre'],
                'options' => [
                    'stock'          => $producto['stock'],
                    'cantidadXpromo' => $cantidadPromo,
                    'tipo_precio_id' => $tipoPrecioId
                ]
            ]);
        }

        return redirect()->to('CarritoList');
    }

    session()->setFlashdata('msg', 'Esta Venta ya esta siendo Modificada por otro Cajero!');
    return redirect()->to('compras');
}

    //Cancelar la modificacion de la Venta Sin Facturar
public function cancelar_edicion_Venta_SF($id_pedido){
    
    $cart = \Config\Services::cart();
    $Cabecera_model = new Cabecera_model();    
    $session = session();
    $tiene_saldo_anterior = $session->get('total_anterior');
    //print_r($estado);
    //exit;   
    
    if($tiene_saldo_anterior != 0 || $tiene_saldo_anterior != null){ 
        $Cabecera_model->update($id_pedido, ['estado' => 'Modificada_SF']);
    }else {
        $Cabecera_model->update($id_pedido, ['estado' => 'Sin_Facturar']);
        }       
    // Después de guardar el pedido (cuando ya no se necesiten los datos de la sesión)
    $session->remove(['pago_efec','pago_transfer','pago_tarjeta','estado','id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta','total_bonificado','total_anterior']);

    
    $cart->destroy();
    return redirect()->to(base_url('compras'));
}

}