<?php
session_start();

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/app/controllers/',
        __DIR__ . '/app/models/',
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
    '' => ['type' => 'file', 'path' => 'public/index.html'],
    'index.html' => ['type' => 'file', 'path' => 'public/index.html'],
    'backoffice.html' => ['type' => 'file', 'path' => 'public/backoffice.html'],
    
    // Routes API pour AJAX (encapsule votre JS existant)
    'api/login' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'login'],
    'api/logout' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'logout'],
    'api/getDoctors' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'getDoctors'],
    'api/getReviews' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'getReviews'],
    'api/submitAppointment' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'submitAppointment'],
    'api/submitReview' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'submitReview'],
    'api/getStats' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'getStats'],
    
    // Routes backoffice CRUD
    'api/users/add' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'addUser'],
    'api/users/edit' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'editUser'],
    'api/users/delete' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'deleteUser'],
    'api/appointments/add' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'addAppointment'],
    'api/appointments/confirm' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'confirmPayment'],
    'api/reviews/approve' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'approveReview'],
    'api/reviews/report' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'reportReview'],
    'api/reviews/delete' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'deleteReview'],
    'api/reviews/notify' => ['type' => 'controller', 'controller' => 'LegacyController', 'action' => 'notifyPatient'],
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
                if ($ext === 'html') {
                    // Lire et inclure le fichier en conservant le JS
                    readfile($filePath);
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
    if (file_exists(__DIR__ . '/public/index.html')) {
        readfile(__DIR__ . '/public/index.html');
    } else {
        http_response_code(404);
        echo "Page non trouvée";
    }
}