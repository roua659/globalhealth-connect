<?php
declare(strict_types=1);

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function jsonResponse(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeUser(array $user): array
    {
        $fullName = trim(($user['nom'] ?? '') . ' ' . ($user['prenom'] ?? ''));
        return [
            'id' => (int) ($user['id_user'] ?? 0),
            'id_user' => (int) ($user['id_user'] ?? 0),
            'nom' => $user['nom'] ?? '',
            'prenom' => $user['prenom'] ?? '',
            'name' => $fullName,
            'age' => isset($user['age']) ? (int) $user['age'] : null,
            'sexe' => $user['sexe'] ?? null,
            'poids' => isset($user['poids']) ? (float) $user['poids'] : null,
            'taille' => isset($user['taille']) ? (float) $user['taille'] : null,
            'email' => $user['email'] ?? '',
            'cas_social' => $user['cas_social'] ?? null,
            'date_naissance' => $user['date_naissance'] ?? null,
            'adresse' => $user['adresse'] ?? null,
            'specialite' => $user['specialite'] ?? null,
            'role' => $user['role'] ?? 'patient',
        ];
    }

    public function list(): void
    {
        try {
            $users = array_map(fn ($u) => $this->normalizeUser($u), $this->userModel->getAll());
            $this->jsonResponse(['success' => true, 'data' => $users]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function doctors(): void
    {
        try {
            $doctors = array_map(fn ($u) => $this->normalizeUser($u), $this->userModel->getDoctors());
            $this->jsonResponse(['success' => true, 'data' => $doctors]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function create(): void
    {
        $input = $this->getJsonInput();
        $required = ['nom', 'prenom', 'age', 'sexe', 'poids', 'taille', 'email', 'mot_de_passe', 'date_naissance', 'adresse', 'role'];
        foreach ($required as $field) {
            if (!array_key_exists($field, $input) || $input[$field] === '') {
                $this->jsonResponse(['success' => false, 'message' => "Champ obligatoire: {$field}"], 422);
                return;
            }
        }

        if (($input['role'] ?? '') === 'medecin' && empty($input['specialite'])) {
            $this->jsonResponse(['success' => false, 'message' => 'La spécialité est obligatoire pour un médecin'], 422);
            return;
        }

        try {
            if ($this->userModel->findByEmail((string) $input['email'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Email déjà utilisé'], 409);
                return;
            }

            $input['mot_de_passe'] = (string) $input['mot_de_passe'];
            $newId = $this->userModel->create($input);
            $created = $this->userModel->getAll();
            $user = array_values(array_filter($created, fn ($u) => (int) $u['id_user'] === $newId))[0] ?? null;
            $this->jsonResponse(['success' => true, 'data' => $user ? $this->normalizeUser($user) : ['id_user' => $newId]], 201);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(): void
    {
        $input = $this->getJsonInput();
        $idUser = isset($input['id_user']) ? (int) $input['id_user'] : 0;
        if ($idUser <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id_user invalide'], 422);
            return;
        }

        try {
            $existing = $this->userModel->findById($idUser);
            if ($existing === null) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
                return;
            }

            $role = (string) ($existing['role'] ?? 'patient');
            $data = [];

            if ($role === 'patient') {
                $nom = trim((string) ($input['nom'] ?? ''));
                $prenom = trim((string) ($input['prenom'] ?? ''));
                $email = trim((string) ($input['email'] ?? ''));
                if ($nom === '' || $prenom === '') {
                    $this->jsonResponse(['success' => false, 'message' => 'Nom et prénom obligatoires'], 422);
                    return;
                }
                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->jsonResponse(['success' => false, 'message' => 'Email invalide'], 422);
                    return;
                }

                $other = $this->userModel->findByEmail($email);
                if ($other !== null && (int) ($other['id_user'] ?? 0) !== $idUser) {
                    $this->jsonResponse(['success' => false, 'message' => 'Cet email est déjà utilisé'], 409);
                    return;
                }

                $age = (int) ($input['age'] ?? 0);
                $poids = (float) ($input['poids'] ?? 0);
                $taille = (float) ($input['taille'] ?? 0);
                if ($age < 0 || $age > 130) {
                    $this->jsonResponse(['success' => false, 'message' => 'Âge invalide'], 422);
                    return;
                }
                if ($poids <= 0 || $taille <= 0) {
                    $this->jsonResponse(['success' => false, 'message' => 'Poids ou taille invalides'], 422);
                    return;
                }

                $sexe = trim((string) ($input['sexe'] ?? ''));
                if ($sexe === '') {
                    $this->jsonResponse(['success' => false, 'message' => 'Sexe obligatoire'], 422);
                    return;
                }

                $dateNaissance = trim((string) ($input['date_naissance'] ?? ''));
                if ($dateNaissance === '') {
                    $this->jsonResponse(['success' => false, 'message' => 'Date de naissance obligatoire'], 422);
                    return;
                }

                $data = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'age' => $age,
                    'sexe' => $sexe,
                    'poids' => $poids,
                    'taille' => $taille,
                    'date_naissance' => $dateNaissance,
                    'adresse' => trim((string) ($input['adresse'] ?? '')),
                    'cas_social' => ($input['cas_social'] ?? null) !== null && trim((string) $input['cas_social']) !== ''
                        ? trim((string) $input['cas_social'])
                        : null,
                    'specialite' => null,
                ];

                $newPass = (string) ($input['mot_de_passe'] ?? '');
                if ($newPass !== '') {
                    if (strlen($newPass) < 6) {
                        $this->jsonResponse(['success' => false, 'message' => 'Mot de passe trop court (min 6)'], 422);
                        return;
                    }
                    $data['mot_de_passe'] = $newPass;
                }
            } else {
                $fullName = trim((string) ($input['name'] ?? ''));
                $nameParts = preg_split('/\s+/', $fullName, 2);
                $nom = $nameParts[0] ?? '';
                $prenom = $nameParts[1] ?? '';
                if ($nom === '') {
                    $this->jsonResponse(['success' => false, 'message' => 'Nom complet invalide'], 422);
                    return;
                }

                $email = trim((string) ($input['email'] ?? ''));
                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->jsonResponse(['success' => false, 'message' => 'Email invalide'], 422);
                    return;
                }

                $other = $this->userModel->findByEmail($email);
                if ($other !== null && (int) ($other['id_user'] ?? 0) !== $idUser) {
                    $this->jsonResponse(['success' => false, 'message' => 'Cet email est déjà utilisé'], 409);
                    return;
                }

                $spec = trim((string) ($input['specialite'] ?? ''));
                if ($role === 'medecin' && $spec === '') {
                    $this->jsonResponse(['success' => false, 'message' => 'Spécialité obligatoire pour un médecin'], 422);
                    return;
                }

                $data = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'cas_social' => ($input['cas_social'] ?? null) !== null && trim((string) $input['cas_social']) !== ''
                        ? trim((string) $input['cas_social'])
                        : null,
                    'specialite' => $role === 'medecin' ? $spec : null,
                ];
            }

            $ok = $this->userModel->update($idUser, $data);
            if (!$ok) {
                $this->jsonResponse(['success' => false, 'message' => 'Aucune modification'], 400);
                return;
            }

            $updated = $this->userModel->findById($idUser);
            $this->jsonResponse([
                'success' => true,
                'data' => $updated ? $this->normalizeUser($updated) : null,
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete(): void
    {
        $input = $this->getJsonInput();
        $idUser = isset($input['id_user']) ? (int) $input['id_user'] : 0;
        if ($idUser <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id_user invalide'], 422);
            return;
        }

        try {
            $ok = $this->userModel->delete($idUser);
            $this->jsonResponse(['success' => $ok]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function registerPatient(): void
    {
        $input = $this->getJsonInput();
        $required = ['nom', 'prenom', 'email', 'mot_de_passe'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->jsonResponse(['success' => false, 'message' => "Champ obligatoire: {$field}"], 422);
                return;
            }
        }

        try {
            if ($this->userModel->findByEmail((string) $input['email'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Email déjà utilisé'], 409);
                return;
            }

            $id = $this->userModel->create([
                'nom' => $input['nom'],
                'prenom' => $input['prenom'],
                'age' => (int) ($input['age'] ?? 0),
                'sexe' => $input['sexe'] ?? '',
                'poids' => (float) ($input['poids'] ?? 0),
                'taille' => (float) ($input['taille'] ?? 0),
                'email' => $input['email'],
                'mot_de_passe' => (string) $input['mot_de_passe'],
                'cas_social' => $input['cas_social'] ?? null,
                'date_naissance' => $input['date_naissance'] ?? date('Y-m-d'),
                'adresse' => $input['adresse'] ?? '',
                'specialite' => null,
                'role' => 'patient',
            ]);

            $user = $this->userModel->findByEmail((string) $input['email']);
            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($user ?? ['id_user' => $id])], 201);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function loginPatient(): void
    {
        $input = $this->getJsonInput();
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['mot_de_passe'] ?? '');
        if ($email === '' || $password === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Email et mot de passe obligatoires'], 422);
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
                return;
            }

            $role = trim((string) ($user['role'] ?? ''));
            if ($role !== '' && !in_array($role, ['patient', 'medecin'], true)) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
                return;
            }

            $storedPassword = trim((string) ($user['mot_de_passe'] ?? ''));
            $enteredPassword = trim($password);
            $passwordMatch = false;

            if ($storedPassword !== '') {
                $passwordMatch = hash_equals($storedPassword, $enteredPassword);
                if (!$passwordMatch) {
                    $passwordInfo = password_get_info($storedPassword);
                    if ($passwordInfo['algo'] !== 0 && password_verify($enteredPassword, $storedPassword)) {
                        $passwordMatch = true;
                        $this->userModel->update((int) $user['id_user'], ['mot_de_passe' => $enteredPassword]);
                    }
                }
            }

            if (!$passwordMatch) {
                $this->jsonResponse(['success' => false, 'message' => 'Mot de passe incorrect'], 401);
                return;
            }

            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($user)]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCurrentUser(): void
    {
        $input = $this->getJsonInput();
        $idUser = (int) ($input['id_user'] ?? 0);
        if ($idUser <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID utilisateur requis'], 422);
            return;
        }

        try {
            $user = $this->userModel->findById($idUser);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
                return;
            }

            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($user)]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function resetPassword(): void
    {
        $input = $this->getJsonInput();
        $email = trim((string) ($input['email'] ?? ''));
        $newPassword = (string) ($input['mot_de_passe'] ?? '');

        if ($email === '' || $newPassword === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Email et nouveau mot de passe obligatoires'], 422);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'message' => 'Email invalide'], 422);
            return;
        }
        if (strlen($newPassword) < 6) {
            $this->jsonResponse(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères'], 422);
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
                return;
            }

            $updated = $this->userModel->update((int) $user['id_user'], [
                'mot_de_passe' => $newPassword
            ]);

            if (!$updated) {
                $this->jsonResponse(['success' => false, 'message' => 'Impossible de réinitialiser le mot de passe'], 500);
                return;
            }

            $this->jsonResponse(['success' => true, 'data' => $this->normalizeUser($this->userModel->findById((int) $user['id_user']))]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
