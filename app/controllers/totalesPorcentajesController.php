<?php

class totalesPorcentajesController extends Controller
{


    private $dao;
    private $daoDia;
    private $daoSegm;
    private $por;
    private $dia;
    private $mes;
    

    public function __construct() {
        $this->dao = new DaoDatosBD();
        $this->daoDia = new DaoDatosXdia();
        $this->daoSegm = new DaoDatosXsegm();
        $this->por = new porcentajes();
        
    }




public function datosTotalesProductos(){


   if (isset($_GET['submit'])) {

    // Recibe el JSON
    $jsonProductos = $_GET['datosProductos'] ?? '';

    // Decodifica a array asociativo
    $datosP = json_decode($jsonProductos, true);

    // Validación básica
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error al decodificar JSON');
    }

    $datos = [
        "datosProductos" => $datosP
    ];

    $this->render('interDatosTotales/datosTotalesProductos', $datos);
}

}


public function porcenXsegmMes(){


   

    // Recibe el JSON
    $json = $_GET['porcenXsegmMes'] ?? '';
    $anio = $_GET['anio'] ?? 0;
    $segmento = $_GET['segmento'] ?? '';
    
    // Decodifica a array asociativo
    $datosMS = json_decode($json, true);

    // Validación básica
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error al decodificar JSON');
    }

    $mesesT=[
      "Enero", "Febrero", "Marzo", "Abril", "Mayo", 
      "Junio", "Julio", "Agosto", "Septiembre",
      "Octubre", "Noviembre", "Diciembre"
      ];

    $datos = [
        "datosMesesXsegm" => $datosMS,
        "anio" => $anio,
        "segmento" => $segmento,
        "mesesT" => $mesesT
    ];

    $this->render('interDatosTotales/porcentajeXsegmXmes', $datos);



}


public function preferenciasXsegmMes()
{
    session_start();

    if (!isset($_SESSION['usuario'])) {
        header('Location: /login');
        exit;
    }

    $mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT);
    $anio = filter_input(INPUT_GET, 'anio', FILTER_VALIDATE_INT);
    $segmento = $_GET['segmento'] ?? '';
    $ventasAnual = filter_input(INPUT_GET, 'totalVentas', FILTER_VALIDATE_FLOAT) ?? 0;

    if (!$mes || $mes < 1 || $mes > 12) {
        header('Location: /Dashboards/inicio');
        exit;
    }

    if (!$anio || $anio < 2000) {
        $anio = date('Y');
    }

    if (!in_array($segmento, ['F', 'M'])) {
        header('Location: /Dashboards/inicio');
        exit;
    }

    // ================= CONSULTAS =================

    $detallesPM   = $this->daoSegm->ComprasXproductosMesSegm($mes, $segmento, $anio) ?? [];
    $totalProdMes = $this->daoSegm->TotalProductosVendidosMesSegm($mes, $segmento, $anio) ?? 0;
    $secciones    = $this->daoSegm->comprasXsesionMesSegm($mes, $segmento, $anio) ?? [];
    $totalMensual = $this->dao->ComprasXmes($anio) ?? [];

    $ventasSegF = $this->daoSegm->comprasXsegmentosMF($mes, $segmento, $anio) ?? [];

    $totalVentasMes = $totalMensual[$mes - 1]['totalVentas'] ?? 0;

    // Evitar divisiones por cero
    $totalVentasMes = max($totalVentasMes, 1);
    $totalProdMes   = max($totalProdMes, 1);
    $ventasAnual    = max($ventasAnual, 1);

    // ================= PORCENTAJES =================

    $detallesSegmPorcen = $this->por->porcentajeXsegmento(
        $ventasSegF,
        $totalVentasMes
    );

    $detallesProd = $this->por->porcentajeProductos(
        $detallesPM,
        $totalProdMes
    );

    $datosXciudad = $this->daoSegm->ComprasPorCiudadMensualSegm($mes, $segmento, $anio) ?? [];
    $totalVentasSegm = $ventasSegF[1]['totalVentas'] ?? 0;
    $totalVentasSegm = max($totalVentasSegm, 1);

    $datosCompletosXciudad = $this->por->porcentajeVentasXciudad(
        $datosXciudad,
        $totalVentasSegm
    );

    $datosXdias = $this->daoSegm->comprasXdiasSegm($mes, $anio, $segmento) ?? [];
    $datosCompletosXdias = $this->por->porcentajeVentasPorDia($datosXdias);

    $detallesSesPorcen = $this->por->porcentajeXsesion(
        $secciones,
        $ventasAnual
    );

    // ================= ENVÍO A VISTA =================

    $datos = [
        "detallesProd"     => $detallesProd,
        "totalProdMes"     => $totalProdMes,
        "datosXsesion"     => $detallesSesPorcen,
        "datosXsegmento"   => $detallesSegmPorcen,
        "ventasPorSegm"    => $ventasSegF,
        "segmento"         => $segmento,
        "datosXciudad"     => $datosCompletosXciudad,
        "datosXdias"       => $datosCompletosXdias,
        "mes"              => $mes,
        "anio"             => $anio
    ];

    $this->render('preferencias', $datos);
}



public function porcentXciudad(){


   

    // Recibe el JSON
    $json = $_GET['datosXciudad'] ?? '';
    
    
    // Decodifica a array asociativo
    $datosXciudad = json_decode($json, true);

    // Validación básica
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error al decodificar JSON');
    }

    $datos = [
        "datosXciudad" => $datosXciudad
        
    ];

    $this->render('interDatosTotales/datosXciudad', $datos);



}



}




?>