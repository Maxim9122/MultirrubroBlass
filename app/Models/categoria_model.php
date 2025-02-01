<?php
namespace App\Models;
use CodeIgniter\Model;
class categoria_model extends Model
{
	protected $table = 'categorias';
    protected $primaryKey = 'categoria_id';
    protected $allowedFields = ['descripcion'];
    public function getCategoria(){

    	return $this->findAll();
    }

}