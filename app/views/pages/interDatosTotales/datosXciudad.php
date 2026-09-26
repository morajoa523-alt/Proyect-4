<?php require_once APP . '/views/inc/headerDatosTotalesPro.php' ?>
<?php require_once APP . '/views/inc/sidebar.php' ?>


<div class="table-wrapper">


<table>
  <caption>Listado De Ciudades</caption>

  <thead>
    <tr>
      <th>Provincia</th>
      <th>Ciudad</th>
      <th>Total clientes</th>
      <th>Total Compras</th>
      <th>Porcentaje</th>
      
    </tr>
  </thead>

  <tbody>

 <?php 
 
 foreach($datosXciudad as $row){
    //print_r($row);
 
 ?> 
    <tr>
      <td><?php echo $row['provincia'] ?></td>
      <td><?php echo $row['ciudad'] ?></td>
      <td><?php echo $row['totalClientes'] ?></td>
      <td><?php echo $row['totalCompras'] ?></td>
      
      <td class="col-porcentaje">
      <div class="barra-bg">
      <div class="barra-fill" style="width: <?= $row['porcentaje'] ?>%"></div>
      <span><?= $row['porcentaje'] ?>%</span>
      </div>
      </td>
    </tr>

     <?php } ?>


  </tbody>

  <tfoot>
    <tr>
      
    </tr>
  </tfoot>
</table>

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



<?php require_once APP . '/views/inc/footer.php' ?>