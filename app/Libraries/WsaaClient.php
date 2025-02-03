<?php

namespace App\Libraries;

use Config\AfipConfig;
use SoapClient;
use SoapFault;
use SimpleXMLElement;

class WsaaClient
{
    private string $wsdl;
    private string $cert;
    private string $privateKey;
    private string $passphrase;
    private string $proxyHost;
    private string $proxyPort;
    private string $url;

    /**
     * Constructor: Carga la configuración desde `AfipConfig.php`
     */
    public function __construct()
    {
        $config = new AfipConfig();

        $this->wsdl = $config->wsdl;
        $this->cert = $config->cert;
        $this->privateKey = $config->privateKey;
        $this->passphrase = $config->passphrase;
        $this->proxyHost = $config->proxyHost;
        $this->proxyPort = $config->proxyPort;
        $this->url = $config->url;
    }

    /**
     * Genera el archivo `TRA.xml` (Ticket Request Authorization)
     * @param string $service Servicio al que se desea autenticar (ej. `wsfe`)
     */
    private function createTRA($service)
    {
        $TRA = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><loginTicketRequest version="1.0"></loginTicketRequest>');
        $TRA->addChild('header');
        $TRA->header->addChild('uniqueId', time()); // Identificador único basado en la hora actual
        $TRA->header->addChild('generationTime', gmdate('c', time() - 60)); // Hora de generación
        $TRA->header->addChild('expirationTime', gmdate('c', time() + 60)); // Expira en 1 minuto
        $TRA->addChild('service', $service); // Nombre del servicio de AFIP
        $TRA->asXML(WRITEPATH . 'TA.xml'); // Guarda el archivo en `writable/`
    }

    /**
     * Firma digitalmente el `TRA.xml` usando el certificado y clave privada.
     * @return string Retorna el archivo firmado en formato CMS.
     * @throws \Exception Si hay un error en la firma.
     */
    private function signTRA()
    {
        $status = openssl_pkcs7_sign(
            WRITEPATH . 'TA.xml',
            WRITEPATH . 'TRA.tmp',
            "file://" . $this->cert,
            ["file://" . $this->privateKey, $this->passphrase],
            [],
            !PKCS7_DETACHED
        );

        if (!$status) {
            throw new \Exception("Error generando firma PKCS#7");
        }

        // Leer el archivo firmado, eliminando las primeras 4 líneas
        $inf = fopen(WRITEPATH . 'TRA.tmp', "r");
        $i = 0;
        $CMS = "";

        while (!feof($inf)) {
            $buffer = fgets($inf);
            if ($i++ >= 4) {
                $CMS .= $buffer;
            }
        }

        fclose($inf);
        unlink(WRITEPATH . 'TRA.tmp'); // Elimina archivo temporal

        return $CMS;
    }

    /**
     * Realiza la petición SOAP a WSAA de AFIP para obtener el Token de Acceso (TA).
     * @param string $CMS Contenido firmado en formato CMS.
     * @return string Retorna el XML con el `TA.xml`.
     * @throws \Exception Si hay un error en la comunicación con AFIP.
     */
    private function callWSAA($CMS)
    {
        $client = new SoapClient($this->wsdl, [
            'proxy_host'     => $this->proxyHost,
            'proxy_port'     => $this->proxyPort,
            'soap_version'   => SOAP_1_2,
            'location'       => $this->url,
            'trace'          => 1,
            'exceptions'     => true
        ]);

        try {
            $response = $client->loginCms(['in0' => $CMS]);

            // Guardar solicitudes y respuestas para depuración
            file_put_contents(WRITEPATH . "request-loginCms.xml", $client->__getLastRequest());
            file_put_contents(WRITEPATH . "response-loginCms.xml", $client->__getLastResponse());

            return $response->loginCmsReturn;
        } catch (SoapFault $e) {
            log_message('error', 'SOAP Fault: ' . $e->getMessage());
            throw new \Exception("Error en la autenticación con AFIP: " . $e->getMessage());
        }
    }

    /**
     * Función principal para obtener el Ticket de Acceso (TA).
     * @param string $servicio Nombre del servicio de AFIP.
     * @return string El XML del Ticket de Acceso (`TA.xml`).
     * @throws \Exception Si ocurre un error en alguna etapa del proceso.
     */
    public function obtenerTicketAcceso($servicio)
    {
        $this->createTRA($servicio);
        $CMS = $this->signTRA();
        return $this->callWSAA($CMS);
    }
}
