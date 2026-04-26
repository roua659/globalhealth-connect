<?php
declare(strict_types=1);

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT id_user, nom, prenom, age, sexe, poids, taille, email, cas_social, date_naissance, adresse, specialite, role
                FROM utilisateur
                ORDER BY id_user DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getDoctors(): array
    {
        $stmt = $this->db->prepare("SELECT id_user, nom, prenom, email, specialite
                                    FROM utilisateur
                                    WHERE role = 'medecin'
                                    ORDER BY nom, prenom");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE TRIM(LOWER(email)) = TRIM(LOWER(:email)) LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $idUser): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE id_user = :id_user LIMIT 1");
        $stmt->execute(['id_user' => $idUser]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO utilisateur
            (nom, prenom, age, sexe, poids, taille, email, mot_de_passe, cas_social, date_naissance, adresse, specialite, role)
            VALUES
            (:nom, :prenom, :age, :sexe, :poids, :taille, :email, :mot_de_passe, :cas_social, :date_naissance, :adresse, :specialite, :role)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'age' => $data['age'],
            'sexe' => $data['sexe'],
            'poids' => $data['poids'],
            'taille' => $data['taille'],
            'email' => $data['email'],
            'mot_de_passe' => $data['mot_de_passe'],
            'cas_social' => $data['cas_social'] ?? null,
            'date_naissance' => $data['date_naissance'],
            'adresse' => $data['adresse'],
            'specialite' => $data['specialite'] ?? null,
            'role' => $data['role'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $idUser, array $data): bool
    {
        $allowed = [
            'nom', 'prenom', 'email', 'cas_social', 'specialite',
            'age', 'sexe', 'poids', 'taille', 'date_naissance', 'adresse',
        ];
        $sets = [];
        $params = ['id_user' => $idUser];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "`{$col}` = :{$col}";
                $params[$col] = $data[$col];
            }
        }
        if (array_key_exists('mot_de_passe', $data) && $data['mot_de_passe'] !== null && $data['mot_de_passe'] !== '') {
            $sets[] = 'mot_de_passe = :mot_de_passe';
            $params['mot_de_passe'] = $data['mot_de_passe'];
        }
        if ($sets === []) {
            return false;
        }

        $sql = 'UPDATE utilisateur SET ' . implode(', ', $sets) . ' WHERE id_user = :id_user';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $idUser): bool
    {
        $stmt = $this->db->prepare("DELETE FROM utilisateur WHERE id_user = :id_user");
        return $stmt->execute(['id_user' => $idUser]);
    }

    // =========================================================
    // MÉTIER : RECHERCHE AVANCÉE MULTI-CRITÈRES
    // =========================================================

    /**
     * Recherche avancée avec combinaison de critères.
     *
     * @param array $filters  Clés acceptées : nom, prenom, email, sexe, role, cas_social
     * @param string $sortField   Champ de tri (whitelist interne)
     * @param string $sortDir     'ASC' ou 'DESC'
     * @return array
     */
    public function search(array $filters = [], string $sortField = 'id_user', string $sortDir = 'DESC'): array
    {
        $allowedSort = ['nom', 'prenom', 'age', 'poids', 'taille', 'date_naissance', 'id_user'];
        $sortField = in_array($sortField, $allowedSort, true) ? $sortField : 'id_user';
        $sortDir   = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $conditions = [];
        $params     = [];

        if (!empty($filters['nom'])) {
            $conditions[] = 'nom LIKE :nom';
            $params['nom'] = '%' . $filters['nom'] . '%';
        }
        if (!empty($filters['prenom'])) {
            $conditions[] = 'prenom LIKE :prenom';
            $params['prenom'] = '%' . $filters['prenom'] . '%';
        }
        if (!empty($filters['email'])) {
            $conditions[] = 'email LIKE :email';
            $params['email'] = '%' . $filters['email'] . '%';
        }
        if (!empty($filters['sexe'])) {
            $conditions[] = 'sexe = :sexe';
            $params['sexe'] = $filters['sexe'];
        }
        if (!empty($filters['role'])) {
            $conditions[] = 'role = :role';
            $params['role'] = $filters['role'];
        }
        if (!empty($filters['cas_social'])) {
            $conditions[] = 'cas_social LIKE :cas_social';
            $params['cas_social'] = '%' . $filters['cas_social'] . '%';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = "SELECT id_user, nom, prenom, age, sexe, poids, taille, email,
                         cas_social, date_naissance, adresse, specialite, role
                  FROM utilisateur
                  {$where}
                  ORDER BY {$sortField} {$sortDir}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // =========================================================
    // MÉTIER : TRI DYNAMIQUE (sur la liste complète)
    // =========================================================

    /**
     * Retourne tous les utilisateurs triés dynamiquement.
     *
     * @param string $field   Champ de tri (whitelist interne)
     * @param string $dir     'ASC' ou 'DESC'
     * @return array
     */
    public function sortBy(string $field, string $dir = 'ASC'): array
    {
        $allowed = ['nom', 'prenom', 'age', 'poids', 'taille', 'date_naissance', 'id_user'];
        $field   = in_array($field, $allowed, true) ? $field : 'id_user';
        $dir     = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT id_user, nom, prenom, age, sexe, poids, taille, email,
                       cas_social, date_naissance, adresse, specialite, role
                FROM utilisateur
                ORDER BY {$field} {$dir}";
        return $this->db->query($sql)->fetchAll();
    }

    // =========================================================
    // MÉTIER : STATISTIQUES
    // =========================================================

    /**
     * Génère les indicateurs statistiques globaux.
     *
     * @return array
     */
    public function getStats(): array
    {
        // Totaux par rôle
        $stmtRoles = $this->db->query(
            "SELECT role, COUNT(*) AS total FROM utilisateur GROUP BY role"
        );
        $roleRows = $stmtRoles->fetchAll();
        $byRole   = [];
        foreach ($roleRows as $row) {
            $byRole[$row['role']] = (int) $row['total'];
        }

        // Répartition par sexe
        $stmtSexe = $this->db->query(
            "SELECT sexe, COUNT(*) AS total FROM utilisateur GROUP BY sexe"
        );
        $sexeRows = $stmtSexe->fetchAll();
        $bySexe   = [];
        foreach ($sexeRows as $row) {
            $bySexe[$row['sexe'] ?? 'Non renseigné'] = (int) $row['total'];
        }

        // Moyennes poids / taille (patients uniquement)
        $stmtAvg = $this->db->query(
            "SELECT
                ROUND(AVG(NULLIF(poids, 0)), 2) AS avg_poids,
                ROUND(AVG(NULLIF(taille, 0)), 2) AS avg_taille
             FROM utilisateur
             WHERE role = 'patient'"
        );
        $avgs = $stmtAvg->fetch();

        // Répartition par cas social
        $stmtSocial = $this->db->query(
            "SELECT
                COALESCE(NULLIF(TRIM(cas_social), ''), 'Non renseigné') AS cas,
                COUNT(*) AS total
             FROM utilisateur
             GROUP BY cas"
        );
        $socialRows = $stmtSocial->fetchAll();
        $bySocial   = [];
        foreach ($socialRows as $row) {
            $bySocial[$row['cas']] = (int) $row['total'];
        }

        $totalUsers = array_sum($byRole);

        return [
            'total_utilisateurs' => $totalUsers,
            'total_patients'     => $byRole['patient']  ?? 0,
            'total_medecins'     => $byRole['medecin']  ?? 0,
            'total_admins'       => $byRole['admin']    ?? 0,
            'par_role'           => $byRole,
            'par_sexe'           => $bySexe,
            'avg_poids_patients' => $avgs['avg_poids']  ?? null,
            'avg_taille_patients'=> $avgs['avg_taille'] ?? null,
            'par_cas_social'     => $bySocial,
        ];
    }

    // =========================================================
    // MÉTIER : EXPORT PDF — données filtrées + triées
    // =========================================================

    /**
     * Retourne exactement les données filtrées et triées destinées à l'export PDF.
     * Identique à search() mais ne retourne que les colonnes utiles à l'impression.
     *
     * @param array  $filters
     * @param string $sortField
     * @param string $sortDir
     * @return array
     */
    public function getForPdfExport(array $filters = [], string $sortField = 'id_user', string $sortDir = 'DESC'): array
    {
        $rows = $this->search($filters, $sortField, $sortDir);

        return array_map(static function (array $row): array {
            return [
                'id_user'        => (int) $row['id_user'],
                'nom'            => $row['nom']            ?? '',
                'prenom'         => $row['prenom']         ?? '',
                'age'            => $row['age']            ?? null,
                'sexe'           => $row['sexe']           ?? '',
                'poids'          => $row['poids']          ?? null,
                'taille'         => $row['taille']         ?? null,
                'email'          => $row['email']          ?? '',
                'cas_social'     => $row['cas_social']     ?? '',
                'date_naissance' => $row['date_naissance'] ?? '',
                'adresse'        => $row['adresse']        ?? '',
                'specialite'     => $row['specialite']     ?? '',
                'role'           => $row['role']           ?? '',
            ];
        }, $rows);
    }
}
