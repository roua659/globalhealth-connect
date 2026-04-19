<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';

// Démarrage de la session (corrigé)
Session::start();

// Déterminer la page demandée
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Redirection selon la page
switch ($page) {
    case 'dashboard':
        require_once 'views/backoffice.php';
        break;
        
    case 'rendezvous':
        require_once 'controllers/RendezVousController.php';
        $controller = new RendezVousController();
        
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $controller->create($_POST);
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php?page=rendezvous';
            header('Location: ' . $redirect);
            exit();
        } elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $rdvId = $_GET['id'] ?? $_POST['id_rdv'] ?? null;
            $result = $rdvId ? $controller->update($rdvId, $_POST) : ['success' => false, 'message' => 'Identifiant du rendez-vous manquant'];
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php?page=rendezvous';
            header('Location: ' . $redirect);
            exit();
        } elseif ($action === 'delete' && (isset($_GET['id']) || isset($_POST['id_rdv']))) {
            $rdvId = $_GET['id'] ?? $_POST['id_rdv'] ?? null;
            $result = $rdvId ? $controller->delete($rdvId) : ['success' => false, 'message' => 'Identifiant du rendez-vous manquant'];
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_GET['redirect_to']) ? $_GET['redirect_to'] : 'index.php?page=rendezvous');
            header('Location: ' . $redirect);
            exit();
        } elseif ($action === 'exportPDF') {
            $controller->exportToPDF();
            exit();
        } elseif ($action === 'exportCSV') {
            $data = $controller->index();
            exportToCSV($data, 'rendez_vous');
            exit();
        } elseif ($action === 'getStats') {
            $stats = $controller->getStatistics();
            header('Content-Type: application/json');
            echo json_encode(['rdv' => $stats]);
            exit();
        } elseif ($action === 'getLast' && isset($_GET['limit'])) {
            $data = $controller->index();
            header('Content-Type: application/json');
            echo json_encode(array_slice($data, 0, $_GET['limit']));
            exit();
        } elseif ($action === 'getOne' && isset($_GET['id'])) {
            $data = $controller->getOne($_GET['id']);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } elseif ($action === 'sendVideoLink' && isset($_GET['id'])) {
            $result = $controller->sendVideoLink($_GET['id']);
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit();
        } elseif ($action === 'checkReminders') {
            $reminders = $controller->getUpcomingReminders();
            header('Content-Type: application/json');
            echo json_encode(['reminders' => $reminders]);
            exit();
        } else {
            $rendez_vous = $controller->index();
            $stats = $controller->getStatistics();
            require_once 'views/backoffice.php';
        }
        break;

    case 'confirmation-rdv':
        require_once 'controllers/RendezVousController.php';
        $controller = new RendezVousController();

        if ($action === 'changeStatus' && isset($_GET['id'], $_GET['statut'])) {
            $result = $controller->updateStatus($_GET['id'], $_GET['statut']);
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            header('Location: index.php?page=confirmation-rdv');
            exit();
        } else {
            $rendez_vous = $controller->index();
            $stats = $controller->getStatistics();
            require_once 'views/backoffice.php';
        }
        break;
        
    case 'dossiers':
        require_once 'controllers/DossierMedicalController.php';
        $controller = new DossierMedicalController();
        
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $controller->create($_POST, $_FILES);
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php?page=dossiers';
            header('Location: ' . $redirect);
            exit();
        } elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $dossierId = $_GET['id'] ?? $_POST['id_dossier'] ?? null;
            $result = $dossierId ? $controller->update($dossierId, $_POST, $_FILES) : ['success' => false, 'message' => 'Identifiant du dossier manquant'];
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php?page=dossiers';
            header('Location: ' . $redirect);
            exit();
        } elseif ($action === 'delete' && (isset($_GET['id']) || isset($_POST['id_dossier']))) {
            $dossierId = $_GET['id'] ?? $_POST['id_dossier'] ?? null;
            $result = $dossierId ? $controller->delete($dossierId) : ['success' => false, 'message' => 'Identifiant du dossier manquant'];
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (!empty($_GET['redirect_to']) ? $_GET['redirect_to'] : 'index.php?page=dossiers');
            header('Location: ' . $redirect);
            exit();
        } elseif ($action === 'exportPDF') {
            $controller->exportToPDF();
            exit();
        } elseif ($action === 'exportCSV') {
            $data = $controller->index();
            exportToCSV($data, 'dossiers_medicaux');
            exit();
        } elseif ($action === 'getOne' && isset($_GET['id'])) {
            $data = $controller->getOne($_GET['id']);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } elseif ($action === 'getStats') {
            $stats = $controller->getStatistics();
            header('Content-Type: application/json');
            echo json_encode(['dossiers' => $stats]);
            exit();
        } else {
            $dossiers = $controller->index();
            $stats = $controller->getStatistics();
            require_once 'views/backoffice.php';
        }
        break;
        
    case 'statistiques':
        require_once 'controllers/RendezVousController.php';
        require_once 'controllers/DossierMedicalController.php';
        $rdvController = new RendezVousController();
        $dossierController = new DossierMedicalController();
        $rdvStats = $rdvController->getStatistics();
        $dossierStats = $dossierController->getStatistics();
        require_once 'views/backoffice.php';
        break;
        
    case 'logout':
        Session::destroy();
        header('Location: views/frontoffice.php');
        exit();
        break;
        
    default:
        require_once 'views/backoffice.php';
        break;
}
?>
