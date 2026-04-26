<?php
session_start();

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/config/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Configuration des routes
$routes = [
    // Routes existantes (vers vos fichiers HTML)
    '' => ['type' => 'file', 'path' => 'views/frontoffice/layout/index.php'],
    'index.php' => ['type' => 'file', 'path' => 'views/frontoffice/layout/index.php'],
    'backoffice.php' => ['type' => 'file', 'path' => 'views/backoffice/layout/backoffice.php'],
    
    // Routes API User
    'api/users/list' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'list'],
    'api/users/doctors' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'doctors'],
    'api/users/create' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'create'],
    'api/users/update' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'update'],
    'api/users/delete' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'delete'],
    'api/users/register-patient' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'registerPatient'],
    'api/users/login-patient' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'loginPatient'],
    'api/users/reset-password' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'resetPassword'],
    'api/users/get-current-user' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'getCurrentUser'],
    // Nouvelles routes métier
    'api/users/search'     => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'search'],
    'api/users/stats'      => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'stats'],
    'api/users/export-pdf' => ['type' => 'controller', 'controller' => 'UserController', 'action' => 'exportPdf'],
];

// Récupérer l'URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$method = $_SERVER['REQUEST_METHOD'];

// Trouver la route correspondante
$routeFound = false;
foreach ($routes as $route => $config) {
    if ($route === $url || ($method === 'POST' && $route === $url)) {
        $routeFound = true;
        
        if ($config['type'] === 'file') {
            // Servir le fichier HTML existant
            $filePath = __DIR__ . '/' . $config['path'];
            if (file_exists($filePath)) {
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                if ($ext === 'php') {
                    require $filePath;
                } else {
                    header('Content-Type: ' . mime_content_type($filePath));
                    readfile($filePath);
                }
            } else {
                http_response_code(404);
                echo "Fichier non trouvé";
            }
        } else if ($config['type'] === 'controller') {
            // Appeler le contrôleur
            $controllerName = $config['controller'];
            $actionName = $config['action'];
            $controller = new $controllerName();
            $controller->$actionName();
        }
        break;
    }
}

// Route par défaut - rediriger vers index.html
if (!$routeFound) {
    if (file_exists(__DIR__ . '/views/frontoffice/layout/index.php')) {
        readfile(__DIR__ . '/views/frontoffice/layout/index.php');
    } else {
        http_response_code(404);
        echo "Page non trouvée";
    }
}