<?php

namespace App\Controllers;

use App\Libraries\WsaaClient;
use CodeIgniter\RESTful\ResourceController;

class FacturacionController extends ResourceController
{
    /**
     * Método para obtener el Ticket de Autorización (TA) desde AFIP
     * @return \CodeIgniter\HTTP\Response Devuelve el TA o un error
     */
    public function autenticarAfip()
    {
        try {
            $wsaa = new WsaaClient();
            $ta = $wsaa->obtenerTicketAcceso('wsfe');
            return $this->respond(['TA' => $ta]); // Devuelve el TA en formato JSON
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
