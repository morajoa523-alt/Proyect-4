<?php

class DaoDatosXsegm{

    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

public function ComprasMesSegm(string $sexo, int $anio): array
{
    $anio = $anio ?? (int) date('Y');
    $result = [];

    $sql = "
        SELECT 
    m.mes,
    COALESCE(COUNT(c.id_usuario), 0) AS totalVentas
FROM (
    SELECT 1 AS mes UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL
    SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL
    SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL
    SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
) m
LEFT JOIN ventas v 
    ON MONTH(v.fecha) = m.mes
   AND YEAR(v.fecha) = ?
LEFT JOIN usuarios c 
    ON c.id_usuario = v.id_usuario AND c.id_tipo = 2
   AND c.sexo = ?
GROUP BY m.mes
ORDER BY totalVentas DESC;



    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $sexo]);
    $valoresXmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($valoresXmes as $row) {
        $result[] = [
            'mes' => (int) $row['mes'],
            'totalVentas' => (int) ($row['totalVentas'] ?? 0)
        ];
    }

    //print_r($result);
    return $result;
}

public function comprasXdiaMes(int $mes, int $anio): array
{
    $anio = $anio ?? (int) date('Y');
    $result = [];

    $sql = "
        SELECT 
            DAY(fecha) AS dia,
            COUNT(*) AS totalVentas
        FROM ventas
        WHERE YEAR(fecha) = ?
          AND MONTH(fecha) = ?
        GROUP BY dia
        ORDER BY dia ASC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $mes]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $result[] = [
            'dia'          => (int) $row['dia'],
            'totalVentas' => (int) $row['totalVentas']
        ];
    }

    return $result;
}

public function ComprasXproductos(string $sexo, int $anio): array
{
    $anio = $anio ?? (int) date('Y');
    $resultFinal = [];

    $sql = "
        SELECT 
            p.id_producto,
            p.nombre AS producto,
            COALESCE(SUM(
                CASE 
                    WHEN c.sexo = ? AND YEAR(v.fecha) = ?
                    THEN dv.cantidad
                    ELSE 0
                END
            ), 0) AS total_vendido,
            p.marca,
            p.modelo,
            p.precio
        FROM productos p
        LEFT JOIN detalle_ventas dv 
            ON dv.id_producto = p.id_producto
        LEFT JOIN ventas v 
            ON dv.id_venta = v.id_venta
        LEFT JOIN usuarios c 
            ON c.id_usuario = v.id_usuario AND c.id_tipo = 2
        GROUP BY p.id_producto
        ORDER BY total_vendido DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$sexo, $anio]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $resultFinal[] = [
            'idProducto'    => (int) $row['id_producto'],
            'producto'      => $row['producto'],
            'total_vendido' => (int) $row['total_vendido'],
            'marca'         => $row['marca'],
            'modelo'        => $row['modelo'],
            'precio'        => (float) $row['precio'],
            'sexo'          => $sexo
        ];
    }

    return $resultFinal;
}





public function comprasXsesion(int $anio, string $sexo): array
{
    $sql = "
        SELECT
            CASE
                WHEN TIME(v.fecha) BETWEEN '06:00:00' AND '11:59:59' THEN 'Matutina'
                WHEN TIME(v.fecha) BETWEEN '12:00:00' AND '17:59:59' THEN 'Vespertina'
                WHEN TIME(v.fecha) BETWEEN '18:00:00' AND '23:59:59' THEN 'Nocturna'
            END AS sesion,
            COUNT(v.id_venta) AS cantidad
        FROM ventas v
        INNER JOIN usuarios c ON c.id_usuario = v.id_usuario AND c.id_tipo = 2
        WHERE YEAR(v.fecha) = :anio
          AND c.sexo = :sexo
        GROUP BY sesion
        ORDER BY cantidad DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmt->bindValue(':sexo', $sexo, PDO::PARAM_STR);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ==========================
       MAPA BASE (asegura 3 sesiones)
       ========================== */
    $resultado = [
        'Matutina'   => 0,
        'Vespertina' => 0,
        'Nocturna'   => 0
    ];

    foreach ($rows as $row) {
        if (!empty($row['sesion'])) {
            $resultado[$row['sesion']] = (int) $row['cantidad'];
        }
    }

    /* ==========================
       FORMATO FINAL
       ========================== */
    return [
        ['sesion' => 'Matutina',   'cantidad' => $resultado['Matutina']],
        ['sesion' => 'Vespertina', 'cantidad' => $resultado['Vespertina']],
        ['sesion' => 'Nocturna',   'cantidad' => $resultado['Nocturna']]
    ];
}




public function ComprasXproductosMesSegm(
    int $mes,
    string $sexo,
    int $anio
): array {
    $anio = $anio ?? (int) date('Y');
    $resultFinal = [];

    $sql = "
        SELECT 
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
        INNER JOIN usuarios c 
            ON c.id_usuario = v.id_usuario AND c.id_tipo = 2
        WHERE 
            MONTH(v.fecha) = ?
            AND YEAR(v.fecha) = ?
            AND c.sexo = ?
        GROUP BY p.id_producto
        ORDER BY total_vendido DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$mes, $anio, $sexo]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $resultFinal[] = [
            'idProducto'    => (int) $row['id_producto'],
            'producto'      => $row['producto'],
            'total_vendido' => (int) $row['total_vendido'],
            'marca'         => $row['marca'],
            'modelo'        => $row['modelo'],
            'precio'        => (float) $row['precio']
        ];
    }

    return $resultFinal;
}

/**
 * Obtiene la cifra total (suma de cantidades) de todos 
 * los productos vendidos en el mes.
 */
public function TotalProductosVendidosMesSegm(
    int $mes,
    string $sexo,
    int $anio
): int {
    $anio = $anio ?? (int) date('Y');

    $sql = "
        SELECT COALESCE(SUM(dv.cantidad), 0) AS total
        FROM detalle_ventas dv
        INNER JOIN ventas v 
            ON dv.id_venta = v.id_venta
        INNER JOIN usuarios c 
            ON c.id_usuario = v.id_usuario AND c.id_tipo = 2
        WHERE 
            MONTH(v.fecha) = ?
            AND YEAR(v.fecha) = ?
            AND c.sexo = ?
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$mes, $anio, $sexo]);
    $row = $stmt->fetch();

    return (int) $row['total'];
}

public function comprasXsesionMesSegm($mes, string $sexo, int $anio): array
{
    $sql = "
        SELECT
            CASE
                WHEN TIME(v.fecha) BETWEEN '06:00:00' AND '11:59:59' THEN 'Matutina'
                WHEN TIME(v.fecha) BETWEEN '12:00:00' AND '17:59:59' THEN 'Vespertina'
                WHEN TIME(v.fecha) BETWEEN '18:00:00' AND '23:59:59' THEN 'Nocturna'
            END AS sesion,
            COUNT(v.id_venta) AS cantidad
        FROM ventas v
        INNER JOIN usuarios c ON c.id_usuario = v.id_usuario AND c.id_tipo = 2
        WHERE YEAR(v.fecha) = :anio
          AND MONTH(v.fecha) = :mes
          AND c.sexo = :sexo
        GROUP BY sesion
        ORDER BY cantidad DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
    $stmt->bindValue(':sexo', $sexo, PDO::PARAM_STR);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ==========================
       MAPA BASE (asegura 3 sesiones)
       ========================== */
    $resultado = [
        'Matutina'   => 0,
        'Vespertina' => 0,
        'Nocturna'   => 0
    ];

    foreach ($rows as $row) {
        if (!empty($row['sesion'])) {
            $resultado[$row['sesion']] = (int) $row['cantidad'];
        }
    }

    /* ==========================
       FORMATO FINAL
       ========================== */
    return [
        ['sesion' => 'Matutina',   'cantidad' => $resultado['Matutina']],
        ['sesion' => 'Vespertina', 'cantidad' => $resultado['Vespertina']],
        ['sesion' => 'Nocturna',   'cantidad' => $resultado['Nocturna']]
    ];
}


public function ComprasXmesSegm($mes, $sexo, $anio){
        
        $result = [];

        $sql = "SELECT COUNT(*) as totalVentas, MONTH(v.fecha) as mes 
        FROM ventas v
        INNER JOIN usuarios c ON c.id_usuario = v.id_usuario AND c.id_tipo = 2
        WHERE YEAR(v.fecha) = ?
        AND MONTH(v.fecha) = ?
        AND c.sexo = ?
        GROUP BY mes ORDER BY mes ASC"; 

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$anio, $mes, $sexo]);
        $valoresXmes = $stmt->fetchAll();

        


       foreach ($valoresXmes as $row) {
        $result[] = [
        'mes' => $row['mes'],
        'totalVentas' => $row['totalVentas']
    ];
       }
        return $result;
    
    }


    public function comprasXsegmentosMF(
    int $mes,
    ?string $sexo = null,
    int $anio
    
): array {

    $sql = "
        SELECT
            c.sexo AS sexo,
            COUNT(*) AS cantidad
        FROM ventas v
        INNER JOIN usuarios c 
            ON v.id_usuario = c.id_usuario AND c.id_tipo = 2
        WHERE 
            MONTH(v.fecha) = :mes
            AND YEAR(v.fecha) = :anio
    ";

    // Si se especifica segmento, se filtra
    if ($sexo !== null) {
        $sql .= " AND c.sexo = :sexo ";
    }

    $sql .= "
        GROUP BY c.sexo
        ORDER BY cantidad DESC
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);

    if ($sexo !== null) {
        $stmt->bindValue(':sexo', $sexo, PDO::PARAM_STR);
    }

    $stmt->execute();
    $valores = $stmt->fetchAll();

    $list  = [];
    $total = 0;

    foreach ($valores as $row) {
        $cantidad = (int) $row['cantidad'];

        $list[] = [
            'sexo'     => $row['sexo'],
            'cantidad' => $cantidad
        ];

        $total += $cantidad;
    }

    // Total del mes/año (y segmento si aplica)
    $list[] = [
        'totalVentas' => $total
    ];

    return $list;
}


public function ComprasPorCiudadMensualSegm(
    int $mes, 
    string $sexo,
    int $anio): array
{
    $result = [];

    $sql = "
        SELECT 
            c.ciudad,
            c.Provincia AS provincia,
            COUNT(v.id_venta) AS totalCompras,
            (
                SELECT COUNT(*)
                FROM usuarios c2
                WHERE c2.id_tipo = 2
                  AND c2.ciudad = c.ciudad
                  AND c2.Provincia = c.Provincia
                  AND (:sexo IS NULL OR c2.sexo = :sexo)
            ) AS totalClientes
        FROM usuarios c
        LEFT JOIN ventas v 
            ON v.id_usuario = c.id_usuario
           AND YEAR(v.fecha) = :anio
           AND MONTH(v.fecha) = :mes
        WHERE c.id_tipo = 2
        AND (:sexo IS NULL OR c.sexo = :sexo)
        GROUP BY c.ciudad, c.Provincia
        ORDER BY totalCompras DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':anio' => $anio,
        ':mes'  => $mes,
        ':sexo' => $sexo
    ]);

    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($datos as $row) {
        $result[] = [
            'ciudad'        => $row['ciudad'],
            'provincia'     => $row['provincia'],
            'totalCompras'  => (int) $row['totalCompras'],
            'totalClientes' => (int) $row['totalClientes']
        ];
    }

    return $result;
}




public function comprasXdiasSegm(int $mes, int $anio, ?string $sexo = null): array
{
    $result = [];

    // Total de días del mes
    $diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

    $sql = "
        SELECT 
            DAY(v.fecha) AS dia,
            COUNT(v.id_venta) AS totalVentas
        FROM ventas v
        INNER JOIN usuarios c ON c.id_usuario = v.id_usuario  AND c.id_tipo = 2
        WHERE YEAR(v.fecha) = ?
          AND MONTH(v.fecha) = ?
          AND (? IS NULL OR c.sexo = ?)
        GROUP BY dia
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        $anio,
        $mes,
        $sexo,
        $sexo
    ]);

    // [dia => totalVentas]
    $datos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Completar todos los días
    for ($dia = 1; $dia <= $diasMes; $dia++) {
        $result[] = [
            'dia'         => $dia,
            'totalVentas' => (int) ($datos[$dia] ?? 0)
        ];
    }

    // 🔽 Ordenar por totalVentas DESC
    usort($result, function ($a, $b) {
        return $b['totalVentas'] <=> $a['totalVentas'];
    });

    return $result;
}






public function TotalVentasMensualPorSegmento(int $mes, int $anio): array
{
    $anio = $anio ?? (int) date('Y');

    $sql = "
        SELECT 
            c.sexo,
            COUNT(v.id_venta) AS totalVentas
        FROM ventas v
        INNER JOIN usuarios c ON c.id_usuario = v.id_usuario  AND c.id_tipo = 2
        WHERE YEAR(v.fecha) = ?
          AND MONTH(v.fecha) = ?
        GROUP BY c.sexo
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $mes]);

    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalizamos la salida (por si un sexo no tiene ventas)
    $result = [
        'F' => 0,
        'M' => 0
    ];

    foreach ($datos as $row) {
        $result[$row['sexo']] = (int) $row['totalVentas'];
    }

    return $result;
}


public function comprasPorTallaSexo($anio, string $sexo, $talla = null): array
{
    $result = [];

    $sql = "
        SELECT  
            c.sexo,
            COUNT(v.id_venta) AS cantidad,
            t.talla
        FROM tallas t
        INNER JOIN productos_tallas pt ON pt.id_talla = t.id_talla
        INNER JOIN detalle_ventas dv ON dv.id_producto = pt.id_producto
        INNER JOIN ventas v 
            ON v.id_venta = dv.id_venta 
           AND YEAR(v.fecha) = :anio
        INNER JOIN usuarios c 
            ON c.id_usuario = v.id_usuario  AND c.id_tipo = 2
           AND c.sexo = :sexo
        WHERE (:talla IS NULL OR t.talla = :talla)
        GROUP BY t.talla, c.sexo
        ORDER BY cantidad DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':anio' => $anio,
        ':sexo' => $sexo,
        ':talla' => $talla

    ]);

    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($datos as $row) {
        $result[] = [
            'sexo'     => $sexo,               // fijo por filtro
            'talla'    => $row['talla'],
            'cantidad' => (int) $row['cantidad']
        ];
    }

    return $result;
}


public function TotalVentasAnualSegm(int $anio, $sexo) {
    $anio = $anio ?? (int)date('Y');

    $sql = "SELECT COUNT(*) AS total 
            FROM ventas v 
            INNER JOIN usuarios c ON c.id_usuario = v.id_usuario  AND c.id_tipo = 2
            AND YEAR(v.fecha) = ? AND c.sexo = ?";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $sexo]);
    $row = $stmt->fetch();

    // Retornamos el valor numérico, si es null devolvemos 0
    return $row['total'] ?? 0;
}


public function comprasXciudadXdiaSegm($dia, $mes, $anio, string $sexo)
{
    $result = [];

    $sql = "
        SELECT 
            c.ciudad,
            c.Provincia AS provincia,
            COUNT(v.id_venta) AS totalCompras,
            COUNT(DISTINCT c.id_usuario) AS totalClientes
        FROM usuarios c
        INNER JOIN ventas v ON v.id_usuario = c.id_usuario
        WHERE c.id_tipo = 2
          AND YEAR(v.fecha) = ? AND MONTH(v.fecha) = ? AND DAY(v.fecha) = ? 
          AND c.sexo = ?
        GROUP BY c.ciudad, c.Provincia
        ORDER BY totalCompras DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $mes, $dia,  $sexo]);
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




}
?>