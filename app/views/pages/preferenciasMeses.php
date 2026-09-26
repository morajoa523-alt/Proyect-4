<?php require_once APP . '/views/inc/headerMeses.php' ?>
<?php require_once APP . '/views/inc/sidebar.php' ?>

<!-- CONTENIDO PRINCIPAL -->
<div class="content">

  <div class="texto-titulo">
    <h1 class="mb-4">Preferencias por Mes</h1>
    <p class="text-muted">Haz clic en un mes para ver las estadísticas detalladas.</p>
  </div>

  <div class="meses-grid">
    <?php
      $length = count($meses);
      for ($i = 0; $i < $length; $i++):
        $mes = $meses[$i];
        $porcentaje = $porcentajes[$i];
    ?>
      <div class="mes-card-wrapper">

        <form action="/Dashboards/update/insertDatosPreferencias"
              method="GET"
              class="link-card-form">

          <input type="hidden" name="mes" value="<?= $i + 1 ?>">
          <input type="hidden" name="porcentaje" value="<?= $porcentaje ?>">
          <input type="hidden" name="anio" value="<?= $anio ?>">
          <input type="hidden" name="ventasXmes" value="<?= $ventasXmes[$i] ?>">

          <button type="submit" name="submit" class="link-card-btn">

            <div class="mes-card">
              <div class="mes-card-header">
                <h4><?= $mes ?></h4>
                <span class="mes-porcentaje"><?= $porcentaje ?>%</span>
              </div>

              <!-- MINI GRÁFICO -->
              <div class="mes-chart">
                <canvas id="chartMes<?= $i ?>"></canvas>
              </div>
            </div>

          </button>
        </form>

      </div>
    <?php endfor; ?>
  </div>

</div>



<!-- =========================
     CHART.JS
========================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const porcentajes = <?= json_encode($porcentajes) ?>;

  porcentajes.forEach((valor, index) => {
    const ctx = document.getElementById(`chartMes${index}`);
    if (!ctx) return;

    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Porcentaje', 'Restante'],
        datasets: [{
          data: [valor, 100 - valor],
          backgroundColor: ['#032757', '#e5e7eb'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: { enabled: false }
        }
      }
    });
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


<?php require_once APP . '/views/inc/footer.php' ?>
