<?php

class Core
{
    protected $controller = ['interfacesController'];
    protected $method = ['inicio'];
    protected $parameters  = [];

    public function __construct()
    {
        
        $url = $this->getUrl();
        $route = new Router();
        

        $found = false;

foreach ($route->rutas as $i => $rt) {

    if ($url && $url[0] == $rt) {

        if (file_exists('../app/Controllers/' . $route->rutasController[$i] . '.php')) {
            $this->controller = $route->rutasController[$i];
            unset($url[0]);
        }

        require_once '../app/Controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        $found = true;
        break;
    }
}

// 👇 si NO se encontró ninguna ruta
if (!$found) {
    $this->method = $this->method[0];
    require_once '../app/Controllers/' . $this->controller[0] . '.php';
    $this->controller = new $this->controller[0];
}


          
        
        // Método
        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            
            $this->method = $url[1];
            unset($url[1]);
        }

        // Parámetros restantes
        $this->parameters = $url ? array_values($url) : [];

        // Ejecutar controlador -> método
        
        call_user_func_array([$this->controller, $this->method], $this->parameters);
       
    }





    public function getUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}

?>
