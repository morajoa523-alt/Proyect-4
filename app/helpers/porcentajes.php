<?php
// helpers/porcentajes.php
class porcentajes {

    
function porcentajeProductos(array $comprasXproduc, int $totalMes): array {
    $porcenProduc = [];
    //echo "--------R: ";print_r($comprasXproduc);
    foreach ($comprasXproduc as $pr) {
      
        $producto = $pr['producto'] ?? 'SIN_NOMBRE';
        $vendido  = (int)($pr['total_vendido'] ?? $pr['total_vendido'] ?? 0);
        //echo "-----------G: " . $comprasXproduc['total_vendido'];
        $marca  = $pr['marca'] ?? 'SIN MARCA';
        $modelo  = $pr['modelo'] ?? 'SIN MODELO';
        $precio = isset($pr['precio']) ? (float) $pr['precio'] : 0.00;
        $precio = round($precio, 2);

        $porcentaje = ($totalMes > 0) ? ($vendido / $totalMes) * 100 : 0;
        $porcenProduc[] = [
            'producto_id' => $pr['idProducto'] ?? null,
            'producto' => $producto,
            'vendidos' => $vendido,
            'porcentaje' => round($porcentaje, 2), // 2 decimales
            'marca'    =>   $marca,
            'modelo'   =>   $modelo,
            'precio'        => $precio
        ];
    }
    return $porcenProduc;
}

function porcentajeXmes(array $meses = [], int $comprasAnio): array {
    $listaPorcentaje = [];
    if ($comprasAnio <= 0) {
        foreach ($meses as $m) {
            $listaPorcentaje[] = 0.00;
        }
        return $listaPorcentaje;
    }
    foreach ($meses as $m) {
        $por = ($m / $comprasAnio) * 100;
        $listaPorcentaje[] = round($por, 2);
    }
    return $listaPorcentaje;
}



 function porcentajeXsegmento(array $listSeg, int $ventasXmes): array
{
    $listaDetallesXseg = [];

    // Caso sin ventas
    if ($ventasXmes <= 0) {
        foreach ($listSeg as $seg) {
            $listaDetallesXseg[] = [
                'sexo'         => $seg['sexo'] ?? '',
                'cantidad'     => 0,
                'porcentaje'   => 0,
                'totalVentas'  => 0
            ];
        }
        return $listaDetallesXseg;
    }

    // Cálculo normal
    foreach ($listSeg as $seg) {
        
        $cantidad = (int) ($seg['cantidad'] ?? 0);
        $porcentaje = ($cantidad / $ventasXmes) * 100;

        $listaDetallesXseg[] = [
            'sexo'        => $seg['sexo'] ?? '',
            'cantidad'    => $cantidad  ?? 0,
            'porcentaje'  => round($porcentaje, 2) ?? 0,
            'totalVentas' => $ventasXmes ?? 0
        ];
    }

    return $listaDetallesXseg;
}

function porcentajeXclientesAnio(array $listSeg, int $totalClientes): array
{
    $listaDetallesXseg = [];

    // Caso sin ventas
    if ($totalClientes <= 0) {
        foreach ($listSeg as $seg) {
            $listaDetallesXseg[] = [
                'sexo'         => $seg['sexo'] ?? '',
                'cantidad'     => 0,
                'porcentaje'   => 0,
                'totalClientes'  => 0
            ];
        }
        return $listaDetallesXseg;
    }

    // Cálculo normal
    foreach ($listSeg as $seg) {
        
        $cantidad = (int) ($seg['cantidad'] ?? 0);
        $porcentaje = ($cantidad / $totalClientes) * 100;

        $listaDetallesXseg[] = [
            'sexo'        => $seg['sexo'] ?? '',
            'cantidad'    => $cantidad  ?? 0,
            'porcentaje'  => round($porcentaje, 2) ?? 0,
            'totalClientes' => $totalClientes ?? 0
        ];
    }

    return $listaDetallesXseg;
}

public function porcentajeXsesion(array $listSesion, int $ventasXmes): array
{
    $resultado = [];

    // Evitar división por cero
    if ($ventasXmes <= 0) {
        foreach ($listSesion as $sesion) {
            $resultado[] = [
                'sesion'       => $sesion['sesion'],
                'cantidad'     => 0,
                'porcentaje'   => 0,
                'totalVentas'  => 0
            ];
        }
        return $resultado;
    }

    // Cálculo normal
    foreach ($listSesion as $sesion) {
        $cantidad = (int) ($sesion['cantidad'] ?? 0);
        $porcentaje = ($cantidad / $ventasXmes) * 100;

        $resultado[] = [
            'sesion'       => $sesion['sesion'],
            'cantidad'     => $cantidad,
            'porcentaje'   => round($porcentaje, 2),
            'totalVentas'  => $ventasXmes
        ];
    }

    return $resultado;
}

public function porcentajeVentasXMes($ventasSegM, $totalVentas): array
{
    $resultado = [];

    // Validar array de ventas
    if (!is_array($ventasSegM) || empty($ventasSegM)) {
        return $resultado; // retorna array vacío
    }

    // Validar total anual
    $totalVentas = (int) ($totalVentas ?? 0);

    foreach ($ventasSegM as $row) {

        // Validaciones por fila
        if (!is_array($row)) {
            continue;
        }

        $mes = isset($row['mes']) ? (int) $row['mes'] : 0;
        $ventasMes = isset($row['totalVentas']) ? (int) $row['totalVentas'] : 0;

        // Evitar división por cero
        $porcentaje = ($totalVentas > 0)
            ? round(($ventasMes / $totalVentas) * 100, 2)
            : 0;

        $resultado[] = [
            'mes'         => $mes,
            'totalVentas' => $ventasMes,
            'porcentaje'  => $porcentaje
        ];
    }

    return $resultado;
}


public function porcentajeVentasXciudad(array $ventasPorCiudad, int $totalVentas): array
{
    $resultado = [];

    // Validar array de datos
    if (empty($ventasPorCiudad)) {
        return $resultado;
    }

    // Validar total general
    $totalVentas = (int) $totalVentas;

    foreach ($ventasPorCiudad as $row) {

        if (!is_array($row)) {
            continue;
        }

        $ciudad       = $row['ciudad'] ?? '';
        $provincia    = $row['provincia'] ?? '';
        $totalCompras = isset($row['totalCompras']) ? (int) $row['totalCompras'] : 0;
        $totalClientes = isset($row['totalClientes']) ? (int) $row['totalClientes'] : 0;
        // Evitar división por cero
        $porcentaje = ($totalVentas > 0)
            ? round(($totalCompras / $totalVentas) * 100, 2)
            : 0;

        $resultado[] = [
            'ciudad'        => $ciudad,
            'provincia'     => $provincia,
            'totalCompras'  => $totalCompras,
            'totalClientes'  => $totalClientes,
            'porcentaje'    => $porcentaje
        ];
    }

    return $resultado;
}

public function porcentajeVentasPorDia(array $ventasPorDia): array
{
    $resultado = [];

    // Validar datos
    if (empty($ventasPorDia)) {
        return $resultado;
    }

    // Calcular total mensual
    $totalMensual = array_sum(array_column($ventasPorDia, 'totalVentas'));

    foreach ($ventasPorDia as $row) {

        if (!is_array($row)) {
            continue;
        }

        $dia         = (int) ($row['dia'] ?? 0);
        $ventasDia   = (int) ($row['totalVentas'] ?? 0);

        // Evitar división por cero
        $porcentaje = ($totalMensual > 0)
            ? round(($ventasDia / $totalMensual) * 100, 2)
            : 0;

        $resultado[] = [
            'dia'         => $dia,
            'totalVentas' => $ventasDia,
            'porcentaje'  => $porcentaje
        ];
    }

    return $resultado;
}

public function porcentajeComprasPorTalla(
    array $comprasPorTalla,
    int $totalVentasAnual
): array
{
    $resultado = [];

    // Validaciones básicas
    if (empty($comprasPorTalla) || $totalVentasAnual <= 0) {
        return $resultado;
    }

    foreach ($comprasPorTalla as $row) {

        if (!isset($row['talla'], $row['cantidad'], $row['sexo'])) {
            continue;
        }

        $cantidad = (int) $row['cantidad'];

        $porcentaje = round(
            ($cantidad / $totalVentasAnual) * 100,
            2
        );

        $resultado[] = [
            'sexo'       => $row['sexo'],
            'talla'      => $row['talla'],
            'cantidad'   => $cantidad,
            'porcentaje' => $porcentaje
        ];
    }

    return $resultado;
}



function porcentajeXmesAnio(array $datos = []): array
{
    $resultado = [];

    if (empty($datos)) {
        return $resultado;
    }

    // Obtener total anual
    $ultimo = end($datos);
    $totalAnio = $ultimo['totalComprasAnio'] ?? 0;

    // Volver el puntero al inicio
    reset($datos);

    foreach ($datos as $item) {

        // Saltar el total anual
        if (!isset($item['mes'])) {
            continue;
        }

        $porcentaje = ($totalAnio > 0)
            ? round(($item['totalVentas'] / $totalAnio) * 100, 2)
            : 0;

        $resultado[] = [
            'mes' => $item['mes'],
            'totalVentas' => $item['totalVentas'],
            'porcentaje' => $porcentaje
        ];
    }

    return $resultado;
}




}
?>