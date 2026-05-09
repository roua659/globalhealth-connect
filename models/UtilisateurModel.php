<?php
require_once __DIR__ . '/../config/database.php';

class UtilisateurModel {
    private $conn;
    private $table_name = "utilisateur";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function getRoleIdByType($roleType) {
        $query = "SELECT id_role FROM role WHERE type_role = :type_role LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":type_role", $roleType);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['id_role'] : null;
    }

    public function readAll() {
        try {
            $query = "SELECT u.*,
                             r.type_role,
                             p.id_patient,
                             m.id_medecin,
                             m.specialite
                      FROM " . $this->table_name . " u
                      LEFT JOIN role r ON u.id_role = r.id_role
                      LEFT JOIN patient p ON u.id_user = p.id_user
                      LEFT JOIN medecin m ON u.id_user = m.id_user
                      ORDER BY u.id_user DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur readAll utilisateur: " . $e->getMessage());
            return [];
        }
    }

    public function getMedecins() {
        try {
            $query = "SELECT u.*, m.id_medecin, m.specialite, r.type_role
                      FROM utilisateur u
                      INNER JOIN medecin m ON u.id_user = m.id_user
                      INNER JOIN role r ON u.id_role = r.id_role
                      WHERE r.type_role = 'medecin'
                      ORDER BY u.nom ASC, u.prenom ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getMedecins: " . $e->getMessage());
            return [];
        }
    }

    public function getPatients() {
        try {
            $query = "SELECT u.*, p.id_patient, r.type_role
                      FROM utilisateur u
                      INNER JOIN patient p ON u.id_user = p.id_user
                      INNER JOIN role r ON u.id_role = r.id_role
                      WHERE r.type_role = 'patient'
                      ORDER BY u.nom ASC, u.prenom ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getPatients: " . $e->getMessage());
            return [];
        }
    }

    public function getOne($id) {
        try {
            $query = "SELECT u.*,
                             r.type_role,
                             p.id_patient,
                             m.id_medecin,
                             m.specialite
                      FROM " . $this->table_name . " u
                      LEFT JOIN role r ON u.id_role = r.id_role
                      LEFT JOIN patient p ON u.id_user = p.id_user
                      LEFT JOIN medecin m ON u.id_user = m.id_user
                      WHERE u.id_user = :id_user
                      LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_user", $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getOne: " . $e->getMessage());
            return null;
        }
    }

    public function findByEmail($email) {
        try {
            $query = "SELECT u.*,
                             r.type_role,
                             p.id_patient,
                             m.id_medecin,
                             m.specialite
                      FROM utilisateur u
                      LEFT JOIN role r ON u.id_role = r.id_role
                      LEFT JOIN patient p ON u.id_user = p.id_user
                      LEFT JOIN medecin m ON u.id_user = m.id_user
                      WHERE TRIM(LOWER(u.email)) = TRIM(LOWER(:email))
                      LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur findByEmail: " . $e->getMessage());
            return null;
        }
    }

    public function createManaged($data) {
        try {
            $this->conn->beginTransaction();

            $roleType = $data['role'] ?? 'patient';
            $roleId = $this->getRoleIdByType($roleType);
            if (!$roleId) {
                throw new Exception("Role introuvable");
            }

            $query = "INSERT INTO " . $this->table_name . " 
                      SET nom=:nom, prenom=:prenom, age=:age, sexe=:sexe,
                          poids=:poids, taille=:taille, email=:email, mot_de_passe=:mot_de_passe,
                          cas_social=:cas_social, date_naissance=:date_naissance,
                          adresse=:adresse, id_role=:id_role";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nom", $data['nom']);
            $stmt->bindParam(":prenom", $data['prenom']);
            $stmt->bindParam(":age", $data['age']);
            $stmt->bindParam(":sexe", $data['sexe']);
            $stmt->bindParam(":poids", $data['poids']);
            $stmt->bindParam(":taille", $data['taille']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":mot_de_passe", $data['mot_de_passe']);
            $stmt->bindParam(":cas_social", $data['cas_social']);
            $stmt->bindParam(":date_naissance", $data['date_naissance']);
            $stmt->bindParam(":adresse", $data['adresse']);
            $stmt->bindParam(":id_role", $roleId);
            $stmt->execute();

            $idUser = (int)$this->conn->lastInsertId();

            if ($roleType === 'patient') {
                $patientStmt = $this->conn->prepare("INSERT INTO patient (id_user) VALUES (:id_user)");
                $patientStmt->bindParam(":id_user", $idUser);
                $patientStmt->execute();
            } elseif ($roleType === 'medecin') {
                $specialite = $data['specialite'] ?? null;
                $medecinStmt = $this->conn->prepare("INSERT INTO medecin (id_user, specialite) VALUES (:id_user, :specialite)");
                $medecinStmt->bindParam(":id_user", $idUser);
                $medecinStmt->bindParam(":specialite", $specialite);
                $medecinStmt->execute();
            }

            $this->conn->commit();
            return $idUser;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erreur createManaged utilisateur: " . $e->getMessage());
            return false;
        }
    }

    public function updateManaged($id, $data) {
        try {
            $existing = $this->getOne($id);
            if (!$existing) {
                return false;
            }

            $query = "UPDATE " . $this->table_name . " 
                      SET nom=:nom, prenom=:prenom, age=:age, sexe=:sexe,
                          poids=:poids, taille=:taille, email=:email,
                          cas_social=:cas_social, date_naissance=:date_naissance,
                          adresse=:adresse
                      WHERE id_user = :id_user";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nom", $data['nom']);
            $stmt->bindParam(":prenom", $data['prenom']);
            $stmt->bindParam(":age", $data['age']);
            $stmt->bindParam(":sexe", $data['sexe']);
            $stmt->bindParam(":poids", $data['poids']);
            $stmt->bindParam(":taille", $data['taille']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":cas_social", $data['cas_social']);
            $stmt->bindParam(":date_naissance", $data['date_naissance']);
            $stmt->bindParam(":adresse", $data['adresse']);
            $stmt->bindParam(":id_user", $id);
            $result = $stmt->execute();

            if (!empty($data['mot_de_passe'])) {
                $passwordStmt = $this->conn->prepare("UPDATE utilisateur SET mot_de_passe = :mot_de_passe WHERE id_user = :id_user");
                $passwordStmt->bindParam(":mot_de_passe", $data['mot_de_passe']);
                $passwordStmt->bindParam(":id_user", $id);
                $passwordStmt->execute();
            }

            if (($existing['type_role'] ?? '') === 'medecin') {
                $specialite = $data['specialite'] ?? null;
                $medecinStmt = $this->conn->prepare("UPDATE medecin SET specialite = :specialite WHERE id_user = :id_user");
                $medecinStmt->bindParam(":specialite", $specialite);
                $medecinStmt->bindParam(":id_user", $id);
                $medecinStmt->execute();
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Erreur updateManaged utilisateur: " . $e->getMessage());
            return false;
        }
    }

    public function deleteManaged($id) {
        try {
            $this->conn->beginTransaction();

            $patientStmt = $this->conn->prepare("DELETE FROM patient WHERE id_user = :id_user");
            $patientStmt->bindParam(":id_user", $id);
            $patientStmt->execute();

            $medecinStmt = $this->conn->prepare("DELETE FROM medecin WHERE id_user = :id_user");
            $medecinStmt->bindParam(":id_user", $id);
            $medecinStmt->execute();

            $userStmt = $this->conn->prepare("DELETE FROM utilisateur WHERE id_user = :id_user");
            $userStmt->bindParam(":id_user", $id);
            $userStmt->execute();

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erreur deleteManaged utilisateur: " . $e->getMessage());
            return false;
        }
    }

    public function authenticateFlexible($email, $password) {
        try {
            $user = $this->findByEmail($email);
            if (!$user) {
                return null;
            }

            $storedPassword = trim((string)($user['mot_de_passe'] ?? ''));
            $enteredPassword = trim((string)$password);
            if ($storedPassword === '') {
                return null;
            }

            $matches = hash_equals($storedPassword, $enteredPassword)
                || hash_equals($storedPassword, md5($enteredPassword));

            if (!$matches && password_get_info($storedPassword)['algo'] !== 0) {
                $matches = password_verify($enteredPassword, $storedPassword);
            }

            return $matches ? $user : null;
        } catch (PDOException $e) {
            error_log("Erreur authenticateFlexible: " . $e->getMessage());
            return null;
        }
    }

    public function getStatistics() {
        try {
            $stats = [
                'total' => count($this->readAll()),
                'patients' => count($this->getPatients()),
                'medecins' => count($this->getMedecins()),
                'admins' => 0
            ];

            $query = "SELECT COUNT(*) as total
                      FROM utilisateur u
                      INNER JOIN role r ON u.id_role = r.id_role
                      WHERE r.type_role = 'admin'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['admins'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            return $stats;
        } catch (PDOException $e) {
            error_log("Erreur getStatistics utilisateur: " . $e->getMessage());
            return ['total' => 0, 'patients' => 0, 'medecins' => 0, 'admins' => 0];
        }
    }

    public function searchManaged($filters = [], $sortField = 'id_user', $sortDir = 'DESC') {
        try {
            $allowedSort = [
                'id_user' => 'u.id_user',
                'nom' => 'u.nom',
                'prenom' => 'u.prenom',
                'email' => 'u.email',
                'age' => 'u.age',
                'date_naissance' => 'u.date_naissance',
                'role' => 'r.type_role',
            ];
            $sortColumn = $allowedSort[$sortField] ?? 'u.id_user';
            $sortDir = strtoupper((string)$sortDir) === 'ASC' ? 'ASC' : 'DESC';

            $conditions = [];
            $params = [];

            foreach (['nom', 'prenom', 'email', 'sexe', 'cas_social'] as $field) {
                if (!empty($filters[$field])) {
                    $conditions[] = "u.{$field} LIKE :{$field}";
                    $params[":{$field}"] = '%' . trim((string)$filters[$field]) . '%';
                }
            }

            if (!empty($filters['role'])) {
                $conditions[] = "r.type_role = :role";
                $params[':role'] = trim((string)$filters['role']);
            }

            $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $query = "SELECT u.*,
                             r.type_role,
                             p.id_patient,
                             m.id_medecin,
                             m.specialite
                      FROM utilisateur u
                      LEFT JOIN role r ON u.id_role = r.id_role
                      LEFT JOIN patient p ON u.id_user = p.id_user
                      LEFT JOIN medecin m ON u.id_user = m.id_user
                      {$where}
                      ORDER BY {$sortColumn} {$sortDir}";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur searchManaged utilisateur: " . $e->getMessage());
            return [];
        }
    }
}
?>
