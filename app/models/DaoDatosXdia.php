<?php

class DaoDatosXdia
{

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

public function comprasXdia($mes, $anio, $dia)
{
    $result = [];

    $sql = "SELECT 
                COUNT(*) AS totalVentas,
                DAY(fecha) AS dia
            FROM ventas
            WHERE YEAR(fecha) = ?
              AND MONTH(fecha) = ?
              AND DAY(fecha) = ?
            GROUP BY dia
            ORDER BY dia ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $mes, $dia]);
    $valoresXdia = $stmt->fetchAll();

    foreach ($valoresXdia as $row) {
        $result[] = [
            'dia' => $row['dia'],
            'totalVentas' => $row['totalVentas']
        ];
    }

    return $result;
}


public function comprasXdias(int $mes, int $anio): array
{
    $result = [];

    // Total de días del mes
    $diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

    $sql = "
        SELECT 
            DAY(fecha) AS dia,
            COUNT(*) AS totalVentas
        FROM ventas
        WHERE YEAR(fecha) = ?
          AND MONTH(fecha) = ?
        GROUP BY dia
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $mes]);

    // [dia => totalVentas]
    $datos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Completar todos los días
    for ($dia = 1; $dia <= $diasMes; $dia++) {
        $result[] = [
            'dia'         => $dia,
            'totalVentas' => (int) ($datos[$dia] ?? 0)
        ];
    }

    // 🔽 ORDENAR por totalVentas DESC
    usort($result, function ($a, $b) {
        return $b['totalVentas'] <=> $a['totalVentas'];
    });

    return $result;
}




public function ComprasXproductosXdia(int $dia, int $mes, int $anio = null)
{
    $anio = $anio ?? (int)date('Y');
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
            WHERE DAY(v.fecha) = ?
              AND MONTH(v.fecha) = ?
              AND YEAR(v.fecha) = ?
            GROUP BY p.id_producto
            ORDER BY total_vendido DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$dia, $mes, $anio]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $resultFinal[] = [
            'idProducto'    => $row['id_producto'],
            'producto'      => $row['producto'],
            'total_vendido' => $row['total_vendido'],
            'marca'         => $row['marca'],
            'modelo'        => $row['modelo'],
            'precio'        => $row['precio']
        ];
    }

    return $resultFinal;
}

public function TotalProductosVendidosDia(int $dia, int $mes, int $anio = null)
{
    $anio = $anio ?? (int)date('Y');

    $sql = "SELECT SUM(dv.cantidad) AS total 
            FROM detalle_ventas dv
            INNER JOIN ventas v ON dv.id_venta = v.id_venta
            WHERE DAY(v.fecha) = ?
              AND MONTH(v.fecha) = ?
              AND YEAR(v.fecha) = ?";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$dia, $mes, $anio]);
    $row = $stmt->fetch();

    // Retornamos el valor numérico, si es null devolvemos 0
    return $row['total'] ?? 0;
}

public function comprasXsegmentosMFDia(int $dia, int $mes, int $anio): array
{
    $sql = "
        SELECT
    u.sexo AS sexo,
    COUNT(*) AS cantidad
FROM ventas v
INNER JOIN usuarios u 
    ON v.id_usuario = u.id_usuario AND u.id_tipo = 2
WHERE DAY(v.fecha) = :dia
  AND MONTH(v.fecha) = :mes
  AND YEAR(v.fecha) = :anio
GROUP BY u.sexo
ORDER BY cantidad DESC;

    ";

    $stmt = $this->db->prepare($sql);

    // Bind de parámetros
    $stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
    $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);

    $stmt->execute();

    $valores = $stmt->fetchAll();

    $list = [];
    $i = 0;
    $total = 0;

    foreach ($valores as $row) {
        $l = [
            'sexo'     => $row['sexo'],
            'cantidad' => $row['cantidad']
        ];

        $list[] = $l;
        $total += $list[$i]['cantidad'];
        $i++;
    }

    // Total general del día
    $list[] = [
        'totalVentas' => $total
    ];

    return $list;
}

public function comprasXsesionDia(int $dia, int $mes, int $anio): array
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
        WHERE DAY(fecha) = :dia
          AND MONTH(fecha) = :mes
          AND YEAR(fecha) = :anio
        GROUP BY sesion
        ORDER BY cantidad DESC;
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
    $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapa base (asegura sesiones aunque no existan registros)
    $resultado = [
        'Matutina'   => 0,
        'Vespertina' => 0,
        'Nocturna'   => 0
    ];

    foreach ($rows as $row) {
        if ($row['sesion']) {
            $resultado[$row['sesion']] = (int) $row['cantidad'];
        }
    }

    // Formato final consistente
    return [
        ['sesion' => 'Matutina',   'cantidad' => $resultado['Matutina']],
        ['sesion' => 'Vespertina', 'cantidad' => $resultado['Vespertina']],
        ['sesion' => 'Nocturna',   'cantidad' => $resultado['Nocturna']]
    ];
}


public function comprasXdiaSegm(
    int $mes,
    int $anio,
    int $dia,
    string $sexo
): array {
    $result = [];

    $sql = "
        SELECT 
    COUNT(*) AS totalVentas,
    DAY(v.fecha) AS dia
FROM ventas v
INNER JOIN usuarios u 
    ON v.id_usuario = u.id_usuario AND u.id_tipo = 2
WHERE 
    YEAR(v.fecha) = ?
    AND MONTH(v.fecha) = ?
    AND DAY(v.fecha) = ?
    AND u.sexo = ?
GROUP BY dia
ORDER BY dia ASC;

    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $mes, $dia, $sexo]);
    $valoresXdia = $stmt->fetchAll();

    foreach ($valoresXdia as $row) {
        $result[] = [
            'dia' => (int) $row['dia'],
            'totalVentas' => (int) $row['totalVentas']
        ];
    }

    return $result;
}


public function ComprasXproductosXdiaSegm(
    int $dia,
    int $mes,
    int $anio = null,
    string $sexo
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
INNER JOIN usuarios u 
    ON u.id_usuario = v.id_usuario AND u.id_tipo = 2
WHERE 
    DAY(v.fecha) = ?
    AND MONTH(v.fecha) = ?
    AND YEAR(v.fecha) = ?
    AND u.sexo = ?
GROUP BY 
    p.id_producto,
    p.nombre,
    p.marca,
    p.modelo,
    p.precio
ORDER BY total_vendido DESC;

    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$dia, $mes, $anio, $sexo]);
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



public function TotalProductosVendidosDiaSegm(
    int $dia,
    int $mes,
    int $anio = null,
    string $sexo
): int {
    $anio = $anio ?? (int) date('Y');

    $sql = "
       SELECT COALESCE(SUM(dv.cantidad), 0) AS total
FROM detalle_ventas dv
INNER JOIN ventas v 
    ON dv.id_venta = v.id_venta
INNER JOIN usuarios u 
    ON u.id_usuario = v.id_usuario AND u.id_tipo = 2
WHERE 
    DAY(v.fecha) = ?
    AND MONTH(v.fecha) = ?
    AND YEAR(v.fecha) = ?
    AND u.sexo = ?;

    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$dia, $mes, $anio, $sexo]);
    $row = $stmt->fetch();

    return (int) $row['total'];
}


public function comprasXsegmentosMFDiaSegm(
    int $dia,
    int $mes,
    int $anio,
    ?string $sexo = null
): array {

    $sql = "
        SELECT
    u.sexo AS sexo,
    COUNT(*) AS cantidad
FROM ventas v
INNER JOIN usuarios u 
    ON v.id_usuario = u.id_usuario AND u.id_tipo = 2
WHERE 
    DAY(v.fecha) = :dia
    AND MONTH(v.fecha) = :mes
    AND YEAR(v.fecha) = :anio
GROUP BY u.sexo
ORDER BY cantidad DESC;

    ";

    // Filtro por segmento si se especifica
    if ($sexo !== null) {
        $sql .= " AND c.sexo = :sexo ";
    }

    $sql .= "
        GROUP BY c.sexo
        ORDER BY cantidad DESC
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
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

    // Total del día (y segmento si aplica)
    $list[] = [
        'totalVentas' => $total
    ];

    return $list;
}


public function comprasXsesionDiaSegm(
    int $dia,
    int $mes,
    int $anio,
    string $sexo
): array {
    $sql = "
        SELECT
    CASE
        WHEN TIME(v.fecha) BETWEEN '06:00:00' AND '11:59:59' THEN 'Matutina'
        WHEN TIME(v.fecha) BETWEEN '12:00:00' AND '17:59:59' THEN 'Vespertina'
        WHEN TIME(v.fecha) BETWEEN '18:00:00' AND '23:59:59' THEN 'Nocturna'
    END AS sesion,
    COUNT(*) AS cantidad
FROM ventas v
INNER JOIN usuarios u 
    ON v.id_usuario = u.id_usuario AND u.id_tipo = 2
WHERE 
    DAY(v.fecha) = :dia
    AND MONTH(v.fecha) = :mes
    AND YEAR(v.fecha) = :anio
    AND u.sexo = :sexo
GROUP BY sesion
ORDER BY cantidad DESC;

    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
    $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmt->bindValue(':sexo', $sexo, PDO::PARAM_STR);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapa base (asegura sesiones aunque no existan registros)
    $resultado = [
        'Matutina'   => 0,
        'Vespertina' => 0,
        'Nocturna'   => 0
    ];

    foreach ($rows as $row) {
        if ($row['sesion']) {
            $resultado[$row['sesion']] = (int) $row['cantidad'];
        }
    }

    // Formato final consistente
    return [
        ['sesion' => 'Matutina',   'cantidad' => $resultado['Matutina']],
        ['sesion' => 'Vespertina', 'cantidad' => $resultado['Vespertina']],
        ['sesion' => 'Nocturna',   'cantidad' => $resultado['Nocturna']]
    ];
}


public function ComprasXCiudadDia(int $dia = null)
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
AND DAY(v.fecha) = ?
GROUP BY 
    u.ciudad,
    u.Provincia
ORDER BY 
    u.Provincia ASC,
    u.ciudad ASC;

    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$dia]);
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


public function comprasXciudadXdia($dia, $mes, $anio)
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
    AND MONTH(v.fecha) = ?
    AND DAY(v.fecha) = ?
GROUP BY 
    u.ciudad,
    u.Provincia
ORDER BY totalCompras DESC;

    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$anio, $mes, $dia]);
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