<?php require_once APP . '/views/inc/headerPrefe.php'; ?>
<?php require_once APP . '/views/inc/sidebar.php'; ?>

<div class="conten-base">
<?php 
function getRutaImagen($idProducto) {

    $cn = Database::getInstance();
    $sql = "SELECT img FROM productos WHERE id_producto = ?";

    $stmt = $cn->getConnection()->prepare($sql);
    $stmt->execute([$idProducto]);

    $fila = $stmt->fetch(); // FETCH_ASSOC ya es el default

    return $fila ? $fila['img'] : null;
}

?>


<!-- =======================
     PRODUCTO
     ======================= -->
<?php
/* ==========================
   OBTENER PRODUCTO CON MAYOR PORCENTAJE
   ========================== */

$productoTop = null;

foreach ($detallesProd as $producto) {
    if ($productoTop === null || $producto['porcentaje'] > $productoTop['porcentaje']) {
        $productoTop = $producto;
    }
}
?>

<!-- ==========================
     CONTENEDOR PRODUCTO
     ========================== -->
<div class="content-producto">

    <h1>Producto mas comprado</h1>

    <div class="img-producto">

        <img src="/Dashboards/img/<?php echo getRutaImagen($detallesProd[0]['producto_id'])?>">
        
    </div>

    <div class="detalles-producto">
       
        <p><strong>Nombre:</strong> <?= htmlspecialchars($productoTop['producto']) ?></p>
        <p><strong>Marca:</strong> <?= htmlspecialchars($productoTop['marca']) ?></p>
        <p><strong>Modelo:</strong> <?= htmlspecialchars($productoTop['modelo']) ?></p>
        <p><strong>Precio:</strong> $<?= number_format($productoTop['precio'], 2) ?></p>
        <p><strong>Participación:</strong> <?= $productoTop['porcentaje'] ?>%</p>
    </div>

    <div class="boton-productos">
        <form action="/Dashboards/datosTotales/datosTotalesProductos" method="GET">
            <input type="hidden" name="datosProductos"
                   value="<?= htmlspecialchars(json_encode($detallesProd)) ?>">
            <button type="submit" name="submit" class="link-card-btn">General</button>
        </form>
    </div>
</div>



<div class="tabla-ciudad">

    

    <table class="tabla-ventas-ciudad">
        <thead>
            <tr>
                <th>Ciudad</th>
                <th>Provincia</th>
                <th>Total Clientes</th>
                <th>Compras Ciudad</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($datosXciudad)) : ?>
                
                
                    <tr>
                        <td><?= htmlspecialchars($datosXciudad[0]['ciudad']) ?></td>
                        <td><?= htmlspecialchars($datosXciudad[0]['provincia']) ?></td>
                        <td><?= $datosXciudad[0]['totalClientes'] ?? 0 ?></td>
                        <td><?= $datosXciudad[0]['totalCompras'] ?></td>
                        <td><?= $datosXciudad[0]['porcentaje'] ?>%</td>
                    </tr>
                
            <?php else : ?>
                <tr>
                    
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <form action="/Dashboards/datosTotales/porcentXciudad" method="GET">

    <input  type="hidden" name="datosXciudad" value="<?= htmlspecialchars(json_encode($datosXciudad)) ?>">

    <button type="submit" name="submit" class="link-card-btn">General</button>
</form>

</div>


<div class ="content-dia">


<h4>Día del Mes con más Compras: <?php echo $datosXdias[0]['dia'];  ?> <h4>
<h4>Porcentaje de Compras: <?php echo $datosXdias[0]['porcentaje'];  ?> % <h4>



<form action="/Dashboards/preferencias/porcentXdias" method="GET">
<input  type="hidden" name="datosXdias" value="<?= htmlspecialchars(json_encode($datosXdias)) ?>">
<input  type="hidden" name="mes" value="<?= $mes ?>">
<input  type="hidden" name="anio" value="<?= $anio ?>">
<input  type="hidden" name="ventasPorSegm" value="<?= $ventasPorSegm ?>">
<input  type="hidden" name="segmento" value="<?= $segmento ?>">

<button type="submit" name="submit" class="link-card-btn">General</button>
</form>


</div>

<!-- ==========================
     RESUMEN INFERIOR
     ========================== -->
<div class="resumen-productos">

    <div class="resumen-card">
        <span class="resumen-titulo">Total De Productos Comprados (mes)</span>
        <span class="resumen-valor"><?= $totalProdMes ?></span>
    </div>

    <div class="resumen-card">
        <span class="resumen-titulo">Unidades Compradas del Producto top</span>
        <span class="resumen-valor"><?= $productoTop['vendidos'] ?></span>
    </div>

    <div class="resumen-card">
        <span class="resumen-titulo">% Preferencia</span>
        <span class="resumen-valor"><?= $productoTop['porcentaje'] ?>%</span>
    </div>

</div>


<?php
/* =======================
   PROCESAR SEGMENTO
   ======================= */

$labelsSegmento = [];
$dataSegmento   = [];
$porcSegmento   = [];
$colorsSegmento = [];

foreach ($datosXsegmento as $fila) {
    
    if (!empty($fila['sexo']) && $fila['cantidad'] > 0) {
        $labelsSegmento[] = $fila['sexo'];
        $dataSegmento[]   = $fila['cantidad'];
        $porcSegmento[]   = $fila['porcentaje'];

        // 🎨 Colores fijos por sexo
        if ($fila['sexo'] === 'F') {
            $colorsSegmento[] = '#FF6384'; // Rosa
        } elseif ($fila['sexo'] === 'M') {
            $colorsSegmento[] = '#36A2EB'; // Azul
        } else {
            $colorsSegmento[] = '#9CA3AF'; // Gris fallback
        }
    }
}

/* =======================
   PROCESAR SESIONES
   ======================= */

$labelsSesion = [];
$dataSesion   = [];
$porcSesion   = [];

foreach ($datosXsesion as $fila) {
    if ($fila['cantidad'] > 0) {
        $labelsSesion[] = $fila['sesion'];
        $dataSesion[]   = $fila['cantidad'];
        $porcSesion[]   = $fila['porcentaje'];
    }
}
?>

<!-- =======================
     SEGMENTO
     ======================= -->
<div class="content-segmento">
    <h1>Segmento</h1>
    <canvas id="graficoSegmento"></canvas>
</div>

<!-- =======================
     HORARIOS
     ======================= -->
<div class="content-horario">
    <h1>Horarios</h1>
    <canvas id="graficoSesiones"></canvas>
</div>




</div> <!-- Final del Content base -->





<!-- =======================
     CHART.JS
     ======================= -->





<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* =======================
   DATOS DESDE PHP (SEGUROS)
   ======================= */
const labelsSegmento = <?= json_encode($labelsSegmento ?? []) ?>;
const dataSegmento   = <?= json_encode($dataSegmento ?? []) ?>;
const porcSegmento   = <?= json_encode($porcSegmento ?? []) ?>;
const colorsSegmento = <?= json_encode($colorsSegmento ?? []) ?>;

const labelsSesion = <?= json_encode($labelsSesion ?? []) ?>;
const dataSesion   = <?= json_encode($dataSesion ?? []) ?>;
const porcSesion   = <?= json_encode($porcSesion ?? []) ?>;

/* =======================
   GRAFICO PASTEL - SEGMENTO
   ======================= */
if (document.getElementById('graficoSegmento')) {
    new Chart(document.getElementById('graficoSegmento'), {
        type: 'pie',
        data: {
            labels: labelsSegmento,
            datasets: [{
                data: dataSegmento,
                backgroundColor: colorsSegmento
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const i = ctx.dataIndex;
                            return `${ctx.label}: ${dataSegmento[i] ?? 0} (${porcSegmento[i] ?? 0}%)`;
                        }
                    }
                }
            }
        }
    });
}

/* =======================
   GRAFICO BARRAS - SESIONES
   ======================= */
if (document.getElementById('graficoSesiones')) {
    new Chart(document.getElementById('graficoSesiones'), {
        type: 'bar',
        data: {
            labels: labelsSesion,
            datasets: [{
                label: 'Cantidad',
                data: dataSesion,
                backgroundColor: '#4BC0C0'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const i = ctx.dataIndex;
                            return `Cantidad: ${dataSesion[i] ?? 0} (${porcSesion[i] ?? 0}%)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
}



/* =======================
   ENVÍO POST
   ======================= */

</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const yearInput = document.getElementById('yearInput');
    const btnPrev = document.getElementById('yearPrev');
    const btnNext = document.getElementById('yearNext');

    const MIN_YEAR = 2000;
    const MAX_YEAR = new Date().getFullYear();

    function updateYear(delta) {
        let year = parseInt(yearInput.value, 10);

        if (isNaN(year)) {
            year = MAX_YEAR;
        }

        year += delta;

        if (year < MIN_YEAR) year = MIN_YEAR;
        if (year > MAX_YEAR) year = MAX_YEAR;

        // 🔥 CAMBIO INMEDIATO EN PANTALLA
        yearInput.value = year;
    }

    btnPrev.addEventListener('click', () => updateYear(-1));
    btnNext.addEventListener('click', () => updateYear(1));
});
</script>




<?php require_once APP . '/views/inc/footer.php'; ?>
