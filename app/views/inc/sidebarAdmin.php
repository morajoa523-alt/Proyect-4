<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$anioActual = date('Y');

if (!isset($_SESSION['anio_seleccionado'])) {
    $_SESSION['anio_seleccionado'] = $anioActual;
}

// Si viene por POST desde el selector
if (isset($_GET['anio'])) {
    $_SESSION['anio_seleccionado'] = (int) $_GET['anio'];
}

?>

<div class="sidebar">
<!-- SELECTOR DE AÑO -->
<form method="GET" action="/Dashboards/update/updateAnio" class="year-selector">
    <button type="button" class="year-btn" id="yearPrev">◀</button>

    <input
        type="text"
        name="anio"
        id="yearInput"
        value="<?= $_SESSION['anio_seleccionado']?>"
        readonly
    >

    <button type="button"  class="year-btn" id="yearNext">▶</button>

    <button type="submit" name="submit" class="year-submit">
        Aplicar
    </button>

</form>




    <h2>Dashboard</h2>
    
    <a href="/Dashboards/inicio/inicio">🏠 Inicio</a>
    <a href="/Dashboards/preferencias/preferenciasXsegmentF">📦 Segment F</a>
    <a href="/Dashboards/preferencias/preferenciasXsegmentM">👥 Segment M</a>
    <a href="/Dashboards/inicio/users">👥 Panel Datos Usuarios</a>
    <a href="/Dashboards/inicio/reportes">📊 Reportes</a>
    
    <a href="/Dashboards/inicio/cerrarSesion">👥 Cerrar Sesión</a>
</div>

