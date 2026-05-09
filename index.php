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
    case 'frontoffice':
        require_once 'views/frontoffice.php';
        break;

    case 'dashboard':
        require_once 'views/backoffice.php';
        break;

    case 'users':
    case 'patients':
    case 'medecins':
        require_once 'controllers/UtilisateurController.php';
        $controller = new UtilisateurController();

        if ($action === 'list') {
            $controller->list();
            exit();
        } elseif ($action === 'doctors') {
            $controller->doctors();
            exit();
        } elseif ($action === 'patients') {
            $controller->patients();
            exit();
        } elseif ($action === 'getOne' && isset($_GET['id'])) {
            $controller->getOne($_GET['id']);
            exit();
        } elseif ($action === 'create') {
            $controller->create();
            exit();
        } elseif ($action === 'update') {
            $controller->update();
            exit();
        } elseif ($action === 'delete') {
            $controller->delete();
            exit();
        } elseif ($action === 'register-patient') {
            $controller->registerPatient();
            exit();
        } elseif ($action === 'login-patient') {
            $controller->loginPatient();
            exit();
        } elseif ($action === 'get-current-user') {
            $controller->getCurrentUser();
            exit();
        } elseif ($action === 'reset-password') {
            $controller->resetPassword();
            exit();
        } else {
            require_once 'views/backoffice.php';
        }
        break;
        
    case 'rendezvous':
        require_once 'controllers/RendezVousController.php';
        $controller = new RendezVousController();
        
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $controller->create($_POST);
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            if (!empty($result['google_auth_url'])) {
                header('Location: ' . $result['google_auth_url']);
                exit();
            }
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
        } elseif ($action === 'getByPatient' && isset($_GET['id_patient'])) {
            $data = $controller->getByPatient($_GET['id_patient']);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } elseif ($action === 'getByMedecin' && isset($_GET['id_medecin'])) {
            $data = $controller->getByMedecin($_GET['id_medecin']);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } elseif ($action === 'changeStatus' && isset($_GET['id'], $_GET['statut'], $_GET['id_medecin'])) {
            $result = $controller->updateStatusForMedecin($_GET['id'], $_GET['id_medecin'], $_GET['statut']);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        } elseif ($action === 'sendVideoLink' && isset($_GET['id'])) {
            $result = $controller->sendVideoLink($_GET['id']);
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit();
        } elseif ($action === 'addGoogleCalendar' && isset($_GET['id'])) {
            $redirect = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : 'views/mes_rdv.php';
            $result = $controller->addToGoogleCalendar($_GET['id'], $redirect);
            if (!empty($result['requires_auth']) && !empty($result['auth_url'])) {
                header('Location: ' . $result['auth_url']);
                exit();
            }
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            header('Location: ' . $redirect);
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

    case 'google-calendar':
        require_once 'controllers/RendezVousController.php';
        $controller = new RendezVousController();

        if ($action === 'connect') {
            $authUrl = $controller->getGoogleCalendarAuthUrl();
            if (!$authUrl) {
                Session::setFlash('error', 'Google Calendar n est pas configure.');
                header('Location: views/frontoffice.php#consultation');
                exit();
            }

            header('Location: ' . $authUrl);
            exit();
        } elseif ($action === 'callback') {
            $redirect = $controller->getGoogleCalendarPendingRedirect();
            $result = $controller->handleGoogleCalendarCallback($_GET['code'] ?? '', $_GET['state'] ?? '');
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            header('Location: ' . $redirect);
            exit();
        } elseif ($action === 'disconnect') {
            $result = $controller->disconnectGoogleCalendar();
            Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            header('Location: views/frontoffice.php#consultation');
            exit();
        }

        header('Location: views/frontoffice.php#consultation');
        exit();

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
        require_once 'controllers/RendezVousController.php';
        require_once 'models/UtilisateurModel.php';
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
        } elseif ($action === 'getByPatient' && isset($_GET['id_patient'])) {
            $data = $controller->getByPatient($_GET['id_patient']);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } elseif ($action === 'getStats') {
            $stats = $controller->getStatistics();
            header('Content-Type: application/json');
            echo json_encode(['dossiers' => $stats]);
            exit();
        } else {
            $rdvController = new RendezVousController();
            $utilisateurModel = new UtilisateurModel();
            $dossiers = $controller->index();
            $stats = $controller->getStatistics();
            $rendez_vous = $rdvController->index();
            $patients = $utilisateurModel->getPatients();
            $medecins = $utilisateurModel->getMedecins();
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

    case 'forum':
    case 'moderation':
    case 'publications':
    case 'commentaires':
    case 'avis':
        require_once 'controllers/ForumController.php';
        $controller = new ForumController();

        if ($page === 'forum' && $action) {
            $controller->handle($action);
            exit();
        }

        require_once 'views/backoffice.php';
        break;
        
    case 'logout':
        Session::destroy();
        header('Location: views/frontoffice.php');
        exit();
        break;

    case 'chatbot':
        require_once 'controllers/ChatbotController.php';
        $controller = new ChatbotController();

        if ($action === 'ask') {
            $controller->ask();
            exit();
        }

        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Action chatbot introuvable'], JSON_UNESCAPED_UNICODE);
        exit();
        
    default:
        require_once 'views/backoffice.php';
        break;
}
?>
