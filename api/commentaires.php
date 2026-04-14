<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/CommentaireController.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$controller = new CommentaireController();
$action = $_GET['action'] ?? 'index';

try {
    switch ($action) {
        case 'index':
        case 'list':
        case 'by-publication':
            $controller->index();
            break;
        
        case 'show':
        case 'get':
            $controller->show();
            break;
        
        case 'approved':
            $controller->approved();
            break;
        
        case 'by-user':
            $controller->byUser();
            break;
        
        case 'pending':
            $controller->pending();
            break;
        
        case 'flagged':
            $controller->flagged();
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
        
        case 'approve':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'POST method required']);
                break;
            }
            $controller->approve();
            break;
        
        case 'reject':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'POST method required']);
                break;
            }
            $controller->reject();
            break;
        
        case 'like':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'POST method required']);
                break;
            }
            $controller->like();
            break;
        
        case 'report':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'POST method required']);
                break;
            }
            $controller->report();
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
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
