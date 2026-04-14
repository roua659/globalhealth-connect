<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/PublicationController.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$controller = new PublicationController();
$action = $_GET['action'] ?? 'index';

try {
    switch ($action) {
        case 'index':
        case 'list':
            $controller->index();
            break;
        
        case 'show':
        case 'get':
        case 'get-with-comments':
            $controller->show();
            break;
        
        case 'store':
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'POST method required']);
                break;
            }
            $controller->store();
            break;
        
        case 'update':
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'POST method required']);
                break;
            }
            $controller->update();
            break;
        
        case 'destroy':
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'POST method required']);
                break;
            }
            $controller->destroy();
            break;
        
        case 'search':
            $controller->search();
            break;
        
        case 'approved-comments':
            $controller->approvedComments();
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
