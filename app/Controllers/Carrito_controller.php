<?php
namespace App\Controllers;

require_once APPPATH . 'Libraries/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
    $perfil = $session->get('perfil_id');
        if($perfil == 2){
            return redirect()->to(base_url('catalogo'));
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

   public function productosAgregados()
{
    $cart = \Config\Services::cart();
    $cartContents = $cart->contents();

    $TiposPrecio_model = new \App\Models\Tipos_precio_model();
    $Producto_model    = new \App\Models\Productos_model();

    $tipos = [];
    $idsProductos = [];

    foreach ($cartContents as $item) {
        $idsProductos[] = $item['id'];
    }

    $idsProductos = array_unique($idsProductos);

    if (!empty($idsProductos)) {

        // 🔹 Traer productos (para precio normal)
        $productos = $Producto_model
            ->whereIn('id', $idsProductos)
            ->findAll();

        $productosIndexados = [];
        foreach ($productos as $prod) {
            $productosIndexados[$prod['id']] = $prod;
        }

        // 🔹 Traer promos
        $tiposData = $TiposPrecio_model
            ->whereIn('id_prod', $idsProductos)
            ->findAll();

        foreach ($tiposData as $tipo) {
            $tipos[$tipo['id_prod']][] = $tipo;
        }

        // 🔥 Agregar NORMAL manualmente
        foreach ($idsProductos as $idProd) {

            if (!isset($productosIndexados[$idProd])) continue;

            $tipos[$idProd][] = [
                'id'         => 0,
                'id_prod'    => $idProd,
                'precio'     => $productosIndexados[$idProd]['precio_vta'],
                'cantidad'   => 1,
                'nom_precio' => 'NORMAL'
            ];
        }
    }

    $data = [
        'titulo'  => 'Productos en el Carrito',
        'carrito' => $cartContents,
        'tipos'   => $tipos
    ];

    echo view('navbar/navbar');
    echo view('header/header', $data);
    echo view('carrito/ProductosEnCarrito', $data);
    echo view('footer/footer');
}

    //Agrega elemento al carrito
public function add()
{
   $session = session();
   $estado = $session->get('estado');
   $id_pedido = $session->get('id_pedido');

   $producto_id = $this->request->getPost('id');
   $nombre      = $this->request->getPost('nombre');
   $precio      = $this->request->getPost('precio_vta');
   $cantidad    = ($this->request->getPost('cantidad') ?? 1);

   $prodModel = new Productos_model();
   $producto  = $prodModel->getProducto($producto_id);

   if (!$producto) {
       $session->setFlashdata('msgEr', 'Producto no encontrado.');
       return redirect()->to(base_url('catalogo'));
   }

   $stock_actual = (int)$producto['stock'];

   $VentaDetalle_model = new VentaDetalle_model();
   $TiposPrecio_model  = new \App\Models\Tipos_precio_model();

   $cantidad_reservada = 0;

   // 🔹 Si estamos modificando una venta existente
   if ($estado == 'Modificando' || $estado == 'Modificando_SF') {

       $detalles = $VentaDetalle_model
           ->where('venta_id', $id_pedido)
           ->where('producto_id', $producto_id)
           ->findAll();

       foreach ($detalles as $det) {

           $tipoPrecioId = $det['tipo_precio_id'] ?? 0;

           if ($tipoPrecioId > 0) {
               $promo = $TiposPrecio_model->find($tipoPrecioId);
               $multiplicador = $promo ? (int)$promo['cantidad'] : 1;
           } else {
               $multiplicador = 1;
           }

           $cantidad_reservada += $det['cantidad'] * $multiplicador;
       }
   }

   // 🔹 Stock real disponible
   $stock_disponible = $stock_actual + $cantidad_reservada;

   if ($stock_disponible <= 0) {
       $session->setFlashdata('msgEr', 'No hay Stock Disponible para este Producto.');
       return redirect()->to(base_url('catalogo'));
   }

   // 🔹 Detectar si es promo según precio seleccionado
   $promo = $TiposPrecio_model
       ->where('id_prod', $producto_id)
       ->where('precio', $precio)
       ->first();

   $cantidadPromo = ($promo) ? (int)$promo['cantidad'] : 1;
   $tipoPrecioId  = ($promo) ? (int)$promo['id'] : 0;

   // 🔹 Calcular unidades reales a descontar
   $unidades_reales = $cantidad * $cantidadPromo;

   $cart = \Config\Services::cart();
   $cart_items = $cart->contents();

   $total_en_carrito = 0;

   foreach ($cart_items as $item) {
       if ($item['id'] == $producto_id) {
           $cantidadItem = (int)($item['options']['cantidadXpromo'] ?? 1);
           $total_en_carrito += $item['qty'] * $cantidadItem;
       }
   }

   // 🔹 Validación real de stock
   if (($unidades_reales + $total_en_carrito) > $stock_disponible) {
       $session->setFlashdata('msgEr', 'No puedes agregar más productos de los disponibles en stock.');
       return redirect()->to(base_url('catalogo'));
   }

   // 🔹 Verificar si ya existe misma combinación producto + precio
   $producto_encontrado = false;

   foreach ($cart_items as $item) {

       if (
           $item['id'] == $producto_id &&
           $item['price'] == $precio
       ) {

           $nueva_cantidad = $item['qty'] + $cantidad;

           $cart->update([
               'rowid' => $item['rowid'],
               'qty'   => $nueva_cantidad
           ]);

           $producto_encontrado = true;
           break;
       }
   }

   // 🔹 Insertar si no existe
   if (!$producto_encontrado) {

       $cart->insert([
           'id'    => $producto_id,
           'qty'   => $cantidad,
           'price' => $precio,
           'name'  => $nombre,
           'options' => [
               'cantidadXpromo' => $cantidadPromo,
               'tipo_precio_id' => $tipoPrecioId,
               'stock'          => $stock_disponible
           ]
       ]);
   }

   $session->setFlashdata('msg', 'Producto Agregado!');
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
		return redirect()->to(base_url('catalogo'));
	}

    public function procesarCarrito()
    {
        $accion = $this->request->getPost('accion');
     //Actualizamos los importes del carrito y cantidades   
        if ($accion == 'actualizar') {
     $session = session();
$id_pedido = $session->get('id_pedido');

$cart = \Config\Services::cart();
$cart_info = $this->request->getPost('cart'); 
$cart_contents = $cart->contents();

$VentaDetalle_model = new VentaDetalle_model();
$Producto_model     = new Productos_model();
$TiposPrecio_model  = new \App\Models\Tipos_precio_model();

$errores_stock = [];
$impactoTotal  = []; // acumulador real por producto


// ============================
// 1️⃣ CALCULAR IMPACTO TOTAL
// ============================

foreach ($cart_info as $rowid => $item_post) {

    if (!isset($cart_contents[$rowid])) {
        continue;
    }

    $item = $cart_contents[$rowid];
    $id_producto = $item['id'];
    $qty   = (int)$item_post['qty'];

    // cantidad real por promo desde carrito
    $cantidadPromo = (isset($item['options']['cantidadXpromo']) && (int)$item['options']['cantidadXpromo'] > 0)
                     ? (int)$item['options']['cantidadXpromo']
                     : 1;

    $unidades_reales = $qty * $cantidadPromo;

    $impactoTotal[$id_producto] = ($impactoTotal[$id_producto] ?? 0) + $unidades_reales;
}


// ============================
// 2️⃣ VALIDAR STOCK REAL TOTAL
// ============================

foreach ($impactoTotal as $id_producto => $totalUnidadesSolicitadas) {

    $producto = $Producto_model->find($id_producto);
    if (!$producto) continue;

    $stock_actual = (int)$producto['stock'];
    $nombre_producto = $producto['nombre'];

    $cantidad_reservada = 0;

    if ($id_pedido) {

        $detallesAnteriores = $VentaDetalle_model
            ->where('venta_id', $id_pedido)
            ->where('producto_id', $id_producto)
            ->findAll();

        foreach ($detallesAnteriores as $detalle) {

            $cantidadDetalle = (int)$detalle['cantidad'];
            $tipoPrecioId    = (int)$detalle['tipo_precio'];

            if ($tipoPrecioId > 0) {

                $promo = $TiposPrecio_model->find($tipoPrecioId);

                if ($promo && $promo['cantidad'] > 0) {
                    $cantidadPromo = (int)$promo['cantidad'];
                    $cantidad_reservada += $cantidadDetalle * $cantidadPromo;
                } else {
                    $cantidad_reservada += $cantidadDetalle;
                }

            } else {
                $cantidad_reservada += $cantidadDetalle;
            }
        }
    }

    $stock_disponible = $stock_actual + $cantidad_reservada;

    if ($totalUnidadesSolicitadas > $stock_disponible) {
        $errores_stock[] = 
            "Producto: <strong>$nombre_producto</strong><br>
             Unidades solicitadas: <strong>$totalUnidadesSolicitadas</strong><br>
             Stock disponible real: <strong>$stock_disponible</strong>";
    }
}


// ============================
// 3️⃣ SI HAY ERRORES → CANCELAR
// ============================

if (!empty($errores_stock)) {

    $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br><br>"
                     . implode("<br><br>", $errores_stock);

    $session->setFlashdata('msgEr', $mensaje_error);
    return redirect()->to(base_url('catalogo'));
}


// ============================
// 4️⃣ ACTUALIZAR CANTIDADES Y TIPO PRECIO
// ============================

foreach ($cart_info as $rowid => $item_post) {

    if (!isset($cart_contents[$rowid])) continue;

    $itemActual = $cart_contents[$rowid];

    $qty = (int)$item_post['qty'];
    $tipoPrecioId = isset($item_post['tipo_precio_id'])
                    ? (int)$item_post['tipo_precio_id']
                    : 0;

    $precioFinal = 0;
    $cantidadPromo = 1;

    // 🔹 Si seleccionó promo
    if ($tipoPrecioId > 0) {

        $promo = $TiposPrecio_model->find($tipoPrecioId);

        if ($promo) {
            $precioFinal   = (float)$promo['precio'];
            $cantidadPromo = (int)$promo['cantidad'];
        }

    } else {

        // 🔥 ES PRECIO NORMAL → buscar precio_vta
        $producto = $Producto_model->find($itemActual['id']);

        if ($producto) {
            $precioFinal   = (float)$producto['precio_vta'];
            $cantidadPromo = 1;
        }
    }

    // 🔹 Actualizar carrito completo
    $cart->update([
        'rowid' => $rowid,
        'qty'   => $qty,
        'price' => $precioFinal,
        'options' => [
            'cantidadXpromo' => $cantidadPromo,
            'tipo_precio_id' => $tipoPrecioId,
            'stock'          => $itemActual['options']['stock'] ?? 0
        ]
    ]);
}

$session->setFlashdata('msg', 'Carrito Actualizado!');
return redirect()->to(base_url('catalogo'));

//Esta parte es para avanzar a CasiListo primero vuelve a validar el $cart
        } elseif ($accion == 'confirmar') {
            
 $cart = \Config\Services::cart();
$cart_info     = $this->request->getPost('cart');
$cart_contents = $cart->contents();

$session    = session();
$id_pedido  = $session->get('id_pedido');

$VentaDetalle_model = new VentaDetalle_model();
$Producto_model     = new Productos_model();
$TiposPrecio_model  = new \App\Models\Tipos_precio_model();

$errores_stock = [];
$impactoTotal  = []; // acumulador real por producto


// =====================================
// 1️⃣ CALCULAR IMPACTO TOTAL REAL
// =====================================

foreach ($cart_info as $rowid => $item_post) {

    if (!isset($cart_contents[$rowid])) {
        continue;
    }

    $item         = $cart_contents[$rowid];
    $id_producto  = $item['id'];
    $qty          = (int)$item_post['qty'];

    $cantidadPromo = (isset($item['options']['cantidadXpromo']) && (int)$item['options']['cantidadXpromo'] > 0)
                     ? (int)$item['options']['cantidadXpromo']
                     : 1;

    $unidades_reales = $qty * $cantidadPromo;

    $impactoTotal[$id_producto] = ($impactoTotal[$id_producto] ?? 0) + $unidades_reales;
}


// =====================================
// 2️⃣ VALIDAR STOCK REAL TOTAL
// =====================================

foreach ($impactoTotal as $id_producto => $totalUnidadesSolicitadas) {

    $producto = $Producto_model->find($id_producto);
    if (!$producto) continue;

    $stock_actual     = (int)$producto['stock'];
    $nombre_producto  = $producto['nombre'];

    $cantidad_reservada = 0;

    if ($id_pedido) {

        $detallesAnteriores = $VentaDetalle_model
            ->where('venta_id', $id_pedido)
            ->where('producto_id', $id_producto)
            ->findAll();

        foreach ($detallesAnteriores as $detalle) {

            $cantidadDetalle = (int)$detalle['cantidad'];
            $tipoPrecioId    = (int)$detalle['tipo_precio'];

            // 🔥 Si es promo
            if ($tipoPrecioId > 0) {

                $promo = $TiposPrecio_model->find($tipoPrecioId);

                if ($promo && $promo['cantidad'] > 0) {
                    $cantidadPromo = (int)$promo['cantidad'];
                    $cantidad_reservada += $cantidadDetalle * $cantidadPromo;
                } else {
                    $cantidad_reservada += $cantidadDetalle;
                }

            } else {
                // 🔥 No es promo
                $cantidad_reservada += $cantidadDetalle;
            }
        }
    }

    $stock_disponible = $stock_actual + $cantidad_reservada;

    if ($totalUnidadesSolicitadas > $stock_disponible) {

        $errores_stock[] =
            "Producto: <strong>$nombre_producto</strong><br>
             Unidades solicitadas reales: <strong>$totalUnidadesSolicitadas</strong><br>
             Stock disponible real: <strong>$stock_disponible</strong>";
    }
}


// =====================================
// 3️⃣ SI HAY ERRORES → CANCELAR TODO
// =====================================

if (!empty($errores_stock)) {

    $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br><br>"
                     . implode("<br><br>", $errores_stock);

    $session->setFlashdata('msgEr', $mensaje_error);
    return redirect()->to(base_url('catalogo'));
}

// =====================================
// 4️⃣ ACTUALIZAR CARRITO (CORREGIDO)
// =====================================

foreach ($cart_info as $rowid => $item_post) {

    if (!isset($cart_contents[$rowid])) continue;

    $itemActual = $cart_contents[$rowid];

    $qty = (int)$item_post['qty'];
    $tipoPrecioId = isset($item_post['tipo_precio_id'])
                    ? (int)$item_post['tipo_precio_id']
                    : ($itemActual['options']['tipo_precio_id'] ?? 0);

    $precioFinal   = 0;
    $cantidadPromo = 1;

    // 🔥 Si es promo
    if ($tipoPrecioId > 0) {

        $promo = $TiposPrecio_model->find($tipoPrecioId);

        if ($promo) {
            $precioFinal   = (float)$promo['precio'];
            $cantidadPromo = (int)$promo['cantidad'];
        }

    } else {

        // 🔥 PRECIO NORMAL
        $producto = $Producto_model->find($itemActual['id']);

        if ($producto) {
            $precioFinal   = (float)$producto['precio_vta'];
            $cantidadPromo = 1;
        }
    }

    $cart->update([
        'rowid' => $rowid,
        'qty'   => $qty,
        'price' => $precioFinal,
        'options' => [
            'cantidadXpromo' => $cantidadPromo,
            'tipo_precio_id' => $tipoPrecioId,
            'stock'          => $itemActual['options']['stock'] ?? 0
        ]
    ]);
}

// =====================================
// 5️⃣ REDIRECCIÓN FINAL
// =====================================

$session->setFlashdata('msg', 'Carrito Actualizado!');
return redirect()->to(base_url('casiListo'));

//Si el proceso es de una Venta que se esta modificando entra aqui.
        } elseif ($accion == 'modificar') {
       $Producto_model = new Productos_model();
$VentaDetalle_model = new VentaDetalle_model();
$TiposPrecio_model = new \App\Models\Tipos_precio_model();
$session = session();
$cart = \Config\Services::cart();

// Recibe los datos del carrito
$cart_info = $this->request->getPost('cart');
$id_pedido = $session->get('id_pedido');

// Array para guardar productos sin stock suficiente
$errores_stock = [];

foreach ($cart_info as $rowid => $carrito) {

    if (!$cart->getItem($rowid)) continue;

    $itemActual  = $cart->getItem($rowid);
    $id_producto = $itemActual['id'];
    $qty         = (int)$carrito['qty'];

    $producto = $Producto_model->find($id_producto);
    if (!$producto) continue;

    $stock_actual    = (int)$producto['stock'];
    $nombre_producto = $producto['nombre'];

    // 🔹 Obtener tipo precio desde POST
    $tipoPrecioId = isset($carrito['tipo_precio_id'])
                    ? (int)$carrito['tipo_precio_id']
                    : 0;

    $precioFinal   = 0;
    $cantidadXpromo = 1;

    // 🔹 Si seleccionó promo
    if ($tipoPrecioId > 0) {

        $promo = $TiposPrecio_model->find($tipoPrecioId);

        if ($promo) {
            $precioFinal   = (float)$promo['precio'];
            $cantidadXpromo = (int)$promo['cantidad'];
        }

    } else {
        // 🔥 Precio normal
        $precioFinal   = (float)$producto['precio_vta'];
        $cantidadXpromo = 1;
    }

    // ===============================
    // 🔹 CALCULAR STOCK REAL
    // ===============================

    $detalles_anteriores = $VentaDetalle_model
        ->where('venta_id', $id_pedido)
        ->where('producto_id', $id_producto)
        ->findAll();

    $cantidad_reservada = 0;

    foreach ($detalles_anteriores as $det) {

        $tipoPrecioAnterior = (int)($det['tipo_precio'] ?? 0);

        if ($tipoPrecioAnterior > 0) {
            $promoAnterior = $TiposPrecio_model->find($tipoPrecioAnterior);
            $multiplicador = $promoAnterior ? (int)$promoAnterior['cantidad'] : 1;
        } else {
            $multiplicador = 1;
        }

        $cantidad_reservada += $det['cantidad'] * $multiplicador;
    }

    $stock_disponible = $stock_actual + $cantidad_reservada;

    $unidades_reales = $qty * $cantidadXpromo;

    // ===============================
    // 🔹 VALIDAR STOCK
    // ===============================

    if ($unidades_reales <= $stock_disponible && $qty >= 1) {

        $cart->update([
            'rowid' => $rowid,
            'price' => $precioFinal,
            'qty'   => $qty,
            'options' => [
                'tipo_precio_id' => $tipoPrecioId,
                'cantidadXpromo' => $cantidadXpromo,
                'stock'          => $itemActual['options']['stock'] ?? 0
            ]
        ]);

    } else {

        $errores_stock[] =
            "Producto: <strong>$nombre_producto</strong> - 
            Cantidad solicitada: <strong>$qty</strong> 
            (Unidades reales: $unidades_reales) - 
            Stock disponible real: <strong>$stock_disponible</strong>";
    }
}

// 🔴 Si hay errores
if (!empty($errores_stock)) {

    $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br>" .
        implode("<br>", $errores_stock);

    session()->setFlashdata('msgEr', $mensaje_error);
    return redirect()->to('catalogo');
}


// 🔹 Calcular total venta
$total_venta = 0;
foreach ($cart->contents() as $item) {
    $total_venta += $item['subtotal'];
}

$id_vendedor = $session->get('id_vendedor');
$id_cliente = $session->get('id_cliente');
$fecha_pedido = $session->get('fecha_pedido');
$tipo_compra = $session->get('tipo_compra');

date_default_timezone_set('America/Argentina/Buenos_Aires');
$hora = date('H:i:s');
$fecha = date('d-m-Y');


// 🔵 ACTUALIZAR VENTA EXISTENTE
if ($id_pedido > 0 && $tipo_compra == 'Compra_Normal') {

    $Cabecera_model = new Cabecera_model();

    // 1️⃣ Actualizar cabecera
    $Cabecera_model->update($id_pedido, [
        'fecha' => $fecha,
        'hora' => $hora,
        'id_cliente' => $id_cliente,
        'id_usuario' => $id_vendedor,
        'total_venta' => $total_venta,
        'total_bonificado' => $total_venta,
        'tipo_compra' => 'Compra_Normal',
        'estado' => 'Pendiente',
    ]);

    // 2️⃣ Obtener productos anteriores
    $productos_anteriores = $VentaDetalle_model
        ->where('venta_id', $id_pedido)
        ->findAll();

    // 3️⃣ DEVOLVER STOCK REAL (con promo)
    foreach ($productos_anteriores as $detalle) {

        $producto = $Producto_model->find($detalle['producto_id']);
        if (!$producto) continue;

        $tipoPrecioId = $detalle['tipo_precio'] ?? 0;

        if ($tipoPrecioId > 0) {
            $promo = $TiposPrecio_model->find($tipoPrecioId);
            $multiplicador = $promo ? (int)$promo['cantidad'] : 1;
        } else {
            $multiplicador = 1;
        }

        $unidades_reales = $detalle['cantidad'] * $multiplicador;

        $nuevo_stock = $producto['stock'] + $unidades_reales;

        $Producto_model->update(
            $detalle['producto_id'],
            ['stock' => $nuevo_stock]
        );
    }

    // 4️⃣ Eliminar detalles anteriores
    $VentaDetalle_model->where('venta_id', $id_pedido)->delete();

    // 5️⃣ Insertar nuevos detalles y descontar stock real
    foreach ($cart->contents() as $item) {

        $cantidadXpromo = (int)($item['options']['cantidadXpromo'] ?? 1);
        $tipoPrecioId = (int)($item['options']['tipo_precio_id'] ?? 0);

        $VentaDetalle_model->save([
            'venta_id' => $id_pedido,
            'producto_id' => $item['id'],
            'cantidad' => $item['qty'],
            'precio' => $item['price'],
            'total' => $item['subtotal'],
            'tipo_precio' => $tipoPrecioId
        ]);

        $producto = $Producto_model->find($item['id']);
        if (!$producto) continue;

        $unidades_reales = $item['qty'] * $cantidadXpromo;

        $stock_edit = $producto['stock'] - $unidades_reales;

        $Producto_model->update(
            $item['id'],
            ['stock' => $stock_edit]
        );
    }

    // Limpiar sesión
    $session->remove([
        'estado','id_vendedor','nombre_vendedor',
        'id_cliente','id_pedido','fecha_pedido',
        'tipo_compra','tipo_pago','total_venta'
    ]);

    $cart->destroy();

    session()->setFlashdata('msg', 'Venta Actualizada con Éxito!');
    return redirect()->to('caja');
}
//Modifica y guarda los cambios de la venta realizada Sin Facturar
        } elseif ($accion == 'GuardarCambios'){
            $Producto_model = new Productos_model();
            $VentaDetalle_model = new VentaDetalle_model();
            $TiposPrecio_model = new \App\Models\Tipos_precio_model();
            $cart = \Config\Services::cart();           
            $session = session();
           
            // Recibe los datos del carrito, calcula y actualiza
            $cart_info = $this->request->getPost('cart');
            $motivo = $this->request->getPost('motivo_modif');            
            $tipo_pago_dif = $this->request->getPost('tipo_pago_dif'); // Puede ser 'Efectivo' o 'Transferencia o Tarjeta'
            $tipo_pago_anterior = $session->get('tipo_pago'); // Puede ser 'Mixto', 'Transferencia' o 'Efectivo'
            $id_pedido = $session->get('id_pedido');
            $tipo_pago_Modif = '';            

          // Array para guardar todos los productos que tengan stock no disponible
            $errores_stock = [];

            foreach ($cart_info as $id => $carrito) {

                $id_producto = $carrito['id'];

                // Obtener producto
                $producto = $Producto_model->find($id_producto);
                if (!$producto) continue;

                $stock_actual = (int)$producto['stock'];
                $nombre_producto = $producto['nombre'];

                // 🔹 Calcular cantidad reservada REAL (con promo)
                $detalles_anteriores = $VentaDetalle_model
                    ->where('venta_id', $id_pedido)
                    ->where('producto_id', $id_producto)
                    ->findAll();

                $cantidad_reservada = 0;

                foreach ($detalles_anteriores as $det) {

                    $tipoPrecioId = $det['tipo_precio'] ?? 0;

                    if ($tipoPrecioId > 0) {
                        $promo = $TiposPrecio_model->find($tipoPrecioId);
                        $multiplicador = $promo ? (int)$promo['cantidad'] : 1;
                    } else {
                        $multiplicador = 1;
                    }

                    $cantidad_reservada += $det['cantidad'] * $multiplicador;
                }

                // 🔹 Stock disponible real
                $stock_disponible = $stock_actual + $cantidad_reservada;

                $rowid = $carrito['rowid'];
                $price = $carrito['price'];
                $qty   = (int)$carrito['qty'];

                // 🔹 Obtener multiplicador desde carrito
                $cantidadXpromo = (int)($carrito['options']['cantidadXpromo'] ?? 1);
                $unidades_reales = $qty * $cantidadXpromo;

                // 🔹 Validación REAL de stock
                if ($unidades_reales <= $stock_disponible && $qty >= 1) {

                    $amount = $price * $qty;

                    $cart->update([
                        'rowid'   => $rowid,
                        'price'   => $price,
                        'amount'  => $amount,
                        'qty'     => $qty
                    ]);

                } else {

                    $errores_stock[] =
                        "Producto: <strong>$nombre_producto</strong> - 
                        Cantidad solicitada: <strong>$qty</strong> 
                        (Unidades reales: $unidades_reales) - 
                        Stock disponible real: <strong>$stock_disponible</strong>";
                }
            }

            // Si hay errores de stock, mostrar mensaje y redirigir
            if (!empty($errores_stock)) {
                $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br>" . implode("<br>", $errores_stock);
                session()->setFlashdata('msgEr', $mensaje_error);
                return redirect()->to('CarritoList');
            }

            //Si el campo del motivo viene vacio lo devuelve a la vista.
                if(!$motivo){
                    session()->setFlashdata('msgEr', 'El Motivo es Obligatorio.!!');
                    return redirect()->to('CarritoList');
                }

            // Comparar ambas variables y asignar el valor a $tipo_pago_Modif
                if ($tipo_pago_dif === $tipo_pago_anterior) {
                    $tipo_pago_Modif = $tipo_pago_dif; // Coinciden
                } elseif (in_array($tipo_pago_dif, ['Efectivo', 'Transferencia', 'Tarjeta'])) {
                    $tipo_pago_Modif = 'Mixto'; // No coinciden
                } else {
                    $tipo_pago_Modif = $tipo_pago_anterior; // Si el valor no es válido, mantener el anterior
                }           
           
            // Inicializar la variable para la suma total de la venta
            $total_venta = 0;

            // Recorrer el carrito y calcular el total
            foreach ($cart->contents() as $item) {
                $total_venta += $item['subtotal']; // Sumar cada subtotal (precio * cantidad)
            }                
        
    
            $id_vendedor = $session->get('id_vendedor');
            $id_cliente = $session->get('id_cliente');            
            $fecha_pedido = $session->get('fecha_pedido');
            $tipo_compra = $session->get('tipo_compra');            
            $total_anterior = $session->get('total_venta');
            $total_anterior_bonif = $session->get('total_bonificado');
            $estado = $session->get('estado');
            $cd_efectivo =$session->get('cd_efectivo'); 

            $pago_efec = $session->get('pago_efec');
            $nuevoPago_Efec = $pago_efec;
            $pago_transfer = $session->get('pago_transfer');
            $nuevoPago_Transfer = $pago_transfer;
            $pago_tarjeta = $session->get('pago_tarjeta'); 
            $nuevoPago_Tarjeta = $pago_tarjeta; 
            $dif_pago_efec = 0;
                  
            //El resto entre el total actual de la venta menos el total anterior que usamos el total bonificado
            $resul_descuento = 0;
            $resul_adicional = 0;           
            $total_bonificado_OK = 0;
            //$total_venta = 200;
            $resto_ActualMenosAnterior = $total_venta - $total_anterior;
             
            //Si el resultado de la resta de los totales actual y anterior da mayor a 0, significa que tiene 
            //que pagar una diferencia, en efectivo o transferencia.
            if($resto_ActualMenosAnterior > 0){ 

            if($tipo_pago_dif == 'Efectivo'){
                //Calculo cuanto tengo que restar al total general de la venta nueva modificada (Bonificacion)
                $resul_descuento = $resto_ActualMenosAnterior / $cd_efectivo;

                $total_bonificado_OK = $total_anterior_bonif + $resul_descuento;
                //Sumo el pago de la nueva diferencia pagada en efectivo al monto anterior de efectivo.
                $nuevoPago_Efec = $nuevoPago_Efec + $resul_descuento;
                
             //Si el pago es con transferencia el total con bonificacion es igual al total general.   
            }elseif ($tipo_pago_dif == 'Transferencia'){
                $total_bonificado_OK = $total_anterior_bonif + $resto_ActualMenosAnterior;
                //Sumamos el resto de la venta nueva menos la anterior al monto transferencia
                $nuevoPago_Transfer = $nuevoPago_Transfer + $resto_ActualMenosAnterior; 
                
            //Si el pago es con tarjeta el total con bonificacion es el total de la venta nueva
            //mas la diferencia con adicional.  
            }elseif ($tipo_pago_dif == 'Tarjeta'){
                $resul_adicional = $resto_ActualMenosAnterior * 1.1;
                $total_bonificado_OK = $total_anterior_bonif + $resul_adicional;

                $nuevoPago_Tarjeta = $nuevoPago_Tarjeta + $resul_adicional;
                
            }
            //Si el resto del total actual menos el total anterior(Bonif) es negativo o igual a 0
            //significa que tiene que devolver parte de la plata del gasto en la venta anterior
            //por eso se le asigna el mismo valor de la venta actual al total bonificado
            }elseif ($resto_ActualMenosAnterior == 0){

                $total_bonificado_OK = $total_anterior_bonif;
                

            }elseif ($resto_ActualMenosAnterior < 0){  
                  
                //Si el nuevo total es menor al disponible en efectivo que ya tenia, el nuevo monto en efectivo
                //es el valor de la nueva venta en precio descuento, se devolvio parte del efectivo
                //mas todo de la tarjeta y todo de la transferencia
                
                if($total_venta <= ($pago_efec * $cd_efectivo)){ 
                    $nuevoPago_Efec = $total_venta / $cd_efectivo;                    
                    //Los otros pagos fueron devueltos al cubrir la nueva venta solo con el efectivo
                    //Entonces quedarian en 0 la tarjeta y la transfer
                    $nuevoPago_Transfer = 0;
                    $nuevoPago_Tarjeta = 0;
                    
                    // El nuevo total bonificado seria el total de la venta pagada solo con saldo del efectivo
                    $total_bonificado_OK = $nuevoPago_Efec;
                    
                 }else if($total_venta > ($pago_efec * $cd_efectivo)){
                    //Al usar todo el efectivo para pagar la nueva venta guardo todo ese efectivo en
                    //nuevo_Pago_EfecT Y QUEDA UN RESTO PARA SEGUIR DESCONTANDO A LOS DEMAS MONTOS
                    $nuevoPago_Efec = $pago_efec;
                    //Calculo cuanto queda de restar el monto en transfer.
                    $nuevoPago_Transfer = $pago_transfer - ($total_venta - ($pago_efec * $cd_efectivo));
                    
                        if($nuevoPago_Transfer <= 0){
                            //Si el resto de restar lo que quedo a pagar despues de usar todo el efectivo
                            //es negativo o 0 significa que tambien se uso todo lo de transferencia.
                            //Solo se asigna lo que se ocupo del salgo de tarjeta para pagar lo que faltó, el resto ya se devolvio                                    
                            $nuevoPago_Tarjeta = abs($nuevoPago_Transfer);
                            //Al resto de lo que quedo del saldo de tarjeta lo multiplicamos por 1.1 para guardar el adicional
                            //que tambien cuenta para guardar.
                            $nuevoPago_Tarjeta = ($nuevoPago_Tarjeta * 1.1);
                            //Al ocuparse todo el saldo de transferencia esta se re asigna el monto total original
                            $nuevoPago_Transfer = $pago_transfer;
                            //Sumo los nuevos pagos, en este caso se uso todo de efectivo y transfer 
                            //y se suma lo que se ocupo del saldo en tarjeta porque lo que sobra se develve
                            $total_bonificado_OK = $nuevoPago_Efec + $nuevoPago_Transfer + $nuevoPago_Tarjeta;
                            
                        //Si queda saldo de transferencia luego de restar significa que alcanzo y es mayor a 0
                        } else if($nuevoPago_Transfer > 0){
                        //Guardamos en nuevo pago tarjeta 0 porque no se uso ese saldo y se devolvio
                            $nuevoPago_Tarjeta = 0;
                        //A la variable $nuevoPago_transfer le quedó saldo y hay que restar el saldo original
                        //menos el resto para guardar el resultado como lo que se uso para pagar.
                            $nuevoPago_Transfer = ($total_venta - ($pago_efec * $cd_efectivo));
                        //Asignamos la suma del saldo efectivo mas lo que se ocupdo del saldo de transferencia
                            $total_bonificado_OK = $nuevoPago_Efec + $nuevoPago_Transfer;
                            
                        }
                 }

            }

            //Formateo para que solo guarde 2 decimales.
            $total_bonificado_OK = number_format($total_bonificado_OK, 2, '.', '');


            //Establecer zona horaria y obtener fecha/hora en formato correcto
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $hora = date('H:i:s'); // Formato TIME
            $fecha = date('d-m-Y'); // Formato DATE
    
            // Actualizar el pedido o Venta existente con los nuevos datos
            if ($estado == 'Modificando_SF') {

            $Producto_model = new Productos_model();
            $VentaDetalle_model = new VentaDetalle_model();
            $TiposPrecio_model = new \App\Models\Tipos_precio_model();
            $Cabecera_model = new Cabecera_model();
            $cart = \Config\Services::cart();
            $session = session();

            $cart_info = $this->request->getPost('cart');
            $id_pedido = $session->get('id_pedido');

            // 🔹 1️⃣ Obtener detalles originales
            $detalles_originales = $VentaDetalle_model
                ->where('venta_id', $id_pedido)
                ->findAll();

            // 🔹 2️⃣ DEVOLVER STOCK REAL (con promo)
            foreach ($detalles_originales as $detalle) {

                $producto = $Producto_model->find($detalle['producto_id']);
                if (!$producto) continue;

                $tipoPrecioId = $detalle['tipo_precio'] ?? 0;

                if ($tipoPrecioId > 0) {
                    $promo = $TiposPrecio_model->find($tipoPrecioId);
                    $multiplicador = $promo ? (int)$promo['cantidad'] : 1;
                } else {
                    $multiplicador = 1;
                }

                $unidades_reales = $detalle['cantidad'] * $multiplicador;

                $nuevo_stock = $producto['stock'] + $unidades_reales;

                $Producto_model->update(
                    $detalle['producto_id'],
                    ['stock' => $nuevo_stock]
                );
            }

            // 🔹 3️⃣ Eliminar detalles originales
            $VentaDetalle_model->where('venta_id', $id_pedido)->delete();

            // 🔹 4️⃣ Insertar nuevos detalles y descontar stock REAL
            foreach ($cart->contents() as $item) {

                $cantidadXpromo = (int)($item['options']['cantidadXpromo'] ?? 1);
                $tipoPrecioId = (int)($item['options']['tipo_precio_id'] ?? 0);

                $VentaDetalle_model->save([
                    'venta_id' => $id_pedido,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['qty'],
                    'precio' => $item['price'],
                    'total' => $item['subtotal'],
                    'tipo_precio' => $tipoPrecioId
                ]);

                $producto = $Producto_model->find($item['id']);
                if (!$producto) continue;

                $unidades_reales = $item['qty'] * $cantidadXpromo;

                $stock_edit = $producto['stock'] - $unidades_reales;

                $Producto_model->update(
                    $item['id'],
                    ['stock' => $stock_edit]
                );
            }

            // 🔹 5️⃣ Actualizar cabecera
            $Cabecera_model->update($id_pedido, [
                'fecha_pedido' => $fecha,
                'hora_entrega' => $hora,
                'id_cliente' => $session->get('id_cliente'),
                'id_usuario' => $session->get('id_vendedor'),
                'tipo_pago' => $tipo_pago_Modif,
                'total_venta' => $total_venta,
                'total_bonificado' => $total_bonificado_OK,
                'motivo' => $motivo,
                'total_anterior' => $total_anterior_bonif,
                'monto_efectivo' => $nuevoPago_Efec,
                'monto_transferencia' => $nuevoPago_Transfer,
                'monto_tarjetaC' => $nuevoPago_Tarjeta,
                'estado' => 'Modificada_SF',
            ]);

            // 🔹 6️⃣ Limpiar sesión y carrito
            $session->remove([
                'pago_efec','pago_transfer','pago_tarjeta',
                'estado','id_vendedor','nombre_vendedor',
                'id_cliente','id_pedido','fecha_pedido',
                'tipo_compra','tipo_pago','total_venta',
                'total_bonificado','total_anterior'
            ]);

            $cart->destroy();

            return redirect()->to('Carrito_controller/generarTicket/' . $id_pedido);
        }
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
    
    $id_pedido = $session->get('id_pedido');
    $cabeceraModel = new Cabecera_model();

    // Obtener los detalles de la venta
    $data['ventas'] = $cabeceraModel->getDetallesVenta($id_pedido);

    $ClientesModel = new Clientes_model();
    $data['clientes'] = $ClientesModel->getClientes();
    $data['titulo'] = 'Confirmar compra';
    
    echo view('navbar/navbar');
    echo view('header/header', $data);        
    echo view('carrito/confirmarCompra', $data);
    echo view('footer/footer');
}


//GUARDA LA COMPRA
    public function guarda_compra()
{    
    $cart = \Config\Services::cart();
    $session = session();
    $perfil = $session->get('perfil_id');
    $estado = $session->get('estado');    
    $id_pedido = $this->request->getPost('id_pedido');
    $TiposPrecio_model = new \App\Models\Tipos_precio_model();
    
    //print_r($facturacion);
    //exit;
    
    if(!$cart){
    return redirect()->to(base_url('catalogo'));
    }
    //id del vendedor
    $id_usuario = $session->get('id');

    if(!$id_pedido){    
    //Nombre provisorio del cliente para identificar venta
    $bombre_provisorios_cliente = $this->request->getPost('nombre_prov');    
    if (!$bombre_provisorios_cliente) {
        session()->setFlashdata('msgEr', 'El Campo nombre cliente es Obligatorio!');
        return redirect()->to('casiListo');
    }
    }

    
    //id del cliente seleccionado o se selecciona Consumidor final por defecto.
    $id_cliente = $this->request->getPost('cliente_id');
    if (!$id_cliente) {
        $id_cliente = 1; // Valor por defecto si no se envía cliente_id
    }

    function convertirAFloat($numero) {
        if (empty($numero)) {
            return 0.0; // Si el valor es vacío, devuelve 0.0
        }
        // Remueve los puntos (miles) y reemplaza la coma por punto (decimal)
        $numero = str_replace('.', '', $numero);
        $numero = str_replace(',', '.', $numero);
        return floatval($numero);
    }
    
    $monto_transferencia = convertirAFloat($this->request->getPost('pagoTransferencia'));
    $monto_en_Efectivo = convertirAFloat($this->request->getPost('pagoEfectivo'));
    $monto_tarjetaC = convertirAFloat($this->request->getPost('pagoTarjetaCredito'));
    if($monto_tarjetaC){
        $monto_tarjetaC = $monto_tarjetaC * 1.1;
    }
    

    //Verificamos si se envio el costo de envio
    $costo_envio =  convertirAFloat($this->request->getPost('costoEnvio'));    
    if(!$costo_envio){
        $costo_envio = 0;
    }
    
    // Contar cuántos tipos de pago tienen un monto mayor a 0
    $metodos_pago = 0;
    if ($monto_en_Efectivo > 0) $metodos_pago++;
    if ($monto_transferencia > 0) $metodos_pago++;
    if ($monto_tarjetaC > 0) $metodos_pago++;

    switch ($metodos_pago) {
        case 1:
            if ($monto_en_Efectivo > 0) {
                $tipo_pago_cobro = 'Efectivo';
            } elseif ($monto_transferencia > 0) {
                $tipo_pago_cobro = 'Transferencia';
            } elseif ($monto_tarjetaC > 0) {
                $tipo_pago_cobro = 'Tarjeta';
            }
            break;
        default:
            $tipo_pago_cobro = 'Mixto';
            break;
    }
       
    //Total de la venta
    $total = $this->request->getPost('total_venta');
    //Total menos el descuento si se pago en efectivo.
    $total_conDescuento = $monto_transferencia + $monto_en_Efectivo + $monto_tarjetaC;

    //print_r($total_conDescuento);
    //exit;
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
    $fecha_pedido = $this->request->getPost('fecha_pedido');
    //print_r($fecha_pedido);
    //exit;
    if (!$fecha_pedido){
        $fecha_pedido = date('d-m-Y');
    }
    //print_r($tipo_compra);
    //exit;
    //Formateamos la fecha del pedido al formato dia-mes-año
    $fecha_pedido_formateada = date('d-m-Y', strtotime($fecha_pedido));   
    
    $id_pedido = $this->request->getPost('id_pedido');
    
    $Producto_model = new Productos_model();    
    $VentaDetalle_model = new VentaDetalle_model();
    //Array para guardar todos los productos que tengan stock no disponible
    $cart = \Config\Services::cart();
$cart_info = $cart->contents(); // Obtiene los productos del carrito almacenados en la sesión

$errores_stock = [];
$impactoTotal  = [];


// ======================================================
// 1️⃣ CALCULAR IMPACTO TOTAL REAL POR PRODUCTO
// ======================================================

foreach ($cart_info as $item) {

    $id_producto = $item['id'];
    $qty         = (int)$item['qty'];

    // Detectar cantidad por promo si existe
    $cantidadPromo = (isset($item['options']['cantidadXpromo']) && (int)$item['options']['cantidadXpromo'] > 0)
        ? (int)$item['options']['cantidadXpromo']
        : 1;

    $unidades_reales = $qty * $cantidadPromo;

    // Acumular impacto total por producto
    $impactoTotal[$id_producto] = ($impactoTotal[$id_producto] ?? 0) + $unidades_reales;
}


// ======================================================
// 2️⃣ VALIDAR STOCK REAL TOTAL
// ======================================================

foreach ($impactoTotal as $id_producto => $totalUnidadesSolicitadas) {

    // Obtener el stock actual desde la base de datos
    $producto = $Producto_model->find($id_producto);
    if (!$producto) continue;

    $stock_actual    = (int)$producto['stock'];
    $nombre_producto = $producto['nombre'];

    // Obtener la cantidad que ya estaba reservada en la venta anterior
    $cantidad_reservada = 0;

     if ($id_pedido) {

        $detallesAnteriores = $VentaDetalle_model
            ->where('venta_id', $id_pedido)
            ->where('producto_id', $id_producto)
            ->findAll();

        foreach ($detallesAnteriores as $detalle) {

            $cantidadDetalle = (int)$detalle['cantidad'];
            $tipoPrecioId    = (int)$detalle['tipo_precio'];

            if ($tipoPrecioId > 0) {

                $promo = $TiposPrecio_model->find($tipoPrecioId);

                if ($promo && $promo['cantidad'] > 0) {
                    $cantidadPromo = (int)$promo['cantidad'];
                    $cantidad_reservada += $cantidadDetalle * $cantidadPromo;
                } else {
                    $cantidad_reservada += $cantidadDetalle;
                }

            } else {
                $cantidad_reservada += $cantidadDetalle;
            }
        }
    }

    // Calcular stock disponible real
    $stock_disponible = $stock_actual + $cantidad_reservada;

    // Validar contra stock disponible real
    if ($totalUnidadesSolicitadas > $stock_disponible) {

        $errores_stock[] =
            "Producto: <strong>$nombre_producto</strong><br>
             Unidades solicitadas reales: <strong>$totalUnidadesSolicitadas</strong><br>
             Stock disponible real: <strong>$stock_disponible</strong>";
    }
}


// ======================================================
// 3️⃣ SI HAY ERRORES → CANCELAR
// ======================================================

if (!empty($errores_stock)) {

    $mensaje_error = "Los siguientes productos no tienen suficiente Stock:<br><br>"
        . implode("<br><br>", $errores_stock);

    session()->setFlashdata('msgEr', $mensaje_error);
    return redirect()->to('catalogo');
}


// ======================================================
// 4️⃣ ACTUALIZAR CARRITO (SOLO SI TODO ES VÁLIDO)
// ======================================================

foreach ($cart_info as $item) {

    $rowid = $item['rowid'];
    $price = $item['price'];
    $qty   = (int)$item['qty'];
    $amount = $price * $qty;

    $cart->update([
        'rowid'  => $rowid,
        'price'  => $price,
        'amount' => $amount,
        'qty'    => $qty
    ]);
}    
    
    // Si se encontró un id de pedido y estado modificando, actualizar el pedido existente con los nuevos datos
    if ($estado == 'Modificando' && $tipo_compra == 'Pedido') {

    // Cargar modelos
    $VentaDetalle_model = new VentaDetalle_model();
    $Producto_model     = new Productos_model();
    $Cabecera_model     = new Cabecera_model();
    $TiposPrecio_model  = new \App\Models\Tipos_precio_model();

    // Obtener los productos del pedido anterior
    $productos_anteriores = $VentaDetalle_model
        ->where('venta_id', $id_pedido)
        ->findAll();

    // 1️⃣ Devolver stock del pedido anterior (calculando real)
    foreach ($productos_anteriores as $detalle) {

        $producto = $Producto_model->find($detalle['producto_id']);
        if (!$producto) continue;

        $cantidadDetalle = (int)$detalle['cantidad'];
        $tipoPrecioId    = (int)$detalle['tipo_precio'];

        // 🔥 calcular unidades reales
        if ($tipoPrecioId > 0) {

            $promo = $TiposPrecio_model->find($tipoPrecioId);

            if ($promo && $promo['cantidad'] > 0) {
                $cantidadPromo = (int)$promo['cantidad'];
                $unidades_reales = $cantidadDetalle * $cantidadPromo;
            } else {
                $unidades_reales = $cantidadDetalle;
            }

        } else {
            $unidades_reales = $cantidadDetalle;
        }

        $nuevo_stock = $producto['stock'] + $unidades_reales;

        $Producto_model->update($detalle['producto_id'], [
            'stock' => $nuevo_stock
        ]);
    }

    // 2️⃣ Eliminar los detalles anteriores
    $VentaDetalle_model
        ->where('venta_id', $id_pedido)
        ->delete();

    // 3️⃣ Actualizar cabecera del pedido
    $Cabecera_model->update($id_pedido, [
        'fecha'            => $fecha,
        'hora'             => $hora,
        'id_cliente'       => $id_cliente,
        'total_venta'      => $total,
        'total_bonificado' => $total_conDescuento,
        'tipo_compra'      => 'Pedido',
        'estado'           => 'Pendiente',
        'fecha_pedido'     => $fecha_pedido_formateada
    ]);

    // 4️⃣ Insertar nuevos detalles y descontar stock real
    if ($cart) {

        foreach ($cart->contents() as $item) {

            $cantidad = (int)$item['qty'];
            $tipoPrecioId = (int)($item['options']['tipo_precio_id'] ?? 0);

            // 🔥 Calcular unidades reales
            if ($tipoPrecioId > 0) {

                $promo = $TiposPrecio_model->find($tipoPrecioId);

                if ($promo && $promo['cantidad'] > 0) {
                    $cantidadPromo = (int)$promo['cantidad'];
                    $unidades_reales = $cantidad * $cantidadPromo;
                } else {
                    $unidades_reales = $cantidad;
                }

            } else {
                $unidades_reales = $cantidad;
            }

            // 🔥 Guardar detalle con tipo_precio
            $VentaDetalle_model->save([
                'venta_id'    => $id_pedido,
                'producto_id' => $item['id'],
                'cantidad'    => $cantidad,
                'precio'      => $item['price'],
                'total'       => $item['subtotal'],
                'tipo_precio' => $tipoPrecioId
            ]);

            // 🔥 Descontar stock real
            $producto = $Producto_model->find($item['id']);
            if ($producto) {

                $nuevo_stock = $producto['stock'] - $unidades_reales;

                $Producto_model->update($item['id'], [
                    'stock' => $nuevo_stock
                ]);
            }
        }
    }

    // Limpiar sesión y carrito
    $session->remove([
        'estado',
        'id_vendedor',
        'nombre_vendedor',
        'id_cliente_pedido',
        'id_pedido',
        'fecha_pedido',
        'tipo_compra',
        'tipo_pago'
    ]);

    $cart->destroy();

    session()->setFlashdata('msg', 'Pedido actualizado con éxito!');
    return redirect()->to('pedidos');
}


    //Identifico si es una compra para facturar si este campo viene con el dato "Factura"
    $facturacion = $this->request->getPost('tipo_proceso');
    //print_r($facturacion);exit;
    //Si el tipo de proceso es para facturar tipo A y el estado es Cobrando se manda a facturar.
    if($estado == 'Cobrando' && $facturacion == "facturaA"){
                
            $Cabecera_model = new Cabecera_model();
            $Cabecera_model->update($id_pedido, [
                'estado'            => 'Error_factura',
                'total_venta'       => $total,
                'tipo_pago'         => $tipo_pago_cobro,
                'total_bonificado'  => $total_conDescuento,               
                'fecha'        => $fecha,
                'hora'         => $hora,
                'fecha_pedido'      => $fecha_pedido_formateada,
                'hora_entrega' => $hora,
                'id_cliente'   => $id_cliente, 
                'costo_envio' => $costo_envio,
                'monto_efectivo'    => $monto_en_Efectivo,
                'monto_transferencia' => $monto_transferencia,
                'monto_tarjetaC' => $monto_tarjetaC              
            ]);           
            $session->remove(['estado','id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
        $session->set([        
                    'tipo_factura' => 'A'
                    ]);
        $cart->destroy(); 
        //Una vez guardada la compra manda a verificar la factura en ARCA.
        return redirect()->to('Carrito_controller/verificarTA/' . $id_pedido);
    }

    //Si el tipo de proceso es para facturar tipo B y el estado es Cobrando se manda a facturar.
    if($estado == 'Cobrando' && $facturacion == "facturaB"){
                
            $Cabecera_model = new Cabecera_model();
            $Cabecera_model->update($id_pedido, [
                'estado'            => 'Error_factura',
                'total_venta'       => $total,
                'tipo_pago'         => $tipo_pago_cobro,
                'total_bonificado'  => $total_conDescuento,               
                'fecha'        => $fecha,
                'hora'         => $hora,
                'fecha_pedido'      => $fecha_pedido_formateada,
                'hora_entrega' => $hora,
                'id_cliente'   => $id_cliente, 
                'costo_envio' => $costo_envio,
                'monto_efectivo'    => $monto_en_Efectivo,
                'monto_transferencia' => $monto_transferencia,
                'monto_tarjetaC' => $monto_tarjetaC              
            ]);           
            $session->remove(['estado','id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
        $session->set([        
                    'tipo_factura' => 'B'
                    ]);
        $cart->destroy(); 
        //Una vez guardada la compra manda a verificar la factura en ARCA.
        return redirect()->to('Carrito_controller/verificarTA/' . $id_pedido);
    }

    // Guardar la nueva cabecera del Pedido (Nuevo) utiliza el mismo carrito.
    if ($tipo_compra == 'Pedido' && $estado == '') { 
        // Guardar cabecera de la venta tipo pedido
        $cabecera_model = new Cabecera_model();
        $ventas_id = $cabecera_model->save([
            'fecha'        => $fecha,
            'hora'         => $hora,
            'id_cliente'   => $id_cliente,
            'nombre_prov_client' => $bombre_provisorios_cliente,
            'id_usuario'   => $id_usuario,
            'total_venta'  => $total,            
            'total_bonificado' => $total_conDescuento,
            'tipo_compra' => $tipo_compra,
            'fecha_pedido' => $fecha_pedido_formateada,
            'estado' => 'Pendiente'
        ]);
        
    } else {
        //Si el perfil es vendedor guarda la compra con el estado Pendiente
        
        if($perfil == 2){ 
             // ⚠️ Verificar si el carrito está vacío
        if (!$cart || count($cart->contents()) == 0) {
            session()->setFlashdata('msgEr', 'Evite registrar una misma venta muchas veces, no presione muchas veces el boton de registrar ni se apresure!');
            return redirect()->to('catalogo');
        }
        // Guardar cabecera de la venta tipo compra normal
        $cabecera_model = new Cabecera_model();
        $ventas_id = $cabecera_model->save([
            'fecha'        => $fecha,
            'hora'         => $hora,
            'id_cliente'   => $id_cliente,
            'nombre_prov_client' => $bombre_provisorios_cliente,
            'id_usuario'   => $id_usuario,
            'total_venta'  => $total,            
            'total_bonificado' => $total_conDescuento,
            'tipo_compra' => $tipo_compra,
            'estado' => 'Pendiente'
        ]);
        }
        
        if($perfil == 3){ 
            // Se está cobrando una venta
            if($estado == 'Cobrando'){
                $Cabecera_model = new Cabecera_model();                
        
                // Actualizar la cabecera de la venta
                $Cabecera_model->update($id_pedido, [
                    'estado'            => 'Sin_Facturar',
                    'total_venta'       => $total,
                    'tipo_pago'         => $tipo_pago_cobro,
                    'total_bonificado'  => $total_conDescuento,                  
                    'fecha_pedido'      => $fecha_pedido_formateada,
                    'fecha'             => $fecha,                                      
                    'hora'              => $hora,
                    'hora_entrega'      => $hora,
                    'id_cliente'        => $id_cliente,
                    'costo_envio'       => $costo_envio,
                    'monto_efectivo'    => $monto_en_Efectivo,
                    'monto_transferencia' => $monto_transferencia,
                    'monto_tarjetaC' => $monto_tarjetaC
                ]);           
                
                $session->remove(['estado','id_vendedor', 'nombre_vendedor', 'id_cliente', 'id_pedido', 'fecha_pedido','tipo_compra','tipo_pago','total_venta']);
            }
            
            $cart->destroy();       
            // Redirige a imprimir el ticket indicando que viene del panel de cobro y no de la lista de ventas     
            return redirect()->to('Carrito_controller/generarTicket/' . $id_pedido. '/' . $facturacion);
        }
        

    }
// Obtener ID de la nueva cabecera guardada
$id_cabecera = $cabecera_model->getInsertID();

// Guardar detalles de la venta si el carrito no está vacío
if ($cart):

    $VentaDetalle_model = new VentaDetalle_model();
    $Producto_model     = new Productos_model();
    $TiposPrecio_model  = new \App\Models\Tipos_precio_model();

    foreach ($cart->contents() as $item):

        $cantidad      = (int)$item['qty'];
        $tipoPrecioId  = (int)($item['options']['tipo_precio_id'] ?? 0);

        // 🔥 Calcular unidades reales
        if ($tipoPrecioId > 0) {

            $promo = $TiposPrecio_model->find($tipoPrecioId);

            if ($promo && $promo['cantidad'] > 0) {
                $cantidadPromo   = (int)$promo['cantidad'];
                $unidades_reales = $cantidad * $cantidadPromo;
            } else {
                $unidades_reales = $cantidad;
            }

        } else {
            $unidades_reales = $cantidad;
        }

        // 🔥 Guardar detalle con tipo_precio
        $VentaDetalle_model->save([
            'venta_id'    => $id_cabecera,
            'producto_id' => $item['id'],
            'cantidad'    => $cantidad,
            'precio'      => $item['price'],
            'total'       => $item['subtotal'],
            'tipo_precio' => $tipoPrecioId
        ]);

        // 🔥 Actualizar stock real
        $producto = $Producto_model->find($item['id']);

        if ($producto && isset($producto['stock'])) {

            $stock_edit = $producto['stock'] - $unidades_reales;

            $Producto_model->update($item['id'], [
                'stock' => $stock_edit
            ]);
        }

    endforeach;

endif;
    
    // Limpiar el carrito y redirigir con mensaje
    $cart->destroy();
    if ($tipo_compra == 'Pedido') {
        session()->setFlashdata('msg', 'Pedido Guardado con Éxito!');
        return redirect()->to('catalogo');
    }
    if($perfil == 2){
        session()->setFlashdata('msg', 'Compra Registrada con Exito!');
        return redirect()->to('catalogo');
    }

    session()->setFlashdata('msg', 'Compra Guardada con Éxito!');
    // Redirige a imprimir el ticket indicando que viene del panel de cobro y no de la lista de ventas
    return redirect()->to('Carrito_controller/generarTicket/' . $id_cabecera);
}



//Genera ticket venta normal
public function generarTicket($id_cabecera,$tipoTicket=null)
{
    // Cargar los modelos necesarios
    $Us_Model = new \App\Models\Usuarios_model();
    $ventaModel = new \App\Models\Cabecera_model();
    $detalleModel = new \App\Models\VentaDetalle_model();
    $productoModel = new \App\Models\Productos_model();
    $clienteModel = new \App\Models\Clientes_model();
    $TiposPrecio_model = new \App\Models\Tipos_precio_model();
    //print_r($tipoTicket);exit;
    $session = session();
    $cajero_nombre = $session->get('nombre');
    $cd_efectivo =$session->get('cd_efectivo');
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    
    $CostoEnvio = $cabecera['costo_envio'];
   
    // Actualizar el campo costo_envio a 0 porque se muestra una sola vez.
    $ventaModel->update($id_cabecera, ['costo_envio' => 0]);
    
    $detalles = $detalleModel->where('venta_id', $id_cabecera)->findAll();
    //print_r($detalles);
    //exit;
    // Obtener los productos relacionados
    $productos = [];
    foreach ($detalles as $detalle) {
        $productos[$detalle['producto_id']] = $productoModel->find($detalle['producto_id']);
    }

    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);

    // Obtener el nombre del vendedor    
    $vendedor = $Us_Model->find($cabecera['id_usuario']);
    $nombreVendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';
    
    //Cambia el estado del Pedido
    if($cabecera['tipo_compra'] == 'Pedido' && $cabecera['total_anterior'] == 0){

        $ventaModel->cambiarEstado($id_cabecera, 'Sin_Facturar');
    }
    // Crear el HTML para la vista previa
    ob_start();
    ?>
    <html>
    <head>
        <style>
            /* Estilos CSS para el ticket */
            body {
                font-family: Arial, sans-serif; /* Cambiar a una fuente más legible */
                margin: 0;
                padding: 0;
                width: 220px; /* Ancho del ticket */
            }
            .ticket {
                width: 100%;
                font-size: 12px; /* Ajustar tamaño de fuente */
            }
            h1 {
                font-size: 18px;
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h3 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h4 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h5 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            .ticket p {
                margin: 2px 0;
                font-size: 10px;
                font-weight: bold;
                text-align: justify; /* Justificar el texto */
            }
            .ticket hr {
                border: 0.5px solid #000;
                margin: 5px 0;
            }
            .ticket .header,
            .ticket .footer {
                text-align: center;
                font-size: 10px;
            }
            .ticket .details {
                margin-top: 3px;
                font-size: 10px;
            }
            .ticket .details td {
                padding: 0px;
            }
            .ticket .details th {
                text-align: left;
                padding-right: 5px;
            }
        </style>
    </head>
    <body>
        <div class="ticket">
            <h3>Remito</h3>
            <h5>no valido como factura</5>
            <!-- Cabecera del ticket -->
            <h1>MULTIRRUBRO BLASS</h1>
            <p>GONZALEZ EMMANUEL ALEJANDRO</p>
            <p>CUIT Nro: 20-36955726-3</p>
            <p>Domicilio: Belgrano 2077, Corrientes (3400)</p>
            <p>Cel: 3794-095020</p>
            <p>Inicio de actividades: 01/02/2023</p>
            <p>Ingresos Brutos: 20-36955726-3</p>
            <p>Resp. Inscripto</p>
            <hr>

            <!-- Información de la venta -->
            <p>Fecha: <?= ($cabecera['tipo_compra'] == 'Pedido') ? date('d-m-Y H:i') : $cabecera['fecha_pedido'] . ' ' . $cabecera['hora_entrega']; ?></p>
        
            <p>Cliente: <?= $cliente['cuil'] > 0 ? $cliente['nombre'] . ' Cuil: ' . $cliente['cuil'] : $cliente['nombre'] ?></p>
            <p>Atendido por: <?= $nombreVendedor ?></p>
            <p>Cajero: <?= $cajero_nombre ?></p>
            <hr>

            <!-- Detalle de la compra -->
            <div class="details" style="width: 100%; font-size: 10px;">
            <h3>Detalle de la Compra</h3>
            <h4>COD: <?= $cabecera['id'] ?></h4>

            <?php foreach ($detalles as $detalle): ?>

                <?php
                    $tipoPrecioId  = (int)($detalle['tipo_precio'] ?? 0);
                    $cantidadPromo = 1;
                    $textoPromo    = '';
                    $indicador     = ''; // ← NUEVO

                    if ($tipoPrecioId > 0) {
                        $promo = $TiposPrecio_model->find($tipoPrecioId);

                        if ($promo) {

                            // Cantidad promo
                            if (isset($promo['cantidad']) && $promo['cantidad'] > 0) {
                                $cantidadPromo = (int)$promo['cantidad'];
                                $textoPromo = ($cantidadPromo > 1) ? " ({$cantidadPromo}u)" : '';
                            }

                            // 🔹 Indicador P / O
                            if (isset($promo['nom_precio'])) {

                                switch ($promo['nom_precio']) {
                                    case 'PROMO1':
                                        $indicador = 'P1';
                                        break;

                                    case 'PROMO2':
                                        $indicador = 'P2';
                                        break;

                                    case 'OUTLET':
                                        $indicador = 'PM';
                                        break;
                                }
                            }
                        }
                    }
                ?>

                <div>
                    <p>
                        <?= $indicador ? '[' . $indicador . '] ' : '' ?>
                        (<?= $detalle['cantidad'] ?>)
                        <?= $productos[$detalle['producto_id']]['nombre'] ?>
                        <!-- <?= $textoPromo ?> -->
                        x $<?= number_format($detalle['precio'], 2) ?>
                    </p>
                </div>

            <?php endforeach; ?>
        </div>

            <!-- Totales 
            <p>Subtotal sin descuentos: $<?= number_format($cabecera['total_venta'], 2) ?></p>
            <p>Descuento: 
            <?= ($cabecera['tipo_pago'] == 'Efectivo' || $cabecera['tipo_pago'] == 'Mixto') 
                ? '$' . number_format(($cabecera['monto_efectivo'] * $cd_efectivo) - $cabecera['monto_efectivo'], 2) 
                : '$0.00' ?>
            </p> -->
            <p>Adicional por Tarjeta: 
            <?= ($cabecera['tipo_pago'] == 'Tarjeta' || $cabecera['tipo_pago'] == 'Mixto') 
                ? '$' . number_format($cabecera['monto_tarjetaC'] - ($cabecera['monto_tarjetaC'] / 1.1), 2) 
                : '$0.00' ?>
            </p>
            <p>Total: $<?= number_format($cabecera['total_bonificado'], 2) ?></p>
            <?php if ($CostoEnvio > 0): ?>
            <p>Costo de Envio: $ <?= $CostoEnvio ?></p>
            <?php endif; ?>
            <hr>

            <!-- Footer -->
            <div class="footer">
                <p>Importante:</p>
                <p>La mercaderia viaja por cuenta y riesgo del comprador.</p>
                <p>Es responsabilidad del cliente controlar su compra antes de salir del local.</p>
                <p>Su compra tiene 48hs para cambio ante fallas previas del producto.</p>
                <p>Instagram: @Blass.Multirrubro</p>
                <p>Facebook: Blass Multirrubro</p>
                <h3>Muchas Gracias por su Compra.!</h3>
            </div>

            <?php if (!empty($cabecera['motivo'])): ?>
            <hr>
            <p>---------------------Recortar Aqui-------------------------</p>
            <p><strong>Motivo de los Cambios:</strong> <?= nl2br(htmlspecialchars($cabecera['motivo'])) ?></p>
            <p><strong>Cajero:</strong> <?= nl2br(htmlspecialchars($cajero_nombre)) ?></p>
            <p><strong>Vendedor:</strong> <?= nl2br(htmlspecialchars($nombreVendedor)) ?></p>
            <p><strong>Fecha y Hora (Original):</strong> <?= date('d-m-Y H:i', strtotime($cabecera['fecha'] . ' ' . $cabecera['hora'])) ?></p>
            <p><strong>Fecha y Hora (Nueva):</strong> <?= date('d-m-Y H:i', strtotime($cabecera['fecha_pedido'] . ' ' . $cabecera['hora_entrega'])) ?></p>
            <p><strong>Total Anterior: $ </strong> <?= number_format($cabecera['total_anterior'], 2) ?></p>
            <p><strong>Total Actual: $ </strong> <?= number_format($cabecera['total_bonificado'], 2) ?></p>
            <p><strong>Diferencia: $ </strong> <?= number_format($cabecera['total_bonificado'] - $cabecera['total_anterior'], 2) ?></p>
            <p>Si la Diferencia es negativa, eso es saldo a favor para el Cliente.</p>
            <?php endif; ?>


            
        </div>
    </body>
    </html>
    <?php
       
    // Generar el PDF
    $html = ob_get_clean();
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();
    
    // Guardar el archivo PDF en un archivo temporal
    $output = $dompdf->output();
    $tempFolder = 'path/to/temp/folder';  // Ruta de la carpeta temporal
    $tempFile = $tempFolder . '/ticket.pdf';  // Ruta completa del archivo PDF
    
    // Crear la carpeta si no existe
    if (!is_dir($tempFolder)) {
        mkdir($tempFolder, 0777, true);  // Crea la carpeta con permisos 0777 (lectura, escritura y ejecución)
    }
    
    // Guardar el archivo PDF en la carpeta temporal
    file_put_contents($tempFile, $output);
    session()->setFlashdata('msg', 'Imprimiendo Ticket.!');

     // Obtener el perfil del usuario desde la sesión
    $perfil = session()->get('perfil_id');

    // Redirigir a una página de confirmación con JavaScript
    echo "<script type='text/javascript'>
        // Descargar el archivo PDF
        window.location.href = '" . base_url('descargar_ticket') . "';

        // Pasar los valores de PHP a JavaScript
        var perfil = " . $perfil . ";
        var tipoTicket = '" . $tipoTicket . "';

        // Redirigir según condiciones
        window.setTimeout(function() {

            if (perfil == 3 && tipoTicket === 'ticket') {
                window.location.href = '" . base_url('caja') . "';
                return;
            }

            if (document.referrer) {
                window.location.href = document.referrer; // Volver a la página anterior
            }

        }, 500);  // 0.5 segundos de espera para asegurar que la descarga termine
    </script>";
    exit;

}

// En tu ruta 'descargar_ticket', puedes usar:
public function descargar_ticket()
{
    $filePath = 'path/to/temp/folder/ticket.pdf';
    if (file_exists($filePath)) {
        return $this->response->download($filePath, null)->setFileName('ticket.pdf');
    }
    // Si no existe el archivo, muestra un error o redirige a otra página.
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
    //Si es un vendedor no le permite
    $perfil=$session->get('perfil_id');
    if($perfil == 2){
            return redirect()->to(base_url('catalogo'));
        }
    
    if ($id_cabecera === null) {
        //session()->setFlashdata('msgEr', 'No se puede facturar sin enviar una Venta.');
        return redirect()->to(base_url('caja'));
    }
    //$id_cabecera = 24;
    // Ruta del archivo TA.xml
    $taPath = ROOTPATH . 'writable/facturacionARCA/TA.xml';

    // Zona horaria de Argentina
    $zonaHorariaArgentina = new \DateTimeZone('America/Argentina/Buenos_Aires');

   // Verificar si el archivo TA.xml existe
   if (!file_exists($taPath)) {

    return redirect()->to('Carrito_controller/generarTA/'. $id_cabecera);
    //$ventaModel->update($id_cabecera,['estado' => 'Error_factura']);
    //session()->setFlashdata('msgEr', 'Problemas con el servidor ARCA, se guardo la compra sin Facturar, intente mas tarde');
    //return redirect()->to(base_url('catalogo'));
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

        $tipo_factura=$session->get('tipo_factura');
       
        //Manda a facturar con el TA y el id de cabecera, y redireccion con msg si es venta o pedido facturado con exito.
       if ($tipo_factura == 'A'){        
         $this->facturar_tipo_A($TA,$id_cabecera);
       }
       if ($tipo_factura == 'B'){        
         $this->facturar_tipo_B($TA,$id_cabecera);
       }
        
        session()->setFlashdata('msg', 'La Factura se realizo con Exito.!');
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
        return redirect()->to(base_url('login')); 
    } 

    if ($id_cabecera === null) {
        return redirect()->to(base_url('catalogo'));
    }

    // Ruta al script wsaa-client.php
    $path = APPPATH . 'Libraries/afip/wsaa-client.php';

    // Configuración de descriptores para la ejecución
    $descriptorspec = [
        0 => ["pipe", "r"],  // Entrada estándar (no usada)
        1 => ["pipe", "w"],  // Salida estándar
        2 => ["pipe", "w"]   // Salida de error
    ];

    // Ejecutar el script PHP con proc_open
    $process = proc_open("php " . escapeshellarg($path) . " wsfe", $descriptorspec, $pipes);
    //print_r($process);exit;
    if (is_resource($process)) {
        $output = stream_get_contents($pipes[1]); // Captura la salida
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process); // Cierra el proceso

        // Mostrar la salida para depuración (puedes comentar esto en producción)
        //echo "<pre>$output</pre>";
        //exit;
    } else {
        echo "Error al ejecutar el proceso.";
        exit;
    }

    return redirect()->to('Carrito_controller/verificarTA/' . $id_cabecera);
}

//Aqui va el xml de factura para enviar a ARCA
//re copiar abajo $TA,$id_cabecera
public function facturar_tipo_A($TA = null,$id_cabecera = null) {
    $session = session();
    $session->remove(['tipo_factura']);
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
    
    $token = $TA['token'];
    //print_r($token);

    //print_r($TA['sign']);
    $sign = $TA['sign'];
    //print_r($sign); 

    $curl2 = curl_init();

    curl_setopt_array($curl2, array(
    CURLOPT_URL => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?op=FECompUltimoAutorizado',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ar="http://ar.gov.afip.dif.FEV1/">
    <soapenv:Header/>
    <soapenv:Body>
        <ar:FECompUltimoAutorizado>
            <ar:Auth>
                <ar:Token>' . $token . '</ar:Token>
                <ar:Sign>' . $sign . '</ar:Sign>
                <ar:Cuit>20369557263</ar:Cuit>
            </ar:Auth>
            <ar:PtoVta>4</ar:PtoVta>
            <ar:CbteTipo>1</ar:CbteTipo>
        </ar:FECompUltimoAutorizado>
    </soapenv:Body>
    </soapenv:Envelope>
    ',
        CURLOPT_HTTPHEADER => array(
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: http://ar.gov.afip.dif.FEV1/FECompUltimoAutorizado',
        'Cookie: f5avraaaaaaaaaaaaaaaa_session_=BEOJDNNOHIPLFGLHAMDOKCCGDEPCHDODGPFBGCEIHDPKBJFLFFNCGAGGPBMNCOHIINGDGDMMKAFOHCKFPPNAJGJNHGLDBPGNAPHONLHOAPLIMDHCHACMKOKHNOBHOHPL; TS0122d503=01229bd671776c2a22dc12ec69133d3eae55024cb502ad60642444ed6bdc8e2e89e58867b55e3cf976f4460faa4d14afa060e5516a'
        ),
        CURLOPT_SSL_CIPHER_LIST => 'DEFAULT:@SECLEVEL=1', // 🔧 Esta es la línea nueva para arreglar la conexion de arca
    ));

    $response2 = curl_exec($curl2);
    curl_close($curl2);
    // Cargar el XML
    $xml = simplexml_load_string($response2);
    if ($xml === false) {
        echo "Error al cargar el XML desde la respuesta de AFIP.<br>";
        echo "Contenido de respuesta:<br><pre>" . htmlspecialchars($response2) . "</pre>";
        exit;
    }
    // Registrar los namespaces
    $namespaces = $xml->getNamespaces(true);

    // Acceder al Body usando el namespace 'soap'
    $body = $xml->children($namespaces['soap'])->Body;

    // El contenido de 'FECompUltimoAutorizadoResponse' está en el default namespace (sin prefijo), se accede con ''
    $response = $body->children($namespaces[''])->FECompUltimoAutorizadoResponse;

    // Acceder al resultado
    $result = $response->FECompUltimoAutorizadoResult;

    // Obtener el número de comprobante
    $ultimoNumero = (int)$result->CbteNro;

    //sumamos uno al ultimo id_cae para que ARCA lo acepte porque tiene que ser de 1 en 1.
    $id_cae_siguiente = $ultimoNumero + 1;
    //print_r($id_cae_siguiente);
    //exit;
    // Obtener los detalles de la venta
    
    //print_r($cabecera);
    //exit;
    //Obtengo el total de la venta, con descuento o sin
    $total_venta = $cabecera['total_bonificado'];
    $IVA = number_format($total_venta * 0.21, 2, '.', '');
    $totalMasIVA = $total_venta + $IVA;
    //print_r($IVA); exit;
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
            <ar:PtoVta>4</ar:PtoVta> <!-- El punto de venta tiene que ser uno habilitado para Factura Electronica -->
            <ar:CbteTipo>1</ar:CbteTipo> <!-- 1 para FACTURA A, 6 es B y 11 es C -->
        </ar:FeCabReq>
        <ar:FeDetReq>
            <ar:FECAEDetRequest>
                <ar:Concepto>1</ar:Concepto> <!-- Productos -->
                <ar:DocTipo>' . $tipoDoc . '</ar:DocTipo> <!-- 80 CUIT, 99 Consumidor_Final-->
                <ar:DocNro>' . $cuil_cliente . '</ar:DocNro> <!-- 0 para C_final-->
                <ar:CbteDesde>' . $id_cae_siguiente . '</ar:CbteDesde> <!-- Nuevo comprobante: debe ser mayor al anterior -->
                <ar:CbteHasta>' . $id_cae_siguiente . '</ar:CbteHasta> <!-- Debe ser igual al número de <CbteDesde> -->
                <ar:CbteFch>' . $fecha_formateadaF . '</ar:CbteFch> <!-- Fecha dentro del rango N-5 a N+5, 5 dias antes o despues del dia vigente-->
                <ar:ImpTotal>' . $totalMasIVA . '</ar:ImpTotal> <!-- Suma de ImpNeto + IVA -->
                <ar:ImpTotConc>0</ar:ImpTotConc>
                <ar:ImpNeto>' . $total_venta . '</ar:ImpNeto>
                <ar:ImpIVA>' .$IVA.  '</ar:ImpIVA> <!-- Total del iba (ImpNeto * 0.21)--> 
                <ar:MonId>PES</ar:MonId>
                <ar:MonCotiz>1</ar:MonCotiz>
                <ar:CondicionIVAReceptorId>1</ar:CondicionIVAReceptorId> <!--5 para facturas B y C, el 1 para las Facturas A -->
                <ar:Iva>
                    <ar:AlicIva>
                        <ar:Id>5</ar:Id> <!--Codigo de IVA 21% -->
                        <ar:BaseImp>' . $total_venta . '</ar:BaseImp> <!-- Importe Neto de la venta-->
                        <ar:Importe>' .$IVA. '</ar:Importe> <!-- Importe del IVA 21% -->
                    </ar:AlicIva>                
            </ar:Iva>
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
       CURLOPT_SSL_CIPHER_LIST => 'DEFAULT:@SECLEVEL=1', // 🔧 Esta es la línea nueva para arreglar la conexion de arca
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
            'nro_factura'=> $id_cae_siguiente,
            'tipo_factura'=> 'A',
            'cae'       => $cae,
            'vto_cae'   => $cae_vencimiento
        ]); // Muestra los errores si la inserción falla
        //Rescato el id del ultimo cae generado y guardado en la DB.
        $new_cae = $caeModel->getInsertID();
        //asignamos el id_cae a la venta y cambiamos el estado a Facturado.
        $ventaModel->facturado($id_cabecera,$new_cae,$IVA,$totalMasIVA);

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
        $this->generarTicketFacturaA($id_cabecera);
}


//Genera el ticket factura tipo A
public function generarTicketFacturaA($id_cabecera)
{
    // Cargar los modelos necesarios
    $Us_Model = new Usuarios_model;
    $ventaModel = new \App\Models\Cabecera_model();
    $detalleModel = new \App\Models\VentaDetalle_model();
    $productoModel = new \App\Models\Productos_model();
    $clienteModel = new \App\Models\Clientes_model();
    $caeModel = new \App\Models\Cae_model();
    $TiposPrecio_model = new \App\Models\Tipos_precio_model();

    // Obtener los detalles de la venta y el CAE
    $cabecera = $ventaModel->find($id_cabecera);
    $detalle_CAE = $caeModel->find($cabecera['id_cae']);
    $detalles = $detalleModel->where('venta_id', $id_cabecera)->findAll();

    $session = session();
    $cd_efectivo =$session->get('cd_efectivo');
    $cajero_nombre = $session->get('nombre');

    $CostoEnvio = $cabecera['costo_envio'];
   
    // Actualizar el campo costo_envio a 0 porque se muestra una sola vez.
    $ventaModel->update($id_cabecera, ['costo_envio' => 0]);

    // Obtener los productos relacionados
    $productos = [];
    foreach ($detalles as $detalle) {
        $productos[$detalle['producto_id']] = $productoModel->find($detalle['producto_id']);
    }

    $total_venta = $cabecera['total_bonificado'];
    $IVA = $cabecera['iva_cobrado'];
    $SubTotalSinIVA = $total_venta - $IVA;
    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);

    // Obtener el nombre del vendedor    
    $vendedor = $Us_Model->find($cabecera['id_usuario']);
    $nombreVendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';

    // Crear el HTML para la vista previa
    ob_start();
    ?>
    <html>
    <head>
        <style>
            /* Estilos CSS para la factura */
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                width: 220px;
            }
            .ticket {
                width: 100%;
                font-size: 12px;
            }
            h1 {
                font-size: 18px;
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h3 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h4 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            .ticket p {
                margin: 2px 0;
                font-size: 10px;
                font-weight: bold;
                text-align: justify;
            }
            .ticket hr {
                border: 0.5px solid #000;
                margin: 5px 0;
            }
            .ticket .header,
            .ticket .footer {
                text-align: center;
                font-size: 10px;
            }
            .ticket .details {
                margin-top: 3px;
                font-size: 10px;
            }
            .ticket .details td {
                padding: 0px;
            }
            .ticket .details th {
                text-align: left;
                padding-right: 5px;
            }
        </style>
    </head>
    <body>
        <div class="ticket">
            <!-- Cabecera del ticket -->
            <h1>MULTIRRUBRO BLASS</h1>
            <p>GONZALEZ EMMANUEL ALEJANDRO</p>
            <p>CUIT Nro: 20-36955726-3</p>
            <p>Domicilio: Belgrano 2077, Corrientes (3400)</p>
            <p>Cel: 3794-095020</p>
            <p>Inicio de actividades: 01/02/2023</p>
            <p>Ingresos Brutos: 20-36955726-3</p>
            <p>Resp. Inscripto</p>
            <hr>

            <!-- Información de la venta -->
            <p>Fecha y Hora: <?= ($cabecera['tipo_compra'] == 'Pedido') ? date('d-m-Y H:i:s') : $cabecera['fecha'] . ' ' . $cabecera['hora']; ?></p>
            <p>Factura A (Cod 001)</p>
            <p>P.Venta: 004    NroFactura: <?= $detalle_CAE['id_cae'] ?></p>
            
            <p>Cliente: <?= $cliente['cuil'] > 0 ? $cliente['nombre'] . ' Cuil: ' . $cliente['cuil'] : 'Consumidor Final Cuil: 0' ?></p>
            <p>Atendido por: <?= $nombreVendedor ?></p>
            <p>Cajero: <?= $cajero_nombre ?></p>
            <hr>

            <!-- Detalle de la compra -->
            <div class="details" style="width: 100%; font-size: 10px;">
            <h3>Detalle de la Compra</h3>
            <h4>COD: <?= $cabecera['id'] ?></h4>

            <?php foreach ($detalles as $detalle): ?>

                <?php
                    $tipoPrecioId  = (int)($detalle['tipo_precio'] ?? 0);
                    $cantidadPromo = 1;
                    $textoPromo    = '';
                    $indicador     = ''; // ← NUEVO

                    if ($tipoPrecioId > 0) {
                        $promo = $TiposPrecio_model->find($tipoPrecioId);

                        if ($promo) {

                            // Cantidad promo
                            if (isset($promo['cantidad']) && $promo['cantidad'] > 0) {
                                $cantidadPromo = (int)$promo['cantidad'];
                                $textoPromo = ($cantidadPromo > 1) ? " ({$cantidadPromo}u)" : '';
                            }

                            // 🔹 Indicador P / O
                            if (isset($promo['nom_precio'])) {

                                switch ($promo['nom_precio']) {
                                    case 'PROMO1':
                                        $indicador = 'P1';
                                        break;

                                    case 'PROMO2':
                                        $indicador = 'P2';
                                        break;

                                    case 'OUTLET':
                                        $indicador = 'PM';
                                        break;
                                }
                            }
                        }
                    }
                ?>

                <div>
                    <p>
                        <?= $indicador ? '[' . $indicador . '] ' : '' ?>
                        (<?= $detalle['cantidad'] ?>)
                        <?= $productos[$detalle['producto_id']]['nombre'] ?>
                        <!-- <?= $textoPromo ?> -->
                        x $<?= number_format($detalle['precio'], 2) ?>
                    </p>
                </div>

            <?php endforeach; ?>
        </div>

            <!-- Totales 
            <p>Subtotal sin descuentos: $<?= number_format($cabecera['total_venta'], 2) ?></p>
            <p>Descuento:
            <?php
            if ($cabecera['tipo_pago'] == 'Efectivo' || $cabecera['tipo_pago'] == 'Mixto') {
                $descuento = ($cabecera['monto_efectivo'] * $cd_efectivo) - $cabecera['monto_efectivo'];
                echo '$' . number_format($descuento, 2);
            } else {
                echo '$0.00';
            }
            ?>
            </p> -->

            <p>Adicional por Tarjeta:
            <?php
            if (!empty($cabecera['monto_tarjetaC']) && ($cabecera['tipo_pago'] == 'Tarjeta' || $cabecera['tipo_pago'] == 'Mixto')) {
                $adicional = $cabecera['monto_tarjetaC'] - ($cabecera['monto_tarjetaC'] / 1.1);
                echo '$' . number_format($adicional, 2);
            } else {
                echo '$0.00';
            }
            ?>
            </p>
            <p>Sub Total: $<?= number_format($SubTotalSinIVA, 2) ?></p> <!-- Como ya fue actualizado el campo de cabecera de precio bonificado, se resta el IVA para ver el subtotal solo con descuento efectivo y adicion de tarjeta-->  
            <p>IVA: $<?= number_format($cabecera['iva_cobrado'], 2) ?></p>
            <p>Total a Pagar: $<?= number_format($cabecera['total_bonificado'], 2) ?></p>            
            <?php if ($CostoEnvio > 0): ?>
            <p>Costo de Envio: $ <?= $CostoEnvio ?></p>
            <?php endif; ?>            
            <hr>
            
            <p>Reg. Transparencia fiscal al consumidor</p>
            <p>Ley 27.743</p>
            <p>IVA CONTENIDO 21%: $ <?= number_format($cabecera['iva_cobrado'], 2) ?></p>
            <p>IVA CONTENIDO 10.05%: $0.00</p>
            <p>Otros Imp. Nac. Indirectos: $0.00</p>
            <p>Tipo de pago: <?=$cabecera['tipo_pago'];?></p>
            <p>Referencia electronica del Comprobante:</p>
            <p>CAE: <?= $detalle_CAE['cae'] ?>   Vto: <?= date('d-m-Y', strtotime($detalle_CAE['vto_cae'])) ?></p>
            
            <hr>
            
            <!-- Footer -->
            <div class="footer">
                <p>Importante:</p>
                <p>La mercaderia viaja por cuenta y riesgo del comprador.</p>
                <p>Es responsabilidad del cliente controlar su compra antes de salir del local.</p>
                <p>Su compra tiene 48hs para cambio ante fallas previas del producto.</p>
                <p>Instagram: @Blass.Multirrubro</p>
                <p>Facebook: Blass Multirrubro</p>
                <h3>Muchas Gracias por su Compra.!</h3>
            </div>
        </div>
    </body>
    </html>
    <?php
    // Generar el PDF
    $html = ob_get_clean();
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();
    
    // Guardar el archivo PDF en un archivo temporal
    $output = $dompdf->output();
    $tempFolder = 'path/to/temp/folder';  // Ruta de la carpeta temporal
    $tempFile = $tempFolder . '/ticket.pdf';  // Ruta completa del archivo PDF
    
    // Crear la carpeta si no existe
    if (!is_dir($tempFolder)) {
        mkdir($tempFolder, 0777, true);  // Crea la carpeta con permisos 0777 (lectura, escritura y ejecución)
    }
    
    // Guardar el archivo PDF en la carpeta temporal
    file_put_contents($tempFile, $output);
    session()->setFlashdata('msg', 'Imprimiendo Ticket.!');

     // Obtener el perfil del usuario desde la sesión
    $perfil = session()->get('perfil_id');
    
    // Redirigir a una página de confirmación con JavaScript
        echo "<script type='text/javascript'>
        // Descargar el archivo PDF
        window.location.href = '" . base_url('descargar_ticket') . "';

        // Pasar el valor de perfil desde PHP a JavaScript
        var perfil = " . $perfil . "; // Asignar el perfil de PHP a la variable JS

        // Redirigir a la página de referencia después de la descarga o a otra según perfil
        window.setTimeout(function() {
            if (perfil == 3) {
                 window.location.href = '" . base_url('caja') . "'; // Redirigir al perfil 3
            } else if (document.referrer) {
                window.location.href = document.referrer; // Volver a la página anterior
            }
        }, 500);  // 0.5 segundos de espera para asegurar que la descarga termine
        </script>";
        exit;

}

//Aqui va el xml de factura para enviar a ARCA
//re copiar abajo $TA,$id_cabecera
public function facturar_tipo_B($TA = null,$id_cabecera = null) {
    $session = session();
    $session->remove(['tipo_factura']);
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
    
    $token = $TA['token'];
    //print_r($token);

    //print_r($TA['sign']);
    $sign = $TA['sign'];
    //print_r($sign); 

    $curl2 = curl_init();

    curl_setopt_array($curl2, array(
    CURLOPT_URL => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?op=FECompUltimoAutorizado',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ar="http://ar.gov.afip.dif.FEV1/">
    <soapenv:Header/>
    <soapenv:Body>
        <ar:FECompUltimoAutorizado>
            <ar:Auth>
                <ar:Token>' . $token . '</ar:Token>
                <ar:Sign>' . $sign . '</ar:Sign>
                <ar:Cuit>20369557263</ar:Cuit>
            </ar:Auth>
            <ar:PtoVta>4</ar:PtoVta>
            <ar:CbteTipo>6</ar:CbteTipo>
        </ar:FECompUltimoAutorizado>
    </soapenv:Body>
    </soapenv:Envelope>
    ',
        CURLOPT_HTTPHEADER => array(
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: http://ar.gov.afip.dif.FEV1/FECompUltimoAutorizado',
        'Cookie: f5avraaaaaaaaaaaaaaaa_session_=BEOJDNNOHIPLFGLHAMDOKCCGDEPCHDODGPFBGCEIHDPKBJFLFFNCGAGGPBMNCOHIINGDGDMMKAFOHCKFPPNAJGJNHGLDBPGNAPHONLHOAPLIMDHCHACMKOKHNOBHOHPL; TS0122d503=01229bd671776c2a22dc12ec69133d3eae55024cb502ad60642444ed6bdc8e2e89e58867b55e3cf976f4460faa4d14afa060e5516a'
        ),
        CURLOPT_SSL_CIPHER_LIST => 'DEFAULT:@SECLEVEL=1', // 🔧 Esta es la línea nueva para arreglar la conexion de arca
    ));

    $response2 = curl_exec($curl2);
    curl_close($curl2);
    // Cargar el XML
    $xml = simplexml_load_string($response2);
    if ($xml === false) {
        echo "Error al cargar el XML desde la respuesta de AFIP.<br>";
        echo "Contenido de respuesta:<br><pre>" . htmlspecialchars($response2) . "</pre>";
        exit;
    }
    // Registrar los namespaces
    $namespaces = $xml->getNamespaces(true);

    // Acceder al Body usando el namespace 'soap'
    $body = $xml->children($namespaces['soap'])->Body;

    // El contenido de 'FECompUltimoAutorizadoResponse' está en el default namespace (sin prefijo), se accede con ''
    $response = $body->children($namespaces[''])->FECompUltimoAutorizadoResponse;

    // Acceder al resultado
    $result = $response->FECompUltimoAutorizadoResult;

    // Obtener el número de comprobante
    $ultimoNumero = (int)$result->CbteNro;

    //sumamos uno al ultimo id_cae para que ARCA lo acepte porque tiene que ser de 1 en 1.
    $id_cae_siguiente = $ultimoNumero + 1;
    //print_r($id_cae_siguiente);
    //exit;
    // Obtener los detalles de la venta
    
    //Obtengo el total de la venta, con descuento o sin
    $total_venta = $cabecera['total_bonificado']; // ESTE es el total con IVA incluido (lo que vos cobrás).

    // Calcular el neto e IVA como exige ARCA cuando el precio es final IVA incluido
    $neto = round($total_venta / 1.21, 2);
    $IVA = round($total_venta - $neto, 2);

    // Para AFIP/ARCA, el total informado siempre es el total que cobrás
    $totalMasIVA = $total_venta;

    //print_r($totalMasIVA);
    //print_r($neto);
    //print_r($IVA); exit;
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
            <ar:PtoVta>4</ar:PtoVta> <!-- El punto de venta tiene que ser uno habilitado para Factura Electronica -->
            <ar:CbteTipo>6</ar:CbteTipo> <!-- 1 para FACTURA A, 6 es B y 11 es C -->
        </ar:FeCabReq>
        <ar:FeDetReq>
            <ar:FECAEDetRequest>
                <ar:Concepto>1</ar:Concepto> <!-- Productos -->
                <ar:DocTipo>' . $tipoDoc . '</ar:DocTipo> <!-- 80 CUIT, 99 Consumidor_Final-->
                <ar:DocNro>' . $cuil_cliente . '</ar:DocNro> <!-- 0 para C_final-->
                <ar:CbteDesde>' . $id_cae_siguiente . '</ar:CbteDesde> <!-- Nuevo comprobante: debe ser mayor al anterior -->
                <ar:CbteHasta>' . $id_cae_siguiente . '</ar:CbteHasta> <!-- Debe ser igual al número de <CbteDesde> -->
                <ar:CbteFch>' . $fecha_formateadaF . '</ar:CbteFch> <!-- Fecha dentro del rango N-5 a N+5, 5 dias antes o despues del dia vigente-->
                <ar:ImpTotal>' . $totalMasIVA . '</ar:ImpTotal>
                <ar:ImpTotConc>0</ar:ImpTotConc>
                <ar:ImpNeto>' . $neto . '</ar:ImpNeto>
                <ar:ImpIVA>' . $IVA . '</ar:ImpIVA>
                <ar:MonId>PES</ar:MonId>
                <ar:MonCotiz>1</ar:MonCotiz>
                <ar:CondicionIVAReceptorId>5</ar:CondicionIVAReceptorId> <!--5 para facturas B y C, el 1 para las Facturas A -->
                <ar:Iva>
                <ar:AlicIva>
                    <ar:Id>5</ar:Id> <!-- IVA 21% -->
                    <ar:BaseImp>' . $neto . '</ar:BaseImp>
                    <ar:Importe>' . $IVA . '</ar:Importe>
                </ar:AlicIva>
            </ar:Iva>
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
       CURLOPT_SSL_CIPHER_LIST => 'DEFAULT:@SECLEVEL=1', // 🔧 Esta es la línea nueva para arreglar la conexion de arca
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
            'nro_factura'=> $id_cae_siguiente,
            'tipo_factura'=> 'B',
            'cae'       => $cae,
            'vto_cae'   => $cae_vencimiento
        ]); // Muestra los errores si la inserción falla
        //Rescato el id del ultimo cae generado y guardado en la DB.
        $new_cae = $caeModel->getInsertID();
        //asignamos el id_cae a la venta y cambiamos el estado a Facturado.
        $ventaModel->facturado($id_cabecera,$new_cae,$IVA,$total_venta); //Paso $total_venta porque factura B no se modifica el total como en la A

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
        $this->generarTicketFacturaB($id_cabecera);
}


//Genera el ticket factura tipo C
public function generarTicketFacturaB($id_cabecera)
{
    // Cargar los modelos necesarios
    $Us_Model = new Usuarios_model;
    $ventaModel = new \App\Models\Cabecera_model();
    $detalleModel = new \App\Models\VentaDetalle_model();
    $productoModel = new \App\Models\Productos_model();
    $clienteModel = new \App\Models\Clientes_model();
    $caeModel = new \App\Models\Cae_model();
    $TiposPrecio_model = new \App\Models\Tipos_precio_model();

    // Obtener los detalles de la venta y el CAE
    $cabecera = $ventaModel->find($id_cabecera);
    $detalle_CAE = $caeModel->find($cabecera['id_cae']);
    $detalles = $detalleModel->where('venta_id', $id_cabecera)->findAll();

    $session = session();
    $cd_efectivo =$session->get('cd_efectivo');
    $cajero_nombre = $session->get('nombre');

    $CostoEnvio = $cabecera['costo_envio'];
   
    // Actualizar el campo costo_envio a 0 porque se muestra una sola vez.
    $ventaModel->update($id_cabecera, ['costo_envio' => 0]);

    // Obtener los productos relacionados
    $productos = [];
    foreach ($detalles as $detalle) {
        $productos[$detalle['producto_id']] = $productoModel->find($detalle['producto_id']);
    }

    // Obtener la información del cliente
    $cliente = $clienteModel->find($cabecera['id_cliente']);

    // Obtener el nombre del vendedor    
    $vendedor = $Us_Model->find($cabecera['id_usuario']);
    $nombreVendedor = $vendedor ? $vendedor['nombre'] : 'No encontrado';

    // Crear el HTML para la vista previa
    ob_start();
    ?>
    <html>
    <head>
        <style>
            /* Estilos CSS para la factura */
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                width: 220px;
            }
            .ticket {
                width: 100%;
                font-size: 12px;
            }
            h1 {
                font-size: 18px;
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h3 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            h4 {
                text-align: center;
                margin: 3px 0;
                font-weight: bold;
            }
            .ticket p {
                margin: 2px 0;
                font-size: 10px;
                font-weight: bold;
                text-align: justify;
            }
            .ticket hr {
                border: 0.5px solid #000;
                margin: 5px 0;
            }
            .ticket .header,
            .ticket .footer {
                text-align: center;
                font-size: 10px;
            }
            .ticket .details {
                margin-top: 3px;
                font-size: 10px;
            }
            .ticket .details td {
                padding: 0px;
            }
            .ticket .details th {
                text-align: left;
                padding-right: 5px;
            }
        </style>
    </head>
    <body>
        <div class="ticket">
            <!-- Cabecera del ticket -->
            <h1>MULTIRRUBRO BLASS</h1>
            <p>GONZALEZ EMMANUEL ALEJANDRO</p>
            <p>CUIT Nro: 20-36955726-3</p>
            <p>Domicilio: Belgrano 2077, Corrientes (3400)</p>
            <p>Cel: 3794-095020</p>
            <p>Inicio de actividades: 01/02/2023</p>
            <p>Ingresos Brutos: 20-36955726-3</p>
            <p>Resp. Inscripto</p>
            <hr>

            <!-- Información de la venta -->
            <p>Fecha y Hora: <?= ($cabecera['tipo_compra'] == 'Pedido') ? date('d-m-Y H:i:s') : $cabecera['fecha'] . ' ' . $cabecera['hora']; ?></p>
            <p>Factura B (Cod 006)</p>
            <p>P.Venta: 004    NroFactura: <?= $detalle_CAE['id_cae'] ?></p>
            
            <p>Cliente: <?= $cliente['cuil'] > 0 ? $cliente['nombre'] . ' Cuil: ' . $cliente['cuil'] : 'Consumidor Final Cuil: 0' ?></p>
            <p>Atendido por: <?= $nombreVendedor ?></p>
            <p>Cajero: <?= $cajero_nombre ?></p>
            <hr>

            <!-- Detalle de la compra -->
            <div class="details" style="width: 100%; font-size: 10px;">
            <h3>Detalle de la Compra</h3>
            <h4>COD: <?= $cabecera['id'] ?></h4>

            <?php foreach ($detalles as $detalle): ?>

                <?php
                    $tipoPrecioId  = (int)($detalle['tipo_precio'] ?? 0);
                    $cantidadPromo = 1;
                    $textoPromo    = '';
                    $indicador     = ''; // ← NUEVO

                    if ($tipoPrecioId > 0) {
                        $promo = $TiposPrecio_model->find($tipoPrecioId);

                        if ($promo) {

                            // Cantidad promo
                            if (isset($promo['cantidad']) && $promo['cantidad'] > 0) {
                                $cantidadPromo = (int)$promo['cantidad'];
                                $textoPromo = ($cantidadPromo > 1) ? " ({$cantidadPromo}u)" : '';
                            }

                            // 🔹 Indicador P / PM                   

                            if (isset($promo['nom_precio'])) {

                                switch ($promo['nom_precio']) {
                                    case 'PROMO1':
                                        $indicador = 'P1';
                                        break;

                                    case 'PROMO2':
                                        $indicador = 'P2';
                                        break;

                                    case 'OUTLET':
                                        $indicador = 'PM';
                                        break;
                                }
                            }
                        }
                    }
                ?>

                <div>
                    <p>
                        <?= $indicador ? '[' . $indicador . '] ' : '' ?>
                        (<?= $detalle['cantidad'] ?>)
                        <?= $productos[$detalle['producto_id']]['nombre'] ?>
                        <!-- <?= $textoPromo ?> -->
                        x $<?= number_format($detalle['precio'], 2) ?>
                    </p>
                </div>

            <?php endforeach; ?>
        </div>

            <!-- Totales 
            <p>Subtotal sin descuentos: $<?= number_format($cabecera['total_venta'], 2) ?></p>
            <p>Descuento:
            <?php
            if ($cabecera['tipo_pago'] == 'Efectivo' || $cabecera['tipo_pago'] == 'Mixto') {
                $descuento = ($cabecera['monto_efectivo'] * $cd_efectivo) - $cabecera['monto_efectivo'];
                echo '$' . number_format($descuento, 2);
            } else {
                echo '$0.00';
            }
            ?>
            </p> -->

            <p>Adicional por Tarjeta:
            <?php
            if (!empty($cabecera['monto_tarjetaC']) && ($cabecera['tipo_pago'] == 'Tarjeta' || $cabecera['tipo_pago'] == 'Mixto')) {
                $adicional = $cabecera['monto_tarjetaC'] - ($cabecera['monto_tarjetaC'] / 1.1);
                echo '$' . number_format($adicional, 2);
            } else {
                echo '$0.00';
            }
            ?>
            </p>
            <p>Total: $<?= number_format($cabecera['total_bonificado'], 2) ?></p>            
            <?php if ($CostoEnvio > 0): ?>
            <p>Costo de Envio: $ <?= $CostoEnvio ?></p>
            <?php endif; ?>            
            <hr>
            
            <p>Reg. Transparencia fiscal al consumidor</p>
            <p>Ley 27.743</p>
            <p>IVA CONTENIDO: $ <?= number_format($cabecera['iva_cobrado'], 2) ?></p>
            <p>Otros Imp. Nac. Indirectos: $0.00</p>
            <p>Tipo de pago: <?=$cabecera['tipo_pago'];?></p>
            <p>Referencia electronica del Comprobante:</p>
            <p>CAE: <?= $detalle_CAE['cae'] ?>   Vto: <?= date('d-m-Y', strtotime($detalle_CAE['vto_cae'])) ?></p>
            
            <hr>
            
            <!-- Footer -->
            <div class="footer">
                <p>Importante:</p>
                <p>La mercaderia viaja por cuenta y riesgo del comprador.</p>
                <p>Es responsabilidad del cliente controlar su compra antes de salir del local.</p>
                <p>Su compra tiene 48hs para cambio ante fallas previas del producto.</p>
                <p>Instagram: @Blass.Multirrubro</p>
                <p>Facebook: Blass Multirrubro</p>
                <h3>Muchas Gracias por su Compra.!</h3>
            </div>
        </div>
    </body>
    </html>
    <?php
    // Generar el PDF
    $html = ob_get_clean();
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();
    
    // Guardar el archivo PDF en un archivo temporal
    $output = $dompdf->output();
    $tempFolder = 'path/to/temp/folder';  // Ruta de la carpeta temporal
    $tempFile = $tempFolder . '/ticket.pdf';  // Ruta completa del archivo PDF
    
    // Crear la carpeta si no existe
    if (!is_dir($tempFolder)) {
        mkdir($tempFolder, 0777, true);  // Crea la carpeta con permisos 0777 (lectura, escritura y ejecución)
    }
    
    // Guardar el archivo PDF en la carpeta temporal
    file_put_contents($tempFile, $output);
    session()->setFlashdata('msg', 'Imprimiendo Ticket.!');

     // Obtener el perfil del usuario desde la sesión
    $perfil = session()->get('perfil_id');
    
    // Redirigir a una página de confirmación con JavaScript
        echo "<script type='text/javascript'>
        // Descargar el archivo PDF
        window.location.href = '" . base_url('descargar_ticket') . "';

        // Pasar el valor de perfil desde PHP a JavaScript
        var perfil = " . $perfil . "; // Asignar el perfil de PHP a la variable JS

        // Redirigir a la página de referencia después de la descarga o a otra según perfil
        window.setTimeout(function() {
            if (perfil == 3) {
                 window.location.href = '" . base_url('caja') . "'; // Redirigir al perfil 3
            } else if (document.referrer) {
                window.location.href = document.referrer; // Volver a la página anterior
            }
        }, 500);  // 0.5 segundos de espera para asegurar que la descarga termine
        </script>";
        exit;

}


}