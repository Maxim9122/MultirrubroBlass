<?php
namespace App\Models;
use CodeIgniter\Model;
class Cabecera_model extends Model
{
	protected $table = 'ventas_cabecera';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id','fecha', 'hora_registro', 'hora' ,'id_cliente', 'total_venta', 'tipo_pago'];

    public function getVentasCabecera(){
      $db = db_connect();
      $builder = $db->table('ventas_cabecera u');
      $builder->join('usuarios d','u.usuario_id = d.id');
      $ventas = $builder->get();
      return $ventas;
    }

    public function getVentasConClientes()
    {
        // Conectarse a la base de datos
        $db = db_connect();
        // Construir la consulta con el join
        $builder = $db->table($this->table . ' u');
        $builder->select('u.id, c.nombre, c.telefono, u.total_venta, u.fecha, u.hora, u.tipo_pago');
        $builder->join('cliente c', 'u.id_cliente = c.id_cliente');
        
        // Ejecutar la consulta y retornar el resultado como array
        $ventas = $builder->get();
        return $ventas->getResultArray();
    }

    public function getVentasPorClienteYFecha($idCliente, $fechaHoy)
    {
        // Conectarse a la base de datos
        $db = db_connect();
        // Construir la consulta con join y filtros
        $builder = $db->table($this->table . ' u');
        $builder->select('u.id, d.nombre, d.apellido, d.telefono, d.direccion, u.total_venta, u.fecha, u.hora, u.tipo_pago');
        $builder->where('u.id_cliente', $idCliente); // Filtrar por cliente
        $builder->where('u.fecha', $fechaHoy);       // Filtrar por fecha
        $builder->join('usuarios d', 'u.id_cliente = d.id'); // Relación con usuarios

        // Ejecutar la consulta y retornar los resultados como array
        $ventas = $builder->get();
        return $ventas->getResultArray();
    }
 
     // Obtener los detalles de una venta específica
     public function getDetallesVenta($idVenta)
     {
         $db = db_connect();
         $builder = $db->table('ventas_detalle u');
         $builder->select('d.id, d.nombre, u.cantidad, u.precio, u.total');
         $builder->where('u.venta_id', $idVenta);
         $builder->join('productos d', 'u.producto_id = d.id');
         $result = $builder->get();
 
         return $result->getResultArray(); // Devuelve todos los resultados como array
     }
}