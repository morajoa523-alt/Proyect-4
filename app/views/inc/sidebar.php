<?php

$tipoUsuario = $_SESSION['usuario']['id_tipo'] ?? null;

switch ($tipoUsuario) {
    case 1: // ADMIN
        require_once APP . '/views/inc/sidebarAdmin.php';
        break;

    case 2: // USUARIO NORMAL
        require_once APP . '/views/inc/sidebarAnalista.php';
        break;

    case 3:
        // Fallback seguro
        
        require_once APP . '/views/inc/sidebarUserAvanzado.php';
        break;

    case 4:
        // Fallback seguro
        
        require_once APP . '/views/inc/sidebarUserReporte.php';
        break;

    
}



?>