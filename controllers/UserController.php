<?php
require_once __DIR__ . '/User.php';

class UserController {

    private $user;

    public function __construct() {
        $this->user = new User();
    }

    // ========== ROUTER PRINCIPAL ==========
    // Appelé depuis index.php selon la méthode HTTP et l'URL
    public function handle($method, $segments) {
        header('Content-Type: application/json');

        $id     = isset($segments[0]) ? (int)$segments[0] : null;
        $action = $segments[1] ?? null;

        switch ($method) {
            case 'GET':
                $id ? $this->show($id) : $this->index();
                break;
            case 'POST':
                $this->store();
                break;
            case 'PUT':
                $id ? $this->update($id) : $this->repondre(['success'=>false,'error'=>'ID manquant.'], 400);
                break;
            case 'DELETE':
                $id ? $this->destroy($id) : $this->repondre(['success'=>false,'error'=>'ID manquant.'], 400);
                break;
            default:
                $this->repondre(['success'=>false,'error'=>'Méthode non supportée.'], 405);
        }
    }

    // ========== LISTER ==========
    // GET /users           → tous les utilisateurs
    // GET /users?role=...  → filtrés par rôle
    private function index() {
        $role  = $_GET['role'] ?? null;
        $users = $role ? $this->user->getByRole($role) : $this->user->getAll();
        $this->repondre(['success' => true, 'count' => count($users), 'users' => $users]);
    }

    // ========== UN SEUL ==========
    // GET /users/5
    private function show($id) {
        $u = $this->user->getById($id);
        $u  ? $this->repondre(['success' => true, 'user' => $u])
            : $this->repondre(['success' => false, 'error' => 'Introuvable.'], 404);
    }

    // ========== AJOUTER ==========
    // POST /users
    // Body JSON : { nom, prenom, email, mot_de_passe, id_role, age, sexe, ... }
    private function store() {
        $input = $this->lireJson();
        if (!$input) { $this->repondre(['success'=>false,'error'=>'JSON invalide.'], 400); return; }

        $result = $this->user->create($input);
        $this->repondre($result, $result['success'] ? 201 : 422);
    }

    // ========== MODIFIER ==========
    // PUT /users/5
    // Body JSON : { champs à modifier seulement }
    private function update($id) {
        $input = $this->lireJson();
        if (!$input) { $this->repondre(['success'=>false,'error'=>'JSON invalide.'], 400); return; }

        $result = $this->user->update($id, $input);
        $this->repondre($result, $result['success'] ? 200 : 422);
    }

    // ========== SUPPRIMER ==========
    // DELETE /users/5
    private function destroy($id) {
        $result = $this->user->delete($id);
        $this->repondre($result, $result['success'] ? 200 : 404);
    }

    // ========== HELPERS ==========
    private function lireJson() {
        $raw = file_get_contents('php://input');
        if (empty($raw)) return null;
        $data = json_decode($raw, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
    }

    private function repondre($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}