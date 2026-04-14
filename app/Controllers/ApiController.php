<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ApiController extends Controller
{
 public function uploadImage()
{
    $img = $this->request->getFile('imagen');

    if (!$img || !$img->isValid()) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'No se recibió imagen válida']);
    }

    $nombre = $img->getClientName();
    $destino = FCPATH . 'assets/uploads';

    $img->move($destino, $nombre);

    return $this->response->setJSON(['success' => true, 'archivo' => $nombre]);
}
    
}