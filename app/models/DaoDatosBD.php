<?php
// models/DaoDatosBD.php
require_once __DIR__ . '/../config/Database.php';

class DaoDatosBD {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

   
  

    // Lista de años en los que hay órdenes
    public function añosRegistrados(){
       $listAños = [];

        $sql = "SELECT DISTINCT YEAR(fecha) as anio FROM ventas ORDER BY anio DESC";
        $stmt = $this->db->query($sql);
        $listAños = $stmt->fetchAll();
        return $listAños;
    }


      public function mesesRegistrados($anio) {
   
        $result = [];
        $sql = "SELECT DISTINCT MONTH(fecha) as mes 
            FROM ventas 
            WHERE YEAR(fecha) = ? 
            ORDER BY mes ASC";

    
         $stmt = $this->db->prepare($sql);

 
            $stmt->execute([$anio]);
            $in = 0;
            $list[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($list[$in] as $mes){

                $result[] = $mes['mes'];
                
             $in++;
            }

           
    return $result;

}


public function ComprasXmes(int $anio = null){
        
        $result = [];

        $sql = "SELECT COUNT(*) as totalVentas, MONTH(fecha) as mes FROM ventas WHERE YEAR(fecha) =?
        GROUP BY mes ORDER BY mes ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$anio]);
        $valoresXmes = $stmt->fetchAll();

        

        $sumtotal = 0;
       foreach ($valoresXmes as $row) {
        $result[] = [
        'mes' => $row['mes'],
        'totalVentas' => $row['totalVentas']
    ];
      $sumtotal += $row['totalVentas'];
       }
        $result[] = ["totalComprasAnio" => $sumtotal];

        return $result;
    
    }



     // Ejemplo: obtiene ventas por producto para un mes (mes = número 1-12)
    /**
 * Obtiene el listado de productos vendidos en un mes específico 
 * indicando el nombre del producto y la cantidad total vendida.
 */
public function ComprasXproductos(int $mes, int $anio = null) {
    $anio = $anio ?? (int)date('Y');
    $result = [];
    $resultFinal = [];
    $sql = "SELECT 
                p.id_producto,
                p.nombre AS producto, 
                SUM(dv.cantidad) AS total_vendido,
                p.marca,
                p.modelo,
                p.precio
            FROM detalle_ventas dv
            INNER JOIN productos p ON dv.id_producto = p.id_producto
            INNER JOIN ventas v ON dv.id_venta = v.id_venta
            WHERE MONTH(v.fecha) = ? AND YEAR(v.fecha) = ?
            GROUP BY p.id_producto
            ORDER BY total_vendido DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$mes, $anio]);
    $rows = $stmt->fetchAll();
         
    foreach ($rows as $row) {
        $result = [
            'idProducto'    => $row['id_producto'],
            'producto'      => $row['producto'],
            'total_vendido' => $row['total_vendido'],
            'marca'         => $row['marca'],
            'modelo'        => $row['modelo'],
            'precio'        => $row['precio']
        ];

        $resultFinal[] = $result;

    }
    //echo "-----------G: " . print_r($resultFinal);
    //echo "-----------G: " . print_r($result);
    return $resultFinal;
}

/**
 * Obtiene la cifra total (suma de cantidades) de todos 
 * los productos vendidos en el mes.
 */
public function TotalProductosVendidosMes(int $mes, int $anio = null) {
    $anio = $anio ?? (int)date('Y');

    $sql = "SELECT SUM(dv.cantidad) AS total 
            FROM detalle_ventas dv
            INNER JOIN ventas v ON dv.id_venta = v.id_venta
            WHERE MONTH(v.fecha) = ? AND YEAR(v.fecha) = ?";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$mes, $anio]);
    $row = $stmt->fetch();

    // Retornamos el valor numérico, si es null devolvemos 0
    return $row['total'] ?? 0;
}

public function TotalProductosVendidos(int $anio = null) {
    $anio = $anio ?? (int)date('Y');

    $sql = "SELECT SUM(dv.cantidad) AS total 
            FROM detalle_ventas dv
            INNER JOIN ventas v ON dv.id_venta = v.id_venta
            WHERE YEAR(v.fecha) = ? ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio]);
    $row = $stmt->fetch();

    // Retornamos el valor numérico, si es null devolvemos 0
    return $row['total'] ?? 0;
}

public function TotalVentas(int $anio = null) {
    $anio = $anio ?? (int)date('Y');

    $sql = "SELECT COUNT(*) AS total 
            FROM ventas 
            WHERE YEAR(fecha) = ? ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio]);
    $row = $stmt->fetch();

    // Retornamos el valor numérico, si es null devolvemos 0
    return $row['total'] ?? 0;
}
    

public function comprasXsegmentosMF(int $mes, int $anio): array
{
    $tipoUsuario = 2;
    $sql = "
        SELECT
           c.sexo AS sexo,
           COUNT(*) AS cantidad
           FROM ventas v
            JOIN usuarios c ON v.id_usuario = c.id_usuario AND c.id_tipo = :tipoUsuario
           WHERE MONTH(v.fecha) = :mes
           AND YEAR(v.fecha) = :anio
            GROUP BY c.sexo 
            ORDER BY cantidad DESC;

    ";

    $stmt = $this->db->prepare($sql);

    // Bind de parámetros
    $stmt->bindValue(':tipoUsuario', $tipoUsuario, PDO::PARAM_INT);
    $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);

    $stmt->execute();

    $valores = $stmt->fetchAll();
     
    $list = [];
    $i = 0;
    $total = 0;
    foreach($valores as $row){
       
        $l = [
            'sexo' => $row['sexo'],
            'cantidad' => $row['cantidad']
        ];
        $list[] = $l;
        
        $total += $list[$i]['cantidad'];
        $i++;
    }
    $l = [
            'totalVentas' =>  $total
        ];

        $list[] = $l;
    return $list;
}


public function comprasXsesion(int $mes, int $anio): array
{
    $sql = "
        SELECT
            CASE
                WHEN TIME(fecha) BETWEEN '06:00:00' AND '11:59:59' THEN 'Matutina'
                WHEN TIME(fecha) BETWEEN '12:00:00' AND '17:59:59' THEN 'Vespertina'
                WHEN TIME(fecha) BETWEEN '18:00:00' AND '23:59:59' THEN 'Nocturna'
            END AS sesion,
            COUNT(*) AS cantidad
        FROM ventas
        WHERE MONTH(fecha) = :mes
          AND YEAR(fecha) = :anio
        GROUP BY sesion
        ORDER BY cantidad DESC;
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapa base
    $resultado = [
        'Matutina'   => 0,
        'Vespertina' => 0,
        'Nocturna'   => 0
    ];

    foreach ($rows as $row) {
        $resultado[$row['sesion']] = (int) $row['cantidad'];
    }

    // Formato final
    return [
        ['sesion' => 'Matutina',   'cantidad' => $resultado['Matutina']],
        ['sesion' => 'Vespertina', 'cantidad' => $resultado['Vespertina']],
        ['sesion' => 'Nocturna',   'cantidad' => $resultado['Nocturna']]
    ];
}

public function ComprasPorCiudadAnual(int $anio = null, $sexo)
{
    $result = [];

    $sql = "
        SELECT  
    u.ciudad,
    u.Provincia AS provincia,
    COUNT(v.id_venta) AS totalCompras,
    COUNT(DISTINCT u.id_usuario) AS totalClientes
FROM usuarios u
INNER JOIN ventas v ON v.id_usuario = u.id_usuario
WHERE u.id_tipo = 2
  AND YEAR(v.fecha) = ?
  AND u.sexo = ?
GROUP BY u.ciudad, u.Provincia
ORDER BY totalCompras DESC;
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $sexo]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($datos as $row) {
        $result[] = [
            'ciudad'        => $row['ciudad'],
            'provincia'     => $row['provincia'],
            'totalCompras'  => $row['totalCompras'],
            'totalClientes' => $row['totalClientes']
        ];
    }

    return $result;
}

public function ComprasPorCiudadMensual(int $mes = null, $anio = null)
{
    $result = [];

    $sql = "
        SELECT 
    u.ciudad,
    u.Provincia AS provincia,
    COUNT(v.id_venta) AS totalCompras,
    (
        SELECT COUNT(*)
        FROM usuarios u2
        WHERE u2.id_tipo = 2
          AND u2.ciudad = u.ciudad
          AND u2.Provincia = u.Provincia
    ) AS totalClientes
FROM usuarios u

LEFT JOIN ventas v 
    ON v.id_usuario = u.id_usuario
    AND YEAR(v.fecha) = ?
    AND MONTH(v.fecha) = ?
    WHERE u.id_tipo = 2
GROUP BY u.ciudad, u.Provincia
ORDER BY totalCompras DESC;

    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $mes]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($datos as $row) {
        $result[] = [
            'ciudad'        => $row['ciudad'],
            'provincia'     => $row['provincia'],
            'totalCompras'  => $row['totalCompras'],
            'totalClientes' => $row['totalClientes']
        ];
    }

    return $result;
}


public function ComprasXproductosAnual(int $anio = null) {
    $anio = $anio ?? (int)date('Y');
    $result = [];
    $resultFinal = [];
    $sql = "SELECT 
    p.id_producto,
    p.nombre AS producto, 
    SUM(dv.cantidad) AS total_vendido,
    p.marca,
    p.modelo,
    p.precio
FROM detalle_ventas dv
INNER JOIN productos p 
    ON dv.id_producto = p.id_producto
INNER JOIN ventas v 
    ON dv.id_venta = v.id_venta
WHERE YEAR(v.fecha) = ?
GROUP BY 
    p.id_producto,
    p.nombre,
    p.marca,
    p.modelo,
    p.precio
ORDER BY total_vendido DESC;
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio]);
    $rows = $stmt->fetchAll();
         
    $sumtotal = 0;
    foreach ($rows as $row) {
        $result = [
            'idProducto'    => $row['id_producto'],
            'producto'      => $row['producto'],
            'total_vendido' => $row['total_vendido'],
            'marca'         => $row['marca'],
            'modelo'        => $row['modelo'],
            'precio'        => $row['precio']
        ];

        $resultFinal[] = $result;

        $sumtotal += $row['total_vendido'];

    }

    $result = [
            "totalComprasXproductAnio" => $sumtotal
        ];

        $resultFinal[] = $result;

    //echo "-----------G: " . print_r($resultFinal);
    //echo "-----------G: " . print_r($result);
    return $resultFinal;
}


public function comprasXsegmentosMFanio(int $anio): array
{
    $tipoUsuario = 2;
    $sql = "
        SELECT
           c.sexo AS sexo,
           COUNT(*) AS cantidad
           FROM ventas v
            JOIN usuarios c ON v.id_usuario = c.id_usuario AND c.id_tipo = :tipoUsuario
           WHERE YEAR(v.fecha) = :anio
            GROUP BY c.sexo 
            ORDER BY cantidad DESC;

    ";

    $stmt = $this->db->prepare($sql);

    // Bind de parámetros
    $stmt->bindValue(':tipoUsuario', $tipoUsuario, PDO::PARAM_INT);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);

    $stmt->execute();

    $valores = $stmt->fetchAll();
     
    $list = [];
    $i = 0;
    $total = 0;
    foreach($valores as $row){
       
        $l = [
            'sexo' => $row['sexo'],
            'cantidad' => $row['cantidad']
        ];
        $list[] = $l;
        
        $total += $row['cantidad'];
        
    }
    $l = [
            'totalVentas' =>  $total
        ];

        $list[] = $l;
    return $list;
}

public function totalClientesXsegmentosAnio(int $anio): array
{
    $tipoUsuario = 2;
    $sql = "
        SELECT
           sexo ,
           COUNT(*) AS cantidad
           FROM usuarios WHERE id_tipo = :tipoUsuario
            GROUP BY sexo 
            ORDER BY cantidad DESC;

    ";
    
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':tipoUsuario', $tipoUsuario, PDO::PARAM_INT);
    // Bind de parámetros
    
    

    $stmt->execute();

    $valores = $stmt->fetchAll();
     
    $list = [];
    $i = 0;
    $total = 0;
    foreach($valores as $row){
       
        $l = [
            'sexo' => $row['sexo'],
            'cantidad' => $row['cantidad']
        ];
        $list[] = $l;
        
        $total += $row['cantidad'];
        
    }
    $l = [
            'totalClientes' =>  $total
        ];

        $list[] = $l;
    return $list;
}

public function comprasXciudadAnual(int $anio = null)
{
    $result = [];

    $sql = "
        SELECT 
    u.ciudad,
    u.Provincia AS provincia,
    COUNT(v.id_venta) AS totalCompras,
    COUNT(DISTINCT u.id_usuario) AS totalClientes
FROM usuarios u
INNER JOIN ventas v 
    ON v.id_usuario = u.id_usuario
WHERE u.id_tipo = 2
AND YEAR(v.fecha) = ?
GROUP BY 
    u.ciudad,
    u.Provincia
ORDER BY totalCompras DESC;

    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $sumtotal = 0;
    foreach ($datos as $row) {
        $result[] = [
            'ciudad'        => $row['ciudad'],
            'provincia'     => $row['provincia'],
            'totalCompras'  => $row['totalCompras'],
            'totalClientes' => $row['totalClientes']
        ];

        $sumtotal += $row['totalCompras'];
    }

    $result[] = ["totalComprasAnio" => $sumtotal];

    

    return $result;
}

public function comprasXmesAnio(int $anio = null){
        
        $result = [];

        $sql = "SELECT COUNT(*) as totalVentas, MONTH(fecha) as mes FROM ventas WHERE YEAR(fecha) =?
        GROUP BY mes ORDER BY totalVentas DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$anio]);
        $valoresXmes = $stmt->fetchAll();

        

        $sumtotal = 0;
       foreach ($valoresXmes as $row) {
        $result[] = [
        'mes' => $row['mes'],
        'totalVentas' => $row['totalVentas']
    ];
      $sumtotal += $row['totalVentas'];
       }
        $result[] = ["totalComprasAnio" => $sumtotal];

        return $result;
    
    }


}
