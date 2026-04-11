<?php
require_once __DIR__ . '/database.php';

class User {

    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    // ==================== LISTER ====================
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT u.id_user, u.nom, u.prenom, u.age, u.sexe,
                   u.poids, u.taille, u.email,
                   u.cas_social, u.date_naissance, u.adresse,
                   r.libelle AS role
            FROM utilisateur u
            LEFT JOIN role r ON u.id_role = r.id_role
            ORDER BY u.id_user DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ==================== UN SEUL USER ====================
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT u.id_user, u.nom, u.prenom, u.age, u.sexe,
                   u.poids, u.taille, u.email,
                   u.cas_social, u.date_naissance, u.adresse,
                   r.libelle AS role
            FROM utilisateur u
            LEFT JOIN role r ON u.id_role = r.id_role
            WHERE u.id_user = :id
        ");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    // ==================== FILTRER PAR ROLE ====================
    public function getByRole($role) {
        $stmt = $this->db->prepare("
            SELECT u.id_user, u.nom, u.prenom, u.age, u.sexe,
                   u.poids, u.taille, u.email,
                   u.cas_social, u.date_naissance, u.adresse,
                   r.libelle AS role
            FROM utilisateur u
            LEFT JOIN role r ON u.id_role = r.id_role
            WHERE r.libelle = :role
            ORDER BY u.id_user DESC
        ");
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll();
    }

    // ==================== EMAIL UNIQUE ====================
    public function emailExiste($email, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id_user FROM utilisateur WHERE email = :email AND id_user != :id");
            $stmt->execute([':email' => strtolower($email), ':id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id_user FROM utilisateur WHERE email = :email");
            $stmt->execute([':email' => strtolower($email)]);
        }
        return $stmt->fetch() ? true : false;
    }

    // ==================== AJOUTER ====================
    public function create($input) {
        // Champs obligatoires
        foreach (['nom', 'prenom', 'email', 'mot_de_passe', 'id_role'] as $champ) {
            if (empty($input[$champ])) {
                return ['success' => false, 'error' => "Le champ '$champ' est obligatoire."];
            }
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => "Email invalide."];
        }

        if ($this->emailExiste($input['email'])) {
            return ['success' => false, 'error' => "Cet email est déjà utilisé."];
        }

        // Vérifier que le rôle existe
        $stmtRole = $this->db->prepare("SELECT id_role FROM role WHERE id_role = :id");
        $stmtRole->execute([':id' => (int)$input['id_role']]);
        if (!$stmtRole->fetch()) {
            return ['success' => false, 'error' => "Le rôle n'existe pas."];
        }

        $hash = password_hash($input['mot_de_passe'], PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("
            INSERT INTO utilisateur
                (nom, prenom, age, sexe, poids, taille, email,
                 mot_de_passe, cas_social, date_naissance, adresse, id_role)
            VALUES
                (:nom, :prenom, :age, :sexe, :poids, :taille, :email,
                 :mot_de_passe, :cas_social, :date_naissance, :adresse, :id_role)
        ");

        $stmt->execute([
            ':nom'            => trim($input['nom']),
            ':prenom'         => trim($input['prenom']),
            ':age'            => !empty($input['age'])            ? (int)$input['age']      : null,
            ':sexe'           => $input['sexe']           ?? null,
            ':poids'          => !empty($input['poids'])          ? (float)$input['poids']  : null,
            ':taille'         => !empty($input['taille'])         ? (float)$input['taille'] : null,
            ':email'          => strtolower(trim($input['email'])),
            ':mot_de_passe'   => $hash,
            ':cas_social'     => $input['cas_social']     ?? null,
            ':date_naissance' => $input['date_naissance'] ?? null,
            ':adresse'        => $input['adresse']        ?? null,
            ':id_role'        => (int)$input['id_role'],
        ]);

        $newUser = $this->getById((int)$this->db->lastInsertId());
        return ['success' => true, 'user' => $newUser];
    }

    // ==================== MODIFIER ====================
    public function update($id, $input) {
        if (!$this->getById($id)) {
            return ['success' => false, 'error' => "Utilisateur introuvable."];
        }

        if (!empty($input['email'])) {
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'error' => "Email invalide."];
            }
            if ($this->emailExiste($input['email'], $id)) {
                return ['success' => false, 'error' => "Cet email est déjà utilisé."];
            }
        }

        // Construction dynamique du SET
        $fields = [];
        $params = [':id' => $id];

        $modifiables = ['nom', 'prenom', 'age', 'sexe', 'poids', 'taille',
                        'email', 'cas_social', 'date_naissance', 'adresse', 'id_role'];

        foreach ($modifiables as $champ) {
            if (array_key_exists($champ, $input)) {
                $fields[]          = "$champ = :$champ";
                $params[":$champ"] = $input[$champ];
            }
        }

        if (!empty($input['mot_de_passe'])) {
            $fields[]               = "mot_de_passe = :mot_de_passe";
            $params[':mot_de_passe'] = password_hash($input['mot_de_passe'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) {
            return ['success' => false, 'error' => "Aucun champ à modifier."];
        }

        $sql  = "UPDATE utilisateur SET " . implode(', ', $fields) . " WHERE id_user = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return ['success' => true, 'user' => $this->getById($id)];
    }

    // ==================== SUPPRIMER ====================
    public function delete($id) {
        if (!$this->getById($id)) {
            return ['success' => false, 'error' => "Utilisateur introuvable."];
        }

        $stmt = $this->db->prepare("DELETE FROM utilisateur WHERE id_user = :id");
        $stmt->execute([':id' => $id]);

        return ['success' => true, 'message' => "Utilisateur supprimé."];
    }
}