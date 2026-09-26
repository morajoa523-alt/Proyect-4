<?php require_once APP . '/views/inc/headerF.php'; ?>
<?php require_once APP . '/views/inc/sidebar.php'; ?>

<?php
/* =========================
   KPI CALCULOS
========================= */
$totalProductosVendidos = $totalVentasAnual;

if (!empty($detallesProd)) {
    foreach ($detallesProd as $p) {
        //$totalProductosVendidos += (int) $p['vendidos'];
    }
}

$cantidadProductoTop = $detallesProd[0]['vendidos'] ?? 0;
$porcentajeProductoTop = $detallesProd[0]['porcentaje'] ?? 0;
?>


<div class="Content-base">

<?php if($totalProductosVendidos !== '0'){ ?>
<!-- =========================
     KPIs
========================= -->
<div class="kpi-container">

    <div class="kpi-card">
        <h4>Total De Productos Comprados (Año)</h4>
        <span><?= $totalProductosVendidos ?></span>
    </div>

    <div class="kpi-card">
        <h4>Unidades Compradas del Producto Top</h4>
        <span><?= $cantidadProductoTop ?></span>
    </div>

    <div class="kpi-card">
        <h4>% Preferencia</h4>
        <span><?= $porcentajeProductoTop ?>%</span>
    </div>

</div>






<?php
/* =========================
   PRODUCTO TOP
========================= */
$productoTop = null;
if (!empty($detallesProd)) {
    foreach ($detallesProd as $p) {
        if ($productoTop === null || $p['porcentaje'] > $productoTop['porcentaje']) {
            $productoTop = $p;
        }
    }
}

/* =========================
   MES CON MAYOR PORCENTAJE
========================= */
$mesTop = null;
if (!empty($datosMesSegmento)) {
    foreach ($datosMesSegmento as $m) {
        if ($mesTop === null || $m['porcentaje'] > $mesTop['porcentaje']) {
            $mesTop = $m;
        }
    }
}

/* =========================
   DATOS PARA GRÁFICOS
========================= */
$productosLabels = [];
$productosData   = [];

foreach ($detallesProd ?? [] as $p) {
    $productosLabels[] = $p['producto'];
    $productosData[]   = $p['porcentaje'];
}

$mesesLabels = [];
$mesesData   = [];
foreach ($porcentajeMes ?? [] as $m) {
    $mesesLabels[] = 'Mes ' . $m['mes'];
    $mesesData[]   = $m['porcentaje'];
}

$sesionLabels = [];
$sesionData   = [];
foreach ($datosXsesion ?? [] as $s) {
    $sesionLabels[] = $s['sesion'];
    $sesionData[]   = $s['porcentaje'];
}
?>

<!-- =========================
     PRODUCTO TOP
========================= -->
<div class="comntent-producto">

<?php if ($productoTop === null): ?>
    <p class="texto-vacio">No Hay Compras Registradas</p>
<?php else: ?>
    <h3>Productos más Comprados</h3>

    <div class="detalles-producto">
        <p><strong>Producto:</strong> <?= htmlspecialchars($productoTop['producto']) ?></p>
        <p><strong>Marca:</strong> <?= htmlspecialchars($productoTop['marca']) ?></p>
        <p><strong>Modelo:</strong> <?= htmlspecialchars($productoTop['modelo']) ?></p>
        <p><strong>Precio:</strong> $<?= number_format($productoTop['precio'], 2) ?></p>
        <p><strong>Participación:</strong> <?= $productoTop['porcentaje'] ?>%</p>
    </div>

    <canvas id="graficoProductos"></canvas>

    <form action="/Dashboards/datosTotales/datosTotalesProductos" method="GET">
        <input type="hidden" name="datosProductos" value="<?= htmlspecialchars(json_encode($detallesProd)) ?>">
        <input type="hidden" name="mes" value="<?= $mesTop[0]['mes'] ?>">
        <button class="btn-detalle" name="submit">Ver más</button>
    </form>
<?php endif; ?>

</div>

<!-- =========================
     MES CON MAYOR PORCENTAJE
========================= -->
<div class="comntent-prefeMes">

<?php $mesTop = $porcentajeMes?>
<?php if ($mesTop === null): ?>
    <p class="texto-vacio">No hay datos mensuales</p>
<?php else: ?>
    <h3>Mes Con Más Compras</h3>

    <p>
        <strong>Mes:</strong> <?=  $mesTop[0]['mes'] ?><br>
        <strong>Porcentaje:</strong> <?=  $mesTop[0]['porcentaje'] ?>%
    </p>

    <canvas id="graficoMeses"></canvas>

    <form action="/Dashboards/datosTotales/porcenXsegmMes" method="GET">
        <input type="hidden" name="porcenXsegmMes" value="<?= htmlspecialchars(json_encode($porcentajeMes)) ?>">
        <input type="hidden" name="anio" value="<?= $anio ?>">
        <input type="hidden" name="segmento" value="<?php  echo $segmento ?>">
        <button class="btn-detalle" name="submit" >Ver más</button>
    </form>
<?php endif; ?>

</div>

<!-- =========================
     SESIONES
========================= -->
<div class="comntent-prefeSesion">

<?php if (empty($datosXsesion)): ?>
    <p class="texto-vacio">No hay datos por sesión</p>
<?php else: ?>
    <h3>Compras Por Sección</h3>
    <canvas id="graficoSesion"></canvas>
<?php endif; ?>

</div>

<div class="content-tallas">

<div class="total-compras">
<h4>Total de Compras Del Segmento: <?= $totalComprasSegm ?> </h4>
</div>

<div class="nombre-talla">
<h4>Talla Más Comprada: <?= $datosXtallas[0]['talla'] ?> </h4>
</div>

<div class="cantidadCompras-talla">
<h4>Cantidad de Compras de la Talla: <?= $datosXtallas[0]['cantidad'] ?> </h4>
</div>

<div class="porcentaje-talla">
<h4>Preferencia: <?= $datosXtallas[0]['porcentaje'] ?> %</h4>
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
            <?php if (!empty($datosXciudadAnualSegm)) : ?>
                
                
                    <tr>
                        <td><?= htmlspecialchars($datosXciudadAnualSegm[0]['ciudad']) ?></td>
                        <td><?= htmlspecialchars($datosXciudadAnualSegm[0]['provincia']) ?></td>
                        <td><?= $datosXciudadAnualSegm[0]['totalClientes'] ?? 0 ?></td>
                        <td><?= $datosXciudadAnualSegm[0]['totalCompras'] ?></td>
                        <td><?= $datosXciudadAnualSegm[0]['porcentaje'] ?>%</td>
                    </tr>
                
            <?php else : ?>
                <tr>
                    
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <form action="/Dashboards/datosTotales/porcentXciudad" method="GET">

    <input  type="hidden" name="datosXciudad" value="<?= htmlspecialchars(json_encode($datosXciudadAnualSegm)) ?>">


<button type="submit" name="submit" class="link-card-btn">General</button>
</form>

</div>


<?php }else{?>
<h1> NO HAY DATOS REGISTRADOS DEL SEGMENTO FEMENINO </h1>
<?php }?>

</div> <!----------FIN CONTENT BASE  ---------->

<!-- =========================
     SCRIPTS GRÁFICOS
========================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* PRODUCTOS */
new Chart(document.getElementById('graficoProductos'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($productosLabels) ?>,
        datasets: [{
            data: <?= json_encode($productosData) ?>
        }]
    }
});

/* MESES */
new Chart(document.getElementById('graficoMeses'), {
    type: 'line',
    data: {
        labels: <?= json_encode($mesesLabels) ?>,
        datasets: [{
            data: <?= json_encode($mesesData) ?>
        }]
    }
});

/* SESIONES */
new Chart(document.getElementById('graficoSesion'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($sesionLabels) ?>,
        datasets: [{
            data: <?= json_encode($sesionData) ?>
        }]
    }
});
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
