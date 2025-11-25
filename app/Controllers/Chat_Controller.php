<?php

namespace App\Controllers;

use App\Models\Chat_model;
use CodeIgniter\Controller;

class Chat_Controller extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    // ---------- LISTAR MENSAJES DEL DÍA ----------
    public function listar()
    {
        $model = new Chat_model();

        $hoy = date('Y-m-d');
        $inicio = $hoy . ' 00:00:00';
        $fin    = $hoy . ' 23:59:59';

        $mensajes = $model
            ->where('fecha >=', $inicio)
            ->where('fecha <=', $fin)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $this->response->setJSON($mensajes);
    }

    // ---------- ENVIAR MENSAJE ----------
    public function enviar()
    {
        $session = session();
        $usuario = $session->get('nombre');

        $ahora = new \DateTime('now', new \DateTimeZone('America/Argentina/Buenos_Aires'));
        $fechaLocal = $ahora->format('Y-m-d H:i:s');

        $model = new Chat_model();
        $model->save([
            'usuario' => $usuario,
            'mensaje' => $this->request->getPost('mensaje'),
            'fecha'   => $fechaLocal
        ]);

        return $this->response->setJSON(['status' => 'ok']);
    }

    // ---------- OBTENER ÚLTIMO MENSAJE ----------
    public function ultimo()
    {
        $model = new Chat_model();
        $row = $model->orderBy('id', 'DESC')->first();

        return $this->response->setJSON(['id' => $row['id'] ?? 0]);
    }

    // ---------- VERIFICAR SI HAY MENSAJES NUEVOS PARA EL USUARIO ----------
    public function nuevos()
    {
    $session = session();
    $usuarioActual = $session->get('nombre');

    $model = new Chat_model();

    // Último mensaje leído por el usuario
    $ultimoLeido = $model->getUltimoLeido($usuarioActual)['ultimo_leido'] ?? 0;

    // Mensajes nuevos de otros usuarios
    $mensajesNuevos = $model
        ->where('id >', $ultimoLeido)
        ->where('usuario !=', $usuarioActual)
        ->orderBy('id', 'ASC')
        ->findAll();

    // Obtener el nombre del usuario del último mensaje no leído
    $nombreUsuario = '';
    if (!empty($mensajesNuevos)) {
        $ultimoMensaje = end($mensajesNuevos);
        $nombreUsuario = $ultimoMensaje['usuario'];
    }

    // Último ID del chat
    $ultimoMensajeGlobal = $model->getUltimoMensaje();

    return $this->response->setJSON([
        'hayNuevos' => !empty($mensajesNuevos),
        'ultimoID' => $ultimoMensajeGlobal['id'] ?? 0,
        'usuario' => $nombreUsuario
    ]);
    }

    // ---------- MARCAR MENSAJES COMO LEÍDOS ----------
    public function marcarLeido()
    {
        $session = session();
        $usuario = $session->get('nombre');
        $ultimoID = $this->request->getPost('ultimoID');

        $model = new Chat_model();
        $model->setUltimoLeido($usuario, $ultimoID);

        return $this->response->setJSON(['status' => 'ok']);
    }
}
