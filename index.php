<?php
session_start();

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

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'front';
$action     = isset($_GET['action'])     ? $_GET['action']     : 'index';
$id         = isset($_GET['id'])         ? $_GET['id']         : null;

// ──────────────────────────────────────────────
// Helpers de protection
// ──────────────────────────────────────────────
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: ?controller=auth&action=login');
        exit();
    }
}

function requireMedecin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
        header('Location: ?controller=auth&action=login');
        exit();
    }
}

function requirePatient() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
        header('Location: ?controller=auth&action=login');
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ──────────────────────────────────────────────
// Router
// ──────────────────────────────────────────────
switch ($controller) {

    // ---------- FRONT (vitrine publique) ----------
    case 'front':
        $ctrl = new FrontController();
        $ctrl->index();
        break;

    // ---------- AUTH ----------
    case 'auth':
        $ctrl = new AuthController();
        switch ($action) {
            case 'login':  $ctrl->login();  break;
            case 'logout': $ctrl->logout(); break;
            default:       $ctrl->login();
        }
        break;

    // ---------- ADMIN (backoffice global — admin seulement) ----------
    case 'admin':
        requireAdmin();
        $ctrl = new AdminController();
        switch ($action) {
            case 'index': $ctrl->index(); break;
            default:      $ctrl->index();
        }
        break;

    // ---------- CONSULTATION (admin seulement — vue globale) ----------
    case 'consultation':
        requireAdmin();
        $ctrl = new ConsultationController();
        switch ($action) {
            case 'index':  $ctrl->index();                       break;
            case 'list':   $ctrl->list();                        break;
            case 'get':    if ($id) $ctrl->get($id);             break;
            case 'create': $ctrl->create();                      break;
            case 'update': $ctrl->update();                      break;
            case 'delete': if ($id) $ctrl->delete($id);          break;
            default:       $ctrl->index();
        }
        break;

    // ---------- SUIVIE (admin seulement — vue globale) ----------
    case 'suivie':
        requireAdmin();
        $ctrl = new SuivieController();
        switch ($action) {
            case 'index':  $ctrl->index();                       break;
            case 'list':   $ctrl->list();                        break;
            case 'get':    if ($id) $ctrl->get($id);             break;
            case 'create': $ctrl->create();                      break;
            case 'update': $ctrl->update();                      break;
            case 'delete': if ($id) $ctrl->delete($id);          break;
            default:       $ctrl->index();
        }
        break;

    // ---------- MEDECIN (backoffice médecin — ajouter consul. & suivis) ----------
    case 'medecin':
        requireMedecin();
        $ctrl = new MedecinController();
        switch ($action) {
            case 'index':        $ctrl->index();        break;
            case 'consultation': $ctrl->consultation(); break;
            case 'suivie':       $ctrl->suivie();       break;
            default:             $ctrl->index();
        }
        break;

    // ---------- PATIENT (backoffice patient — lecture seule) ----------
    case 'patient':
        requirePatient();
        $ctrl = new PatientController();
        switch ($action) {
            case 'index':         $ctrl->index();           break;
            case 'consultations': $ctrl->consultations();   break;
            case 'suivis':        $ctrl->suivis();          break;
            default:              $ctrl->index();
        }
        break;

    // ---------- DEFAULT ----------
    default:
        $ctrl = new FrontController();
        $ctrl->index();
}