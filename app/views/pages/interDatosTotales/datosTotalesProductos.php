<?php require_once APP . '/views/inc/headerDatosTotalesPro.php' ?>
<?php require_once APP . '/views/inc/sidebar.php' ?>

<h1> <?php  //print_r($datosProductos) ?></h1>

<div class="table-wrapper">
  


<table>
  <caption>Listado De Productos</caption>

  <thead>
    <tr>
      <th>ID</th>
      <th>Producto</th>
      <th>Marca</th>
      <th>Modelo</th>
      <th>Precio</th>
      <th>Preferencia</th>
    </tr>
  </thead>

  <tbody>

 <?php 
 
 foreach($datosProductos as $row){
    //print_r($row);
 
 ?> 
    <tr>
      <td><?php echo $row['producto_id'] ?></td>
      <td><?php echo $row['producto'] ?></td>
      <td><?php echo $row['marca'] ?></td>
      <td><?php echo $row['modelo'] ?></td>
      <td>$ <?php echo $row['precio'] ?></td>
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