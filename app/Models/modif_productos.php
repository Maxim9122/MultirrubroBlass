<?php
namespace App\Models;
use CodeIgniter\Model;
class modif_productos extends Model
{
	protected $table = 'modif_productos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id','dia_modif','hora_modif','usuario_id','id_prod','stock_anterior','nvo_stock','precio_vta','nvo_precio_vta'];

    public function obtenerHistorialPorFechas(array $filtros = []) {
    $db = db_connect();
    $builder = $db->table('modif_productos p');

    $builder->select("
        p.id,
        p.dia_modif,
        p.hora_modif,
        u.nombre AS nombre_usuario,        
        p.stock_anterior,
        p.nvo_stock AS stock,
        p.precio_vta AS precio_anterior,
        pr.nombre AS nombre_producto,
        pr.imagen,        
        p.nvo_precio_vta As precio_actual
    ");

    $builder->join('productos pr', 'p.id_prod = pr.id');
    $builder->join('usuarios u', 'p.usuario_id = u.id');

    // Si llegan fechas, aplicar filtros
    if (!empty($filtros['desde'])) {
        $fechaDesde = date('Y-m-d', strtotime($filtros['desde']));
        $builder->where("STR_TO_DATE(p.dia_modif, '%d-%m-%Y') >=", $fechaDesde);
    }

    if (!empty($filtros['hasta'])) {
        $fechaHasta = date('Y-m-d', strtotime($filtros['hasta']));
        $builder->where("STR_TO_DATE(p.dia_modif, '%d-%m-%Y') <=", $fechaHasta);
    }

    // Si no se pasó ninguna fecha, no filtrar por fecha y mostrar últimos 100
    $builder->orderBy("STR_TO_DATE(p.dia_modif, '%d-%m-%Y')", 'DESC', false);
    $builder->orderBy("STR_TO_DATE(p.hora_modif, '%H:%i:%s')", 'DESC', false);
    $builder->limit(100);

    return $builder->get()->getResultArray();
}

}