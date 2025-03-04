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
    if($cabecera['estado'] == 'Pendiente'){
    // Actualizar el estado del pedido a "Modificando"
    $cabecera_model->update($id_vta, ['estado' => 'Cobrando']);

    $id_vendedor = $cabecera ? $cabecera['id_usuario'] : null;
    $vendedor = $US_model->find($id_vendedor);
    $nombre_vendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';
    $id_cliente = $cabecera ? $cabecera['id_cliente'] : null;
    $id_pedido = $cabecera ? $cabecera['id'] : null;
    $fecha_pedido = $cabecera ? $cabecera['fecha_pedido'] : null;
    $tipo_compra = $cabecera ? $cabecera['tipo_compra'] : null;
    $tipo_pago = $cabecera ? $cabecera['tipo_pago'] : null;
    $total_venta = $cabecera ? $cabecera['total_venta'] : null;
    $session->set([
        'id_vendedor' => $id_vendedor,
        'nombre_vendedor' => $nombre_vendedor,
        'id_cliente' => $id_cliente,
        'id_pedido' => $id_pedido,
        'fecha_pedido' => $fecha_pedido,
        'tipo_compra' => $tipo_compra,
        'tipo_pago' => $tipo_pago,
        'total_venta' => $total_venta
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
        $Cabecera_model = new Cabecera_model();
        $Cabecera_model->update($id_pedido, ['estado' => 'Pendiente']);           
        $session->remove(['id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
        session()->setFlashdata('msg', 'Se Cancelo el cobro de la Venta!');
        return redirect()->to('caja');
    }

}