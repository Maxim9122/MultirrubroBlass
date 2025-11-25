<?php
namespace App\Models;
use CodeIgniter\Model;

class Chat_model extends Model
{
    protected $DBGroup = 'ChatsDB_Belgrano';
    protected $table = 'chat_interno';
    protected $primaryKey = 'id';
    protected $allowedFields = ['usuario', 'mensaje', 'fecha'];

    // Obtener el último mensaje del chat
    public function getUltimoMensaje() {
        return $this->orderBy('id', 'DESC')->first();
    }

    // Obtener el último mensaje leído por usuario , compara el id del ultimo msj leido
    // id del ultimo mensaje registrado
    public function getUltimoLeido($usuario) {
        return $this->db->table('chat_usuarios')
                        ->where('usuario', $usuario)
                        ->get()
                        ->getRowArray();
    }

    // Actualizar o insertar el último mensaje leído por usuario
    public function setUltimoLeido($usuario, $ultimoID) {
    $db = $this->db->table('chat_usuarios');

    $existe = $db->where('usuario', $usuario)->get()->getRowArray();

    if ($existe) {
        $db->where('usuario', $usuario)->update(['ultimo_leido' => $ultimoID]);
    } else {
        $db->insert(['usuario' => $usuario, 'ultimo_leido' => $ultimoID]);
    }
    }

}
