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
}
