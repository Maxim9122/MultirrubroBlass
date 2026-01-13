<?php
namespace App\Models;
use CodeIgniter\Model;
class NotaCredito_model extends Model
{
	protected $table = 'nota_credito';
    protected $primaryKey = 'id_notaCred';
    protected $allowedFields = ['nro_notaCred','tipo_FactNotaCred','cae_notaCred','vto_notaCred'];
}