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
    $session = session();
    $usuario = $session->get('nombre');

    $model = new Chat_model();

    // Rango del día
    $hoy = date('Y-m-d');
    $inicio = $hoy . ' 00:00:00';
    $fin    = $hoy . ' 23:59:59';

    // Obtener último leido para este usuario (0 si no hay registro)
    $ultimoLeidoRow = $model->getUltimoLeido($usuario);
    $ultimoLeido = $ultimoLeidoRow['ultimo_leido'] ?? 0;

    // 1) Mensajes del día que YA FUERON leídos (id <= ultimo_leido)
    $mensajesLeidosHoy = [];
    if ($ultimoLeido > 0) {
        $mensajesLeidosHoy = $model
            ->where('fecha >=', $inicio)
            ->where('fecha <=', $fin)
            ->where('id <=', $ultimoLeido)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    // 2) Mensajes del día que SON NUEVOS para este usuario (id > ultimo_leido)
    $mensajesNuevosHoy = $model
        ->where('fecha >=', $inicio)
        ->where('fecha <=', $fin)
        ->where('id >', $ultimoLeido)
        ->orderBy('id', 'ASC')
        ->findAll();

    // Respuesta: dos grupos separados + ultimoLeido por si lo necesitás en la vista
    return $this->response->setJSON([
        'leidosHoy'    => $mensajesLeidosHoy,
        'nuevosHoy'    => $mensajesNuevosHoy,
        'ultimoLeido'  => $ultimoLeido
    ]);
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

    // Último leido por el usuario
    $ultimoLeido = $model->getUltimoLeido($usuarioActual)['ultimo_leido'] ?? 0;

    // Limites de hoy
    $hoy = date('Y-m-d');
    $inicio = $hoy . ' 00:00:00';
    $fin    = $hoy . ' 23:59:59';

    // Mensajes nuevos SOLO de hoy y de otros usuarios
    $mensajesNuevos = $model
        ->where('id >', $ultimoLeido)
        ->where('usuario !=', $usuarioActual)
        ->where('fecha >=', $inicio)
        ->where('fecha <=', $fin)
        ->orderBy('id', 'ASC')
        ->findAll();

    // Nombre del último usuario
    $nombreUsuario = '';
    if (!empty($mensajesNuevos)) {
        $ultimoMensaje = end($mensajesNuevos);
        $nombreUsuario = $ultimoMensaje['usuario'];
    }

    // Último ID global (por si lo necesitás)
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
