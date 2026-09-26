<?php

class interfacesController extends Controller
{
  private $anio;
  private $dao;
  private $daoDia;
  private $daoUsuario;
   // private $daoSegm;
    private $por;
    private $dia;
    private $mes;
    //private $anio;

    public function __construct() {
        $this->dao = new DaoDatosBD();
        $this->daoDia = new DaoDatosXdia();
        $this->daoSegm = new DaoDatosXsegm();
        $this->daoUsuario = new DaoUsuario();
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



    

    public function inicio(){
     $anio = $this->anio;
       

      $datosProductAnio = $this->dao->ComprasXproductosAnual($anio);
      $datosCompletosProdAnio = $this->por->porcentajeProductos(
        $datosProductAnio,
        $datosProductAnio[COUNT($datosProductAnio) - 1]['totalComprasXproductAnio']
      );
      $datosXcomprasCliente = $this->dao->comprasXsegmentosMFanio($anio);
      $datosCompletosXcomprasClientes = $this->por->porcentajeXsegmento(
        $datosXcomprasCliente,
        $datosXcomprasCliente[COUNT($datosXcomprasCliente) - 1]['totalVentas']);

        $datosTotalClientes = $this->dao->totalClientesXsegmentosAnio($anio);


       
        $datosCompletosClientes = $this->por->porcentajeXclientesAnio(
          $datosTotalClientes,
          $datosTotalClientes[COUNT($datosTotalClientes) - 1]['totalClientes']);

          $datosXciudadAnio = $this->dao->comprasXciudadAnual($this->anio);
          $datosCompletosXciudadAnio = $this->por->porcentajeVentasXciudad(
            $datosXciudadAnio, 
            $datosXciudadAnio[COUNT($datosXciudadAnio) - 1]['totalComprasAnio']);

            $datosXmesAnio = $this->dao->comprasXmesAnio($anio);
            $datosCompletosXmesAnio = $this->por->porcentajeXmesAnio($datosXmesAnio);
               
       $mesesT=[
      "Enero", "Febrero", "Marzo", "Abril", "Mayo", 
      "Junio", "Julio", "Agosto", "Septiembre",
      "Octubre", "Noviembre", "Diciembre"
      ];

      $datos = [

        "datosXproductos" => $datosCompletosProdAnio ,
        "datosXcomprasClientes"  =>  $datosCompletosXcomprasClientes ?? '',
        "datosXclientes"  => $datosCompletosClientes ?? '',
        "datosXciudad" => $datosCompletosXciudadAnio ?? '',
        "datosXmesAnio" => $datosCompletosXmesAnio ?? '',
        "mesesT"       => $mesesT ?? ''

      ];
      $this->render('inicio', $datos);
    }


  public function preferenciasXmes()
  {
     
   $anio = $this->anio;
   
   
   $meses = [];

       
     
    
   $meses = $this->dao->mesesRegistrados($anio);

   $lon = count($meses);
   
  for($i = 0; $i < $lon; $i++){

       $meses2[] = $meses[$i];

  }
   
  $mesesT=[
      "Enero", "Febrero", "Marzo", "Abril", "Mayo", 
      "Junio", "Julio", "Agosto", "Septiembre",
      "Octubre", "Noviembre", "Diciembre"
  ];
  $resultM = [];
  $logitud = count($meses2);
  
  for($i = 0; $i < $logitud; $i++){
   
  $digiMes = $meses2[$i] - 1;
 
  if (isset($mesesT[$digiMes])) {
        $resultM[] = $mesesT[$digiMes];
    }

  }
  
   $list = $this->dao->comprasXmes($anio);
   
   $ventasAnual = 0;
   $ventasXmes = [];
   
   for($i = 0; $i < COUNT($list) - 1; $i++){
  
    $ventasXmes[] = $list[$i]['totalVentas'];

   }

   $porsXmes = [];
   $porsXmes = $this->por->porcentajeXmes($ventasXmes, $list[COUNT($list) - 1]['totalComprasAnio']);

    $datos = [
      "anio" => $anio,
      "meses" => $resultM,
      "porcentajes" => $porsXmes,
      "ventasXmes"  => $ventasXmes
      

    ];
    //print_r($datos['meses']);
    $this->render('preferenciasMeses', $datos);
  }



  

 public function inicioChat()
  {
      $datos = [
      "anio" => ""
      

    ];
    //print_r($datos['meses']);
    $this->render('inicioChat', $datos);
  }


   public function reportes()
{
    // 1️⃣ Usuarios que pueden ser "usuario reporte"
    // (ajusta el método si filtras por rol o tipo específico)
    $usuariosReporteDisponibles = $this->daoUsuario->selectUsuariosReporte();

    // 2️⃣ Tipos de acceso (tabla tipo_acceso_usuario)
    $tiposAcceso = $this->daoUsuario->obtenerTiposAccesoUsuario();

    // 3️⃣ Usuarios reporte ya registrados (JOIN)
    $usuariosReporte = $this->daoUsuario->obtenerUsuariosReporte();
 
    // 4️⃣ Datos para la vista
    $datos = [
        'usuarios'        => $usuariosReporteDisponibles,
        'tiposAcceso'     => $tiposAcceso,
        'usuariosReporte' => $usuariosReporte,
        'anio'            => $this->anio
    ];

    // 5️⃣ Renderizar vista
    $this->render('reporteUrl', $datos);
}





  public function users()
  {
    session_start();

    $datosRolUser = $this->daoUsuario->selectTiposUsuario();
    $datosUsers = $this->daoUsuario->selectUsuarios();
    $datosUserLogueado = $this->daoUsuario->selectUsuario($_SESSION['usuario']['id_usuario']);
    
      $datos = [
      "tiposUsuario" => $datosRolUser,
      "usuarioLogueado" => $datosUserLogueado,
      "usuarios"  => $datosUsers 

      

    ];
    //print_r($datos['meses']);
    $this->render('userAndRegisterUser', $datos);
  }


 public function cerrarSesion()
{
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vaciar variables de sesión
    $_SESSION = [];

    // Eliminar cookie de sesión (MUY IMPORTANTE)
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destruir la sesión
    session_destroy();

    // Redirigir al login
    header('Location: /Dashboards/inicio/login');
    exit;
}


  public function login()
  {
      $datos = [
      "anio" => ""
      

    ];
    //print_r($datos['meses']);
    $this->render('login', $datos);
  }


public function panelUser()
  {
      $datos = [
      "usuario" => ""
      

    ];
    //print_r($datos['meses']);
    $this->render('panelUserNoAdmin', $datos);
  }

  public function iniciarSesion()
{
    if (isset($_SESSION['usuario'])) {
        header('Location: /Dashboards/inicio/inicio');
        exit;
    }

    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $correo     = $_POST['correo'] ?? null;
        $contrasena = $_POST['contrasena'] ?? null;

        if (!$correo || !$contrasena) {
            $error = 'Debe completar todos los campos';
        } else {

            $usuario = $this->daoUsuario->buscarPorCorreo($correo);

            if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
                $error = 'Correo o contraseña incorrectos';
            } else {

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // Crear sesión base
                $_SESSION['usuario'] = [
                    'id_usuario' => $usuario['id_usuario'],
                    'correo'     => $usuario['correo'],
                    'rol'        => $usuario['rol'],
                    'id_tipo'    => $usuario['id_tipo']
                ];

                // ================================
                // 🔐 CONTROL DE USUARIO REPORTE
                // ================================
                
                if ($usuario['rol'] === 'usuario reporte') {
                  
                    $acceso = $this->daoUsuario
                        ->obtenerAccesoReportePorUsuario($usuario['id_usuario']);

                    // ❌ No tiene acceso asignado
                 
                    if ($acceso['acceso'] !== 'Permitido' || empty($acceso['ruta_reporte'])) {
                        session_destroy();
                        $error = 'No tiene acceso a ningún reporte';
                        
                    } else {

                        // Guardar info de acceso en sesión (opcional)
                        $_SESSION['usuario']['acceso'] = $acceso['acceso'];
                        $_SESSION['usuario']['ruta_reporte'] = $acceso['ruta_reporte'];

                        // 🔁 Redirigir al reporte autorizado
                        
                        header('Location: ' . $acceso['ruta_reporte']);
                        exit;
                    }

                } else {
                    // Usuario normal / admin
                    header('Location: /Dashboards/inicio/inicio');
                    exit;
                }
            }
        }

        // Error → volver a login
        $this->render('login', ['mensaje' => $error]);
    }
}


 public function editarUsuario()
{
    session_start();

    $id = $_GET['id_usuario'] ?? 0;
    if ($id == 0) {
        header('Location: /Dashboards/inicio/users');
        exit;
    }

    $datosUser = $this->daoUsuario->selectUsuario($id);
    
    $_SESSION['user'] = [
        "idUsuario"     => $id,
        "datosUsuario"  => $datosUser,
        "verCampoPass"  => true
    ];

    $this->render('edicionDatosUser');
}


  

  public function update($id)
  {
     if(empty($id)){
            exit("No se estableció el parametro 'id'");
        }else{
            echo $id[2];
        }
  }

}
