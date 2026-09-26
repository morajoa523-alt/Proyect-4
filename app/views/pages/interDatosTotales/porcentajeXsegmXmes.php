<?php require_once APP . '/views/inc/headerPorcenXsegmXmes.php' ?>
<?php require_once APP . '/views/inc/sidebar.php' ?>

<div class="contenedor-base">



    <h2>Compras por Mes</h2>

    <?php if (empty($datosMesesXsegm)): ?>
        <p class="texto-vacio">No hay datos disponibles</p>
    <?php else: ?>
    
    <table class="tabla-meses">
        <thead>
            <tr>
                <th>Mes</th>
                <th>Total Compras</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
 
        <?php foreach ($datosMesesXsegm as $row): ?>
<tr class="fila-submit">
    <td colspan="3">
        <form method="GET" action="/Dashboards/datosTotales/preferenciasXsegmMes">
            <input type="hidden" name="anio" value="<?= htmlspecialchars($anio) ?>">
            <input type="hidden" name="segmento" value="<?= htmlspecialchars($segmento) ?>">
            <input type="hidden" name="mes" value="<?= htmlspecialchars($row['mes']) ?>">
            <input type="hidden" name="totalVentas" value="<?= htmlspecialchars($row['totalVentas']) ?>">
            
            <button type="submit" class="btn-fila">
                <span><?= $mesesT[$row['mes'] - 1] ?></span>
                <span><?= htmlspecialchars($row['totalVentas']) ?></span>
                <span><?= htmlspecialchars($row['porcentaje']) ?>%</span>
            </button>
        </form>
    </td>
</tr>
<?php endforeach; ?>

<?php endif; ?>


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

<?php require_once APP . '/views/inc/footer.php' ?>