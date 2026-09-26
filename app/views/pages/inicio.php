<?php require_once APP . '/views/inc/header.php'; ?>
<?php require_once APP . '/views/inc/sidebar.php'; ?>

<div class="content-Base">

<?php if($datosXcomprasClientes[0]['totalVentas'] !== 0){ ?>
  <!-- ================= IZQUIERDA ================= -->
  <div class="content-izquierdo">

    <div class="producto-top">
      <p><strong>Producto Más Comprado</strong></p>
      <p>Nombre: <?= $datosXproductos[0]['producto'] ?></p>
      <p>Marca: <?= $datosXproductos[0]['marca'] ?></p>
      <p>Modelo: <?= $datosXproductos[0]['modelo'] ?></p>
      <p>Precio: $ <?= $datosXproductos[0]['precio'] ?></p>
      <p>Porcentaje: <?= $datosXproductos[0]['porcentaje'] ?>%</p>

      <form action="/Dashboards/datosTotales/datosTotalesProductos" method="GET">
        <input type="hidden" name="datosProductos" value='<?= htmlspecialchars(json_encode($datosXproductos)) ?>'>
        <button type="submit" name="submit" >General</button>
      </form>

      <canvas id="chartProducto"></canvas>
    </div>

    <div class="total-usuarios-clientes">
      <p><strong>Total clientes:</strong> <?= $datosXclientes[0]['totalClientes'] ?></p>
      
    </div>

    <div class="totalUsuariosX-segmento">
      <p>Mujeres: <?= $datosXclientes[0]['sexo'] === 'F' ? $datosXclientes[0]['cantidad'] : $datosXclientes[1]['cantidad'] ?? 0 ?></p>
      <p>Hombres: <?= $datosXclientes[0]['sexo'] === 'M' ? $datosXclientes[0]['cantidad'] : $datosXclientes[1]['cantidad'] ?? 0?></p>
      <canvas id="chartUsuariosSegmento"></canvas>
    </div>

  </div>

  <!-- ================= DERECHA ================= -->
  <div class="content-derecho">

    <div class="totalCompras">
      <p><strong>Total Compras:</strong> <?= $datosXcomprasClientes[0]['totalVentas'] ?></p>
      
    </div>

    <div class="totalCompras-segmentos-porcentaje">
      <p>Mujeres: <?= $datosXcomprasClientes[0]['sexo'] === 'F' ? $datosXcomprasClientes[0]['cantidad'] : $datosXcomprasClientes[1]['cantidad'] ?? 0 ?></p>
      <p>Hombres: <?= $datosXcomprasClientes[0]['sexo'] === 'M' ? $datosXcomprasClientes[0]['cantidad'] : $datosXcomprasClientes[1]['cantidad'] ?? 0 ?></p>
      <canvas id="chartComprasSegmento"></canvas>
    </div>

    <div class="ciudadConMayor-compras">
      <p><strong>Ciudad con mayor compras</strong></p>
      <p><?= $datosXciudad[0]['ciudad'] ?> (<?= $datosXciudad[0]['provincia'] ?>)</p>
      <p>Clientes: <?= $datosXciudad[0]['totalClientes'] ?></p>
      <p>Compras: <?= $datosXciudad[0]['totalCompras'] ?></p>

      <form action="/Dashboards/datosTotales/porcentXciudad" method="GET">
        <input type="hidden" name="datosXciudad" value='<?= htmlspecialchars(json_encode($datosXciudad)) ?>'>
        <button type="submit">General</button>
      </form>

      <canvas id="chartCiudad"></canvas>
    </div>

    <div class="mes-conMayorVentas">
      
      <p><strong>Mes con mayor compras</strong></p>
      <p><?= $mesesT[$datosXmesAnio[0]['mes'] - 1] ?? '' ?></p>
      <p>Total: <?= $datosXmesAnio[0]['totalVentas'] ?? 0?></p>

      <a href="/Dashboards/inicio/preferenciasXmes"><button>General</button></a>

     
    </div>

  </div>

  <?php }else{ ?>

  <h1> NO HAY DATOS REGISTRADOS EN ESTE AÑO </h1>

  <?php } ?>
</div><!-------Fin content base ----------->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {




  /* =========================
     DATOS DESDE PHP
  ========================= */

  const datosProductos = <?= json_encode($datosXproductos) ?>;
  const datosClientes  = <?= json_encode($datosXclientes) ?>;
  const datosCompras   = <?= json_encode($datosXcomprasClientes) ?>;
  const datosCiudad    = <?= json_encode($datosXciudad) ?>;
  const datosMes       = <?= json_encode($datosXmesAnio) ?>;
  
  /* =========================
     PRODUCTO
  ========================= */
  const chartProducto = document.getElementById('chartProducto');
  if (chartProducto && datosProductos.length) {
    new Chart(chartProducto, {
      type: 'bar',
      data: {
        labels: [datosProductos[0].producto],
        datasets: [{
          data: [datosProductos[0].porcentaje],
          backgroundColor: '#032757'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
  }

  /* =========================
     USUARIOS
  ========================= */
  const chartUsuarios = document.getElementById('chartUsuariosSegmento');
  if (chartUsuarios && datosClientes.length) {
    new Chart(chartUsuarios, {
      type: 'doughnut',
      data: {
        labels: ['Mujeres', 'Hombres'],
        datasets: [{
          data: [
            datosClientes.find(x => x.sexo === 'F')?.cantidad || 0,
            datosClientes.find(x => x.sexo === 'M')?.cantidad || 0
          ],
          backgroundColor: ['#BD004E', '#032757']
        }]
      }
    });
  }

  /* =========================
     COMPRAS
  ========================= */
  const chartCompras = document.getElementById('chartComprasSegmento');
  if (chartCompras && datosCompras.length) {
    new Chart(chartCompras, {
      type: 'doughnut',
      data: {
        labels: ['Mujeres', 'Hombres'],
        datasets: [{
          data: [
            datosCompras.find(x => x.sexo === 'F')?.cantidad || 0,
            datosCompras.find(x => x.sexo === 'M')?.cantidad || 0
          ],
          backgroundColor: ['#BD004E', '#032757']
        }]
      }
    });
  }

  /* =========================
     CIUDAD
  ========================= */
  const chartCiudad = document.getElementById('chartCiudad');
  if (chartCiudad && datosCiudad.length) {
    new Chart(chartCiudad, {
      type: 'bar',
      data: {
        labels: ['Clientes', 'Compras'],
        datasets: [{
          data: [
            datosCiudad[0].totalClientes || 0,
            datosCiudad[0].totalCompras || 0
          ],
          backgroundColor: ['#032757', '#BD004E']
        }]
      }
    });
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
