<?php
declare(strict_types=1);

/**
 * Modèle : documents de validation des médecins
 */
class DocumentMedecin
{
    private PDO $db;

    private const ALLOWED_TYPES = ['diplome', 'carte_professionnelle', 'cin', 'certificat_exercice'];
    private const ALLOWED_EXT   = ['pdf', 'jpg', 'jpeg', 'png'];
    private const MAX_SIZE_MB   = 5;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Récupérer tous les documents d'un médecin ──────────
    public function getByMedecin(int $idMedecin): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM document_medecin WHERE id_medecin = :id ORDER BY date_upload DESC"
        );
        $stmt->execute(['id' => $idMedecin]);
        return $stmt->fetchAll();
    }

    // ── Insérer ou remplacer un document (upsert par type) ─
    public function upsert(int $idMedecin, string $type, string $fichierUrl): int
    {
        // Supprimer l'ancien fichier physique si existant
        $old = $this->getByMedecinAndType($idMedecin, $type);
        if ($old) {
            $oldPath = __DIR__ . '/../' . ltrim($old['fichier_url'], '/');
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $stmt = $this->db->prepare(
                "UPDATE document_medecin
                 SET fichier_url = :url, date_upload = NOW(), statut = 'en_attente'
                 WHERE id_medecin = :id AND type_document = :type"
            );
            $stmt->execute(['url' => $fichierUrl, 'id' => $idMedecin, 'type' => $type]);
            return (int) $old['id_document'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO document_medecin (id_medecin, type_document, fichier_url)
             VALUES (:id, :type, :url)"
        );
        $stmt->execute(['id' => $idMedecin, 'type' => $type, 'url' => $fichierUrl]);
        return (int) $this->db->lastInsertId();
    }

    public function getByMedecinAndType(int $idMedecin, string $type): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM document_medecin
             WHERE id_medecin = :id AND type_document = :type LIMIT 1"
        );
        $stmt->execute(['id' => $idMedecin, 'type' => $type]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Vérifier que les 4 documents obligatoires sont uploadés
    public function hasAllDocuments(int $idMedecin): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT type_document) AS cnt
             FROM document_medecin
             WHERE id_medecin = :id"
        );
        $stmt->execute(['id' => $idMedecin]);
        $row = $stmt->fetch();
        return (int) ($row['cnt'] ?? 0) >= count(self::ALLOWED_TYPES);
    }

    // ── Valider un fichier uploadé ─────────────────────────
    public function validateUpload(array $file): ?string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return 'Erreur lors de l\'upload du fichier.';
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            return 'Format non autorisé. Utilisez : ' . implode(', ', self::ALLOWED_EXT);
        }
        $sizeMb = $file['size'] / 1024 / 1024;
        if ($sizeMb > self::MAX_SIZE_MB) {
            return "Fichier trop volumineux (max " . self::MAX_SIZE_MB . " MB).";
        }
        return null;
    }

    // ── Sauvegarder le fichier sur le disque ───────────────
    public function saveFile(array $file, int $idMedecin, string $type): string
    {
        $uploadDir = __DIR__ . '/../uploads/documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $idMedecin . '_' . $type . '_' . time() . '.' . $ext;
        $dest     = $uploadDir . $filename;
        move_uploaded_file($file['tmp_name'], $dest);
        return 'uploads/documents/' . $filename;
    }

    public static function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }
}
