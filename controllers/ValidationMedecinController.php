<?php
declare(strict_types=1);

/**
 * Contrôleur : validation des médecins + upload documents
 */
class ValidationMedecinController
{
    private ValidationMedecin  $validationModel;
    private DocumentMedecin    $documentModel;

    public function __construct()
    {
        $this->validationModel = new ValidationMedecin();
        $this->documentModel   = new DocumentMedecin();
    }

    // ── Helpers ────────────────────────────────────────────

    private function jsonResponse(array $payload, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw || trim($raw) === '') return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** Vérifie que l'utilisateur connecté est admin (via header X-User-Id + X-User-Role) */
    private function requireAdmin(): bool
    {
        $role = trim((string) ($_SERVER['HTTP_X_USER_ROLE'] ?? ''));
        if ($role !== 'admin') {
            $this->jsonResponse(['success' => false, 'message' => 'Accès interdit — admin requis'], 403);
            return false;
        }
        return true;
    }

    /** Vérifie que l'utilisateur connecté est le médecin concerné ou un admin */
    private function requireMedecinOrAdmin(int $idMedecin): bool
    {
        $role   = trim((string) ($_SERVER['HTTP_X_USER_ROLE'] ?? ''));
        $userId = (int) ($_SERVER['HTTP_X_USER_ID'] ?? 0);
        if ($role === 'admin') return true;
        if ($role === 'medecin' && $userId === $idMedecin) return true;
        $this->jsonResponse(['success' => false, 'message' => 'Accès interdit'], 403);
        return false;
    }

    // =========================================================
    // MÉDECIN : statut de validation
    // GET /api/validation/statut?id_medecin=X
    // =========================================================
    public function statut(): void
    {
        $idMedecin = (int) ($_GET['id_medecin'] ?? 0);
        if ($idMedecin <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id_medecin requis'], 422);
            return;
        }
        if (!$this->requireMedecinOrAdmin($idMedecin)) return;

        try {
            $data = $this->validationModel->getStatut($idMedecin);
            if (!$data) {
                $this->jsonResponse(['success' => false, 'message' => 'Médecin introuvable'], 404);
                return;
            }
            $documents = $this->documentModel->getByMedecin($idMedecin);
            $this->jsonResponse([
                'success'   => true,
                'data'      => [
                    'statut_validation' => $data['statut_validation'],
                    'date_inscription'  => $data['date_inscription'],
                    'date_validation'   => $data['date_validation'],
                    'motif_refus'       => $data['motif_refus'],
                    'documents'         => $documents,
                    'has_all_documents' => $this->documentModel->hasAllDocuments($idMedecin),
                ],
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // MÉDECIN : upload d'un document
    // POST /api/validation/upload  (multipart/form-data)
    // Champs : id_medecin, type_document, fichier
    // =========================================================
    public function upload(): void
    {
        $idMedecin    = (int) ($_POST['id_medecin']    ?? 0);
        $typeDocument = trim((string) ($_POST['type_document'] ?? ''));

        if ($idMedecin <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id_medecin requis'], 422);
            return;
        }
        if (!$this->requireMedecinOrAdmin($idMedecin)) return;

        $allowedTypes = DocumentMedecin::getAllowedTypes();
        if (!in_array($typeDocument, $allowedTypes, true)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'type_document invalide. Valeurs : ' . implode(', ', $allowedTypes),
            ], 422);
            return;
        }

        if (!isset($_FILES['fichier'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Aucun fichier reçu'], 422);
            return;
        }

        $error = $this->documentModel->validateUpload($_FILES['fichier']);
        if ($error !== null) {
            $this->jsonResponse(['success' => false, 'message' => $error], 422);
            return;
        }

        try {
            $url  = $this->documentModel->saveFile($_FILES['fichier'], $idMedecin, $typeDocument);
            $idDoc = $this->documentModel->upsert($idMedecin, $typeDocument, $url);

            // Si le médecin était refusé et re-uploade, on remet en attente
            $statut = $this->validationModel->getStatut($idMedecin);
            if (($statut['statut_validation'] ?? '') === 'refuse') {
                $this->validationModel->remettreEnAttente($idMedecin);
            }

            $this->jsonResponse([
                'success'     => true,
                'message'     => 'Document uploadé avec succès',
                'id_document' => $idDoc,
                'fichier_url' => $url,
                'has_all_documents' => $this->documentModel->hasAllDocuments($idMedecin),
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // ADMIN : liste des médecins en attente
    // GET /api/validation/admin/en-attente
    // =========================================================
    public function adminEnAttente(): void
    {
        if (!$this->requireAdmin()) return;
        try {
            $medecins = $this->validationModel->getMedecinsEnAttente();
            $this->jsonResponse(['success' => true, 'data' => $medecins, 'count' => count($medecins)]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // ADMIN : liste de tous les médecins
    // GET /api/validation/admin/medecins
    // =========================================================
    public function adminTousMedecins(): void
    {
        if (!$this->requireAdmin()) return;
        try {
            $medecins = $this->validationModel->getAllMedecins();
            $this->jsonResponse(['success' => true, 'data' => $medecins, 'count' => count($medecins)]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // ADMIN : détails d'un médecin + documents
    // GET /api/validation/admin/details?id_medecin=X
    // =========================================================
    public function adminDetails(): void
    {
        if (!$this->requireAdmin()) return;
        $idMedecin = (int) ($_GET['id_medecin'] ?? 0);
        if ($idMedecin <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id_medecin requis'], 422);
            return;
        }
        try {
            $data = $this->validationModel->getDetailsMedecin($idMedecin);
            if (!$data) {
                $this->jsonResponse(['success' => false, 'message' => 'Médecin introuvable'], 404);
                return;
            }
            $this->jsonResponse(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // ADMIN : valider un médecin
    // POST /api/validation/admin/valider  { id_medecin: X }
    // =========================================================
    public function adminValider(): void
    {
        if (!$this->requireAdmin()) return;
        $input     = $this->getJsonInput();
        $idMedecin = (int) ($input['id_medecin'] ?? 0);
        if ($idMedecin <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id_medecin requis'], 422);
            return;
        }
        try {
            $ok = $this->validationModel->valider($idMedecin);
            $this->jsonResponse(['success' => $ok, 'message' => $ok ? 'Médecin validé' : 'Erreur lors de la validation']);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // ADMIN : refuser un médecin
    // POST /api/validation/admin/refuser  { id_medecin: X, motif: "..." }
    // =========================================================
    public function adminRefuser(): void
    {
        if (!$this->requireAdmin()) return;
        $input     = $this->getJsonInput();
        $idMedecin = (int) ($input['id_medecin'] ?? 0);
        $motif     = trim((string) ($input['motif'] ?? ''));
        if ($idMedecin <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id_medecin requis'], 422);
            return;
        }
        if ($motif === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Le motif de refus est obligatoire'], 422);
            return;
        }
        try {
            $ok = $this->validationModel->refuser($idMedecin, $motif);
            $this->jsonResponse(['success' => $ok, 'message' => $ok ? 'Médecin refusé' : 'Erreur lors du refus']);
        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
