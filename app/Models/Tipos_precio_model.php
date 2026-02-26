<?php
namespace App\Models;
use CodeIgniter\Model;
class Tipos_precio_model extends Model
{
	protected $table = 'tipos_precio';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_prod','cae','nom_precio','precio','cantidad'];

}