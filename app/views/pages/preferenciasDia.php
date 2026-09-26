<?php require_once APP . '/views/inc/headerPrefe.php'; ?>
<?php require_once APP . '/views/inc/sidebar.php'; ?>

<div class="conten-base">
<h1>Preferencia por Día</h1>
<!-- =======================
     PRODUCTO
     ======================= -->

<?php if (
    $detallesProd === null &&
    $datosXsegmento === null &&
    $datosXsesion === null
): ?>

    <div class="alert alert-warning no-ventas">
        <strong>Sin Compras registradas</strong><br>
        No se realizaron Compras en la fecha seleccionada.
    </div>

<?php else: ?>

    <!-- CONTENIDO NORMAL CUANDO HAY DATOS -->
    <!-- aquí va toda tu estructura HTML existente -->







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

    <h1>Producto Más Comprado</h1>

    <div class="img-producto">
        <span>Imagen</span>
    </div>

    <div class="detalles-producto">
        <p><strong>Nombre:</strong> <?= htmlspecialchars($productoTop['producto']) ?></p>
        <p><strong>Marca:</strong> <?= htmlspecialchars($productoTop['marca']) ?></p>
        <p><strong>Modelo:</strong> <?= htmlspecialchars($productoTop['modelo']) ?></p>
        <p><strong>Precio:</strong> $ <?= number_format($productoTop['precio'], 2) ?></p>
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
                <th>Ventas Ciudad</th>
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


<!-- ==========================
     RESUMEN INFERIOR
     ========================== -->
<div class="resumen-productos">

    <div class="resumen-card">
        <span class="resumen-titulo">Total De Productos Comprados (Día)</span>
        <span class="resumen-valor"><?= $totalProdDia ?></span>
    </div>

    <div class="resumen-card">
        <span class="resumen-titulo">Unidades Compradas Del Producto Top</span>
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






<?php endif; ?>


</div> <!-- Final del Content base -->





<!-- =======================
     CHART.JS
     ======================= -->





<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* =======================
   DATOS DESDE PHP
   ======================= */
const labelsSegmento = <?= json_encode($labelsSegmento) ?>;
const dataSegmento   = <?= json_encode($dataSegmento) ?>;
const porcSegmento   = <?= json_encode($porcSegmento) ?>;
const colorsSegmento = <?= json_encode($colorsSegmento) ?>;

const labelsSesion = <?= json_encode($labelsSesion) ?>;
const dataSesion   = <?= json_encode($dataSesion) ?>;
const porcSesion   = <?= json_encode($porcSesion) ?>;

/* =======================
   GRAFICO PASTEL - SEGMENTO
   ======================= */
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
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const i = ctx.dataIndex;
                        return `${ctx.label}: ${dataSegmento[i]} (${porcSegmento[i]}%)`;
                    }
                }
            }
        }
    }
});

/* =======================
   GRAFICO BARRAS - HORARIOS
   ======================= */
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
                    label: function(ctx) {
                        const i = ctx.dataIndex;
                        return `Cantidad: ${dataSesion[i]} (${porcSesion[i]}%)`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// CONFIGURACIÓN
const mes = <?php echo $mes - 1; ?>; // 0 = Enero
const anio = <?php echo $anio ?? date('Y'); ?>;

const contenedor = document.getElementById("diasMes");

// Obtener cantidad de días del mes
const diasEnMes = new Date(anio, mes + 1, 0).getDate();

for (let dia = 1; dia <= diasEnMes; dia++) {
    const divDia = document.createElement("div");
    divDia.classList.add("dia");
    divDia.textContent = dia;

    divDia.addEventListener("click", () => {
        enviarDia(dia);
    });

    contenedor.appendChild(divDia);
}

// Función para enviar POST al controlador
function enviarDia(dia) {
    const form = document.createElement("form");
    form.method = "GET";
    form.action = "/Dashboards/preferencias/preferenciaXdia"; // 🔴 ajusta ruta

    // Día
    const inputDia = document.createElement("input");
    inputDia.type = "hidden";
    inputDia.name = "dia";
    inputDia.value = dia;

    // Mes
    const inputMes = document.createElement("input");
    inputMes.type = "hidden";
    inputMes.name = "mes";
    inputMes.value = mes + 1;

    // Año
    const inputAnio = document.createElement("input");
    inputAnio.type = "hidden";
    inputAnio.name = "anio";
    inputAnio.value = anio;

    form.appendChild(inputDia);
    form.appendChild(inputMes);
    form.appendChild(inputAnio);

    document.body.appendChild(form);
    form.submit();
}








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
