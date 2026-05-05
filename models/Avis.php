<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Avis
{
    public static function ensureSchema(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS avis (
                id_avis INT AUTO_INCREMENT PRIMARY KEY,
                patient_name VARCHAR(150) NOT NULL,
                id_patient INT NULL,
                id_medecin INT NOT NULL,
                rating TINYINT NOT NULL,
                commentaire TEXT NOT NULL,
                statut ENUM('pending','approved','reported') NOT NULL DEFAULT 'pending',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL,
                INDEX idx_avis_medecin (id_medecin),
                INDEX idx_avis_statut (statut),
                INDEX idx_avis_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public static function create(PDO $pdo, array $data): int
    {
        self::ensureSchema($pdo);
        $stmt = $pdo->prepare("
            INSERT INTO avis (patient_name, id_patient, id_medecin, rating, commentaire, statut)
            VALUES (:patient_name, :id_patient, :id_medecin, :rating, :commentaire, :statut)
        ");
        $stmt->execute([
            'patient_name' => $data['patient_name'],
            'id_patient' => $data['id_patient'] ?? null,
            'id_medecin' => $data['id_medecin'],
            'rating' => $data['rating'],
            'commentaire' => $data['commentaire'],
            'statut' => $data['statut'] ?? 'pending',
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function all(PDO $pdo): array
    {
        self::ensureSchema($pdo);
        $stmt = $pdo->query("
            SELECT a.id_avis,
                   a.patient_name,
                   a.id_patient,
                   a.id_medecin,
                   a.rating,
                   a.commentaire,
                   a.statut,
                   a.created_at,
                   CONCAT(COALESCE(m.prenom, ''), ' ', COALESCE(m.nom, '')) AS doctor_name,
                   m.email AS doctor_email
            FROM avis a
            LEFT JOIN utilisateur m ON m.id_user = a.id_medecin
            ORDER BY a.created_at DESC
            LIMIT 300
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateStatus(PDO $pdo, int $idAvis, string $status): bool
    {
        self::ensureSchema($pdo);
        if (!in_array($status, ['pending', 'approved', 'reported'], true)) {
            $status = 'pending';
        }

        $stmt = $pdo->prepare("UPDATE avis SET statut = :statut, updated_at = NOW() WHERE id_avis = :id");
        return $stmt->execute([
            'statut' => $status,
            'id' => $idAvis,
        ]);
    }

    public static function delete(PDO $pdo, int $idAvis): bool
    {
        self::ensureSchema($pdo);
        $stmt = $pdo->prepare("DELETE FROM avis WHERE id_avis = :id");
        return $stmt->execute(['id' => $idAvis]);
    }
}
