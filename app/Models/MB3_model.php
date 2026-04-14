<?php

namespace App\Models;

use CodeIgniter\Model;

class MB2_model extends Model
{
    protected $DBGroup = 'mb3';
    protected $table = 'productos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre','descripcion', 'imagen' ,'categoria_id', 'precio', 'precio_vta', 'stock','stock_min','eliminado', 'codigo_barra'];
}
