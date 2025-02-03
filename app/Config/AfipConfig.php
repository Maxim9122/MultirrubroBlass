<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AfipConfig extends BaseConfig
{
    public string $wsdl = WRITEPATH . 'wsaa.wsdl';  // Ruta del archivo WSDL
    public string $cert = WRITEPATH . 'ghf.crt';  // Ruta del certificado
    public string $privateKey = WRITEPATH . 'ghf.key';  // Ruta de la clave privada
    public string $passphrase = 'xxxxx';  // Frase de contraseña de la clave privada
    public string $proxyHost = '10.20.152.112';  // IP del proxy
    public string $proxyPort = '80';  // Puerto del proxy
    public string $url = 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms';  // URL del WSAA
}
