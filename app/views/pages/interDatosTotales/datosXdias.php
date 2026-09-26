<?php require_once APP . '/views/inc/headerPorcenXdia.php'; ?>
<?php require_once APP . '/views/inc/sidebar.php'; ?>

<?php
// Ordenar por día ascendente
usort($datosXdias, function ($a, $b) {
    return $a['dia'] <=> $b['dia'];
});


?>

<div class="contenedor-dias">

    <?php if (!empty($datosXdias)) : ?>

        <?php if($segmento == null){ ?>
        <?php foreach ($datosXdias as $item) : ?>

    

      <form action="/Dashboards/preferencias/preferenciaXdia" method="GET" class="link-card-form">
        <input type="hidden" name="dia" value="<?= $item['dia']; ?>">
        <input type="hidden" name="mes" value="<?= $mes; ?>">
        <input type="hidden" name="anio" value="<?= $anio; ?>">
        <input type="hidden" name="segmento" value="<?= $segmento; ?>">
        
       


    <button type="submit" name="submit" class="link-card-btn">
            <div class="card-dia">
                <div class="card-dia-numero">
                    Día <?= htmlspecialchars($item['dia']) ?>
                </div>
                <div class="card-dia-porcentaje">
                    <?= htmlspecialchars($item['porcentaje']) ?>%
                </div>
            </div>
    </button>

        </form>


        <?php endforeach; ?>
        <?php }else{ ?>
            
        <?php foreach ($datosXdias as $item) : ?>
        <form action="/Dashboards/preferencias/preferenciaXdiaSegm" method="GET" class="link-card-form">
        <input type="hidden" name="dia" value="<?= $item['dia']; ?>">
        <input type="hidden" name="mes" value="<?= $mes; ?>">
        <input type="hidden" name="anio" value="<?= $anio; ?>">
        <input type="hidden" name="segmento" value="<?= $segmento; ?>">
        
       


    <button type="submit" name="submit" class="link-card-btn">
            <div class="card-dia">
                <div class="card-dia-numero">
                    Día <?= htmlspecialchars($item['dia']) ?>
                </div>
                <div class="card-dia-porcentaje">
                    <?= htmlspecialchars($item['porcentaje']) ?>%
                </div>
            </div>
    </button>

        </form>


        <?php endforeach; ?>


            <?php } ?>
    <?php else : ?>
        <p>No hay datos disponibles</p>
    <?php endif; ?>

</div>

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
