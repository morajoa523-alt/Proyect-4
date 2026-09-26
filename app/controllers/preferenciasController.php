<?php
// controllers/PreferenciasController.php
require_once __DIR__ . '/../models/DaoDatosBD.php';
require_once __DIR__ . '/../helpers/porcentajes.php';

class preferenciasController extends Controller{
    private $dao;
    private $daoDia;
   // private $daoSegm;
    private $por;
    private $dia;
    private $mes;
    private $anio;

    public function __construct() {
        $this->dao = new DaoDatosBD();
        $this->daoDia = new DaoDatosXdia();
        $this->daoSegm = new DaoDatosXsegm();
        $this->por = new porcentajes();
        
        $archivoJson = APP . '/helpers/datosJson/valorAnio.json';
      
        $anio = null;

       if (file_exists($archivoJson)) {
        
       $contenido = file_get_contents($archivoJson);
       $data = json_decode($contenido, true);

       if (isset($data['anio'])) {
        $anio = (int) $data['anio'];
        }
      }  
        $this->anio = $anio;
    }

    
    public function preferenciaMes() {

        $archivoJson = APP . '/helpers/datosJson/valorPreferenciaG.json';

        if(file_exists($archivoJson)){

            $data = file_get_contents($archivoJson);

           $datosJ =  json_decode($data, true);


        }
        

       

          $anio = $datosJ['anio']??  (int)date('Y');
          $mes  = $datosJ['mes']?? (int)date('n');
          $ventasXmes  = $datosJ['ventasXmes'] ?? 0;

          
           
          
           $comprasXproduc = $this->dao->ComprasXproductos($mes, $anio);
          
           $totalProdMes = $this->dao->TotalProductosVendidosMes($mes, $anio);
           $detalles = $this->por->porcentajeProductos($comprasXproduc, $totalProdMes);

           $datosSegment = $this->dao->comprasXsegmentosMF($mes, $anio);
           $detallesSegmPorcen = $this->por->porcentajeXsegmento($datosSegment, $ventasXmes);
           $detallesSesion = $this->dao->comprasXsesion($mes, $anio);
           $detallesSesPorcen = $this->por->porcentajeXsesion($detallesSesion, $ventasXmes);
 
           $datosXciudad = $this->dao->ComprasPorCiudadMensual($mes, $anio);
           $datosCompletosXciudad = $this->por->porcentajeVentasXciudad($datosXciudad, $ventasXmes);

           

           $datosXdias = $this->daoDia->comprasXdias($mes, $anio);
           $datosCompletosXdias = $this->por->porcentajeVentasPorDia($datosXdias);

           //print_r($datosCompletosXdias);
          //los porcentaje de segmento se en listan de forma mayor a menor por lo cual 
          // el primer valor siempre va ser el mayor porcentaje, a excesion de que
          // haya un 50% que serían dos valores
          $diasT =[

            '1' => 'Lunes',
            '2'

          ];
           $datos = [
            "detallesProd" => $detalles,
            "totalProdMes" => $totalProdMes,
            "datosXsegmento" => $detallesSegmPorcen,
            "datosXsesion" =>  $detallesSesPorcen,
            "datosXciudad" =>  $datosCompletosXciudad,
            'segmento'    => null,
            'ventasXmes'    => $ventasXmes,
            'datosXdias'    => $datosCompletosXdias,
            
            "mes" => $mes,
            "anio" => $anio
           ];

         
        $this->render('preferencias', $datos);


        }
       

         
    


    public function preferenciaXdia()
{
    // 1. Obtener fecha (día específico)
    $dia  = isset($_GET['dia'])  ? (int) $_GET['dia']  : (int) date('j');
    $mes  = isset($_GET['mes'])  ? (int) $_GET['mes']  : (int) date('n');
    $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : (int) date('Y');
    //$segmento = isset($_POST['segmento']) ?? null;

    
    
    // 2. Total de ventas del día
    $ventasXdia = $this->daoDia->comprasXdia($mes, $anio, $dia);
   
    $totalVentasDia = $ventasXdia[0]['totalVentas'] ?? 0;

    

    /* ==========================
       VALIDACIÓN CLAVE
       ========================== */
    if ($totalVentasDia <= 0) {

        // No hubo ventas → enviar todo en null
        $datos = [
            'detallesProd'   => null,
            'totalProdDia'   => null,
            'datosXsegmento' => null,
            'datosXsesion'   => null,
            'datosXciudad'   => null,
            //'segmento'  => null,
            'dia'            => $dia,
            'mes'            => $mes,
            'anio'           => $anio
        ];

        $this->render('preferenciasDia', $datos);
        return; // corta ejecución
    }

    /* ==========================
       SI HAY VENTAS
       ========================== */

    // 3. Productos vendidos en el día
    $comprasXproductoDia = $this->daoDia->ComprasXproductosXdia($dia, $mes, $anio);

    // 4. Total de productos vendidos en el día
    $totalProdDia = $this->daoDia->TotalProductosVendidosDia($dia, $mes, $anio);

    // 5. Porcentaje por producto
    $detallesProd = $this->por->porcentajeProductos(
        $comprasXproductoDia,
        $totalProdDia
    );

    // 6. Segmentos MF por día
    $datosSegmentoDia = $this->daoDia->comprasXsegmentosMFDia($dia, $mes, $anio);
    $detallesSegmPorcen = $this->por->porcentajeXsegmento(
        $datosSegmentoDia,
        $totalVentasDia
    );

    // 7. Sesiones por día
    $datosSesionDia = $this->daoDia->comprasXsesionDia($dia, $mes, $anio);
    $detallesSesPorcen = $this->por->porcentajeXsesion(
        $datosSesionDia,
        $totalVentasDia
    );

    $datosXciudadDia = $this->daoDia->comprasXciudadXdia($dia, $mes, $anio);
    $datosXciudadDiaCompletos = $this->por->porcentajeVentasXciudad($datosXciudadDia, $totalVentasDia);

    // 8. Enviar datos a la vista
    $datos = [
        'detallesProd'     => $detallesProd,
        'totalProdDia'     => $totalProdDia,
        'datosXsegmento'   => $detallesSegmPorcen,
        'datosXsesion'     => $detallesSesPorcen,
        'datosXciudad'     => $datosXciudadDiaCompletos,
        //'segmento'         => $segmento,
        'dia'              => $dia,
        'mes'              => $mes,
        'anio'             => $anio
    ];

    $this->render('preferenciasDia', $datos);
}




 
public function porcentXdias(){

$json = $_GET['datosXdias'] ?? '';
$mes = $_GET['mes'] ?? 0;
$anio = $_GET['anio'] ?? 0;
$ventasPorSegm = $_GET['ventasPorSegm'] ?? '';
$segmento = $_GET['segmento'] ?? null;

$datosXdias = json_decode($json, true);

    // Validación básica
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error al decodificar JSON');
    }


 
$datos = [


    'datosXdias'        => $datosXdias,
    'mes'               => $mes,
    'anio'              => $anio,
    'ventasPorSegm'     => $ventasPorSegm,
    'segmento'          => $segmento

];



    $this->render('interDatosTotales/datosXdias', $datos);
}






public function preferenciasXsegmentF()
{
   

        
        $anio = $this->anio;
        
       

        // Ventas mensuales segmento Femenino
        $ventasSegF = $this->daoSegm->ComprasMesSegm('F', $anio);
        $detallesP = $this->daoSegm->ComprasXproductos('F',$anio);
        $totalProd = $this->dao->TotalProductosVendidos($anio);
        $secciones = $this->daoSegm->comprasXsesion($anio, 'F');
        $totalVentas = $this->dao->TotalVentas($anio);
       
        $porsentSegmMes = $this->por->porcentajeVentasXMes($ventasSegF, $totalVentas);


        $detallesProd = $this->por->porcentajeProductos(
        $detallesP,
        $totalProd
        );

    //print_r($detallesProd);

    // 7. Sesiones
    //echo "--------------p ";
    $detallesSesPorcen = $this->por->porcentajeXsesion(
        $secciones,
        $totalVentas
    );

    


    $datosXtallas = $this->daoSegm->comprasPorTallaSexo($anio, 'F', null);
     $datosCompletosXtallas = $this->por->porcentajeComprasPorTalla($datosXtallas, $totalVentas);
    
     $totalComprasSegm = $this->daoSegm->TotalVentasAnualSegm($anio, 'F');

     $datosXciudad = $this->dao->ComprasPorCiudadAnual($anio, 'F');
     $datosCompletosXciudadSegm = $this->por->porcentajeVentasXciudad($datosXciudad, $totalComprasSegm);


       
        $datos = [
            'segmento' => 'F',
            'ventas'   => $ventasSegF,
            'anio'     => $anio,
            'detallesProd' => $detallesProd,
            'datosXsesion' => $detallesSesPorcen,
            'porcentajeMes' => $porsentSegmMes,
            'totalVentasAnual' => $totalVentas, 
            'datosXtallas' => $datosCompletosXtallas,
            'totalComprasSegm' => $totalComprasSegm,
            'datosXciudadAnualSegm' => $datosCompletosXciudadSegm
            
        ];

        $this->render('segmentF', $datos);
    
}

public function preferenciasXsegmentM()
{
    
   $anio = $this->anio;

        // Ventas mensuales segmento Masculino
        $ventasSegM = $this->daoSegm->ComprasMesSegm('M', $anio);
        $totalVentas = $this->dao->TotalVentas($anio);


        $detallesP = $this->daoSegm->ComprasXproductos('M',$anio);
        $totalProd = $this->dao->TotalProductosVendidos($anio);
        $secciones = $this->daoSegm->comprasXsesion($anio, 'M');
        
        
       $porsentSegmMes = $this->por->porcentajeVentasXMes($ventasSegM, $totalVentas);

        $detallesProd = $this->por->porcentajeProductos(
        $detallesP,
        $totalProd
    );
    
    //print_r($detallesProd);

    // 7. Sesiones
    //echo "--------------l ";
    $detallesSesPorcen = $this->por->porcentajeXsesion(
        $secciones,
        $totalVentas
    );

    $datosXtallas = $this->daoSegm->comprasPorTallaSexo($anio, 'M', null);
    $datosCompletosXtallas = $this->por->porcentajeComprasPorTalla($datosXtallas, $totalVentas);
    $totalComprasSegm = $this->daoSegm->TotalVentasAnualSegm($anio, 'M');

    $datosXciudad = $this->dao->ComprasPorCiudadAnual($anio, 'M');
    $datosCompletosXciudadSegm = $this->por->porcentajeVentasXciudad($datosXciudad, $totalComprasSegm);
    //print_r($detallesSesPorcen);
       
        $datos = [
            'segmento' => 'M',
            'ventas'   => $ventasSegM,
            'anio'     => $anio,
            'detallesProd' => $detallesProd,
            'datosXsesion' => $detallesSesPorcen,
            'porcentajeMes' => $porsentSegmMes,
            'totalVentasAnual' => $totalVentas,
            'datosXtallas' => $datosCompletosXtallas,
            'totalComprasSegm' => $totalComprasSegm,
            'datosXciudadAnualSegm' => $datosCompletosXciudadSegm
        ];

        $this->render('segmentM', $datos);
    
}

       

         

public function preferenciaXdiaSegm()
{
    // ==========================
    // 1. Obtener fecha y segmento
    // ==========================

    
    $dia  = isset($_GET['dia'])  ? (int) $_GET['dia']  : (int) date('j');
    $mes  = isset($_GET['mes'])  ? (int) $_GET['mes']  : (int) date('n');
    $anio = isset($_GET['anio']) ? (int) $_GET['anio'] : (int) date('Y');

    // Segmento (sexo)
    $sexo = isset($_GET['segmento']) ? $_GET['segmento'] : null;
     
    /* ==========================
       VALIDACIÓN DE SEGMENTO
       ========================== */
    if ($sexo === null) {
        $this->render('preferenciasDia', [
            'detallesProd'   => null,
            'totalProdDia'   => null,
            'datosXsegmento' => null,
            'datosXsesion'   => null,
            'dia'            => $dia,
            'mes'            => $mes,
            'anio'           => $anio
        ]);
        return;
    }

    // ==========================
    // 2. Total de ventas del día (SEGMENTO)
    // ==========================
    $ventasXdia = $this->daoDia->comprasXdiaSegm($mes, $anio, $dia, $sexo);
    $totalVentasDia = $ventasXdia[0]['totalVentas'] ?? 0;

    /* ==========================
       VALIDACIÓN CLAVE
       ========================== */
    if ($totalVentasDia <= 0) {

        $datos = [
            'detallesProd'   => null,
            'totalProdDia'   => null,
            'datosXsegmento' => null,
            'datosXsesion'   => null,
            'datosXciudad'   => null,
            'dia'            => $dia,
            'mes'            => $mes,
            'anio'           => $anio,
            'sexo'           => $sexo
        ];

        $this->render('preferenciasDia', $datos);
        return;
    }

    /* ==========================
       SI HAY VENTAS (SEGMENTO)
       ========================== */

    // 3. Productos vendidos en el día (SEGMENTO)
    $comprasXproductoDia = $this->daoDia
        ->ComprasXproductosXdiaSegm($dia, $mes, $anio, $sexo);

    // 4. Total de productos vendidos en el día (SEGMENTO)
    $totalProdDia = $this->daoDia
        ->TotalProductosVendidosDiaSegm($dia, $mes, $anio, $sexo);

    // 5. Porcentaje por producto
    $detallesProd = $this->por->porcentajeProductos(
        $comprasXproductoDia,
        $totalProdDia
    );

    // 6. Segmento MF del día (SEGMENTO)
    //    → aquí solo retorna ese segmento, pero mantiene estructura
    $datosSegmentoDia = $this->daoDia
        ->comprasXsegmentosMFDiaSegm($dia, $mes, $anio, $sexo);

    $detallesSegmPorcen = $this->por->porcentajeXsegmento(
        $datosSegmentoDia,
        $totalVentasDia
    );

    // 7. Sesiones por día (SEGMENTO)
    $datosSesionDia = $this->daoDia
        ->comprasXsesionDiaSegm($dia, $mes, $anio, $sexo);

    $detallesSesPorcen = $this->por->porcentajeXsesion(
        $datosSesionDia,
        $totalVentasDia
    );

    $datosXciudadDia = $this->daoSegm->comprasXciudadXdiaSegm($dia, $mes, $anio, $sexo);
    $datosXciudadDiaCompletos = $this->por->porcentajeVentasXciudad($datosXciudadDia, $totalVentasDia);


    

    // ==========================
    // 8. Enviar datos a la vista
    // ==========================
    $datos = [
        'detallesProd'     => $detallesProd,
        'totalProdDia'     => $totalProdDia,
        'datosXsegmento'   => $detallesSegmPorcen,
        'datosXsesion'     => $detallesSesPorcen,
        'datosXciudad'     => $datosXciudadDiaCompletos,
        'dia'              => $dia,
        'mes'              => $mes,
        'anio'             => $anio,
        'sexo'             => $sexo
    ];

    $this->render('preferenciasDia', $datos);
}

    
    
}


