<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Publication.php';
require_once __DIR__ . '/../controllers/PublicationController.php';

// Create controller instance
$controller = new PublicationController();

// Get the action from query parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Route to appropriate method
switch ($action) {
    case 'list':
    case 'index':
        $controller->index();
        break;
    case 'show':
        $controller->show();
        break;
    case 'store':
        $controller->store();
        break;
    case 'update':
        $controller->update();
        break;
    case 'destroy':
        $controller->destroy();
        break;
    case 'search':
        $controller->search();
        break;
    case 'approved-comments':
        $controller->approvedComments();
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Action not found']);
        break;
}
