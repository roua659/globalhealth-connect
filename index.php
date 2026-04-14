<?php
// Routeur principal
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'consultation';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Autoloader simple
spl_autoload_register(function ($class_name) {
    $paths = [
        'controllers/' . $class_name . '.php',
        'models/' . $class_name . '.php'
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Router
switch ($controller) {
    case 'consultation':
        $controllerObj = new ConsultationController();
        switch ($action) {
            case 'index':
                $controllerObj->index();
                break;
            case 'list':
                $controllerObj->list();
                break;
            case 'get':
                if ($id) $controllerObj->get($id);
                break;
            case 'create':
                $controllerObj->create();
                break;
            case 'update':
                $controllerObj->update();
                break;
            case 'delete':
                if ($id) $controllerObj->delete($id);
                break;
            default:
                $controllerObj->index();
        }
        break;
        
    case 'suivie':
        $controllerObj = new SuivieController();
        switch ($action) {
            case 'index':
                $controllerObj->index();
                break;
            case 'list':
                $controllerObj->list();
                break;
            case 'get':
                if ($id) $controllerObj->get($id);
                break;
            case 'create':
                $controllerObj->create();
                break;
            case 'update':
                $controllerObj->update();
                break;
            case 'delete':
                if ($id) $controllerObj->delete($id);
                break;
            default:
                $controllerObj->index();
        }
        break;
        
    default:
        $controllerObj = new ConsultationController();
        $controllerObj->index();
}
?>