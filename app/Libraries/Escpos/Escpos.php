<?php

namespace App\Libraries\Escpos;

// Cargar manualmente las clases de escpos-php
require_once __DIR__ . '/src/Mike42/Escpos/Printer.php';
require_once __DIR__ . '/src/Mike42/Escpos/PrintConnectors/PrintConnector.php'; // Interfaz requerida
require_once __DIR__ . '/src/Mike42/Escpos/PrintConnectors/WindowsPrintConnector.php';
require_once __DIR__ . '/src/Mike42/Escpos/PrintConnectors/FilePrintConnector.php';
require_once __DIR__ . '/src/Mike42/Escpos/PrintConnectors/NetworkPrintConnector.php';
require_once __DIR__ . '/src/Mike42/Escpos/CapabilityProfile.php'; // Clase CapabilityProfile
require_once __DIR__ . '/src/Mike42/Escpos/CodePage.php'; // Clase CodePage
require_once __DIR__ . '/src/Mike42/Escpos/PrintBuffers/PrintBuffer.php'; // Interfaz PrintBuffer
require_once __DIR__ . '/src/Mike42/Escpos/PrintBuffers/EscposPrintBuffer.php'; // Clase EscposPrintBuffer

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\CapabilityProfile; // Añadir el uso de CapabilityProfile
use Mike42\Escpos\CodePage; // Añadir el uso de CodePage
use Mike42\Escpos\PrintBuffers\EscposPrintBuffer; // Añadir el uso de EscposPrintBuffer

class Escpos
{
    protected $printer;
    protected $buffer;
    /**
     * Constructor de la clase Escpos.
     *
     * @param string $connectorType Tipo de conector (windows, file, network).
     * @param array $connectorConfig Configuración del conector.
     * @throws \Exception Si el tipo de conector no es válido.
     */
    public function __construct($connectorType = 'windows', $connectorConfig = [])
    {
        switch (strtolower($connectorType)) {
            case 'windows':
                if (!isset($connectorConfig['printer_name'])) {
                    throw new \Exception("El nombre de la impresora es requerido para el conector de Windows.");
                }
                // Cargar el perfil de capacidades
                $profile = CapabilityProfile::load('default');
                $buffer = new EscposPrintBuffer(); // Crear el buffer de impresión
                $this->printer = new Printer(new WindowsPrintConnector($connectorConfig['printer_name']), $profile, $buffer);
                break;

            case 'file':
                if (!isset($connectorConfig['file_path'])) {
                    throw new \Exception("La ruta del archivo es requerida para el conector de archivo.");
                }
                // Cargar el perfil de capacidades
                $profile = CapabilityProfile::load('default');
                $buffer = new EscposPrintBuffer(); // Crear el buffer de impresión
                $this->printer = new Printer(new FilePrintConnector($connectorConfig['file_path']), $profile, $buffer);
                break;

            case 'network':
                if (!isset($connectorConfig['ip']) || !isset($connectorConfig['port'])) {
                    throw new \Exception("La IP y el puerto son requeridos para el conector de red.");
                }
                // Cargar el perfil de capacidades
                $profile = CapabilityProfile::load('default');
                $buffer = new EscposPrintBuffer(); // Crear el buffer de impresión
                $this->printer = new Printer(new NetworkPrintConnector($connectorConfig['ip'], $connectorConfig['port']), $profile, $buffer);
                break;

            default:
                throw new \Exception("Tipo de conector no válido.");
        }
    }

    /**
     * Obtiene la instancia de la impresora.
     *
     * @return Printer
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * Cierra la conexión con la impresora al destruir la instancia.
     */
    public function __destruct()
    {
        if ($this->printer) {
            $this->printer->close();
        }
    }
}