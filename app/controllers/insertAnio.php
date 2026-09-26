<?php
class insertAnio extends Controller{


public function updateAnio(){


          $archivoJson = APP . '/helpers/datosJson/valorAnio.json';
      
       
      if (isset($_GET['anio'])) {
        
       $anio = (int) $_GET['anio'];

        $data = [
        'anio' => $anio
        ];

        file_put_contents($archivoJson, json_encode($data, JSON_PRETTY_PRINT));
       }
       $ini = new interfacesController();
       $ini->inicio();
       //require_once  APP . '/inicio/inicio';

       
       



}

public function insertDatosPreferencias(){

    $archivoJson = APP . '/helpers/datosJson/valorPreferenciaG.json';
      
       
      if (isset($_GET['submit'])) {
        
       $anio = (int) $_GET['anio'];
       
       $mes = (int) $_GET['mes'];
        
       $porcentajes = $_GET['porcentaje'] ?? '';
       $ventasXmes = $_GET['ventasXmes'] ?? '';
      
       $data = [
        'anio' => $anio,
        'mes' => $mes,
        'porcentajes' => $porcentajes,
        'ventasXmes' => $ventasXmes
        ];

        file_put_contents($archivoJson, json_encode($data, JSON_PRETTY_PRINT));

        $objPrefe = new preferenciasController();
        $objPrefe->preferenciaMes();
}

      





}

}




?>