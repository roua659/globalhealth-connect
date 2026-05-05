<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Publication.php';
require_once __DIR__ . '/../../../models/Commentaire.php';
require_once __DIR__ . '/../../../models/Avis.php';
require_once __DIR__ . '/../../../services/ForumModerationService.php';
require_once __DIR__ . '/../../../services/MailService.php';

$usersApiBase = gh_users_api_base();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdoInit = config::getConnexion();
    ForumModerationService::ensureForumSchema($pdoInit);
    Avis::ensureSchema($pdoInit);
} catch (Exception $e) {
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;

function currentForumUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function userOwnsPublication(PDO $pdo, int $publicationId, int $userId): bool
{
    if ($publicationId <= 0 || $userId <= 0) {
        return false;
    }

    $stmt = $pdo->prepare("
        SELECT 1
        FROM publication p
        INNER JOIN medecin m ON m.id_medecin = p.id_medecin
        WHERE p.id_publication = ? AND m.id_user = ?
        LIMIT 1
    ");
    $stmt->execute([$publicationId, $userId]);
    return (bool) $stmt->fetchColumn();
}

function userOwnsComment(PDO $pdo, int $commentId, int $userId): bool
{
    if ($commentId <= 0 || $userId <= 0) {
        return false;
    }

    $stmt = $pdo->prepare("SELECT 1 FROM commentaire WHERE id_commentaire = ? AND id_user = ? LIMIT 1");
    $stmt->execute([$commentId, $userId]);
    return (bool) $stmt->fetchColumn();
}

if ($action && ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET')) {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true) ?? $_REQUEST;


    if ($action === 'add-publication') {
        try {
            if (empty($input['id_medecin']) || empty($input['contenu'])) {
                echo json_encode(['success' => false, 'error' => 'Donn??es manquantes']);
                exit;
            }

            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT id_user FROM utilisateur WHERE id_user = ?");
            $stmt->execute([$input['id_medecin']]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'L\'utilisateur s??lectionn?? n\'existe pas']);
                exit;
            }

            $publication = new Publication();
            $publication->setIdMedecin($input['id_medecin']);
            $publication->setContenu($input['contenu']);
            $publication->setDatePublication(date('Y-m-d H:i:s'));

            if (!empty($input['url_image'])) {
                $publication->setUrlImage($input['url_image']);
            }
            if (!empty($input['url_video'])) {
                $publication->setUrlVideo($input['url_video']);
            }

            $analysis = ForumModerationService::analyze((string)$input['contenu']);
            ForumModerationService::applyAnalysisToPublication($publication, $analysis);

            $result = $publication->create();
            echo json_encode($result['success']
                ? ['success' => true, 'message' => 'Publication cr????e avec succ??s', 'id' => $result['id'], 'moderation' => $analysis]
                : ['success' => false, 'error' => $result['error'] ?? 'Erreur lors de la cr??ation']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'delete-publication') {
        try {
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                exit;
            }

            $pdo = config::getConnexion();
            $publicationId = (int) $input['id'];
            $userId = currentForumUserId();
            if ($userId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Utilisateur non connect??']);
                exit;
            }
            if (!userOwnsPublication($pdo, $publicationId, $userId)) {
                echo json_encode(['success' => false, 'error' => 'Action non autoris??e']);
                exit;
            }

            $publication = new Publication();
            if (!$publication->findById($publicationId)) {
                echo json_encode(['success' => false, 'error' => 'Publication non trouv??e']);
                exit;
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM commentaire WHERE id_publication = ?");
            $stmt->execute([$publicationId]);
            $result = $publication->delete();
            if (!($result['success'] ?? false)) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Erreur lors de la suppression']);
                exit;
            }
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Publication supprim??e']);
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'update-publication') {
        try {
            $data = !empty($_POST) ? $_POST : $input;
            if (empty($data['id']) || empty($data['contenu'])) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                exit;
            }

            $pdo = config::getConnexion();
            $publicationId = (int) $data['id'];

            $publication = new Publication();
            if (!$publication->findById($publicationId)) {
                echo json_encode(['success' => false, 'error' => 'Publication non trouvée']);
                exit;
            }

            $publication->setContenu($data['contenu']);

            // Gestion upload nouvelle image
            if (!empty($_FILES['image']['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../../uploads/publications/';
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($ext, $allowed)) {
                    $filename = uniqid('pub_', true) . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        $publication->setUrlImage('/globalhealth-connect1/uploads/publications/' . $filename);
                    }
                }
            }

            if (array_key_exists('url_video', $data)) {
                $publication->set('url_video', !empty($data['url_video']) ? $data['url_video'] : null);
            }

            $analysis = ForumModerationService::analyze((string)$data['contenu']);
            ForumModerationService::applyAnalysisToPublication($publication, $analysis);

            $result = $publication->update();
            echo json_encode($result['success']
                ? ['success' => true, 'message' => 'Publication modifiée avec succès']
                : ['success' => false, 'error' => $result['error'] ?? 'Erreur lors de la modification']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'toggle-publication-status') {
        try {
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                exit;
            }

            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT statut FROM publication WHERE id_publication = ?");
            $stmt->execute([(int) $input['id']]);
            $row = $stmt->fetch();
            if (!$row) {
                echo json_encode(['success' => false, 'error' => 'Publication non trouvée']);
                exit;
            }

            $newStatut = $row['statut'] === 'approved' ? 'blocked' : 'approved';
            $moderationStatus = $newStatut === 'approved' ? 'safe' : 'blocked';
            ForumModerationService::setModerationStatus($pdo, (int) $input['id'], $moderationStatus);
            echo json_encode(['success' => true, 'statut' => $newStatut]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get-publications') {
        try {
            $publication = new Publication();
            echo json_encode(['success' => true, 'data' => $publication->getAll(100, 0)]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get-doctors') {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT id_user, nom, prenom, email FROM utilisateur WHERE id_role = 2 ORDER BY nom ASC");
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get-current-user') {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
                exit;
            }

            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT id_user, nom, prenom, email, id_role FROM utilisateur WHERE id_user = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            echo json_encode($user
                ? ['success' => true, 'data' => $user]
                : ['success' => false, 'error' => 'Utilisateur non trouvé']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'add-comment') {
        try {
            if (empty($input['id_publication']) || empty($input['contenu'])) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                exit;
            }

            $pdo = config::getConnexion();
            ForumModerationService::ensureCommentSchema($pdo);
            $commentUserId = (int) ($_SESSION['user_id'] ?? ($input['id_user'] ?? 0));

            $stmt = $pdo->prepare("SELECT id_publication FROM publication WHERE id_publication = ?");
            $stmt->execute([(int) $input['id_publication']]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Publication non trouvée']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT id_user FROM utilisateur WHERE id_user = ?");
            $stmt->execute([$commentUserId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
                exit;
            }

            $commentaire = new Commentaire();
            $commentaire->setIdPublication($input['id_publication']);
            $commentaire->setIdUser($commentUserId);
            $commentaire->setContenu($input['contenu']);
            $commentaire->setDatePublication(date('Y-m-d H:i:s'));
            $analysis = ForumModerationService::analyze((string)$input['contenu']);
            ForumModerationService::applyAnalysisToComment($commentaire, $analysis);

            $result = $commentaire->create();
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Commentaire cree avec succes',
                    'id' => $result['id'],
                    'moderation' => $analysis,
                ]);
                exit;
            }
            echo json_encode($result['success']
                ? ['success' => true, 'message' => 'Commentaire créé avec succès', 'id' => $result['id']]
                : ['success' => false, 'error' => $result['error'] ?? 'Erreur lors de la création']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get-comments') {
        try {
            if (empty($input['id_publication'])) {
                echo json_encode(['success' => false, 'error' => 'ID publication manquant']);
                exit;
            }

            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT c.*, u.nom, u.prenom
                FROM commentaire c
                JOIN utilisateur u ON c.id_user = u.id_user
                WHERE c.id_publication = ? AND c.statut = 'publie'
                ORDER BY c.date_publication DESC
            ");
            $stmt->execute([(int) $input['id_publication']]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get-all-comments') {
        try {
            $pdo = config::getConnexion();
            ForumModerationService::ensureCommentSchema($pdo);
            $stmt = $pdo->prepare("
                SELECT c.id_commentaire, c.contenu, c.statut, c.date_publication, c.id_publication,
                       c.moderation_status, c.toxicity_score, c.sensitive_score, c.medical_risk_score,
                       c.moderation_reason, c.moderation_source, c.flagged_at, c.reviewed_at,
                       CONCAT(u.prenom, ' ', u.nom) AS user_name,
                       SUBSTRING(p.contenu, 1, 50) AS post_content,
                       CONCAT(med.prenom, ' ', med.nom) AS doctor_name
                FROM commentaire c
                JOIN utilisateur u ON c.id_user = u.id_user
                JOIN publication p ON c.id_publication = p.id_publication
                LEFT JOIN medecin m ON p.id_medecin = m.id_medecin
                LEFT JOIN utilisateur med ON m.id_user = med.id_user
                ORDER BY c.date_publication DESC
                LIMIT 200
            ");
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'update-comment') {
        try {
            if (empty($input['id']) || empty($input['contenu'])) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                exit;
            }

            $contenu = trim($input['contenu']);
            if (strlen($contenu) < 2) {
                echo json_encode(['success' => false, 'error' => 'Le commentaire est trop court']);
                exit;
            }

            $pdo = config::getConnexion();
            ForumModerationService::ensureCommentSchema($pdo);
            $commentId = (int) $input['id'];
            $userId = currentForumUserId();
            if ($userId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
                exit;
            }
            if (!userOwnsComment($pdo, $commentId, $userId)) {
                echo json_encode(['success' => false, 'error' => 'Action non autorisée']);
                exit;
            }

            $commentaire = new Commentaire();
            if (!$commentaire->findById($commentId)) {
                echo json_encode(['success' => false, 'error' => 'Commentaire non trouvÃ©']);
                exit;
            }

            $commentaire->setContenu($contenu);
            $analysis = ForumModerationService::analyze($contenu);
            ForumModerationService::applyAnalysisToComment($commentaire, $analysis);
            $result = $commentaire->update();

            echo json_encode($result['success']
                ? ['success' => true, 'moderation' => $analysis]
                : ['success' => false, 'error' => $result['error'] ?? 'Erreur lors de la modification']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'update-comment-status') {
        try {
            if (empty($input['id']) || empty($input['statut'])) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                exit;
            }

            $validStatuses = ['en_attente', 'publie', 'supprime'];
            if (!in_array($input['statut'], $validStatuses, true)) {
                echo json_encode(['success' => false, 'error' => 'Statut invalide']);
                exit;
            }

            $pdo = config::getConnexion();
            ForumModerationService::ensureCommentSchema($pdo);
            $moderationStatus = $input['statut'] === 'publie' ? 'safe' : ($input['statut'] === 'supprime' ? 'blocked' : 'review');
            $flaggedAtSql = $moderationStatus === 'safe' ? 'NULL' : 'COALESCE(flagged_at, NOW())';
            $stmt = $pdo->prepare("
                UPDATE commentaire
                SET statut = :statut,
                    moderation_status = :moderation_status,
                    flagged_at = {$flaggedAtSql},
                    reviewed_at = NOW()
                WHERE id_commentaire = :id
            ");
            $stmt->execute([
                'statut' => $input['statut'],
                'moderation_status' => $moderationStatus,
                'id' => (int) $input['id'],
            ]);
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'delete-comment-db') {
        try {
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                exit;
            }

            $pdo = config::getConnexion();
            $commentId = (int) $input['id'];
            $userId = currentForumUserId();
            if ($userId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
                exit;
            }
            if (!userOwnsComment($pdo, $commentId, $userId)) {
                echo json_encode(['success' => false, 'error' => 'Action non autorisée']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM commentaire WHERE id_commentaire = ?");
            $stmt->execute([$commentId]);
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get-all-publications') {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT p.id_publication, p.contenu, p.date_publication, p.statut, p.url_image, p.url_video,
                       p.moderation_status, p.toxicity_score, p.sensitive_score, p.medical_risk_score,
                       p.moderation_reason, p.moderation_source, p.flagged_at, p.reviewed_at,
                       CONCAT(u.prenom, ' ', u.nom) AS doctor_name
                FROM publication p
                LEFT JOIN medecin m ON m.id_medecin = p.id_medecin
                LEFT JOIN utilisateur u ON u.id_user = m.id_user
                ORDER BY p.date_publication DESC
                LIMIT 200
            ");
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get-moderation-publications') {
        try {
            $pdo = config::getConnexion();
            ForumModerationService::ensurePublicationSchema($pdo);
            $stmt = $pdo->prepare("
                SELECT p.id_publication, p.contenu, p.date_publication, p.statut, p.url_image, p.url_video,
                       p.moderation_status, p.toxicity_score, p.sensitive_score, p.medical_risk_score,
                       p.moderation_reason, p.moderation_source, p.flagged_at, p.reviewed_at,
                       CONCAT(u.prenom, ' ', u.nom) AS doctor_name
                FROM publication p
                LEFT JOIN medecin m ON m.id_medecin = p.id_medecin
                LEFT JOIN utilisateur u ON u.id_user = m.id_user
                WHERE p.moderation_status IN ('review', 'blocked')
                ORDER BY COALESCE(p.flagged_at, p.date_publication) DESC
                LIMIT 100
            ");
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'set-publication-moderation-status') {
        try {
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                exit;
            }

            $pdo = config::getConnexion();
            ForumModerationService::ensurePublicationSchema($pdo);
            $status = (string)($input['status'] ?? 'review');
            ForumModerationService::setModerationStatus($pdo, (int)$input['id'], $status);
            echo json_encode([
                'success' => true,
                'moderation_status' => $status,
                'statut' => $status === 'safe' ? 'approved' : 'blocked',
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'admin-delete-publication') {
        try {
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                exit;
            }
            $pdo = config::getConnexion();
            $publicationId = (int) $input['id'];
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM commentaire WHERE id_publication = ?")->execute([$publicationId]);
            $pdo->prepare("DELETE FROM publication WHERE id_publication = ?")->execute([$publicationId]);
            $pdo->commit();
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'admin-delete-comment') {
        try {
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                exit;
            }
            $pdo = config::getConnexion();
            $pdo->prepare("DELETE FROM commentaire WHERE id_commentaire = ?")->execute([(int) $input['id']]);
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'admin-update-comment-status') {
        try {
            if (empty($input['id']) || !isset($input['statut'])) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                exit;
            }
            $validStatuses = ['en_attente', 'publie', 'supprime'];
            if (!in_array($input['statut'], $validStatuses, true)) {
                echo json_encode(['success' => false, 'error' => 'Statut invalide']);
                exit;
            }
            $pdo = config::getConnexion();
            ForumModerationService::ensureCommentSchema($pdo);
            $moderationStatus = $input['statut'] === 'publie' ? 'safe' : ($input['statut'] === 'supprime' ? 'blocked' : 'review');
            $flaggedAtSql = $moderationStatus === 'safe' ? 'NULL' : 'COALESCE(flagged_at, NOW())';
            $stmt = $pdo->prepare("
                UPDATE commentaire
                SET statut = :statut,
                    moderation_status = :moderation_status,
                    flagged_at = {$flaggedAtSql},
                    reviewed_at = NOW()
                WHERE id_commentaire = :id
            ");
            $stmt->execute([
                'statut' => $input['statut'],
                'moderation_status' => $moderationStatus,
                'id' => (int) $input['id'],
            ]);
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get-all-reviews') {
        try {
            $pdo = config::getConnexion();
            echo json_encode(['success' => true, 'data' => Avis::all($pdo)]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'admin-update-review-status') {
        try {
            if (empty($input['id']) || empty($input['statut'])) {
                echo json_encode(['success' => false, 'error' => 'Donnees manquantes']);
                exit;
            }
            $pdo = config::getConnexion();
            Avis::updateStatus($pdo, (int)$input['id'], (string)$input['statut']);
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'admin-delete-review') {
        try {
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                exit;
            }
            $pdo = config::getConnexion();
            Avis::delete($pdo, (int)$input['id']);
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'notify-review-patient') {
        try {
            if (empty($input['id_patient']) || empty($input['message'])) {
                echo json_encode(['success' => false, 'error' => 'Patient et message obligatoires']);
                exit;
            }

            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT id_user, nom, prenom, email
                FROM utilisateur
                WHERE id_user = ? AND id_role = 1
                LIMIT 1
            ");
            $stmt->execute([(int)$input['id_patient']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$patient) {
                echo json_encode(['success' => false, 'error' => 'Patient introuvable']);
                exit;
            }

            $mail = MailService::sendPatientNotification([
                'patient_name' => trim((string)$patient['prenom'] . ' ' . (string)$patient['nom']),
                'patient_email' => (string)$patient['email'],
                'message' => (string)$input['message'],
            ]);

            echo json_encode([
                'success' => $mail['success'],
                'message' => $mail['success'] ? 'Notification envoyee au patient' : 'Email non envoye',
                'mail' => $mail,
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get-users') {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT id_user, nom, prenom FROM utilisateur ORDER BY id_user LIMIT 10");
            $stmt->execute();
            $users = $stmt->fetchAll();

            if (!$users) {
                echo json_encode(['success' => false, 'error' => 'No users found']);
                exit;
            }

            echo json_encode(['success' => true, 'data' => $users]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'add-user-db') {
        try {
            if (empty($input['name']) || empty($input['email'])) {
                echo json_encode(['success' => false, 'error' => 'Nom et Email obligatoires']);
                exit;
            }

            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
            $stmt->execute([$input['email']]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Cet email existe déjà']);
                exit;
            }

            $roleId = 3;
            if (($input['role'] ?? null) === 'doctor') {
                $roleId = 2;
            } elseif (($input['role'] ?? null) === 'admin') {
                $roleId = 1;
            }

            $names = explode(' ', trim((string) $input['name']), 2);
            $prenom = array_pop($names);
            $nom = array_pop($names) ?: $prenom;

            $stmt = $pdo->prepare("
                INSERT INTO utilisateur (nom, prenom, email, telephone, password, id_role, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $password = password_hash($input['password'] ?? 'password123', PASSWORD_DEFAULT);
            $stmt->execute([
                $nom,
                $prenom,
                $input['email'],
                $input['phone'] ?? null,
                $password,
                $roleId,
            ]);

            $userId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Utilisateur créé avec succès en base de données',
                'id' => $userId,
                'data' => [
                    'id' => $userId,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $input['email'],
                ],
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth Connect - Backoffice Médical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --medical-white: #ffffff;
            --medical-blue: #2b7be4;
            --medical-light-blue: #e8f4ff;
            --medical-green: #2ecc71;
            --medical-gray: #f5f7fa;
            --medical-text: #2c3e50;
            --sidebar-width: 280px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--medical-gray);
            color: var(--medical-text);
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            box-shadow: 2px 0 20px rgba(0,0,0,0.05);
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 18px;
            border-radius: 16px;
            color: var(--medical-text);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            cursor: pointer;
        }
        .sidebar-menu a:hover { background: var(--medical-light-blue); color: var(--medical-blue); }
        .sidebar-menu a.active {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            color: white;
            box-shadow: 0 5px 15px rgba(43,123,228,0.3);
        }
        .sidebar-footer {
            position: absolute;
            bottom: 30px;
            left: 20px;
            right: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 25px 35px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 25px;
            border-radius: 20px;
        }
        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 18px;
        }
        .stat-number { font-size: 2.2rem; font-weight: 800; margin: 10px 0 5px; }
        .stat-label { color: #6c7a8a; font-size: 0.9rem; }
        
        .data-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            animation: slideIn 0.4s ease-out;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .data-table td a[href^="mailto:"] {
            color: var(--medical-blue);
            text-decoration: none;
            font-weight: 500;
        }
        .data-table td a[href^="mailto:"]:hover { text-decoration: underline; }
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-scroll .data-table { min-width: 920px; }
        .data-table th {
            font-weight: 600;
            color: var(--medical-blue);
            background: var(--medical-light-blue);
        }
        .status-badge {
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-approved { background: #e8f8f0; color: #2ecc71; }
        .status-pending { background: #fff3e0; color: #f39c12; }
        .status-warning { background: #fff3e0; color: #f39c12; }
        .status-danger { background: #fee; color: #e74c3c; }
        .status-reported { background: #fee; color: #e74c3c; }
        
        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px 12px;
            margin: 0 3px;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .icon-btn:hover { background: var(--medical-gray); transform: scale(1.05); }
        .icon-btn.approve { color: #2ecc71; }
        .icon-btn.delete { color: #e74c3c; }
        .icon-btn.edit { color: var(--medical-blue); }
        .icon-btn.flag { color: #f39c12; }
        
        .btn-medical {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-medical:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(43,123,228,0.3); }
        .btn-outline-medical {
            background: transparent;
            border: 2px solid var(--medical-blue);
            color: var(--medical-blue);
            padding: 8px 22px;
            border-radius: 40px;
            font-weight: 600;
        }
        
        .modal-custom .modal-content {
            border-radius: 28px;
            border: none;
            padding: 10px;
        }
        .form-control-custom {
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 12px 16px;
        }
        .form-control-custom:focus {
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 3px rgba(43,123,228,0.1);
        }
        
        .notification-toast {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: white;
            padding: 14px 24px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateX(450px);
            transition: transform 0.3s;
            z-index: 1001;
            border-left: 4px solid #2ecc71;
        }
        .notification-toast.show { transform: translateX(0); }
        
        .chart-bar {
            background: var(--medical-gray);
            border-radius: 12px;
            height: 40px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .chart-bar-fill {
            background: linear-gradient(90deg, var(--medical-blue), var(--medical-green));
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 15px;
            color: white;
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c7a8a;
        }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; opacity: 0.3; }
        
        .btn-group-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Style pour le suivi */
        .followup-card {
            background: var(--medical-gray);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--medical-blue);
        }
        .followup-card h6 {
            margin-bottom: 10px;
            color: var(--medical-blue);
        }
    </style>
</head>
<body>

<!-- Accès direct - Pas de login -->
<div id="mainContent">
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-icon"><i class="fas fa-heartbeat"></i></div>
                <div><strong style="font-size:1.3rem;">GlobalHealth</strong><br><small>BACKOFFICE</small></div>
            </div>
            <ul class="sidebar-menu">
                <li><a onclick="switchModule('dashboard');"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a onclick="switchModule('users');"><i class="fas fa-users"></i> Utilisateurs</a></li>
                <li><a onclick="switchModule('forum');"><i class="fas fa-newspaper"></i> Publications</a></li>
                <li><a onclick="switchModule('moderation');"><i class="fas fa-shield-alt"></i> Modération IA</a></li>
                <li><a onclick="switchModule('comments');"><i class="fas fa-comments"></i> Commentaires</a></li>
                <li><a onclick="switchModule('reviews');"><i class="fas fa-star"></i> Avis Patients</a></li>
                <li><a onclick="switchModule('appointments');"><i class="fas fa-calendar-check"></i> Rendez-vous</a></li>
                <li><a onclick="switchModule('consultations');"><i class="fas fa-stethoscope"></i> Consultation & Suivi</a></li>
            </ul>
            <div class="sidebar-footer">
                <!-- Bouton réinitialiser supprimé -->
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title" id="pageTitle">Dashboard</h1>
                <div><button class="btn-outline-medical" onclick="refreshModule()"><i class="fas fa-sync-alt"></i> Actualiser</button></div>
            </div>
            <div id="moduleContent"></div>
        </div>
    </div>
</div>

<div class="notification-toast" id="notificationToast"></div>

<!-- Modals CRUD -->
<div class="modal fade" id="addPostModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title"><i class="fas fa-newspaper me-2"></i>Ajouter une publication</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="addPostForm"><div class="mb-3"><label>Médecin</label><select class="form-select form-control-custom" id="postDoctorId" required></select></div>
<div class="mb-3"><label>Contenu</label><textarea class="form-control form-control-custom" id="postContent" rows="3" placeholder="Partagez votre expertise médicale..." required></textarea></div>
<div class="mb-3"><label>Image (URL)</label><input type="text" class="form-control form-control-custom" id="postImage" placeholder="https://..."></div>
<div class="mb-3"><label>Vidéo (URL)</label><input type="text" class="form-control form-control-custom" id="postVideo" placeholder="https://..."></div>
<button type="submit" class="btn btn-medical w-100">Publier</button></form></div></div></div></div>

<div class="modal fade" id="addUserModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title">Ajouter un utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="addUserForm" novalidate><div class="row g-3">
<div class="col-md-6"><label>Nom</label><input type="text" class="form-control form-control-custom" id="newUserNom"></div>
<div class="col-md-6"><label>Prénom</label><input type="text" class="form-control form-control-custom" id="newUserPrenom"></div>
<div class="col-md-4"><label>Age</label><input type="number" class="form-control form-control-custom" id="newUserAge" min="0" max="130"></div>
<div class="col-md-4"><label>Sexe</label><select class="form-select form-control-custom" id="newUserSexe"><option value="">Sélectionner</option><option value="Homme">Homme</option><option value="Femme">Femme</option></select></div>
<div class="col-md-4"><label>Date naissance</label><input type="date" class="form-control form-control-custom" id="newUserDateNaissance"></div>
<div class="col-md-6"><label>Poids (kg)</label><input type="number" class="form-control form-control-custom" id="newUserPoids" min="0" step="0.1"></div>
<div class="col-md-6"><label>Taille (m)</label><input type="number" class="form-control form-control-custom" id="newUserTaille" min="0" step="0.01"></div>
<div class="col-md-6"><label>Email</label><input type="email" class="form-control form-control-custom" id="newUserEmail"></div>
<div class="col-md-6"><label>Mot de passe</label><input type="password" class="form-control form-control-custom" id="newUserMotDePasse" minlength="6"></div>
<div class="col-md-6"><label>Cas social</label><input type="text" class="form-control form-control-custom" id="newUserCasSocial" placeholder="Ex: assuré CNSS"></div>
<div class="col-md-6"><label>Rôle</label><select class="form-select form-control-custom" id="newUserRole" onchange="toggleSpecialtyField()"><option value="patient">Patient</option><option value="medecin">Médecin</option><option value="admin">Admin</option></select></div>
<div class="col-12"><label>Adresse</label><textarea class="form-control form-control-custom" id="newUserAdresse" rows="2"></textarea></div>
<div class="col-12" id="specialtyField" style="display:none"><label>Spécialité</label><input type="text" class="form-control form-control-custom" id="newUserSpecialite" placeholder="Ex: Cardiologue"></div>
</div>
<button type="submit" class="btn btn-medical w-100 mt-3">Créer</button></form></div></div></div></div>

<div class="modal fade" id="editUserModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title" id="editUserModalTitle">Modifier utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="editUserForm">
<input type="hidden" id="editUserId">
<input type="hidden" id="editUserRole">
<div id="editPatientSection" style="display:none">
    <p class="text-muted small mb-3">Fiche patient — tous les champs correspondent à la table <code>utilisateur</code>.</p>
    <div class="row g-3">
        <div class="col-md-6"><label>Nom</label><input type="text" class="form-control form-control-custom" id="editPatientNom" autocomplete="family-name"></div>
        <div class="col-md-6"><label>Prénom</label><input type="text" class="form-control form-control-custom" id="editPatientPrenom" autocomplete="given-name"></div>
        <div class="col-md-4"><label>Âge</label><input type="number" class="form-control form-control-custom" id="editPatientAge" min="0" max="130"></div>
        <div class="col-md-4"><label>Sexe</label><select class="form-select form-control-custom" id="editPatientSexe"><option value="">Sélectionner</option><option value="Homme">Homme</option><option value="Femme">Femme</option></select></div>
        <div class="col-md-4"><label>Date de naissance</label><input type="date" class="form-control form-control-custom" id="editPatientDateNaissance"></div>
        <div class="col-md-6"><label>Poids (kg)</label><input type="number" class="form-control form-control-custom" id="editPatientPoids" min="0" step="0.1"></div>
        <div class="col-md-6"><label>Taille (m)</label><input type="number" class="form-control form-control-custom" id="editPatientTaille" min="0" step="0.01"></div>
        <div class="col-md-6"><label>Email</label><input type="email" class="form-control form-control-custom" id="editPatientEmail" autocomplete="email"></div>
        <div class="col-md-6"><label>Cas social</label><input type="text" class="form-control form-control-custom" id="editPatientCasSocial" placeholder="Ex: assuré CNSS"></div>
        <div class="col-12"><label>Adresse</label><textarea class="form-control form-control-custom" id="editPatientAdresse" rows="2"></textarea></div>
        <div class="col-12"><label>Nouveau mot de passe</label><input type="password" class="form-control form-control-custom" id="editPatientMotDePasse" minlength="6" autocomplete="new-password" placeholder="Laisser vide pour ne pas modifier"></div>
    </div>
</div>
<div id="editStaffSection" style="display:none">
    <div class="mb-3"><label>Nom complet</label><input type="text" class="form-control form-control-custom" id="editUserName" placeholder="Nom Prénom"></div>
    <div class="mb-3"><label>Email</label><input type="email" class="form-control form-control-custom" id="editUserEmail"></div>
    <div class="mb-3"><label>Cas social</label><input type="text" class="form-control form-control-custom" id="editUserPhone"></div>
    <div class="mb-3" id="editMedecinSpecialtyWrap" style="display:none"><label>Spécialité (médecin)</label><input type="text" class="form-control form-control-custom" id="editUserSpecialty" placeholder="Ex: Cardiologue"></div>
</div>
<button type="submit" class="btn btn-medical w-100 mt-3">Enregistrer les modifications</button>
</form></div></div></div></div>

<div class="modal fade" id="notifyReviewModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title">Notifier un patient</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="notifyReviewForm"><div class="mb-3"><label>Patient</label><select class="form-select form-control-custom" id="notifyPatientId" required></select></div>
<div class="mb-3"><label>Message</label><textarea class="form-control form-control-custom" id="notifyMessage" rows="3">📝 Nous espérons que votre consultation s'est bien passée ! N'oubliez pas de donner votre avis et de noter votre médecin sur 5 étoiles. 🌟</textarea></div>
<button type="submit" class="btn btn-medical w-100">Envoyer la notification</button></form></div></div></div></div>

<!-- Modal Consultation & Suivi -->
<div class="modal fade" id="addConsultationModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title"><i class="fas fa-stethoscope me-2"></i>Ajouter une consultation / suivi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="addConsultationForm"><div class="row g-3">
    <div class="col-md-6"><label>ID Patient</label><input type="text" class="form-control form-control-custom" id="consultation_id_patient" placeholder="ID Patient" required></div>
    <div class="col-md-6"><label>ID Médecin</label><input type="text" class="form-control form-control-custom" id="consultation_id_medecin" placeholder="ID Médecin" required></div>
    <div class="col-md-6"><label>ID Rendez-vous</label><input type="text" class="form-control form-control-custom" id="consultation_id_rdv" placeholder="ID Rendez-vous"></div>
    <div class="col-md-6"><label>Date de la consultation</label><input type="date" class="form-control form-control-custom" id="consultation_date" required></div>
    <div class="col-12"><label>Symptômes</label><textarea class="form-control form-control-custom" id="consultation_symptomes" rows="2" placeholder="Décrivez les symptômes..." required></textarea></div>
    <div class="col-12"><label>Diagnostic</label><textarea class="form-control form-control-custom" id="consultation_diagnostic" rows="2" placeholder="Diagnostic médical..." required></textarea></div>
    <div class="col-12"><label>Traitement prescrit</label><textarea class="form-control form-control-custom" id="consultation_traitement" rows="2" placeholder="Traitement prescrit..." required></textarea></div>
    <div class="col-12"><label>Ordonnance</label><textarea class="form-control form-control-custom" id="consultation_ordonnance" rows="3" placeholder="Ordonnance (médicaments, posologie, durée...)"></textarea></div>
    <div class="col-12"><label>Notes du médecin</label><textarea class="form-control form-control-custom" id="consultation_notes" rows="2" placeholder="Notes complémentaires..."></textarea></div>
    <div class="col-12"><label>Suivi / Évolution</label><textarea class="form-control form-control-custom" id="consultation_suivi" rows="3" placeholder="Suivi de l'évolution du patient..."></textarea></div>
</div>
<button type="submit" class="btn btn-medical w-100 mt-3">Enregistrer</button></form></div></div></div></div>

<div class="modal fade" id="editConsultationModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier consultation / suivi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="editConsultationForm"><input type="hidden" id="editConsultationId"><div class="row g-3">
    <div class="col-md-6"><label>ID Patient</label><input type="text" class="form-control form-control-custom" id="edit_consultation_id_patient" required></div>
    <div class="col-md-6"><label>ID Médecin</label><input type="text" class="form-control form-control-custom" id="edit_consultation_id_medecin" required></div>
    <div class="col-md-6"><label>ID Rendez-vous</label><input type="text" class="form-control form-control-custom" id="edit_consultation_id_rdv"></div>
    <div class="col-md-6"><label>Date de la consultation</label><input type="date" class="form-control form-control-custom" id="edit_consultation_date" required></div>
    <div class="col-12"><label>Symptômes</label><textarea class="form-control form-control-custom" id="edit_consultation_symptomes" rows="2" required></textarea></div>
    <div class="col-12"><label>Diagnostic</label><textarea class="form-control form-control-custom" id="edit_consultation_diagnostic" rows="2" required></textarea></div>
    <div class="col-12"><label>Traitement prescrit</label><textarea class="form-control form-control-custom" id="edit_consultation_traitement" rows="2"></textarea></div>
    <div class="col-12"><label>Ordonnance</label><textarea class="form-control form-control-custom" id="edit_consultation_ordonnance" rows="3"></textarea></div>
    <div class="col-12"><label>Notes du médecin</label><textarea class="form-control form-control-custom" id="edit_consultation_notes" rows="2"></textarea></div>
    <div class="col-12"><label>Suivi / Évolution</label><textarea class="form-control form-control-custom" id="edit_consultation_suivi" rows="3"></textarea></div>
</div>
<button type="submit" class="btn btn-medical w-100 mt-3">Modifier</button></form></div></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ============================================
    // DONNÉES PERSISTANTES
    // ============================================
    let usersData = [];
    let forumPosts = [];
    let moderationPosts = [];
    let allCommentsFromDb = [];
    let reviewsData = [];
    let appointmentsData = [];
    let consultationsData = [];
    let currentModule = 'dashboard';
    
    // Données de démo pour les consultations
    function initDemoConsultations() {
        if(consultationsData.length === 0) {
            consultationsData = [
                { id: 1, id_patient: "P001", id_medecin: "D001", id_rdv: "RDV001", date: "2024-01-15", symptomes: "Fièvre, toux, fatigue", diagnostic: "Grippe saisonnière", traitement: "Paracétamol, repos", ordonnance: "Doliprane 1000mg 3x/jour", notes_medecin: "Patient à surveiller", suivi: "Amélioration après 3 jours" },
                { id: 2, id_patient: "P002", id_medecin: "D002", id_rdv: "RDV002", date: "2024-01-20", symptomes: "Douleurs thoraciques", diagnostic: "Angine de poitrine", traitement: "Traitement cardiologique", ordonnance: "Aspirine, repos", notes_medecin: "Cardiologue consulté", suivi: "Stable sous traitement" }
            ];
            saveConsultations();
        }
    }
    
    const USERS_API_BASE = <?php echo json_encode($usersApiBase, JSON_THROW_ON_ERROR); ?>;

    async function apiRequest(endpoint, method = 'GET', payload = null) {
        const options = { method, headers: {} };
        if (payload !== null) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }
        const url = `${USERS_API_BASE}/${endpoint}`;
        const response = await fetch(url, options);
        let result;
        try {
            result = await response.json();
        } catch (e) {
            throw new Error(`Réponse invalide (${response.status}). Vérifiez l'URL: ${url}`);
        }
        if (!response.ok || !result.success) {
            throw new Error(result.message || `Erreur API (${response.status})`);
        }
        return result.data;
    }

    const BACKOFFICE_URL = '/globalhealth-connect1/views/backoffice/layout/backoffice.php';

    async function loadUsersFromApi() {
        usersData = await apiRequest('list', 'GET');
    }

    async function loadPublicationsFromDb() {
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=get-all-publications`);
            const data = await res.json();
            if (data.success) {
                forumPosts = data.data.map(p => ({
                    id: p.id_publication,
                    doctor_name: p.doctor_name || `Médecin #${p.id_publication}`,
                    content: p.contenu || '',
                    date: p.date_publication || '',
                    status: p.statut || 'approved',
                    moderation_status: p.moderation_status || 'safe',
                    toxicity_score: Number(p.toxicity_score || 0),
                    sensitive_score: Number(p.sensitive_score || 0),
                    medical_risk_score: Number(p.medical_risk_score || 0),
                    moderation_reason: p.moderation_reason || '',
                    moderation_source: p.moderation_source || 'fallback',
                    flagged_at: p.flagged_at || '',
                    reviewed_at: p.reviewed_at || '',
                    url_image: p.url_image,
                    url_video: p.url_video,
                    comments: []
                }));
            }
        } catch (e) {
            forumPosts = [];
        }
    }

    async function loadModerationPublicationsFromDb() {
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=get-moderation-publications`);
            const data = await res.json();
            if (data.success) {
                moderationPosts = data.data.map(p => ({
                    id: p.id_publication,
                    doctor_name: p.doctor_name || `Médecin #${p.id_publication}`,
                    content: p.contenu || '',
                    date: p.date_publication || '',
                    status: p.statut || 'blocked',
                    moderation_status: p.moderation_status || 'review',
                    toxicity_score: Number(p.toxicity_score || 0),
                    sensitive_score: Number(p.sensitive_score || 0),
                    medical_risk_score: Number(p.medical_risk_score || 0),
                    moderation_reason: p.moderation_reason || '',
                    moderation_source: p.moderation_source || 'fallback',
                    flagged_at: p.flagged_at || '',
                    reviewed_at: p.reviewed_at || ''
                }));
            }
        } catch (e) {
            moderationPosts = [];
        }
    }

    async function loadAllCommentsFromDb() {
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=get-all-comments`);
            const data = await res.json();
            if (data.success) allCommentsFromDb = data.data;
        } catch (e) {
            allCommentsFromDb = [];
        }
    }

    async function loadReviewsFromDb() {
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=get-all-reviews`);
            const data = await res.json();
            if (data.success) {
                reviewsData = data.data.map(r => ({
                    id: Number(r.id_avis),
                    patient_name: r.patient_name || '',
                    doctor_id: Number(r.id_medecin || 0),
                    doctor_name: (r.doctor_name || '').trim() || `Medecin #${r.id_medecin}`,
                    doctor_email: r.doctor_email || '',
                    rating: Number(r.rating || 0),
                    comment: r.commentaire || '',
                    date: r.created_at || '',
                    status: r.statut || 'pending'
                }));
            }
        } catch (e) {
            reviewsData = [];
        }
    }

    async function loadAllData() {
        try {
            await loadUsersFromApi();
        } catch (error) {
            usersData = [];
            showNotification(`Erreur chargement utilisateurs: ${error.message}`, true);
        }

        try {
            await loadPublicationsFromDb();
            await loadModerationPublicationsFromDb();
            await loadAllCommentsFromDb();
            await loadReviewsFromDb();
        } catch (error) {
            forumPosts = [];
            moderationPosts = [];
            allCommentsFromDb = [];
            reviewsData = [];
        }

        const storedPosts = localStorage.getItem('globalhealthBack_posts');
        if(false) forumPosts = JSON.parse(storedPosts || '[]'); // disabled: using DB now
        
        const storedReviews = localStorage.getItem('globalhealthBack_reviews');
        if(false) reviewsData = JSON.parse(storedReviews || '[]'); // disabled: using DB now
        
        const storedAppointments = localStorage.getItem('globalhealthBack_appointments');
        if(storedAppointments) appointmentsData = JSON.parse(storedAppointments);
        else appointmentsData = [];
        
        const storedConsultations = localStorage.getItem('globalhealthBack_consultations');
        if(storedConsultations) consultationsData = JSON.parse(storedConsultations);
        else consultationsData = [];
        
        initDemoConsultations();
    }
    
    function savePosts() { localStorage.setItem('globalhealthBack_posts', JSON.stringify(forumPosts)); }
    function saveReviews() { localStorage.setItem('globalhealthBack_reviews', JSON.stringify(reviewsData)); }
    function saveAppointments() { localStorage.setItem('globalhealthBack_appointments', JSON.stringify(appointmentsData)); }
    function saveConsultations() { localStorage.setItem('globalhealthBack_consultations', JSON.stringify(consultationsData)); }
    
    function syncWithFrontoffice() {
        const doctors = usersData.filter(u => u.role === 'medecin').map(d => ({
            id: d.id || d.id_user,
            name: d.name || `${d.nom || ''} ${d.prenom || ''}`.trim(),
            specialty: d.specialite || 'Médecin généraliste',
            email: d.email,
            phone: d.cas_social || ''
        }));
        localStorage.setItem('globalhealth_doctors', JSON.stringify(doctors));
        localStorage.setItem('globalhealth_forumPosts', JSON.stringify(forumPosts));
        localStorage.setItem('globalhealth_reviews', JSON.stringify(reviewsData));
    }
    
    // ============================================
    // NAVIGATION
    // ============================================
    function switchModule(module) {
        currentModule = module;
        document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
        const activeLink = Array.from(document.querySelectorAll('.sidebar-menu a')).find(a => a.innerText.toLowerCase().includes(module));
        if(activeLink) activeLink.classList.add('active');
        
        const titles = {
            dashboard: 'Dashboard - Vue d\'ensemble',
            users: 'Gestion des Utilisateurs',
            moderation: 'Modération IA - Forum',
            forum: 'Forum - Publications des Médecins',
            comments: 'Gestion des Commentaires',
            reviews: 'Avis Patients - Modération',
            appointments: 'Rendez-vous & Paiements',
            consultations: 'Consultation & Suivi médical'
        };
        document.getElementById('pageTitle').innerHTML = titles[module] || module;
        loadModuleContent(module);
    }
    
    function loadModuleContent(module) {
        const body = document.getElementById('moduleContent');
        if(module === 'dashboard') body.innerHTML = renderDashboard();
        else if(module === 'users') body.innerHTML = renderUsers();
        else if(module === 'forum') body.innerHTML = renderForum();
        else if(module === 'moderation') {
            body.innerHTML = renderModeration();
            loadModerationPublicationsFromDb().then(() => {
                if (currentModule === 'moderation') body.innerHTML = renderModeration();
            });
        }
        else if(module === 'comments') body.innerHTML = renderComments();
        else if(module === 'reviews') body.innerHTML = renderReviews();
        else if(module === 'appointments') body.innerHTML = renderAppointments();
        else if(module === 'consultations') body.innerHTML = renderConsultations();
    }
    
    async function refreshModule() {
        if (currentModule === 'forum' || currentModule === 'comments' || currentModule === 'dashboard') {
            await loadPublicationsFromDb();
            await loadAllCommentsFromDb();
        }
        if (currentModule === 'moderation') {
            await loadModerationPublicationsFromDb();
        }
        loadModuleContent(currentModule);
        showNotification('Module actualisé');
    }
    
    function showNotification(msg, isError=false) {
        const t = document.getElementById('notificationToast');
        if(t){
            t.textContent = msg;
            t.style.borderLeftColor = isError ? '#dc3545' : '#2ecc71';
            t.classList.add('show');
            setTimeout(()=>t.classList.remove('show'),3000);
        }
    }
    
    function escapeHtml(str) {
        if(!str) return '';
        return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));
    }

    function userId(u) {
        return u.id ?? u.id_user ?? 0;
    }

    function userFullName(u) {
        const n = (u.name || `${u.nom || ''} ${u.prenom || ''}`.trim()).trim();
        return n || '—';
    }

    function formatDateNaissance(iso) {
        if (!iso) return '—';
        const s = String(iso).slice(0, 10);
        const p = s.split('-');
        if (p.length !== 3) return String(iso);
        return `${p[2]}/${p[1]}/${p[0]}`;
    }

    function displayMetric(v, unit) {
        if (v === null || v === undefined || v === '') return '—';
        const n = Number(v);
        if (Number.isNaN(n)) return '—';
        return unit === 'kg' ? `${n} kg` : unit === 'm' ? `${n} m` : String(n);
    }
    
    function showStats(moduleName) {
        showNotification(`📊 Statistiques - ${moduleName} (Fonctionnalité à venir)`);
    }
    
    function exportToPDF(elementId, filename) {
        const element = document.getElementById(elementId);
        if(element && typeof html2pdf !== 'undefined') {
            html2pdf().from(element).set({ filename: filename }).save();
            showNotification('Export PDF en cours...');
        } else {
            showNotification('Export PDF');
        }
    }
    
    // ==================== DASHBOARD ====================
    function renderDashboard() {
        const totalUsers = usersData.length;
        const totalDoctors = usersData.filter(u => u.role === 'medecin').length;
        const totalPatients = usersData.filter(u => u.role === 'patient').length;
        const totalPosts = forumPosts.length;
        const totalComments = allCommentsFromDb.length;
        const pendingComments = allCommentsFromDb.filter(c => c.statut === 'en_attente').length;
        const totalReviews = reviewsData.length;
        const pendingReviews = reviewsData.filter(r => r.status === 'pending').length;
        const approvedReviews = reviewsData.filter(r => r.status === 'approved');
        const avgRating = approvedReviews.length ? (approvedReviews.reduce((s,r)=>s+r.rating,0)/approvedReviews.length).toFixed(1) : 0;
        const totalAppointments = appointmentsData.length;
        const totalConsultations = consultationsData.length;
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-icon" style="background:#e8f4ff;color:#2b7be4;"><i class="fas fa-users"></i></div><div class="stat-number">${totalUsers}</div><div class="stat-label">Utilisateurs</div><small>${totalDoctors} médecins, ${totalPatients} patients</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#e8f8f0;color:#2ecc71;"><i class="fas fa-newspaper"></i></div><div class="stat-number">${totalPosts}</div><div class="stat-label">Publications</div><small>forum médical</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#fff3e0;color:#f39c12;"><i class="fas fa-comments"></i></div><div class="stat-number">${totalComments}</div><div class="stat-label">Commentaires</div><small>${pendingComments} en attente</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#fee;color:#e74c3c;"><i class="fas fa-star"></i></div><div class="stat-number">${avgRating}</div><div class="stat-label">Note moyenne</div><small>${totalReviews} avis (${pendingReviews} en attente)</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#e8f4ff;color:#2b7be4;"><i class="fas fa-calendar-check"></i></div><div class="stat-number">${totalAppointments}</div><div class="stat-label">Rendez-vous</div><small>consultations</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#2ecc71;color:white;"><i class="fas fa-stethoscope"></i></div><div class="stat-number">${totalConsultations}</div><div class="stat-label">Consultations</div><small>suivis médicaux</small></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribution des notes des médecins</h5>
                    <div class="btn-group-actions">
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Dashboard')"><i class="fas fa-chart-line me-1"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('dashboardStats', 'dashboard-stats.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                <div id="dashboardStats">
                ${usersData.filter(u => u.role === 'medecin').map(doctor => {
                    const doctorReviews = reviewsData.filter(r => r.doctor_id === doctor.id && r.status === 'approved');
                    const avg = doctorReviews.length ? (doctorReviews.reduce((s,r)=>s+r.rating,0)/doctorReviews.length).toFixed(1) : 0;
                    return `<div class="chart-bar"><div class="chart-bar-fill" style="width:${(avg/5)*100}%">${doctor.name || `${doctor.nom || ''} ${doctor.prenom || ''}`.trim()}: ${avg}/5 ★</div></div>`;
                }).join('')}
                ${usersData.filter(u => u.role === 'medecin').length === 0 ? '<div class="empty-state"><i class="fas fa-chart-line"></i><p>Aucun médecin pour afficher les statistiques</p></div>' : ''}
                </div>
            </div>
        `;
    }
    
    // ==================== CONSULTATION & SUIVI ====================
    function renderConsultations() {
        if(consultationsData.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-stethoscope"></i><p>Aucune consultation</p><button class="btn btn-medical" onclick="showAddConsultationModal()"><i class="fas fa-plus"></i> Ajouter une consultation</button></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${consultationsData.length}</div><div class="stat-label">Total consultations</div></div>
                <div class="stat-card"><div class="stat-number">${consultationsData.filter(c => c.suivi && c.suivi !== '').length}</div><div class="stat-label">Avec suivi</div></div>
                <div class="stat-card"><div class="stat-number">${consultationsData.filter(c => !c.suivi || c.suivi === '').length}</div><div class="stat-label">Sans suivi</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Liste des consultations et suivis</h5>
                    <div class="btn-group-actions">
                        <button class="btn-medical btn-sm" onclick="showAddConsultationModal()"><i class="fas fa-plus"></i> Nouvelle consultation</button>
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Consultations')"><i class="fas fa-chart-line"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('consultationsTable', 'consultations-suivi.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                <div id="consultationsTable">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Patient</th><th>Médecin</th><th>Date</th><th>Diagnostic</th><th>Traitement</th><th>Suivi</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    ${consultationsData.map(c => `
                        <tr>
                            <td>${c.id}</td>
                            <td>${escapeHtml(c.id_patient)}</td>
                            <td>${escapeHtml(c.id_medecin)}</td>
                            <td>${c.date}</td>
                            <td>${escapeHtml(c.diagnostic.substring(0,30))}${c.diagnostic.length > 30 ? '...' : ''}</td>
                            <td>${escapeHtml(c.traitement ? c.traitement.substring(0,30) : '-')}${c.traitement && c.traitement.length > 30 ? '...' : ''}</td>
                            <td>${escapeHtml(c.suivi ? (c.suivi.substring(0,30) + (c.suivi.length > 30 ? '...' : '')) : '-')}</td>
                            <td>
                                <button class="icon-btn edit" onclick="editConsultation(${c.id})"><i class="fas fa-edit"></i></button>
                                <button class="icon-btn delete" onclick="deleteConsultation(${c.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `).join('')}
                    </tbody>
                </table>
                </div>
            </div>
            <div class="data-card">
                <h5><i class="fas fa-chart-line me-2"></i>Derniers suivis ajoutés</h5>
                ${consultationsData.slice(-3).reverse().map(c => `
                    <div class="followup-card">
                        <h6><i class="fas fa-calendar-alt me-2"></i> ${c.date} - Patient: ${escapeHtml(c.id_patient)}</h6>
                        <p><strong>Diagnostic:</strong> ${escapeHtml(c.diagnostic)}</p>
                        <p><strong>Suivi:</strong> ${escapeHtml(c.suivi || 'Aucun suivi pour le moment')}</p>
                        <small class="text-muted">Médecin: ${escapeHtml(c.id_medecin)}</small>
                    </div>
                `).join('')}
                ${consultationsData.length === 0 ? '<div class="empty-state"><i class="fas fa-chart-line"></i><p>Aucun suivi disponible</p></div>' : ''}
            </div>
        `;
    }
    
    function showAddConsultationModal() {
        document.getElementById('addConsultationForm').reset();
        new bootstrap.Modal(document.getElementById('addConsultationModal')).show();
    }
    
    document.getElementById('addConsultationForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const newConsultation = {
            id: Date.now(),
            id_patient: document.getElementById('consultation_id_patient').value,
            id_medecin: document.getElementById('consultation_id_medecin').value,
            id_rdv: document.getElementById('consultation_id_rdv').value || null,
            date: document.getElementById('consultation_date').value,
            symptomes: document.getElementById('consultation_symptomes').value,
            diagnostic: document.getElementById('consultation_diagnostic').value,
            traitement: document.getElementById('consultation_traitement').value,
            ordonnance: document.getElementById('consultation_ordonnance').value,
            notes_medecin: document.getElementById('consultation_notes').value,
            suivi: document.getElementById('consultation_suivi').value
        };
        consultationsData.push(newConsultation);
        saveConsultations();
        bootstrap.Modal.getInstance(document.getElementById('addConsultationModal')).hide();
        document.getElementById('addConsultationForm').reset();
        showNotification('Consultation ajoutée avec succès');
        refreshModule();
    });
    
    function editConsultation(id) {
        const consultation = consultationsData.find(c => c.id === id);
        if(!consultation) return;
        
        document.getElementById('editConsultationId').value = consultation.id;
        document.getElementById('edit_consultation_id_patient').value = consultation.id_patient;
        document.getElementById('edit_consultation_id_medecin').value = consultation.id_medecin;
        document.getElementById('edit_consultation_id_rdv').value = consultation.id_rdv || '';
        document.getElementById('edit_consultation_date').value = consultation.date;
        document.getElementById('edit_consultation_symptomes').value = consultation.symptomes;
        document.getElementById('edit_consultation_diagnostic').value = consultation.diagnostic;
        document.getElementById('edit_consultation_traitement').value = consultation.traitement || '';
        document.getElementById('edit_consultation_ordonnance').value = consultation.ordonnance || '';
        document.getElementById('edit_consultation_notes').value = consultation.notes_medecin || '';
        document.getElementById('edit_consultation_suivi').value = consultation.suivi || '';
        
        new bootstrap.Modal(document.getElementById('editConsultationModal')).show();
    }
    
    document.getElementById('editConsultationForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('editConsultationId').value);
        const index = consultationsData.findIndex(c => c.id === id);
        
        if(index !== -1) {
            consultationsData[index] = {
                ...consultationsData[index],
                id_patient: document.getElementById('edit_consultation_id_patient').value,
                id_medecin: document.getElementById('edit_consultation_id_medecin').value,
                id_rdv: document.getElementById('edit_consultation_id_rdv').value || null,
                date: document.getElementById('edit_consultation_date').value,
                symptomes: document.getElementById('edit_consultation_symptomes').value,
                diagnostic: document.getElementById('edit_consultation_diagnostic').value,
                traitement: document.getElementById('edit_consultation_traitement').value,
                ordonnance: document.getElementById('edit_consultation_ordonnance').value,
                notes_medecin: document.getElementById('edit_consultation_notes').value,
                suivi: document.getElementById('edit_consultation_suivi').value
            };
            saveConsultations();
            bootstrap.Modal.getInstance(document.getElementById('editConsultationModal')).hide();
            showNotification('Consultation modifiée avec succès');
            refreshModule();
        }
    });
    
    function deleteConsultation(id) {
        if(confirm('Supprimer cette consultation ?')) {
            consultationsData = consultationsData.filter(c => c.id !== id);
            saveConsultations();
            showNotification('Consultation supprimée');
            refreshModule();
        }
    }
    
    // ==================== UTILISATEURS ====================
    function renderUsers() {
        const doctors = usersData.filter(u => u.role === 'medecin');
        const patients = usersData.filter(u => u.role === 'patient');
        
        if(usersData.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-users"></i><p>Aucun utilisateur</p><button class="btn btn-medical" onclick="showAddUserModal()"><i class="fas fa-plus"></i> Ajouter un utilisateur</button></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${usersData.length}</div><div class="stat-label">Total utilisateurs</div></div>
                <div class="stat-card"><div class="stat-number">${doctors.length}</div><div class="stat-label">Médecins</div></div>
                <div class="stat-card"><div class="stat-number">${patients.length}</div><div class="stat-label">Patients</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Médecins (${doctors.length})</h5>
                    <div class="btn-group-actions">
                        <button class="btn-medical btn-sm" onclick="showAddUserModal()"><i class="fas fa-plus"></i> Ajouter</button>
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Utilisateurs')"><i class="fas fa-chart-line"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('usersTable', 'medecins-list.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                <div id="usersTable">
                <table class="data-table"><thead><tr><th>Nom</th><th>Email</th><th>Spécialité</th><th>Actions</th></tr></thead>
                <tbody>${doctors.map(d => {
                    const uid = userId(d);
                    const spec = (d.specialite && String(d.specialite).trim()) ? String(d.specialite).trim() : 'Généraliste';
                    return `<tr>
                    <td><strong>${escapeHtml(userFullName(d))}</strong></td>
                    <td><a href="mailto:${escapeHtml(d.email)}">${escapeHtml(d.email)}</a></td>
                    <td><span class="status-badge status-approved">${escapeHtml(spec)}</span></td>
                    <td>
                        <button type="button" class="icon-btn edit" onclick="editUser(${uid})" title="Modifier"><i class="fas fa-edit"></i></button>
                        <button type="button" class="icon-btn delete" onclick="deleteUser(${uid})" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
                }).join('')}</tbody>
                </table>
                </div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Patients (${patients.length})</h5>
                    <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('patientsTable', 'patients-list.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                </div>
                <div id="patientsTable" class="table-scroll">
                <table class="data-table"><thead><tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Âge</th>
                    <th>Date naissance</th>
                    <th>Poids</th>
                    <th>Taille</th>
                    <th>Cas social</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>${patients.map(p => {
                    const pid = userId(p);
                    const age = (p.age !== null && p.age !== undefined && p.age !== '') ? String(p.age) : '—';
                    return `<tr>
                    <td><strong>${escapeHtml(userFullName(p))}</strong></td>
                    <td><a href="mailto:${escapeHtml(p.email)}">${escapeHtml(p.email)}</a></td>
                    <td>${escapeHtml(age)}</td>
                    <td>${escapeHtml(formatDateNaissance(p.date_naissance))}</td>
                    <td>${escapeHtml(displayMetric(p.poids, 'kg'))}</td>
                    <td>${escapeHtml(displayMetric(p.taille, 'm'))}</td>
                    <td>${escapeHtml(p.cas_social && String(p.cas_social).trim() ? p.cas_social : '—')}</td>
                    <td>
                        <button type="button" class="icon-btn edit" onclick="editUser(${pid})" title="Modifier"><i class="fas fa-edit"></i></button>
                        <button type="button" class="icon-btn delete" onclick="deleteUser(${pid})" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
                }).join('')}</tbody>
                </table>
                </div>
            </div>
        `;
    }
    
    function toggleSpecialtyField() {
        const role = document.getElementById('newUserRole').value;
        document.getElementById('specialtyField').style.display = role === 'medecin' ? 'block' : 'none';
    }
    
    function showAddUserModal() { new bootstrap.Modal(document.getElementById('addUserModal')).show(); }
    
    function validateUserPayload(user) {
        const requiredFields = ['nom', 'prenom', 'age', 'sexe', 'poids', 'taille', 'email', 'mot_de_passe', 'date_naissance', 'adresse', 'role'];
        const missing = requiredFields.find((field) => !user[field] && user[field] !== 0);
        if (missing) return `Champ obligatoire manquant: ${missing}`;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(user.email)) return 'Email invalide';
        if (Number(user.age) < 0 || Number(user.age) > 130) return 'Age invalide';
        if (Number(user.poids) <= 0 || Number(user.taille) <= 0) return 'Poids/Taille invalides';
        if (!['admin', 'medecin', 'patient'].includes(user.role)) return 'Rôle invalide';
        if (user.role === 'medecin' && !user.specialite) return 'La spécialité est obligatoire pour un médecin';
        if (user.mot_de_passe.length < 6) return 'Mot de passe trop court (min 6)';
        return null;
    }

    document.getElementById('addUserForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newUser = {
            nom: document.getElementById('newUserNom').value.trim(),
            prenom: document.getElementById('newUserPrenom').value.trim(),
            age: Number(document.getElementById('newUserAge').value),
            sexe: document.getElementById('newUserSexe').value,
            poids: Number(document.getElementById('newUserPoids').value),
            taille: Number(document.getElementById('newUserTaille').value),
            email: document.getElementById('newUserEmail').value,
            mot_de_passe: document.getElementById('newUserMotDePasse').value,
            cas_social: document.getElementById('newUserCasSocial').value.trim(),
            date_naissance: document.getElementById('newUserDateNaissance').value,
            adresse: document.getElementById('newUserAdresse').value.trim(),
            role: document.getElementById('newUserRole').value,
            specialite: document.getElementById('newUserSpecialite').value.trim() || null,
            name: '',
            status: 'active'
        };
        newUser.name = `${newUser.nom} ${newUser.prenom}`.trim();
        const error = validateUserPayload(newUser);
        if (error) {
            showNotification(error, true);
            return;
        }
        try {
            const createdUser = await apiRequest('create', 'POST', newUser);
            usersData.unshift(createdUser);
            syncWithFrontoffice();
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            document.getElementById('addUserForm').reset();
            toggleSpecialtyField();
            showNotification(`Utilisateur ${createdUser.name} ajouté`);
            refreshModule();
        } catch (error) {
            showNotification(error.message, true);
        }
    });
    
    function editUser(id) {
        const user = usersData.find(u => (u.id || u.id_user) === id);
        if (!user) return;
        const uid = userId(user);
        document.getElementById('editUserId').value = String(uid);
        document.getElementById('editUserRole').value = user.role || '';

        const isPatient = user.role === 'patient';
        document.getElementById('editPatientSection').style.display = isPatient ? 'block' : 'none';
        document.getElementById('editStaffSection').style.display = isPatient ? 'none' : 'block';
        document.getElementById('editMedecinSpecialtyWrap').style.display = user.role === 'medecin' ? 'block' : 'none';
        const titleEl = document.getElementById('editUserModalTitle');
        if (titleEl) {
            titleEl.textContent = isPatient ? 'Modifier le patient' : (user.role === 'medecin' ? 'Modifier le médecin' : 'Modifier l\'utilisateur');
        }

        if (isPatient) {
            document.getElementById('editPatientNom').value = user.nom || '';
            document.getElementById('editPatientPrenom').value = user.prenom || '';
            document.getElementById('editPatientAge').value = user.age != null && user.age !== '' ? String(user.age) : '';
            document.getElementById('editPatientSexe').value = user.sexe || '';
            const dn = user.date_naissance ? String(user.date_naissance).slice(0, 10) : '';
            document.getElementById('editPatientDateNaissance').value = dn;
            document.getElementById('editPatientPoids').value = user.poids != null && user.poids !== '' ? String(user.poids) : '';
            document.getElementById('editPatientTaille').value = user.taille != null && user.taille !== '' ? String(user.taille) : '';
            document.getElementById('editPatientEmail').value = user.email || '';
            document.getElementById('editPatientCasSocial').value = user.cas_social || '';
            document.getElementById('editPatientAdresse').value = user.adresse || '';
            document.getElementById('editPatientMotDePasse').value = '';
        } else {
            document.getElementById('editUserName').value = user.name || `${user.nom || ''} ${user.prenom || ''}`.trim();
            document.getElementById('editUserEmail').value = user.email || '';
            document.getElementById('editUserPhone').value = user.cas_social || '';
            document.getElementById('editUserSpecialty').value = user.specialite || '';
        }
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }
    
    document.getElementById('editUserForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('editUserId').value, 10);
        const user = usersData.find(u => (u.id || u.id_user) === id);
        if (!user) return;
        const role = user.role;
        try {
            let payload;
            if (role === 'patient') {
                payload = {
                    id_user: id,
                    nom: document.getElementById('editPatientNom').value.trim(),
                    prenom: document.getElementById('editPatientPrenom').value.trim(),
                    email: document.getElementById('editPatientEmail').value.trim(),
                    age: Number(document.getElementById('editPatientAge').value),
                    sexe: document.getElementById('editPatientSexe').value,
                    poids: Number(document.getElementById('editPatientPoids').value),
                    taille: Number(document.getElementById('editPatientTaille').value),
                    date_naissance: document.getElementById('editPatientDateNaissance').value,
                    adresse: document.getElementById('editPatientAdresse').value.trim(),
                    cas_social: document.getElementById('editPatientCasSocial').value.trim()
                };
                const np = document.getElementById('editPatientMotDePasse').value;
                if (np) payload.mot_de_passe = np;
            } else {
                payload = {
                    id_user: id,
                    name: document.getElementById('editUserName').value,
                    email: document.getElementById('editUserEmail').value,
                    cas_social: document.getElementById('editUserPhone').value,
                    specialite: role === 'medecin' ? document.getElementById('editUserSpecialty').value.trim() : ''
                };
            }
            const updated = await apiRequest('update', 'POST', payload);
            const idx = usersData.findIndex(u => (u.id || u.id_user) === id);
            if (idx !== -1 && updated) {
                usersData[idx] = { ...usersData[idx], ...updated };
            }
            syncWithFrontoffice();
            showNotification(`Utilisateur ${updated && updated.name ? updated.name : userFullName(user)} modifié`);
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            refreshModule();
        } catch (error) {
            showNotification(error.message, true);
        }
    });
    
    async function deleteUser(id) {
        if(confirm('Supprimer cet utilisateur ?')) {
            try {
                await apiRequest('delete', 'POST', { id_user: id });
                usersData = usersData.filter(u => (u.id || u.id_user) !== id);
                syncWithFrontoffice();
                showNotification('Utilisateur supprimé');
                refreshModule();
            } catch (error) {
                showNotification(error.message, true);
            }
        }
    }
    
    // ==================== FORUM PUBLICATIONS ====================
    function getFilteredPosts() {
        const search = (document.getElementById('forumSearch')?.value || '').toLowerCase();
        const statusFilter = document.getElementById('forumStatusFilter')?.value || '';
        return forumPosts.filter(p => {
            const matchSearch = !search ||
                (p.doctor_name||'').toLowerCase().includes(search) ||
                (p.content||'').toLowerCase().includes(search);
            const matchStatus = !statusFilter || p.status === statusFilter;
            return matchSearch && matchStatus;
        });
    }

    function filterForumTable() {
        const filtered = getFilteredPosts();
        const tbody = document.getElementById('forumTableBody');
        const count = document.getElementById('forumResultCount');
        if (count) count.textContent = `Résultats: ${filtered.length} publication(s)`;
        if (tbody) tbody.innerHTML = filtered.map(p => `<tr>
            <td>${escapeHtml(p.doctor_name)}</td>
            <td title="${escapeHtml(p.content||'')}">${escapeHtml((p.content||'').substring(0,50))}${(p.content||'').length>50?'...':''}</td>
            <td>${p.date ? p.date.substring(0,10) : ''}</td>
            <td><span class="status-badge ${p.status==='approved'?'status-approved':'status-pending'}">${p.status==='approved'?'Approuvée':'Bloquée'}</span></td>
            <td>
                <button class="icon-btn ${p.status==='approved'?'flag':'approve'}" onclick="togglePostStatus(${p.id})"><i class="fas ${p.status==='approved'?'fa-ban':'fa-check-circle'}"></i></button>
                <button class="icon-btn delete" onclick="deletePost(${p.id})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`).join('') || '<tr><td colspan="5" class="text-center text-muted py-3">Aucun résultat</td></tr>';
    }

    function buildForumStats() {
        const total = forumPosts.length;
        const approved = forumPosts.filter(p => p.status === 'approved').length;
        const blocked = forumPosts.filter(p => p.status === 'blocked').length;
        const gemini = forumPosts.filter(p => (p.moderation_source || '') === 'gemini').length;
        const fallback = forumPosts.filter(p => (p.moderation_source || 'fallback') === 'fallback').length;
        const avgToxicity = total ? forumPosts.reduce((sum, p) => sum + Number(p.toxicity_score || 0), 0) / total : 0;
        const avgSensitive = total ? forumPosts.reduce((sum, p) => sum + Number(p.sensitive_score || 0), 0) / total : 0;
        const avgMedical = total ? forumPosts.reduce((sum, p) => sum + Number(p.medical_risk_score || 0), 0) / total : 0;
        const byDoctor = {};
        forumPosts.forEach(p => {
            const name = p.doctor_name || 'Medecin';
            if (!byDoctor[name]) byDoctor[name] = {total: 0, approved: 0, blocked: 0};
            byDoctor[name].total++;
            if (p.status === 'approved') byDoctor[name].approved++;
            if (p.status === 'blocked') byDoctor[name].blocked++;
        });
        const topDoctors = Object.entries(byDoctor).sort((a, b) => b[1].total - a[1].total).slice(0, 5);
        return {total, approved, blocked, gemini, fallback, avgToxicity, avgSensitive, avgMedical, topDoctors};
    }

    function renderForumStatsPanel() {
        const s = buildForumStats();
        const approvalRate = s.total ? Math.round((s.approved / s.total) * 100) : 0;
        const blockedRate = s.total ? Math.round((s.blocked / s.total) * 100) : 0;
        return `
            <div id="forumStatsPanel" class="data-card" style="display:none; box-shadow:none; border:1px solid #eef2f7; margin-bottom:16px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistiques des publications</h5>
                    <button class="btn-outline-medical btn-sm" onclick="toggleForumStats()"><i class="fas fa-times me-1"></i>Fermer</button>
                </div>
                <div class="stats-grid">
                    <div class="stat-card"><div class="stat-number">${approvalRate}%</div><div class="stat-label">Taux approbation</div><small>${s.approved}/${s.total} publication(s)</small></div>
                    <div class="stat-card"><div class="stat-number">${blockedRate}%</div><div class="stat-label">Taux blocage</div><small>${s.blocked}/${s.total} publication(s)</small></div>
                    <div class="stat-card"><div class="stat-number">${s.gemini}</div><div class="stat-label">Analyses Gemini</div><small>${s.fallback} fallback</small></div>
                </div>
                <h6 class="mb-2">Scores IA moyens</h6>
                <div class="chart-bar"><div class="chart-bar-fill" style="width:${Math.round(s.avgToxicity * 100)}%">Toxicite ${percentScore(s.avgToxicity)}</div></div>
                <div class="chart-bar"><div class="chart-bar-fill" style="width:${Math.round(s.avgSensitive * 100)}%">Sensible ${percentScore(s.avgSensitive)}</div></div>
                <div class="chart-bar"><div class="chart-bar-fill" style="width:${Math.round(s.avgMedical * 100)}%">Risque medical ${percentScore(s.avgMedical)}</div></div>
                <h6 class="mt-3 mb-2">Publications par medecin</h6>
                <table class="data-table">
                    <thead><tr><th>Medecin</th><th>Total</th><th>Approuvees</th><th>Bloquees</th></tr></thead>
                    <tbody>${s.topDoctors.map(([name, data]) => `<tr><td>${escapeHtml(name)}</td><td>${data.total}</td><td>${data.approved}</td><td>${data.blocked}</td></tr>`).join('') || '<tr><td colspan="4" class="text-center text-muted">Aucune donnee</td></tr>'}</tbody>
                </table>
            </div>
        `;
    }

    function toggleForumStats() {
        const panel = document.getElementById('forumStatsPanel');
        if (panel) panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }

    function renderForum() {
        const approvedPosts = forumPosts.filter(p => p.status === 'approved').length;
        const blockedPosts = forumPosts.filter(p => p.status === 'blocked').length;

        if(forumPosts.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-newspaper"></i><p>Aucune publication</p></div></div>`;
        }

        const initialRows = forumPosts.map(p => `<tr>
            <td>${escapeHtml(p.doctor_name)}</td>
            <td>${escapeHtml((p.content||'').substring(0,50))}${(p.content||'').length>50?'...':''}</td>
            <td>${p.date ? p.date.substring(0,10) : ''}</td>
            <td><span class="status-badge ${p.status==='approved'?'status-approved':'status-pending'}">${p.status==='approved'?'Approuvée':'Bloquée'}</span></td>
            <td>
                <button class="icon-btn ${p.status==='approved'?'flag':'approve'}" onclick="togglePostStatus(${p.id})"><i class="fas ${p.status==='approved'?'fa-ban':'fa-check-circle'}"></i></button>
                <button class="icon-btn delete" onclick="deletePost(${p.id})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`).join('');

        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${forumPosts.length}</div><div class="stat-label">Total publications</div></div>
                <div class="stat-card"><div class="stat-number">${approvedPosts}</div><div class="stat-label">Approuvées</div></div>
                <div class="stat-card"><div class="stat-number">${blockedPosts}</div><div class="stat-label">Bloquées</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Publications des médecins</h5>
                    <div class="btn-group-actions">
                        <button class="btn-outline-medical btn-sm" onclick="toggleForumStats()"><i class="fas fa-chart-line"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('forumTable', 'forum-publications.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                ${renderForumStatsPanel()}
                <div class="d-flex gap-2 mb-2 flex-wrap align-items-center">
                    <input type="text" id="forumSearch"
                        placeholder="Rechercher par médecin ou contenu..."
                        class="form-control form-control-custom" style="flex:1;min-width:220px;"
                        oninput="filterForumTable()">
                    <select id="forumStatusFilter" class="form-select form-control-custom" style="width:170px;" onchange="filterForumTable()">
                        <option value="">Tous les statuts</option>
                        <option value="approved">Approuvées</option>
                        <option value="blocked">Bloquées</option>
                    </select>
                </div>
                <div id="forumResultCount" class="text-muted small mb-2">Résultats: ${forumPosts.length} publication(s)</div>
                <div id="forumTable">
                <table class="data-table"><thead><tr><th>Médecin</th><th>Contenu</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody id="forumTableBody">${initialRows}</tbody>
                </table>
                </div>
            </div>
        `;
    }

    function showAddPostModal() {
        const select = document.getElementById('postDoctorId');
        const doctors = usersData.filter(u => u.role === 'medecin');
        if(select) select.innerHTML = '<option value="">Sélectionner un médecin</option>' + doctors.map(d => `<option value="${d.id}">${escapeHtml(d.name || `${d.nom || ''} ${d.prenom || ''}`.trim())}</option>`).join('');
        if(doctors.length === 0) { showNotification('Veuillez d\'abord ajouter des médecins', true); return; }
        new bootstrap.Modal(document.getElementById('addPostModal')).show();
    }
    
    document.getElementById('addPostForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const doctorId = parseInt(document.getElementById('postDoctorId').value);
        const doctor = usersData.find(u => u.id === doctorId);
        if(!doctor) { showNotification('Veuillez sélectionner un médecin', true); return; }
        
        const newPost = {
            id: Date.now(),
            doctor_id: doctorId,
            doctor_name: doctor.name || `${doctor.nom || ''} ${doctor.prenom || ''}`.trim(),
            doctor_avatar: (doctor.name || `${doctor.nom || ''} ${doctor.prenom || ''}`.trim()).substring(0,2).toUpperCase(),
            content: document.getElementById('postContent').value,
            image: document.getElementById('postImage').value || null,
            video: document.getElementById('postVideo').value || null,
            date: new Date().toLocaleDateString('fr-FR'),
            status: 'pending',
            comments: []
        };
        forumPosts.push(newPost);
        savePosts();
        syncWithFrontoffice();
        bootstrap.Modal.getInstance(document.getElementById('addPostModal')).hide();
        document.getElementById('addPostForm').reset();
        showNotification('Publication ajoutée, en attente de validation');
        refreshModule();
    });
    
    async function togglePostStatus(id) {
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=toggle-publication-status`, {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id})
            });
            const data = await res.json();
            if (data.success) {
                const post = forumPosts.find(p => p.id === id);
                if (post) post.status = data.statut;
                showNotification(`Publication ${data.statut === 'approved' ? 'approuvée' : 'bloquée'}`);
                filterForumTable();
            } else showNotification(data.error || 'Erreur', true);
        } catch(e) { showNotification(e.message, true); }
    }
    async function deletePost(id) {
        if(!confirm('Supprimer cette publication ?')) return;
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=admin-delete-publication`, {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id})
            });
            const data = await res.json();
            if (data.success) {
                forumPosts = forumPosts.filter(p => p.id !== id);
                allCommentsFromDb = allCommentsFromDb.filter(c => c.id_publication !== id);
                showNotification('Publication supprimée');
                refreshModule();
            } else showNotification(data.error || 'Erreur', true);
        } catch(e) { showNotification(e.message, true); }
    }

    function moderationBadgeClass(status) {
        if (status === 'blocked') return 'status-danger';
        if (status === 'review') return 'status-warning';
        return 'status-approved';
    }

    function moderationLabel(status) {
        if (status === 'blocked') return 'Bloqué';
        if (status === 'review') return 'À vérifier';
        return 'Validé';
    }

    function percentScore(value) {
        return `${Math.round(Number(value || 0) * 100)}%`;
    }

    function renderModeration() {
        const reviewCount = moderationPosts.filter(p => p.moderation_status === 'review').length;
        const blockedCount = moderationPosts.filter(p => p.moderation_status === 'blocked').length;
        const rows = moderationPosts.map(p => `
            <div class="data-card mb-3">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div style="min-width:260px;flex:1;">
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <h6 class="mb-0">${escapeHtml(p.doctor_name || 'Médecin')}</h6>
                            <span class="status-badge ${moderationBadgeClass(p.moderation_status)}">${moderationLabel(p.moderation_status)}</span>
                            <span class="text-muted small">Source: ${escapeHtml(p.moderation_source || 'fallback')}</span>
                        </div>
                        <p class="text-muted small mb-2">${p.date ? escapeHtml(p.date.substring(0,16)) : ''}</p>
                        <p class="mb-2">${escapeHtml(p.content || '')}</p>
                        <p class="text-muted small mb-0">${escapeHtml(p.moderation_reason || 'Aucune raison détaillée.')}</p>
                    </div>
                    <div style="min-width:260px;">
                        <div class="small text-muted mb-2">
                            Toxicité: <strong>${percentScore(p.toxicity_score)}</strong> ·
                            Sensible: <strong>${percentScore(p.sensitive_score)}</strong> ·
                            Risque médical: <strong>${percentScore(p.medical_risk_score)}</strong>
                        </div>
                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                            <button class="btn btn-success btn-sm" onclick="setPublicationModerationStatus(${p.id}, 'safe')">
                                <i class="fas fa-check-circle me-1"></i>Approuver
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="setPublicationModerationStatus(${p.id}, 'blocked')">
                                <i class="fas fa-ban me-1"></i>Bloquer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${moderationPosts.length}</div><div class="stat-label">Publications signalées</div></div>
                <div class="stat-card"><div class="stat-number">${reviewCount}</div><div class="stat-label">À vérifier</div></div>
                <div class="stat-card"><div class="stat-number">${blockedCount}</div><div class="stat-label">Bloquées par IA</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1"><i class="fas fa-shield-alt me-2"></i>Modération IA - Forum</h5>
                        <p class="text-muted small mb-0">Vérifiez les publications détectées automatiquement avant leur affichage public.</p>
                    </div>
                    <button class="btn-outline-medical btn-sm" onclick="refreshModule()"><i class="fas fa-sync-alt me-1"></i>Actualiser</button>
                </div>
                ${rows || '<div class="empty-state"><i class="fas fa-shield-alt"></i><p>Aucune publication signalée.</p><small>Les nouveaux contenus suspects apparaîtront ici.</small></div>'}
            </div>
        `;
    }

    async function setPublicationModerationStatus(id, status) {
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=set-publication-moderation-status`, {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id, status})
            });
            const data = await res.json();
            if (!data.success) {
                showNotification(data.error || 'Erreur de modération', true);
                return;
            }
            moderationPosts = moderationPosts.filter(p => p.id !== id);
            const post = forumPosts.find(p => p.id === id);
            if (post) {
                post.status = data.statut;
                post.moderation_status = data.moderation_status;
            }
            showNotification(status === 'safe' ? 'Publication approuvée' : 'Publication bloquée');
            document.getElementById('moduleContent').innerHTML = renderModeration();
        } catch(e) {
            showNotification(e.message, true);
        }
    }
    
    // ==================== COMMENTAIRES ====================
    let commentSortField = 'date';
    let commentSortDir = -1;

    function commentStatutLabel(statut) {
        if (statut === 'publie') return 'Publié';
        if (statut === 'en_attente') return 'En attente';
        return 'Supprimé';
    }
    function commentStatutClass(statut) {
        if (statut === 'publie') return 'status-approved';
        if (statut === 'en_attente') return 'status-pending';
        return 'status-reported';
    }

    function renderCommentRow(c) {
        const isBlocked = c.statut === 'supprime';
        const isPending = c.statut === 'en_attente';
        return `<tr>
            <td>${escapeHtml(c.user_name||'')}</td>
            <td title="${escapeHtml(c.contenu||'')}">${escapeHtml((c.contenu||'').substring(0,60))}${(c.contenu||'').length>60?'...':''}</td>
            <td>${escapeHtml((c.post_content||'').substring(0,40))}${(c.post_content||'').length>40?'...':''}</td>
            <td>${escapeHtml(c.doctor_name||'')}</td>
            <td>
                <div class="small">
                    <div>Tox: <strong>${percentScore(c.toxicity_score)}</strong></div>
                    <div>Sens: <strong>${percentScore(c.sensitive_score)}</strong></div>
                    <div>Med: <strong>${percentScore(c.medical_risk_score)}</strong></div>
                    <div class="text-muted" title="${escapeHtml(c.moderation_reason || '')}">${escapeHtml(c.moderation_source || 'fallback')}</div>
                </div>
            </td>
            <td>${(c.date_publication||'').substring(0,10)}</td>
            <td><span class="status-badge ${commentStatutClass(c.statut)}">${commentStatutLabel(c.statut)}</span></td>
            <td>
                ${(isPending||isBlocked)?`<button class="icon-btn approve" onclick="approveComment(${c.id_commentaire})" title="Approuver"><i class="fas fa-check-circle"></i></button>`:''}
                ${(!isBlocked&&!isPending)?`<button class="icon-btn flag" onclick="blockComment(${c.id_commentaire})" title="Bloquer"><i class="fas fa-ban"></i></button>`:''}
                <button class="icon-btn delete" onclick="deleteCommentDb(${c.id_commentaire})" title="Supprimer"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
    }

    function getFilteredComments() {
        const search = (document.getElementById('commentSearch')?.value || '').toLowerCase();
        const statusFilter = document.getElementById('commentStatusFilter')?.value || '';
        let filtered = allCommentsFromDb.filter(c => {
            const matchSearch = !search ||
                (c.contenu||'').toLowerCase().includes(search) ||
                (c.user_name||'').toLowerCase().includes(search) ||
                (c.doctor_name||'').toLowerCase().includes(search) ||
                (c.post_content||'').toLowerCase().includes(search);
            const matchStatus = !statusFilter || c.statut === statusFilter;
            return matchSearch && matchStatus;
        });
        filtered = [...filtered].sort((a, b) => {
            let va, vb;
            if (commentSortField === 'alpha') { va = (a.user_name||'').toLowerCase(); vb = (b.user_name||'').toLowerCase(); }
            else if (commentSortField === 'statut') { va = a.statut||''; vb = b.statut||''; }
            else { va = a.date_publication||''; vb = b.date_publication||''; }
            return commentSortDir * (va < vb ? -1 : va > vb ? 1 : 0);
        });
        return filtered;
    }

    function filterCommentsTable() {
        const filtered = getFilteredComments();
        const tbody = document.getElementById('commentsTableBody');
        const count = document.getElementById('commentsResultCount');
        if (count) count.textContent = `Résultats: ${filtered.length} commentaire(s)`;
        if (tbody) tbody.innerHTML = filtered.map(renderCommentRow).join('') || '<tr><td colspan="8" class="text-center text-muted py-3">Aucun résultat</td></tr>';
    }

    function sortCommentsBy(field) {
        if (commentSortField === field) commentSortDir *= -1; else { commentSortField = field; commentSortDir = -1; }
        filterCommentsTable();
    }

    function renderComments() {
        const allComments = allCommentsFromDb;
        const pendingCount  = allComments.filter(c => c.statut === 'en_attente').length;
        const publishedCount = allComments.filter(c => c.statut === 'publie').length;
        const blockedCount  = allComments.filter(c => c.statut === 'supprime').length;

        if(allComments.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-comments"></i><p>Aucun commentaire</p></div></div>`;
        }

        const thead = `<thead><tr><th>Utilisateur</th><th>Commentaire</th><th>Publication</th><th>Médecin</th><th>IA</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>`;
        const initialRows = allComments.map(renderCommentRow).join('');

        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${allComments.length}</div><div class="stat-label">Total</div></div>
                <div class="stat-card"><div class="stat-number">${pendingCount}</div><div class="stat-label">En attente</div></div>
                <div class="stat-card"><div class="stat-number">${publishedCount}</div><div class="stat-label">Publiés</div></div>
                <div class="stat-card"><div class="stat-number">${blockedCount}</div><div class="stat-label">Bloqués</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="fas fa-comments me-2"></i>Gestion des commentaires</h5>
                    <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('allCommentsTable', 'commentaires.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                </div>
                <div class="d-flex gap-2 mb-2 flex-wrap align-items-center">
                    <input type="text" id="commentSearch"
                        placeholder="Rechercher un commentaire, utilisateur ou médecin..."
                        class="form-control form-control-custom" style="flex:1;min-width:220px;"
                        oninput="filterCommentsTable()">
                    <select id="commentStatusFilter" class="form-select form-control-custom" style="width:170px;" onchange="filterCommentsTable()">
                        <option value="">Tous les statuts</option>
                        <option value="publie">Publiés</option>
                        <option value="en_attente">En attente</option>
                        <option value="supprime">Bloqués</option>
                    </select>
                    <button class="btn-outline-medical btn-sm" onclick="sortCommentsBy('date')" title="Trier par date"><i class="fas fa-calendar-alt me-1"></i>Date</button>
                    <button class="btn-outline-medical btn-sm" onclick="sortCommentsBy('alpha')" title="Trier par utilisateur"><i class="fas fa-sort-alpha-down me-1"></i>Alphabet</button>
                    <button class="btn-outline-medical btn-sm" onclick="sortCommentsBy('statut')" title="Trier par statut"><i class="fas fa-filter me-1"></i>Statut</button>
                </div>
                <div id="commentsResultCount" class="text-muted small mb-2">Résultats: ${allComments.length} commentaire(s)</div>
                <div id="allCommentsTable">
                <table class="data-table">${thead}<tbody id="commentsTableBody">${initialRows}</tbody></table>
                </div>
            </div>
        `;
    }

    async function approveComment(commentId) {
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=admin-update-comment-status`, {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id: commentId, statut: 'publie'})
            });
            const data = await res.json();
            if (data.success) {
                const c = allCommentsFromDb.find(x => x.id_commentaire == commentId);
                if (c) c.statut = 'publie';
                showNotification('Commentaire approuvé');
                filterCommentsTable();
            } else showNotification(data.error || 'Erreur', true);
        } catch(e) { showNotification(e.message, true); }
    }

    async function blockComment(commentId) {
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=admin-update-comment-status`, {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id: commentId, statut: 'supprime'})
            });
            const data = await res.json();
            if (data.success) {
                const c = allCommentsFromDb.find(x => x.id_commentaire == commentId);
                if (c) c.statut = 'supprime';
                showNotification('Commentaire bloqué');
                filterCommentsTable();
            } else showNotification(data.error || 'Erreur', true);
        } catch(e) { showNotification(e.message, true); }
    }

    async function deleteCommentDb(commentId) {
        if(!confirm('Supprimer ce commentaire ?')) return;
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=admin-delete-comment`, {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id: commentId})
            });
            const data = await res.json();
            if (data.success) {
                allCommentsFromDb = allCommentsFromDb.filter(c => c.id_commentaire != commentId);
                showNotification('Commentaire supprimé');
                filterCommentsTable();
            } else showNotification(data.error || 'Erreur', true);
        } catch(e) { showNotification(e.message, true); }
    }
    
    // ==================== AVIS PATIENTS ====================
    function renderReviews() {
        const pendingReviews = reviewsData.filter(r => r.status === 'pending');
        const approvedReviews = reviewsData.filter(r => r.status === 'approved');
        const reportedReviews = reviewsData.filter(r => r.status === 'reported');
        const avgRating = approvedReviews.length ? (approvedReviews.reduce((s,r)=>s+r.rating,0)/approvedReviews.length).toFixed(1) : 0;
        const ratingCounts = {1:0,2:0,3:0,4:0,5:0};
        approvedReviews.forEach(r => ratingCounts[r.rating]++);
        
        if(reviewsData.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-star"></i><p>Aucun avis patient</p><button class="btn btn-medical" onclick="showNotifyReviewModal()"><i class="fas fa-bell"></i> Notifier un patient</button></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${avgRating}</div><div class="stat-label">Note moyenne</div></div>
                <div class="stat-card"><div class="stat-number">${reviewsData.length}</div><div class="stat-label">Total avis</div><small>${pendingReviews.length} en attente</small></div>
                <div class="stat-card"><div class="stat-number">${approvedReviews.length}</div><div class="stat-label">Approuvés</div><small>${reportedReviews.length} signalés</small></div>
            </div>
            <div class="data-card"><h6>Distribution des notes</h6>${[5,4,3,2,1].map(star => { const count = ratingCounts[star]; const pct = approvedReviews.length ? (count/approvedReviews.length*100) : 0; return `<div class="chart-bar"><div class="chart-bar-fill" style="width:${pct}%">${star}★ (${count})</div></div>`; }).join('')}</div>
            ${pendingReviews.length ? `<div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Avis en attente (${pendingReviews.length})</h5>
                    <button class="btn-outline-medical btn-sm" onclick="showStats('Avis Patients')"><i class="fas fa-chart-line"></i> Statistiques</button>
                </div>
                <div id="pendingReviewsTable">
                <table class="data-table"><thead><tr><th>Patient</th><th>Médecin</th><th>Note</th><th>Commentaire</th><th>Actions</th></tr></thead>
                <tbody>${pendingReviews.map(r => `<tr>
                    <td>${escapeHtml(r.patient_name)}</td>
                    <td>${escapeHtml(r.doctor_name)}</td>
                    <td>${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</td>
                    <td>${escapeHtml(r.comment)}</td>
                    <td>
                        <button class="icon-btn approve" onclick="approveReview(${r.id})"><i class="fas fa-check-circle"></i></button>
                        <button class="icon-btn flag" onclick="reportReview(${r.id})"><i class="fas fa-flag"></i></button>
                        <button class="icon-btn delete" onclick="deleteReview(${r.id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('')}</tbody>
                </table>
                </div>
            </div>` : ''}
            <div class="data-card">
                <button class="btn-medical me-2" onclick="showNotifyReviewModal()"><i class="fas fa-bell"></i> Notifier un patient</button>
                <button class="btn-outline-medical" onclick="exportToPDF('pendingReviewsTable', 'avis-patients.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF avis</button>
                <button class="btn-outline-medical ms-2" onclick="sendAutoReviewNotification()"><i class="fas fa-clock"></i> Auto-notification</button>
            </div>
        `;
    }
    
    function approveReview(id) { const r = reviewsData.find(r => r.id === id); if(r){ r.status = 'approved'; saveReviews(); syncWithFrontoffice(); showNotification(`Avis approuvé`); refreshModule(); } }
    function reportReview(id) { const r = reviewsData.find(r => r.id === id); if(r){ r.status = 'reported'; saveReviews(); syncWithFrontoffice(); showNotification(`Avis signalé`); refreshModule(); } }
    function deleteReview(id) { if(confirm('Supprimer cet avis ?')){ reviewsData = reviewsData.filter(r => r.id !== id); saveReviews(); syncWithFrontoffice(); showNotification('Avis supprimé'); refreshModule(); } }
    
    async function updateReviewStatusDb(id, status) {
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=admin-update-review-status`, {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id, statut: status})
            });
            const data = await res.json();
            if (!data.success) {
                showNotification(data.error || 'Erreur avis', true);
                return;
            }
            const r = reviewsData.find(item => item.id === id);
            if (r) r.status = status;
            syncWithFrontoffice();
            showNotification(status === 'approved' ? 'Avis approuve' : 'Avis signale');
            refreshModule();
        } catch(e) {
            showNotification(e.message, true);
        }
    }

    approveReview = function(id) { updateReviewStatusDb(id, 'approved'); };
    reportReview = function(id) { updateReviewStatusDb(id, 'reported'); };
    deleteReview = async function(id) {
        if(!confirm('Supprimer cet avis ?')) return;
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=admin-delete-review`, {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id})
            });
            const data = await res.json();
            if (!data.success) {
                showNotification(data.error || 'Erreur suppression avis', true);
                return;
            }
            reviewsData = reviewsData.filter(r => r.id !== id);
            syncWithFrontoffice();
            showNotification('Avis supprime');
            refreshModule();
        } catch(e) {
            showNotification(e.message, true);
        }
    };

    function showNotifyReviewModal() {
        const select = document.getElementById('notifyPatientId');
        const patients = usersData.filter(u => u.role === 'patient');
        if(select) select.innerHTML = '<option value="">Sélectionner</option>' + patients.map(p => `<option value="${p.id}">${escapeHtml(p.name || `${p.nom || ''} ${p.prenom || ''}`.trim())}</option>`).join('');
        if(patients.length === 0) { showNotification('Aucun patient disponible', true); return; }
        new bootstrap.Modal(document.getElementById('notifyReviewModal')).show();
    }
    
    document.getElementById('notifyReviewForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const patient = usersData.find(u => u.id == document.getElementById('notifyPatientId').value);
        if (!patient) {
            showNotification('Veuillez selectionner un patient', true);
            return;
        }
        const message = document.getElementById('notifyMessage')?.value.trim() || '';
        if (message.length < 5) {
            showNotification('Message trop court', true);
            return;
        }
        try {
            const res = await fetch(`${BACKOFFICE_URL}?action=notify-review-patient`, {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id_patient: patient.id || patient.id_user, message})
            });
            const data = await res.json();
            if (!data.success) {
                showNotification(data.mail?.error || data.error || 'Email non envoye', true);
                return;
            }
            showNotification(`Notification envoyee a ${patient.name || `${patient.nom || ''} ${patient.prenom || ''}`.trim()}`);
            bootstrap.Modal.getInstance(document.getElementById('notifyReviewModal')).hide();
            e.target.reset();
            return;
        } catch(error) {
            showNotification(error.message, true);
            return;
        }
        if(patient) { showNotification(`📧 Notification envoyée à ${patient.name || `${patient.nom || ''} ${patient.prenom || ''}`.trim()}`); bootstrap.Modal.getInstance(document.getElementById('notifyReviewModal')).hide(); }
        else showNotification('Veuillez sélectionner un patient', true);
    });
    
    function sendAutoReviewNotification() { showNotification(`🔔 Notification envoyée à ${usersData.filter(u=>u.role==='patient').length} patient(s)`); }
    
    // ==================== RENDEZ-VOUS ====================
    function renderAppointments() {
        const paidAppointments = appointmentsData.filter(a => a.payment_status === 'payé').length;
        const pendingPayments = appointmentsData.filter(a => a.payment_status === 'en attente').length;
        const totalAmount = appointmentsData.reduce((sum, a) => sum + (a.amount || 0), 0);
        
        if(appointmentsData.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-calendar-alt"></i><p>Aucun rendez-vous</p></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${appointmentsData.length}</div><div class="stat-label">Total RDV</div></div>
                <div class="stat-card"><div class="stat-number">${paidAppointments}</div><div class="stat-label">Payés</div></div>
                <div class="stat-card"><div class="stat-number">${pendingPayments}</div><div class="stat-label">En attente</div></div>
                <div class="stat-card"><div class="stat-number">${totalAmount}€</div><div class="stat-label">CA total</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Liste des rendez-vous</h5>
                    <div class="btn-group-actions">
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Rendez-vous')"><i class="fas fa-chart-line"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('appointmentsTable', 'rendez-vous.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                <div id="appointmentsTable">
                <table class="data-table"><thead><tr><th>Patient</th><th>Médecin</th><th>Date</th><th>Montant</th><th>Paiement</th><th>Actions</th></tr></thead>
                <tbody>${appointmentsData.map(a => `<tr>
                    <td>${escapeHtml(a.patient_name)}</td>
                    <td>${escapeHtml(a.doctor_name)}</td>
                    <td>${a.date}</td>
                    <td>${a.amount}€</td>
                    <td><span class="status-badge ${a.payment_status==='payé'?'status-approved':'status-pending'}">${a.payment_status}</span></td>
                    <td><button class="icon-btn" onclick="confirmPayment(${a.id})"><i class="fas fa-credit-card"></i></button><button class="icon-btn delete" onclick="deleteAppointment(${a.id})"><i class="fas fa-trash"></i></button></td>
                </tr>`).join('')}</tbody>
                </table>
                </div>
            </div>
        `;
    }
    
    function confirmPayment(id) { 
        const a = appointmentsData.find(a => a.id === id); 
        if(a){ a.payment_status = 'payé'; saveAppointments(); showNotification('Paiement confirmé'); refreshModule(); } 
    }
    function deleteAppointment(id) { 
        if(confirm('Annuler ce rendez-vous ?')){ appointmentsData = appointmentsData.filter(a => a.id !== id); saveAppointments(); showNotification('Rendez-vous annulé'); refreshModule(); } 
    }
    
    // ==================== INIT ====================
    async function initBackoffice() {
        await loadAllData();
        syncWithFrontoffice();
        switchModule('dashboard');
    }
    
    initBackoffice();
</script>
</body>
</html>

