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
use App\Models\NotaCredito_model;


class NotasDe_Creditos_controller extends Controller{

	public function __construct(){
           helper(['form', 'url']);
	}

	//Verifica que todo este bien para Facturar
public function verificarTA_NotaCredito($id_cabecera = null) {
 
    $ventaModel = new \App\Models\Cabecera_model();
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    //print_r($cabecera);
    //exit;    
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

    return redirect()->to('NotasDe_Creditos_controller/generarTA/'. $id_cabecera);
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

        //Rescato el tipo de factura segun su id_cabecera
       $tipo_factura=$ventaModel->getTipoFactura($id_cabecera);
       //print_r($tipo_factura);exit;
        //Manda a facturar con el TA y el id de cabecera, y redireccion con msg si es venta o pedido facturado con exito.
       if ($tipo_factura == 'A'){        
         $this->NotaCredito_tipo_A($TA,$id_cabecera);
       }
       if ($tipo_factura == 'B'){        
         $this->NotaCredito_tipo_B($TA,$id_cabecera);
       }
        
        session()->setFlashdata('msg', 'La Nota de Credito se realizo con Exito.!');
        return redirect()->to(base_url('compras'));
    } else {
        // El ticket ha expirado, eliminar el archivo y generar uno nuevo
        //unlink($taPath);
        rename($taPath, $taPath . ".bak");
        //echo "El ticket ha expirado y se eliminó TA.xml. Generando uno nuevo...<br>";
        return redirect()->to('NotasDe_Creditos_controller/generarTA/'. $id_cabecera);
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

    return redirect()->to('NotasDe_Creditos_controller/verificarTA_NotaCredito/' . $id_cabecera);
	}

	//Aqui va el xml de factura para enviar a ARCA
//re copiar abajo $TA,$id_cabecera
public function NotaCredito_tipo_A($TA = null,$id_cabecera = null) {
    $session = session();    
    $ventaModel = new \App\Models\Cabecera_model();
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    //print_r($cabecera);
    //exit;   
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        } 
    if ($id_cabecera === null) {
        //session()->setFlashdata('msgEr', 'No se puede facturar sin enviar una Venta.');
        return redirect()->to(base_url('catalogo'));
    }

    $notaCredModel = new \App\Models\NotaCredito_model();
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
            <ar:CbteTipo>3</ar:CbteTipo>
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
    $id_siguienteNotaCred = $ultimoNumero + 1;

    //Numero de factura en Arca
    $NumFactura = $ventaModel->NumFactura($id_cabecera);
    //print_r($NumFactura);
    //exit;
    // Obtener los detalles de la venta   
    
    //Obtengo el total de la venta, con descuento o sin
    $total_venta = $cabecera['total_bonificado'] - $cabecera['iva_cobrado']; //Le resto el iva cobrado para la Nota de credito
    //print_r($total_venta); exit;
    
    $IVA = number_format($total_venta * 0.21, 2, '.', '');
    $totalMasIVA = $total_venta + $IVA;
    //print_r($IVA); exit;
    //Obtengo la fecha
    $fecha_YMD = date('Ymd');
    ///print_r($fecha_formateadaF);    
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
            <ar:CbteTipo>3</ar:CbteTipo> <!-- Nota Credito, 3 para FACTURA A, 8 es B -->
        </ar:FeCabReq>
        <ar:FeDetReq>
            <ar:FECAEDetRequest>
                <ar:Concepto>1</ar:Concepto> <!-- Productos -->
                <ar:DocTipo>' . $tipoDoc . '</ar:DocTipo> <!-- 80 CUIT, 99 Consumidor_Final-->
                <ar:DocNro>' . $cuil_cliente . '</ar:DocNro> <!-- 0 para C_final-->

                <!-- Asociación a la factura original -->
                        <ar:CbtesAsoc>
                            <ar:CbteAsoc>
                                <ar:Tipo>1</ar:Tipo> <!-- 1 = Factura A -->
                                <ar:PtoVta>4</ar:PtoVta> <!-- Mismo punto de venta -->
                                <ar:Nro>' .$NumFactura. '</ar:Nro> <!-- Número de la factura A original a dar de baja -->
                            </ar:CbteAsoc>
                        </ar:CbtesAsoc>

                <!-- Datos de la nueva nota de crédito -->
                <ar:CbteDesde>' . $id_siguienteNotaCred . '</ar:CbteDesde> <!-- Correlativo (nuevo número de nota de crédito)-->
                <ar:CbteHasta>' . $id_siguienteNotaCred . '</ar:CbteHasta>
                <ar:CbteFch>' . $fecha_YMD . '</ar:CbteFch> <!-- Fecha dentro del rango N-5 a N+5, 5 dias antes o despues del dia vigente-->
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
        $notaCredModel->save([
            'nro_notaCred'=> $id_siguienteNotaCred,
            'tipo_FactNotaCred'=> 'A',
            'cae_notaCred'       => $cae,
            'vto_notaCred'   => $cae_vencimiento
        ]); // Muestra los errores si la inserción falla
        //Rescato el id de la ultima Nota de credito generada y guardado en la DB.
        $Nro_new_NotaCred = $notaCredModel->getInsertID();
        $NumCae_Vta = $cabecera['id_cae'];
        //Guardamos el numero de Nota de credito en el Numero de Cae correspondiente
        $caeModel->update($NumCae_Vta,['id_notaCred' => $Nro_new_NotaCred]);
        //cambiamos el estado de la venta a Nota_Credito.
        $ventaModel->update($id_cabecera,['estado' => 'Nota_Credito']);

		session()->setFlashdata('msg', 'Nota de Credito generada con Exito!');    
    }else{ 
     
        //Si tiene una R en resultado redirecciona por rechazado
        session()->setFlashdata('msgEr', 'No se pudo generar la Nota de Credito, Motivo: ' . $mensaje_error . '--Reintente');
        return redirect()->to(base_url('compras'));
    }
          
        //$this->generarTicketFacturaA($id_cabecera);
        return redirect()->to(base_url('compras'));
    }

    	//Aqui va el xml de factura para enviar a ARCA
//re copiar abajo $TA,$id_cabecera
public function NotaCredito_tipo_B($TA = null,$id_cabecera = null) {
    $session = session();    
    $ventaModel = new \App\Models\Cabecera_model();
    // Obtener los detalles de la venta
    $cabecera = $ventaModel->find($id_cabecera);
    //print_r($cabecera);
    //exit;   
    $session = session();
        // Verifica si el usuario está logueado
        if (!$session->has('id')) { 
            return redirect()->to(base_url('login')); // Redirige al login si no hay sesión
        } 
    if ($id_cabecera === null) {
        //session()->setFlashdata('msgEr', 'No se puede facturar sin enviar una Venta.');
        return redirect()->to(base_url('catalogo'));
    }

    $notaCredModel = new \App\Models\NotaCredito_model();
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
            <ar:CbteTipo>8</ar:CbteTipo>
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
    $id_siguienteNotaCred = $ultimoNumero + 1;

    //Numero de factura en Arca
    $NumFactura = $ventaModel->NumFactura($id_cabecera);
    //print_r($NumFactura);
    //exit;
    // Obtener los detalles de la venta   
    
    //Obtengo el total de la venta, con descuento o sin
    $total_venta = $cabecera['total_bonificado']; // ESTE es el total con IVA incluido (lo que vos cobrás).

    // Calcular el neto e IVA como exige ARCA cuando el precio es final IVA incluido
    $neto = round($total_venta / 1.21, 2);
    $IVA = round($total_venta - $neto, 2);

    // Para AFIP/ARCA, el total informado siempre es el total que cobrás
    $totalMasIVA = $total_venta;
    //print_r($IVA); exit;
    //Obtengo la fecha
    $fecha_YMD = date('Ymd');
    ///print_r($fecha_formateadaF);    
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
            <ar:CbteTipo>8</ar:CbteTipo> <!-- Nota Credito, 3 para FACTURA A, 8 es B -->
        </ar:FeCabReq>
        <ar:FeDetReq>
            <ar:FECAEDetRequest>
                <ar:Concepto>1</ar:Concepto> <!-- Productos -->
                <ar:DocTipo>' . $tipoDoc . '</ar:DocTipo> <!-- 80 CUIT, 99 Consumidor_Final-->
                <ar:DocNro>' . $cuil_cliente . '</ar:DocNro> <!-- 0 para C_final-->

                <!-- Asociación a la factura original -->
                        <ar:CbtesAsoc>
                            <ar:CbteAsoc>
                                <ar:Tipo>6</ar:Tipo> <!-- 1 = Factura B -->
                                <ar:PtoVta>4</ar:PtoVta> <!-- Mismo punto de venta -->
                                <ar:Nro>' .$NumFactura. '</ar:Nro> <!-- Número de la factura A original a dar de baja -->
                            </ar:CbteAsoc>
                        </ar:CbtesAsoc>

                <!-- Datos de la nueva nota de crédito -->
                <ar:CbteDesde>' . $id_siguienteNotaCred . '</ar:CbteDesde> <!-- Correlativo (nuevo número de nota de crédito)-->
                <ar:CbteHasta>' . $id_siguienteNotaCred . '</ar:CbteHasta>
                <ar:CbteFch>' . $fecha_YMD . '</ar:CbteFch> <!-- Fecha dentro del rango N-5 a N+5, 5 dias antes o despues del dia vigente-->
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
        $notaCredModel->save([
            'nro_notaCred'=> $id_siguienteNotaCred,
            'tipo_FactNotaCred'=> 'B',
            'cae_notaCred'       => $cae,
            'vto_notaCred'   => $cae_vencimiento
        ]); // Muestra los errores si la inserción falla
        //Rescato el id de la ultima Nota de credito generada y guardado en la DB.
        $Nro_new_NotaCred = $notaCredModel->getInsertID();
        $NumCae_Vta = $cabecera['id_cae'];
        //Guardamos el numero de Nota de credito en el Numero de Cae correspondiente
        $caeModel->update($NumCae_Vta,['id_notaCred' => $Nro_new_NotaCred]);
        //cambiamos el estado de la venta a Nota_Credito.
        $ventaModel->update($id_cabecera,['estado' => 'Nota_Credito']);

		session()->setFlashdata('msg', 'Nota de Credito generada con Exito!');    
    }else{ 
     
        //Si tiene una R en resultado redirecciona por rechazado
        session()->setFlashdata('msgEr', 'No se pudo generar la Nota de Credito, Motivo: ' . $mensaje_error . '--Reintente');
        return redirect()->to(base_url('compras'));
    }
          
        //$this->generarTicketFacturaA($id_cabecera);
        return redirect()->to(base_url('compras'));
    }
}
