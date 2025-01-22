<?php
namespace App\Models;
use CodeIgniter\Model;
class Usuarios_model extends Model
{
	protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'apellido','telefono', 'direccion', 'email', 'pass','perfil_id','baja'];

    public function getUsuario($id){

    	return $this->where('id',$id)->first($id);
    }

    public function updateDatos($id,$datos){

    	return $this->update($id,$datos);
    }

    public function getUsBaja($baja){

    	return $this->where('baja',$baja)->findAll();
    }

}
