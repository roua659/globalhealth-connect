<?php
require_once __DIR__ . '/../models/UtilisateurModel.php';

class UtilisateurController {
    private $model;

    public function __construct() {
        $this->model = new UtilisateurModel();
    }

    private function jsonResponse($payload, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function getJsonInput() {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeUser($user) {
        $fullName = trim(($user['nom'] ?? '') . ' ' . ($user['prenom'] ?? ''));
        return [
            'id' => (int)($user['id_user'] ?? 0),
            'id_user' => (int)($user['id_user'] ?? 0),
            'id_patient' => isset($user['id_patient']) ? (int)$user['id_patient'] : null,
            'id_medecin' => isset($user['id_medecin']) ? (int)$user['id_medecin'] : null,
            'nom' => $user['nom'] ?? '',
            'prenom' => $user['prenom'] ?? '',
            'name' => $fullName,
            'age' => isset($user['age']) ? (int)$user['age'] : null,
            'sexe' => $user['sexe'] ?? null,
            'poids' => isset($user['poids']) ? (float)$user['poids'] : null,
            'taille' => isset($user['taille']) ? (float)$user['taille'] : null,
            'email' => $user['email'] ?? '',
            'cas_social' => $user['cas_social'] ?? null,
            'date_naissance' => $user['date_naissance'] ?? null,
            'adresse' => $user['adresse'] ?? null,
            'specialite' => $user['specialite'] ?? null,
            'role' => $user['type_role'] ?? 'patient',
        ];
    }

    private function validateUserData($input, $isRegistration = false) {
        $required = ['nom', 'prenom', 'email'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return "Champ obligatoire: {$field}";
            }
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Email invalide';
        }

        if (!$isRegistration) {
            $numericFields = ['age', 'poids', 'taille'];
            foreach ($numericFields as $field) {
                if (!isset($input[$field]) || $input[$field] === '') {
                    return "Champ obligatoire: {$field}";
                }
            }
        }

        if (($input['role'] ?? 'patient') === 'medecin' && empty($input['specialite'])) {
            return 'La specialite est obligatoire pour un medecin';
        }

        if (!empty($input['mot_de_passe']) && strlen((string)$input['mot_de_passe']) < 6) {
            return 'Mot de passe trop court (min 6)';
        }

        return null;
    }

    public function list() {
        try {
            $users = array_map([$this, 'normalizeUser'], $this->model->readAll());
            $this->jsonResponse(['success' => true, 'data' => $users]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function doctors() {
        try {
            $doctors = array_map([$this, 'normalizeUser'], $this->model->getMedecins());
            $this->jsonResponse(['success' => true, 'data' => $doctors]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function patients() {
        try {
            $patients = array_map([$this, 'normalizeUser'], $this->model->getPatients());
            $this->jsonResponse(['success' => true, 'data' => $patients]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getOne($id) {
        try {
            $user = $this->model->getOne((int)$id);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
                return;
            }
            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($user)]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function create() {
        $input = $this->getJsonInput();
        $validationError = $this->validateUserData($input);
        if ($validationError) {
            $this->jsonResponse(['success' => false, 'message' => $validationError], 422);
            return;
        }

        try {
            if ($this->model->findByEmail((string)$input['email'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Email deja utilise'], 409);
                return;
            }

            $newId = $this->model->createManaged([
                'nom' => trim((string)$input['nom']),
                'prenom' => trim((string)$input['prenom']),
                'age' => (int)$input['age'],
                'sexe' => trim((string)$input['sexe']),
                'poids' => (float)$input['poids'],
                'taille' => (float)$input['taille'],
                'email' => trim((string)$input['email']),
                'mot_de_passe' => (string)$input['mot_de_passe'],
                'cas_social' => !empty($input['cas_social']) ? trim((string)$input['cas_social']) : null,
                'date_naissance' => trim((string)$input['date_naissance']),
                'adresse' => trim((string)$input['adresse']),
                'role' => trim((string)($input['role'] ?? 'patient')),
                'specialite' => !empty($input['specialite']) ? trim((string)$input['specialite']) : null,
            ]);

            if (!$newId) {
                $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la creation'], 500);
                return;
            }

            $created = $this->model->getOne($newId);
            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($created)], 201);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update() {
        $input = $this->getJsonInput();
        $idUser = isset($input['id_user']) ? (int)$input['id_user'] : 0;
        if ($idUser <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id_user invalide'], 422);
            return;
        }

        try {
            $existing = $this->model->getOne($idUser);
            if (!$existing) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
                return;
            }

            $email = trim((string)($input['email'] ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse(['success' => false, 'message' => 'Email invalide'], 422);
                return;
            }

            $other = $this->model->findByEmail($email);
            if ($other && (int)($other['id_user'] ?? 0) !== $idUser) {
                $this->jsonResponse(['success' => false, 'message' => 'Cet email est deja utilise'], 409);
                return;
            }

            $payload = [
                'nom' => trim((string)($input['nom'] ?? $existing['nom'] ?? '')),
                'prenom' => trim((string)($input['prenom'] ?? $existing['prenom'] ?? '')),
                'age' => isset($input['age']) ? (int)$input['age'] : (int)($existing['age'] ?? 0),
                'sexe' => trim((string)($input['sexe'] ?? $existing['sexe'] ?? '')),
                'poids' => isset($input['poids']) ? (float)$input['poids'] : (float)($existing['poids'] ?? 0),
                'taille' => isset($input['taille']) ? (float)$input['taille'] : (float)($existing['taille'] ?? 0),
                'email' => $email,
                'cas_social' => array_key_exists('cas_social', $input) ? trim((string)$input['cas_social']) : ($existing['cas_social'] ?? null),
                'date_naissance' => trim((string)($input['date_naissance'] ?? $existing['date_naissance'] ?? '')),
                'adresse' => trim((string)($input['adresse'] ?? $existing['adresse'] ?? '')),
                'specialite' => array_key_exists('specialite', $input) ? trim((string)$input['specialite']) : ($existing['specialite'] ?? null),
                'mot_de_passe' => !empty($input['mot_de_passe']) ? (string)$input['mot_de_passe'] : null,
            ];

            $validationError = $this->validateUserData(array_merge($payload, ['role' => $existing['type_role'] ?? 'patient']), false);
            if ($validationError) {
                $this->jsonResponse(['success' => false, 'message' => $validationError], 422);
                return;
            }

            $ok = $this->model->updateManaged($idUser, $payload);
            if (!$ok) {
                $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la modification'], 500);
                return;
            }

            $updated = $this->model->getOne($idUser);
            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($updated)]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete() {
        $input = $this->getJsonInput();
        $idUser = isset($input['id_user']) ? (int)$input['id_user'] : 0;
        if ($idUser <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id_user invalide'], 422);
            return;
        }

        try {
            $ok = $this->model->deleteManaged($idUser);
            $this->jsonResponse(['success' => $ok, 'message' => $ok ? 'Utilisateur supprime' : 'Suppression impossible']);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function registerPatient() {
        $input = $this->getJsonInput();
        $validationError = $this->validateUserData([
            'nom' => $input['nom'] ?? '',
            'prenom' => $input['prenom'] ?? '',
            'email' => $input['email'] ?? '',
            'mot_de_passe' => $input['mot_de_passe'] ?? '',
            'role' => 'patient'
        ], true);
        if ($validationError) {
            $this->jsonResponse(['success' => false, 'message' => $validationError], 422);
            return;
        }

        try {
            if ($this->model->findByEmail((string)$input['email'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Email deja utilise'], 409);
                return;
            }

            $newId = $this->model->createManaged([
                'nom' => trim((string)$input['nom']),
                'prenom' => trim((string)$input['prenom']),
                'age' => isset($input['age']) ? (int)$input['age'] : 0,
                'sexe' => trim((string)($input['sexe'] ?? '')),
                'poids' => isset($input['poids']) ? (float)$input['poids'] : 0,
                'taille' => isset($input['taille']) ? (float)$input['taille'] : 0,
                'email' => trim((string)$input['email']),
                'mot_de_passe' => (string)$input['mot_de_passe'],
                'cas_social' => !empty($input['cas_social']) ? trim((string)$input['cas_social']) : null,
                'date_naissance' => !empty($input['date_naissance']) ? trim((string)$input['date_naissance']) : date('Y-m-d'),
                'adresse' => !empty($input['adresse']) ? trim((string)$input['adresse']) : '',
                'role' => 'patient',
                'specialite' => null,
            ]);

            if (!$newId) {
                $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la creation'], 500);
                return;
            }

            $created = $this->model->getOne($newId);
            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($created)], 201);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function loginPatient() {
        $input = $this->getJsonInput();
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['mot_de_passe'] ?? '');

        if ($email === '' || $password === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Email et mot de passe obligatoires'], 422);
            return;
        }

        try {
            $user = $this->model->authenticateFlexible($email, $password);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'Email ou mot de passe incorrect'], 401);
                return;
            }

            $role = trim((string)($user['type_role'] ?? ''));
            if ($role !== '' && !in_array($role, ['patient', 'medecin', 'admin'], true)) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur non autorise'], 403);
                return;
            }

            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($user)]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCurrentUser() {
        $input = $this->getJsonInput();
        $idUser = (int)($input['id_user'] ?? 0);
        if ($idUser <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID utilisateur requis'], 422);
            return;
        }

        try {
            $user = $this->model->getOne($idUser);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
                return;
            }

            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($user)]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function resetPassword() {
        $input = $this->getJsonInput();
        $email = trim((string)($input['email'] ?? ''));
        $newPassword = (string)($input['mot_de_passe'] ?? '');

        if ($email === '' || $newPassword === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Email et nouveau mot de passe obligatoires'], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'message' => 'Email invalide'], 422);
            return;
        }

        if (strlen($newPassword) < 6) {
            $this->jsonResponse(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caracteres'], 422);
            return;
        }

        try {
            $user = $this->model->findByEmail($email);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
                return;
            }

            $ok = $this->model->updateManaged((int)$user['id_user'], [
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'age' => (int)($user['age'] ?? 0),
                'sexe' => $user['sexe'] ?? '',
                'poids' => (float)($user['poids'] ?? 0),
                'taille' => (float)($user['taille'] ?? 0),
                'email' => $user['email'],
                'cas_social' => $user['cas_social'] ?? null,
                'date_naissance' => $user['date_naissance'] ?? date('Y-m-d'),
                'adresse' => $user['adresse'] ?? '',
                'specialite' => $user['specialite'] ?? null,
                'mot_de_passe' => $newPassword,
            ]);

            if (!$ok) {
                $this->jsonResponse(['success' => false, 'message' => 'Impossible de reinitialiser le mot de passe'], 500);
                return;
            }

            $updated = $this->model->getOne((int)$user['id_user']);
            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($updated)]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
?>
