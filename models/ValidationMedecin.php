<?php
declare(strict_types=1);

/**
 * Modèle : gestion du statut de validation des médecins
 */
class ValidationMedecin
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Statut + motif d'un médecin ────────────────────────
    public function getStatut(int $idMedecin): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id_user, nom, prenom, email, specialite,
                    statut_validation, date_inscription, date_validation, motif_refus
             FROM utilisateur
             WHERE id_user = :id AND role = 'medecin'
             LIMIT 1"
        );
        $stmt->execute(['id' => $idMedecin]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Liste des médecins en attente (admin) ──────────────
    public function getMedecinsEnAttente(): array
    {
        $stmt = $this->db->query(
            "SELECT u.id_user, u.nom, u.prenom, u.email, u.specialite,
                    u.statut_validation, u.date_inscription, u.date_validation, u.motif_refus,
                    COUNT(d.id_document) AS nb_documents
             FROM utilisateur u
             LEFT JOIN document_medecin d ON d.id_medecin = u.id_user
             WHERE u.role = 'medecin' AND u.statut_validation = 'en_attente'
             GROUP BY u.id_user
             ORDER BY u.date_inscription ASC"
        );
        return $stmt->fetchAll();
    }

    // ── Liste de tous les médecins (admin) ─────────────────
    public function getAllMedecins(): array
    {
        $stmt = $this->db->query(
            "SELECT u.id_user, u.nom, u.prenom, u.email, u.specialite,
                    u.statut_validation, u.date_inscription, u.date_validation, u.motif_refus,
                    COUNT(d.id_document) AS nb_documents
             FROM utilisateur u
             LEFT JOIN document_medecin d ON d.id_medecin = u.id_user
             WHERE u.role = 'medecin'
             GROUP BY u.id_user
             ORDER BY u.date_inscription DESC"
        );
        return $stmt->fetchAll();
    }

    // ── Détails d'un médecin + ses documents (admin) ───────
    public function getDetailsMedecin(int $idMedecin): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, COUNT(d.id_document) AS nb_documents
             FROM utilisateur u
             LEFT JOIN document_medecin d ON d.id_medecin = u.id_user
             WHERE u.id_user = :id AND u.role = 'medecin'
             GROUP BY u.id_user
             LIMIT 1"
        );
        $stmt->execute(['id' => $idMedecin]);
        $medecin = $stmt->fetch();
        if (!$medecin) return null;

        $stmtDocs = $this->db->prepare(
            "SELECT * FROM document_medecin WHERE id_medecin = :id ORDER BY type_document"
        );
        $stmtDocs->execute(['id' => $idMedecin]);
        $medecin['documents'] = $stmtDocs->fetchAll();
        return $medecin;
    }

    // ── Valider un médecin ─────────────────────────────────
    public function valider(int $idMedecin): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE utilisateur
             SET statut_validation = 'valide',
                 date_validation   = NOW(),
                 motif_refus       = NULL
             WHERE id_user = :id AND role = 'medecin'"
        );
        return $stmt->execute(['id' => $idMedecin]);
    }

    // ── Refuser un médecin ─────────────────────────────────
    public function refuser(int $idMedecin, string $motif): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE utilisateur
             SET statut_validation = 'refuse',
                 date_validation   = NOW(),
                 motif_refus       = :motif
             WHERE id_user = :id AND role = 'medecin'"
        );
        return $stmt->execute(['id' => $idMedecin, 'motif' => $motif]);
    }

    // ── Remettre en attente (après re-upload) ──────────────
    public function remettreEnAttente(int $idMedecin): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE utilisateur
             SET statut_validation = 'en_attente',
                 date_validation   = NULL,
                 motif_refus       = NULL
             WHERE id_user = :id AND role = 'medecin'"
        );
        return $stmt->execute(['id' => $idMedecin]);
    }

    // ── Vérifier si un médecin est validé ─────────────────
    public function estValide(int $idMedecin): bool
    {
        $stmt = $this->db->prepare(
            "SELECT statut_validation FROM utilisateur
             WHERE id_user = :id AND role = 'medecin' LIMIT 1"
        );
        $stmt->execute(['id' => $idMedecin]);
        $row = $stmt->fetch();
        return ($row['statut_validation'] ?? '') === 'valide';
    }
}
