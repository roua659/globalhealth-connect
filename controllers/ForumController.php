<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/MailModel.php';

class ForumController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->ensureSchema();
    }

    public static function getConnection() {
        $database = new Database();
        return $database->getConnection();
    }

    public static function publications($limit = 200) {
        $pdo = self::getConnection();
        self::ensureForumSchema($pdo);
        try {
            $stmt = $pdo->prepare("
                SELECT p.*,
                       CASE
                           WHEN p.url_image LIKE '/globalhealth-connect1/%'
                           THEN REPLACE(p.url_image, '/globalhealth-connect1/', '/rdv4/')
                           ELSE p.url_image
                       END AS url_image,
                       CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS medecin_nom,
                       u.email AS medecin_email,
                       COALESCE(m_by_medecin.id_user, m_by_user.id_user, p.id_medecin) AS owner_user_id,
                       COALESCE(m_by_medecin.id_medecin, m_by_user.id_medecin) AS owner_medecin_id,
                       COUNT(DISTINCT c.id_commentaire) AS commentaires_count,
                       COUNT(DISTINCT pl.id_like) AS likes_count
                FROM publication p
                LEFT JOIN medecin m_by_medecin ON m_by_medecin.id_medecin = p.id_medecin
                LEFT JOIN medecin m_by_user ON m_by_user.id_user = p.id_medecin AND m_by_medecin.id_medecin IS NULL
                LEFT JOIN utilisateur u ON u.id_user = COALESCE(m_by_medecin.id_user, m_by_user.id_user, p.id_medecin)
                LEFT JOIN commentaire c ON c.id_publication = p.id_publication
                LEFT JOIN publication_like pl ON pl.id_publication = p.id_publication
                GROUP BY p.id_publication
                ORDER BY p.date_publication DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function moderationPublications($limit = 100) {
        $pdo = self::getConnection();
        self::ensureForumSchema($pdo);
        try {
            $stmt = $pdo->prepare("
                SELECT p.*,
                       CASE
                           WHEN p.url_image LIKE '/globalhealth-connect1/%'
                           THEN REPLACE(p.url_image, '/globalhealth-connect1/', '/rdv4/')
                           ELSE p.url_image
                       END AS url_image,
                       CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS doctor_name,
                       u.email AS doctor_email
                       ,COALESCE(m_by_medecin.id_user, m_by_user.id_user, p.id_medecin) AS owner_user_id
                       ,COALESCE(m_by_medecin.id_medecin, m_by_user.id_medecin) AS owner_medecin_id
                FROM publication p
                LEFT JOIN medecin m_by_medecin ON m_by_medecin.id_medecin = p.id_medecin
                LEFT JOIN medecin m_by_user ON m_by_user.id_user = p.id_medecin AND m_by_medecin.id_medecin IS NULL
                LEFT JOIN utilisateur u ON u.id_user = COALESCE(m_by_medecin.id_user, m_by_user.id_user, p.id_medecin)
                WHERE p.moderation_status IN ('review', 'blocked')
                ORDER BY COALESCE(p.flagged_at, p.date_publication) DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function comments($limit = 300) {
        $pdo = self::getConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT c.*,
                       CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS user_name,
                       SUBSTRING(p.contenu, 1, 90) AS publication_excerpt
                FROM commentaire c
                LEFT JOIN utilisateur u ON u.id_user = c.id_user
                LEFT JOIN publication p ON p.id_publication = c.id_publication
                ORDER BY c.date_publication DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function reviews($limit = 300) {
        $pdo = self::getConnection();
        self::ensureAvisSchema($pdo);
        try {
            $stmt = $pdo->prepare("
                SELECT a.*,
                       CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS doctor_name,
                       u.email AS doctor_email
                FROM avis a
                LEFT JOIN medecin m_by_medecin ON m_by_medecin.id_medecin = a.id_medecin
                LEFT JOIN medecin m_by_user ON m_by_user.id_user = a.id_medecin AND m_by_medecin.id_medecin IS NULL
                LEFT JOIN utilisateur u ON u.id_user = COALESCE(m_by_medecin.id_user, m_by_user.id_user, a.id_medecin)
                ORDER BY a.created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function handle($action) {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input') ?: '', true);
        if (!is_array($input)) {
            $input = $_REQUEST;
        }
        if (!empty($_FILES)) {
            $input['_files'] = $_FILES;
        }
        if (!empty($_GET['id']) && empty($input['id'])) {
            $input['id'] = $_GET['id'];
        }

        try {
            switch ($action) {
                case 'get-users':
                    $this->json(['success' => true, 'data' => $this->getUsers()]);
                    break;
                case 'get-doctors':
                    $this->json(['success' => true, 'data' => $this->getDoctors()]);
                    break;
                case 'get-publications':
                case 'get-all-publications':
                case 'list':
                case 'index':
                    $this->json(['success' => true, 'data' => self::publications()]);
                    break;
                case 'show':
                    $this->showPublication($input);
                    break;
                case 'search':
                    $this->searchPublications($input);
                    break;
                case 'improve':
                    $this->improvePublication($input);
                    break;
                case 'approved-comments':
                    $this->approvedComments($input);
                    break;
                case 'toggle-publication-like':
                    $this->togglePublicationLike($input);
                    break;
                case 'get-publication-like-status':
                    $this->getPublicationLikeStatus($input);
                    break;
                case 'add-publication':
                case 'store':
                    $this->addPublication($input);
                    break;
                case 'update-publication':
                    $this->updatePublication($input);
                    break;
                case 'delete-publication':
                case 'admin-delete-publication':
                case 'destroy':
                    $this->deletePublication($input);
                    break;
                case 'toggle-publication-status':
                    $this->togglePublicationStatus($input);
                    break;
                case 'get-moderation-publications':
                    $this->json(['success' => true, 'data' => self::moderationPublications()]);
                    break;
                case 'set-publication-moderation-status':
                    $this->setPublicationModerationStatus($input);
                    break;
                case 'get-comments':
                    $this->getComments($input);
                    break;
                case 'get-all-comments':
                    $this->json(['success' => true, 'data' => self::comments()]);
                    break;
                case 'pending-comments':
                    $this->getCommentsByStatus('en_attente');
                    break;
                case 'flagged-comments':
                    $this->getFlaggedComments();
                    break;
                case 'add-comment':
                    $this->addComment($input);
                    break;
                case 'update-comment':
                    $this->updateComment($input);
                    break;
                case 'delete-comment-db':
                case 'admin-delete-comment':
                    $this->deleteComment($input);
                    break;
                case 'update-comment-status':
                case 'admin-update-comment-status':
                    $this->updateCommentStatus($input);
                    break;
                case 'like-comment':
                case 'like':
                    $this->likeComment($input);
                    break;
                case 'report-comment':
                case 'report':
                    $this->reportComment($input);
                    break;
                case 'get-reviews':
                case 'get-all-reviews':
                    $this->getReviews();
                    break;
                case 'add-review':
                    $this->addReview($input);
                    break;
                case 'notify-review-patient':
                    $this->notifyReviewPatient($input);
                    break;
                case 'admin-update-review-status':
                    $this->updateReviewStatus($input);
                    break;
                case 'admin-delete-review':
                    $this->deleteReview($input);
                    break;
                default:
                    http_response_code(404);
                    $this->json(['success' => false, 'error' => 'Action forum introuvable']);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function showPublication($input) {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'ID publication manquant']);
        }

        $stmt = $this->conn->prepare("
            SELECT p.*,
                   CASE
                       WHEN p.url_image LIKE '/globalhealth-connect1/%'
                       THEN REPLACE(p.url_image, '/globalhealth-connect1/', '/rdv4/')
                       ELSE p.url_image
                   END AS url_image,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS medecin_nom,
                   u.email AS medecin_email,
                   COALESCE(m_by_medecin.id_user, m_by_user.id_user, p.id_medecin) AS owner_user_id,
                   COALESCE(m_by_medecin.id_medecin, m_by_user.id_medecin) AS owner_medecin_id
            FROM publication p
            LEFT JOIN medecin m_by_medecin ON m_by_medecin.id_medecin = p.id_medecin
            LEFT JOIN medecin m_by_user ON m_by_user.id_user = p.id_medecin AND m_by_medecin.id_medecin IS NULL
            LEFT JOIN utilisateur u ON u.id_user = COALESCE(m_by_medecin.id_user, m_by_user.id_user, p.id_medecin)
            WHERE p.id_publication = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $publication = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$publication) {
            http_response_code(404);
            $this->json(['success' => false, 'error' => 'Publication introuvable']);
        }

        $commentsStmt = $this->conn->prepare("
            SELECT c.*, u.nom, u.prenom
            FROM commentaire c
            LEFT JOIN utilisateur u ON u.id_user = c.id_user
            WHERE c.id_publication = ?
            ORDER BY c.date_publication DESC
        ");
        $commentsStmt->execute([$id]);

        $this->json([
            'success' => true,
            'publication' => $publication,
            'comments' => $commentsStmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    private function searchPublications($input) {
        $keyword = trim((string)($input['keyword'] ?? ''));
        if (strlen($keyword) < 2) {
            $this->json(['success' => false, 'error' => 'Mot de recherche trop court']);
        }

        $stmt = $this->conn->prepare("
            SELECT p.*,
                   CASE
                       WHEN p.url_image LIKE '/globalhealth-connect1/%'
                       THEN REPLACE(p.url_image, '/globalhealth-connect1/', '/rdv4/')
                       ELSE p.url_image
                   END AS url_image,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS medecin_nom,
                   u.email AS medecin_email,
                   COALESCE(m_by_medecin.id_user, m_by_user.id_user, p.id_medecin) AS owner_user_id,
                   COALESCE(m_by_medecin.id_medecin, m_by_user.id_medecin) AS owner_medecin_id,
                   COUNT(c.id_commentaire) AS commentaires_count
            FROM publication p
            LEFT JOIN medecin m_by_medecin ON m_by_medecin.id_medecin = p.id_medecin
            LEFT JOIN medecin m_by_user ON m_by_user.id_user = p.id_medecin AND m_by_medecin.id_medecin IS NULL
            LEFT JOIN utilisateur u ON u.id_user = COALESCE(m_by_medecin.id_user, m_by_user.id_user, p.id_medecin)
            LEFT JOIN commentaire c ON c.id_publication = p.id_publication
            WHERE p.contenu LIKE :keyword
               OR u.nom LIKE :keyword
               OR u.prenom LIKE :keyword
            GROUP BY p.id_publication
            ORDER BY p.date_publication DESC
            LIMIT 50
        ");
        $stmt->execute(['keyword' => '%' . $keyword . '%']);
        $this->json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'keyword' => $keyword]);
    }

    private function improvePublication($input) {
        $content = trim((string)($input['contenu'] ?? ''));
        if (strlen($content) < 10) {
            $this->json(['success' => false, 'error' => 'Ecrivez au moins 10 caracteres avant d utiliser l IA']);
        }

        $config = $this->getGeminiConfig();
        if (empty($config['api_key'])) {
            $this->json(['success' => false, 'error' => 'Cle Gemini manquante. Ajoutez config/gemini.local.php.']);
        }
        if (!function_exists('curl_init')) {
            $this->json(['success' => false, 'error' => 'Extension PHP cURL non activee.']);
        }

        $model = (string)($config['model'] ?? 'gemini-2.0-flash');
        $prompt = "Tu es assistant editorial pour une plateforme medicale appelee GlobalHealth Connect.\n"
            . "Ameliore cette publication en francais: rends-la claire, professionnelle, rassurante et utile pour des patients.\n"
            . "Garde le meme sens, n'invente pas de diagnostic, de traitement, de chiffres ou de promesse medicale.\n"
            . "Retourne uniquement le texte final, sans guillemets, sans markdown.\n\n"
            . "Texte a ameliorer:\n" . $content;

        $payload = [
            'contents' => [[
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature' => 0.45,
                'maxOutputTokens' => 700,
            ],
        ];

        try {
            $response = $this->callGemini($model, (string)$config['api_key'], $payload);
            $improved = trim((string)($response['candidates'][0]['content']['parts'][0]['text'] ?? ''));
            if ($improved === '') {
                $this->json(['success' => false, 'error' => 'Gemini n a pas retourne de texte.']);
            }
            $this->json(['success' => true, 'original' => $content, 'improved' => $improved]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function approvedComments($input) {
        $publicationId = (int)($input['id'] ?? $input['id_publication'] ?? 0);
        if ($publicationId <= 0) {
            $this->json(['success' => false, 'error' => 'ID publication manquant']);
        }

        $stmt = $this->conn->prepare("
            SELECT c.*, u.nom, u.prenom
            FROM commentaire c
            LEFT JOIN utilisateur u ON u.id_user = c.id_user
            WHERE c.id_publication = ? AND c.statut = 'publie'
            ORDER BY c.date_publication DESC
        ");
        $stmt->execute([$publicationId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json(['success' => true, 'data' => $comments, 'count' => count($comments)]);
    }

    private function togglePublicationLike($input) {
        $publicationId = (int)($input['id_publication'] ?? $input['id'] ?? 0);
        $userId = (int)($input['id_user'] ?? $input['actor_user_id'] ?? 0);
        if ($publicationId <= 0 || $userId <= 0) {
            $this->json(['success' => false, 'error' => 'Utilisateur connecte et publication obligatoires']);
        }

        $stmt = $this->conn->prepare("SELECT id_like FROM publication_like WHERE id_publication = ? AND id_user = ? LIMIT 1");
        $stmt->execute([$publicationId, $userId]);
        $existingLike = (int)$stmt->fetchColumn();

        if ($existingLike > 0) {
            $stmt = $this->conn->prepare("DELETE FROM publication_like WHERE id_like = ?");
            $stmt->execute([$existingLike]);
            $liked = false;
        } else {
            $stmt = $this->conn->prepare("INSERT INTO publication_like (id_publication, id_user) VALUES (?, ?)");
            $stmt->execute([$publicationId, $userId]);
            $liked = true;
        }

        $this->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $this->countPublicationLikes($publicationId),
        ]);
    }

    private function getPublicationLikeStatus($input) {
        $publicationId = (int)($input['id_publication'] ?? $input['id'] ?? 0);
        $userId = (int)($input['id_user'] ?? $input['actor_user_id'] ?? 0);
        if ($publicationId <= 0) {
            $this->json(['success' => false, 'error' => 'Publication manquante']);
        }

        $liked = false;
        if ($userId > 0) {
            $stmt = $this->conn->prepare("SELECT 1 FROM publication_like WHERE id_publication = ? AND id_user = ? LIMIT 1");
            $stmt->execute([$publicationId, $userId]);
            $liked = (bool)$stmt->fetchColumn();
        }

        $this->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $this->countPublicationLikes($publicationId),
        ]);
    }

    private function countPublicationLikes($publicationId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM publication_like WHERE id_publication = ?");
        $stmt->execute([(int)$publicationId]);
        return (int)$stmt->fetchColumn();
    }

    private function addPublication($input) {
        $doctorId = $this->resolveDoctorIdForNewPublication($input);
        $content = trim((string)($input['contenu'] ?? ''));
        if ($doctorId <= 0 || strlen($content) < 10) {
            $this->json(['success' => false, 'error' => 'Donnees publication invalides']);
        }

        $analysis = $this->analyzeContent($content);
        $publicationStatus = $analysis['status'] === 'safe' ? 'approved' : 'blocked';

        $stmt = $this->conn->prepare("
            INSERT INTO publication (
                contenu, date_publication, url_image, url_video, id_medecin, statut,
                moderation_status, toxicity_score, sensitive_score, medical_risk_score,
                moderation_reason, moderation_source, flagged_at, reviewed_at
            )
            VALUES (
                :contenu, NOW(), :url_image, :url_video, :id_medecin, :statut,
                :moderation_status, :toxicity_score, :sensitive_score, :medical_risk_score,
                :moderation_reason, :moderation_source, :flagged_at, NULL
            )
        ");
        $stmt->execute([
            'contenu' => $content,
            'url_image' => $this->resolvePublicationImage($input),
            'url_video' => $input['url_video'] ?? null,
            'id_medecin' => $doctorId,
            'statut' => $publicationStatus,
            'moderation_status' => $analysis['status'],
            'toxicity_score' => $analysis['toxicity'],
            'sensitive_score' => $analysis['sensitive'],
            'medical_risk_score' => $analysis['medicalRisk'],
            'moderation_reason' => implode(' | ', $analysis['reasons']),
            'moderation_source' => $analysis['source'],
            'flagged_at' => $analysis['status'] === 'safe' ? null : date('Y-m-d H:i:s'),
        ]);
        $this->json(['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'moderation' => $analysis]);
    }

    private function resolveDoctorIdForNewPublication($input) {
        $actorUserId = (int)($input['actor_user_id'] ?? 0);
        $actorRole = (string)($input['actor_role'] ?? '');

        if ($actorUserId > 0) {
            if ($actorRole !== 'medecin') {
                http_response_code(403);
                $this->json(['success' => false, 'error' => 'Seuls les medecins peuvent ajouter une publication.']);
            }

            $stmt = $this->conn->prepare("SELECT id_medecin FROM medecin WHERE id_user = ? LIMIT 1");
            $stmt->execute([$actorUserId]);
            $doctorId = (int)$stmt->fetchColumn();
            if ($doctorId <= 0) {
                http_response_code(403);
                $this->json(['success' => false, 'error' => 'Profil medecin introuvable pour cet utilisateur.']);
            }

            return $doctorId;
        }

        $postedDoctorId = (int)($input['id_medecin'] ?? 0);
        if ($postedDoctorId <= 0) {
            return 0;
        }

        $stmt = $this->conn->prepare("
            SELECT id_medecin
            FROM medecin
            WHERE id_medecin = :id OR id_user = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $postedDoctorId]);
        return (int)$stmt->fetchColumn();
    }

    private function updatePublication($input) {
        $id = (int)($input['id'] ?? 0);
        $content = trim((string)($input['contenu'] ?? ''));
        if ($id <= 0 || strlen($content) < 10) {
            $this->json(['success' => false, 'error' => 'Donnees publication invalides']);
        }
        $this->assertPublicationOwnerIfProvided($id, $input);

        $analysis = $this->analyzeContent($content);
        $publicationStatus = $analysis['status'] === 'safe' ? 'approved' : 'blocked';

        $stmt = $this->conn->prepare("
            UPDATE publication
            SET contenu = :contenu,
                url_image = :url_image,
                url_video = :url_video,
                statut = :statut,
                moderation_status = :moderation_status,
                toxicity_score = :toxicity_score,
                sensitive_score = :sensitive_score,
                medical_risk_score = :medical_risk_score,
                moderation_reason = :moderation_reason,
                moderation_source = :moderation_source,
                flagged_at = :flagged_at,
                reviewed_at = NULL
            WHERE id_publication = :id
        ");
        $stmt->execute([
            'contenu' => $content,
            'url_image' => $this->resolvePublicationImage($input),
            'url_video' => $input['url_video'] ?? null,
            'statut' => $publicationStatus,
            'moderation_status' => $analysis['status'],
            'toxicity_score' => $analysis['toxicity'],
            'sensitive_score' => $analysis['sensitive'],
            'medical_risk_score' => $analysis['medicalRisk'],
            'moderation_reason' => implode(' | ', $analysis['reasons']),
            'moderation_source' => $analysis['source'],
            'flagged_at' => $analysis['status'] === 'safe' ? null : date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
        $this->json(['success' => true, 'moderation' => $analysis]);
    }

    private function resolvePublicationImage($input) {
        $files = $input['_files'] ?? [];
        $file = $files['image'] ?? $files['url_image_file'] ?? null;

        if (is_array($file) && !empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            $ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed, true)) {
                $this->json(['success' => false, 'error' => 'Format image non supporte']);
            }

            if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024) {
                $this->json(['success' => false, 'error' => 'Image trop volumineuse, maximum 5 Mo']);
            }

            $dir = __DIR__ . '/../uploads/publications';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $filename = uniqid('pub_', true) . '.' . $ext;
            $target = $dir . DIRECTORY_SEPARATOR . $filename;
            if (!move_uploaded_file($file['tmp_name'], $target)) {
                $this->json(['success' => false, 'error' => 'Upload image impossible']);
            }

            return BASE_URL . 'uploads/publications/' . $filename;
        }

        $image = trim((string)($input['url_image'] ?? ''));
        return $image !== '' ? $image : null;
    }

    private function assertPublicationOwnerIfProvided($publicationId, $input) {
        if (!isset($input['actor_user_id']) && !isset($input['id_user'])) {
            return;
        }
        if (($input['actor_role'] ?? '') === 'admin') {
            return;
        }

        $actorUserId = (int)($input['actor_user_id'] ?? $input['id_user'] ?? 0);
        if ($actorUserId <= 0 || !$this->isPublicationOwnedByUser((int)$publicationId, $actorUserId)) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'Vous pouvez modifier ou supprimer seulement vos propres publications.']);
        }
    }

    private function assertCommentOwnerIfProvided($commentId, $input) {
        if (!isset($input['actor_user_id']) && !isset($input['id_user'])) {
            return;
        }
        if (($input['actor_role'] ?? '') === 'admin') {
            return;
        }

        $actorUserId = (int)($input['actor_user_id'] ?? $input['id_user'] ?? 0);
        if ($actorUserId <= 0) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'Utilisateur non connecte.']);
        }

        $stmt = $this->conn->prepare("SELECT id_user FROM commentaire WHERE id_commentaire = ? LIMIT 1");
        $stmt->execute([(int)$commentId]);
        if ((int)$stmt->fetchColumn() !== $actorUserId) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'Vous pouvez modifier ou supprimer seulement vos propres commentaires.']);
        }
    }

    private function isPublicationOwnedByUser($publicationId, $userId) {
        $stmt = $this->conn->prepare("
            SELECT p.id_publication
            FROM publication p
            LEFT JOIN medecin m_by_medecin ON m_by_medecin.id_medecin = p.id_medecin
            LEFT JOIN medecin m_by_user ON m_by_user.id_user = p.id_medecin AND m_by_medecin.id_medecin IS NULL
            WHERE p.id_publication = :publication_id
              AND COALESCE(m_by_medecin.id_user, m_by_user.id_user, p.id_medecin) = :user_id
            LIMIT 1
        ");
        $stmt->execute([
            'publication_id' => (int)$publicationId,
            'user_id' => (int)$userId,
        ]);
        return (bool)$stmt->fetchColumn();
    }

    private function deletePublication($input) {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'ID publication manquant']);
        }
        $this->assertPublicationOwnerIfProvided($id, $input);

        $this->conn->beginTransaction();
        $stmt = $this->conn->prepare("DELETE FROM commentaire WHERE id_publication = ?");
        $stmt->execute([$id]);
        $stmt = $this->conn->prepare("DELETE FROM publication WHERE id_publication = ?");
        $stmt->execute([$id]);
        $this->conn->commit();
        $this->json(['success' => true]);
    }

    private function togglePublicationStatus($input) {
        $id = (int)($input['id'] ?? 0);
        $stmt = $this->conn->prepare("SELECT statut FROM publication WHERE id_publication = ?");
        $stmt->execute([$id]);
        $current = (string)$stmt->fetchColumn();
        $newStatus = $current === 'approved' ? 'blocked' : 'approved';
        $moderationStatus = $newStatus === 'approved' ? 'safe' : 'blocked';
        $this->updatePublicationModeration($id, $moderationStatus);
        $this->json(['success' => true, 'statut' => $newStatus, 'moderation_status' => $moderationStatus]);
    }

    private function setPublicationModerationStatus($input) {
        $id = (int)($input['id'] ?? 0);
        $status = (string)($input['status'] ?? 'review');
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'ID publication manquant']);
        }
        if (!in_array($status, ['safe', 'review', 'blocked'], true)) {
            $status = 'review';
        }

        $this->updatePublicationModeration($id, $status);
        $this->json([
            'success' => true,
            'moderation_status' => $status,
            'statut' => $status === 'safe' ? 'approved' : 'blocked',
        ]);
    }

    private function getComments($input) {
        $publicationId = (int)($input['id_publication'] ?? 0);
        $stmt = $this->conn->prepare("
            SELECT c.*, u.nom, u.prenom
            FROM commentaire c
            LEFT JOIN utilisateur u ON u.id_user = c.id_user
            WHERE c.id_publication = ? AND c.statut = 'publie'
            ORDER BY c.date_publication DESC
        ");
        $stmt->execute([$publicationId]);
        $this->json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    private function addComment($input) {
        $publicationId = (int)($input['id_publication'] ?? 0);
        $userId = (int)($input['id_user'] ?? 0);
        $content = trim((string)($input['contenu'] ?? ''));
        if ($publicationId <= 0 || $userId <= 0 || strlen($content) < 2) {
            $this->json(['success' => false, 'error' => 'Donnees commentaire invalides']);
        }

        $analysis = $this->analyzeContent($content);
        $commentStatus = $analysis['status'] === 'safe' ? 'publie' : 'supprime';
        $stmt = $this->conn->prepare("
            INSERT INTO commentaire (
                contenu, date_publication, id_publication, id_user, statut, signalements,
                moderation_status, toxicity_score, sensitive_score, medical_risk_score,
                moderation_reason, moderation_source, flagged_at, reviewed_at
            )
            VALUES (
                :contenu, NOW(), :id_publication, :id_user, :statut, 0,
                :moderation_status, :toxicity_score, :sensitive_score, :medical_risk_score,
                :moderation_reason, :moderation_source, :flagged_at, NULL
            )
        ");
        $stmt->execute([
            'contenu' => $content,
            'id_publication' => $publicationId,
            'id_user' => $userId,
            'statut' => $commentStatus,
            'moderation_status' => $analysis['status'],
            'toxicity_score' => $analysis['toxicity'],
            'sensitive_score' => $analysis['sensitive'],
            'medical_risk_score' => $analysis['medicalRisk'],
            'moderation_reason' => implode(' | ', $analysis['reasons']),
            'moderation_source' => $analysis['source'],
            'flagged_at' => $analysis['status'] === 'safe' ? null : date('Y-m-d H:i:s'),
        ]);
        $this->json(['success' => true, 'id' => (int)$this->conn->lastInsertId(), 'moderation' => $analysis]);
    }

    private function updateComment($input) {
        $id = (int)($input['id'] ?? 0);
        $content = trim((string)($input['contenu'] ?? ''));
        if ($id <= 0 || strlen($content) < 2) {
            $this->json(['success' => false, 'error' => 'Donnees commentaire invalides']);
        }
        $this->assertCommentOwnerIfProvided($id, $input);

        $analysis = $this->analyzeContent($content);
        $commentStatus = $analysis['status'] === 'safe' ? 'publie' : 'supprime';
        $stmt = $this->conn->prepare("
            UPDATE commentaire
            SET contenu = :contenu,
                statut = :statut,
                moderation_status = :moderation_status,
                toxicity_score = :toxicity_score,
                sensitive_score = :sensitive_score,
                medical_risk_score = :medical_risk_score,
                moderation_reason = :moderation_reason,
                moderation_source = :moderation_source,
                flagged_at = :flagged_at,
                reviewed_at = NULL
            WHERE id_commentaire = :id
        ");
        $stmt->execute([
            'contenu' => $content,
            'statut' => $commentStatus,
            'moderation_status' => $analysis['status'],
            'toxicity_score' => $analysis['toxicity'],
            'sensitive_score' => $analysis['sensitive'],
            'medical_risk_score' => $analysis['medicalRisk'],
            'moderation_reason' => implode(' | ', $analysis['reasons']),
            'moderation_source' => $analysis['source'],
            'flagged_at' => $analysis['status'] === 'safe' ? null : date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
        $this->json(['success' => true, 'moderation' => $analysis]);
    }

    private function deleteComment($input) {
        $id = (int)($input['id'] ?? 0);
        $this->assertCommentOwnerIfProvided($id, $input);
        $stmt = $this->conn->prepare("DELETE FROM commentaire WHERE id_commentaire = ?");
        $stmt->execute([$id]);
        $this->json(['success' => true]);
    }

    private function updateCommentStatus($input) {
        $id = (int)($input['id'] ?? 0);
        $status = (string)($input['statut'] ?? 'en_attente');
        if (!in_array($status, ['en_attente', 'publie', 'supprime'], true)) {
            $status = 'en_attente';
        }
        $moderationStatus = $status === 'publie' ? 'safe' : ($status === 'supprime' ? 'blocked' : 'review');
        $flaggedAtSql = $moderationStatus === 'safe' ? 'NULL' : 'COALESCE(flagged_at, NOW())';
        $stmt = $this->conn->prepare("
            UPDATE commentaire
            SET statut = :statut,
                moderation_status = :moderation_status,
                flagged_at = {$flaggedAtSql},
                reviewed_at = NOW()
            WHERE id_commentaire = :id
        ");
        $stmt->execute([
            'statut' => $status,
            'moderation_status' => $moderationStatus,
            'id' => $id,
        ]);
        $this->json(['success' => true]);
    }

    private function getCommentsByStatus($status) {
        $stmt = $this->conn->prepare("
            SELECT c.*,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS user_name,
                   SUBSTRING(p.contenu, 1, 90) AS publication_excerpt
            FROM commentaire c
            LEFT JOIN utilisateur u ON u.id_user = c.id_user
            LEFT JOIN publication p ON p.id_publication = c.id_publication
            WHERE c.statut = ?
            ORDER BY c.date_publication DESC
            LIMIT 300
        ");
        $stmt->execute([$status]);
        $this->json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    private function getFlaggedComments() {
        $stmt = $this->conn->query("
            SELECT c.*,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS user_name,
                   SUBSTRING(p.contenu, 1, 90) AS publication_excerpt
            FROM commentaire c
            LEFT JOIN utilisateur u ON u.id_user = c.id_user
            LEFT JOIN publication p ON p.id_publication = c.id_publication
            WHERE c.signalements > 0 OR c.moderation_status IN ('review', 'blocked')
            ORDER BY COALESCE(c.flagged_at, c.date_publication) DESC
            LIMIT 300
        ");
        $this->json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    private function likeComment($input) {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'ID commentaire manquant']);
        }
        $this->addColumnIfMissing('commentaire', 'likes', "ALTER TABLE commentaire ADD COLUMN likes INT NOT NULL DEFAULT 0");
        $stmt = $this->conn->prepare("UPDATE commentaire SET likes = likes + 1 WHERE id_commentaire = ?");
        $stmt->execute([$id]);
        $this->json(['success' => true]);
    }

    private function reportComment($input) {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'ID commentaire manquant']);
        }
        $stmt = $this->conn->prepare("
            UPDATE commentaire
            SET signalements = signalements + 1,
                moderation_status = IF(moderation_status = 'safe', 'review', moderation_status),
                flagged_at = COALESCE(flagged_at, NOW())
            WHERE id_commentaire = ?
        ");
        $stmt->execute([$id]);
        $this->json(['success' => true]);
    }

    private function getReviews() {
        $reviews = array_map(function ($review) {
            return [
                'id' => (int)$review['id_avis'],
                'patient_name' => $review['patient_name'],
                'doctor_id' => (int)$review['id_medecin'],
                'doctor_name' => trim((string)$review['doctor_name']),
                'rating' => (int)$review['rating'],
                'comment' => $review['commentaire'],
                'date' => $review['created_at'],
                'status' => $review['statut'],
            ];
        }, self::reviews());

        $this->json(['success' => true, 'data' => $reviews]);
    }

    private function addReview($input) {
        $patientName = trim((string)($input['patient_name'] ?? ''));
        $doctorId = (int)($input['doctor_id'] ?? 0);
        $rating = (int)($input['rating'] ?? 0);
        $comment = trim((string)($input['comment'] ?? ''));
        if ($patientName === '' || $doctorId <= 0 || $rating < 1 || $rating > 5 || strlen($comment) < 10) {
            $this->json(['success' => false, 'error' => 'Donnees avis invalides']);
        }

        self::ensureAvisSchema($this->conn);
        $stmt = $this->conn->prepare("
            INSERT INTO avis (patient_name, id_patient, id_medecin, rating, commentaire, statut)
            VALUES (:patient_name, :id_patient, :id_medecin, :rating, :commentaire, 'pending')
        ");
        $stmt->execute([
            'patient_name' => $patientName,
            'id_patient' => $input['patient_id'] ?? null,
            'id_medecin' => $doctorId,
            'rating' => $rating,
            'commentaire' => $comment,
        ]);

        $doctor = $this->findDoctorForReview($doctorId);
        $mail = MailModel::sendReviewNotification([
            'patient_name' => $patientName,
            'doctor_name' => $doctor['name'] ?? 'Docteur',
            'doctor_email' => $doctor['email'] ?? '',
            'rating' => $rating,
            'comment' => $comment,
        ]);

        $this->json([
            'success' => true,
            'id' => (int)$this->conn->lastInsertId(),
            'mail' => $mail,
        ]);
    }

    private function notifyReviewPatient($input) {
        $mail = MailModel::sendPatientNotification([
            'patient_email' => $input['patient_email'] ?? '',
            'patient_name' => $input['patient_name'] ?? 'Patient',
            'message' => $input['message'] ?? '',
        ]);

        $this->json([
            'success' => (bool)($mail['success'] ?? false),
            'mail' => $mail,
        ]);
    }

    private function updateReviewStatus($input) {
        $id = (int)($input['id'] ?? 0);
        $status = (string)($input['statut'] ?? 'pending');
        if (!in_array($status, ['pending', 'approved', 'reported'], true)) {
            $status = 'pending';
        }
        self::ensureAvisSchema($this->conn);
        $stmt = $this->conn->prepare("UPDATE avis SET statut = ?, updated_at = NOW() WHERE id_avis = ?");
        $stmt->execute([$status, $id]);
        $this->json(['success' => true]);
    }

    private function deleteReview($input) {
        $id = (int)($input['id'] ?? 0);
        self::ensureAvisSchema($this->conn);
        $stmt = $this->conn->prepare("DELETE FROM avis WHERE id_avis = ?");
        $stmt->execute([$id]);
        $this->json(['success' => true]);
    }

    private function getDoctors() {
        $stmt = $this->conn->query("
            SELECT u.id_user AS id, u.id_user, u.nom, u.prenom, u.email, m.id_medecin, m.specialite,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS name
            FROM utilisateur u
            INNER JOIN medecin m ON m.id_user = u.id_user
            ORDER BY u.nom ASC, u.prenom ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function findDoctorForReview($doctorId) {
        $stmt = $this->conn->prepare("
            SELECT u.email,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS name
            FROM utilisateur u
            LEFT JOIN medecin m ON m.id_user = u.id_user
            WHERE u.id_user = :id OR m.id_medecin = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => (int)$doctorId]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($doctor) ? $doctor : [];
    }

    private function getUsers() {
        $stmt = $this->conn->query("
            SELECT u.id_user AS id, u.id_user, u.nom, u.prenom, u.email,
                   CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS name
            FROM utilisateur u
            ORDER BY u.nom ASC, u.prenom ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function ensureSchema() {
        self::ensureForumSchema($this->conn);
    }

    private static function ensureForumSchema($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS publication (
                id_publication INT AUTO_INCREMENT PRIMARY KEY,
                contenu TEXT NOT NULL,
                date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                url_video VARCHAR(500) NULL,
                url_image VARCHAR(500) NULL,
                id_medecin INT NOT NULL,
                statut ENUM('approved','blocked') NOT NULL DEFAULT 'approved',
                moderation_status VARCHAR(20) NOT NULL DEFAULT 'safe',
                toxicity_score DECIMAL(5,4) NOT NULL DEFAULT 0,
                sensitive_score DECIMAL(5,4) NOT NULL DEFAULT 0,
                medical_risk_score DECIMAL(5,4) NOT NULL DEFAULT 0,
                moderation_reason TEXT NULL,
                moderation_source VARCHAR(20) NOT NULL DEFAULT 'fallback',
                flagged_at DATETIME NULL,
                reviewed_at DATETIME NULL,
                INDEX idx_publication_date (date_publication),
                INDEX idx_publication_statut (statut)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS commentaire (
                id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
                contenu TEXT NOT NULL,
                date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                id_publication INT NOT NULL,
                id_user INT NOT NULL,
                statut ENUM('en_attente','publie','supprime') NOT NULL DEFAULT 'publie',
                note INT DEFAULT NULL,
                signalements INT NOT NULL DEFAULT 0,
                moderation_status VARCHAR(20) NOT NULL DEFAULT 'safe',
                toxicity_score DECIMAL(5,4) NOT NULL DEFAULT 0,
                sensitive_score DECIMAL(5,4) NOT NULL DEFAULT 0,
                medical_risk_score DECIMAL(5,4) NOT NULL DEFAULT 0,
                moderation_reason TEXT NULL,
                moderation_source VARCHAR(20) NOT NULL DEFAULT 'fallback',
                flagged_at DATETIME NULL,
                reviewed_at DATETIME NULL,
                INDEX idx_comment_publication (id_publication),
                INDEX idx_comment_statut (statut)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS publication_like (
                id_like INT AUTO_INCREMENT PRIMARY KEY,
                id_publication INT NOT NULL,
                id_user INT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_publication_user_like (id_publication, id_user),
                INDEX idx_publication_like_publication (id_publication),
                INDEX idx_publication_like_user (id_user)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        self::addColumnIfMissingStatic($pdo, 'publication', 'statut', "ALTER TABLE publication ADD COLUMN statut ENUM('approved','blocked') NOT NULL DEFAULT 'approved'");
        self::addColumnIfMissingStatic($pdo, 'publication', 'moderation_status', "ALTER TABLE publication ADD COLUMN moderation_status VARCHAR(20) NOT NULL DEFAULT 'safe'");
        self::addColumnIfMissingStatic($pdo, 'publication', 'toxicity_score', "ALTER TABLE publication ADD COLUMN toxicity_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissingStatic($pdo, 'publication', 'sensitive_score', "ALTER TABLE publication ADD COLUMN sensitive_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissingStatic($pdo, 'publication', 'medical_risk_score', "ALTER TABLE publication ADD COLUMN medical_risk_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissingStatic($pdo, 'publication', 'moderation_reason', "ALTER TABLE publication ADD COLUMN moderation_reason TEXT NULL");
        self::addColumnIfMissingStatic($pdo, 'publication', 'moderation_source', "ALTER TABLE publication ADD COLUMN moderation_source VARCHAR(20) NOT NULL DEFAULT 'fallback'");
        self::addColumnIfMissingStatic($pdo, 'publication', 'flagged_at', "ALTER TABLE publication ADD COLUMN flagged_at DATETIME NULL");
        self::addColumnIfMissingStatic($pdo, 'publication', 'reviewed_at', "ALTER TABLE publication ADD COLUMN reviewed_at DATETIME NULL");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'statut', "ALTER TABLE commentaire ADD COLUMN statut ENUM('en_attente','publie','supprime') NOT NULL DEFAULT 'publie'");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'note', "ALTER TABLE commentaire ADD COLUMN note INT DEFAULT NULL");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'signalements', "ALTER TABLE commentaire ADD COLUMN signalements INT NOT NULL DEFAULT 0");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'moderation_status', "ALTER TABLE commentaire ADD COLUMN moderation_status VARCHAR(20) NOT NULL DEFAULT 'safe'");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'toxicity_score', "ALTER TABLE commentaire ADD COLUMN toxicity_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'sensitive_score', "ALTER TABLE commentaire ADD COLUMN sensitive_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'medical_risk_score', "ALTER TABLE commentaire ADD COLUMN medical_risk_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'moderation_reason', "ALTER TABLE commentaire ADD COLUMN moderation_reason TEXT NULL");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'moderation_source', "ALTER TABLE commentaire ADD COLUMN moderation_source VARCHAR(20) NOT NULL DEFAULT 'fallback'");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'flagged_at', "ALTER TABLE commentaire ADD COLUMN flagged_at DATETIME NULL");
        self::addColumnIfMissingStatic($pdo, 'commentaire', 'reviewed_at', "ALTER TABLE commentaire ADD COLUMN reviewed_at DATETIME NULL");
        self::ensureAvisSchema($pdo);
    }

    private function addColumnIfMissing($table, $column, $sql) {
        self::addColumnIfMissingStatic($this->conn, $table, $column, $sql);
    }

    private static function addColumnIfMissingStatic($pdo, $table, $column, $sql) {
        $stmt = $pdo->query("SHOW COLUMNS FROM {$table} LIKE " . $pdo->quote($column));
        if ($stmt && $stmt->rowCount() === 0) {
            $pdo->exec($sql);
        }
    }

    private function updatePublicationModeration($id, $status) {
        $publicationStatus = $status === 'safe' ? 'approved' : 'blocked';
        $flaggedAtSql = $status === 'safe' ? 'NULL' : 'COALESCE(flagged_at, NOW())';
        $stmt = $this->conn->prepare("
            UPDATE publication
            SET statut = :statut,
                moderation_status = :moderation_status,
                flagged_at = {$flaggedAtSql},
                reviewed_at = NOW()
            WHERE id_publication = :id
        ");
        $stmt->execute([
            'statut' => $publicationStatus,
            'moderation_status' => $status,
            'id' => $id,
        ]);
    }

    private function analyzeContent($text) {
        $text = trim((string)$text);
        if ($text === '') {
            return $this->emptyAnalysis();
        }

        $config = $this->getGeminiConfig();
        if (!empty($config['api_key']) && function_exists('curl_init')) {
            try {
                return $this->analyzeWithGemini($text, $config);
            } catch (Throwable $e) {
                return $this->fallbackAnalyze($text);
            }
        }

        return $this->fallbackAnalyze($text);
    }

    private function analyzeWithGemini($text, $config) {
        $model = (string)($config['model'] ?? 'gemini-2.0-flash');
        $prompt = "Tu es un moderateur IA d'un forum sante. Retourne uniquement un JSON strict "
            . "{\"toxicity\":0.0,\"sensitive\":0.0,\"medical_risk\":0.0,\"reasons\":[\"raison courte\"]}. "
            . "toxicity=insultes/haine/harcelement, sensitive=danger/violence/suicide, medical_risk=conseil medical dangereux. Texte: "
            . $text;
        $payload = [
            'contents' => [[
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature' => 0,
                'maxOutputTokens' => 600,
            ],
        ];

        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent');
        if ($ch === false) {
            throw new RuntimeException('curl init failed');
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . (string)$config['api_key'],
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 20,
        ]);
        $raw = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false || $statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException('Gemini moderation failed');
        }

        $response = json_decode((string)$raw, true);
        $content = (string)($response['candidates'][0]['content']['parts'][0]['text'] ?? '');
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start !== false && $end !== false && $end >= $start) {
            $content = substr($content, $start, $end - $start + 1);
        }
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Gemini JSON');
        }

        $toxicity = $this->clamp01((float)($decoded['toxicity'] ?? 0));
        $sensitive = $this->clamp01((float)($decoded['sensitive'] ?? 0));
        $medicalRisk = $this->clamp01((float)($decoded['medical_risk'] ?? 0));
        return [
            'status' => $this->resolveStatus($toxicity, $sensitive, $medicalRisk),
            'toxicity' => $toxicity,
            'sensitive' => $sensitive,
            'medicalRisk' => $medicalRisk,
            'reasons' => $this->normalizeReasons($decoded['reasons'] ?? []),
            'source' => 'gemini',
        ];
    }

    private function callGemini($model, $apiKey, $payload) {
        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode((string)$model) . ':generateContent');
        if ($ch === false) {
            throw new RuntimeException('Impossible d initialiser cURL.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . (string)$apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
        ]);

        $raw = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Erreur reseau Gemini: ' . $curlError);
        }

        $decoded = json_decode((string)$raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Reponse Gemini invalide.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException((string)($decoded['error']['message'] ?? 'Erreur Gemini'));
        }

        return $decoded;
    }

    private function fallbackAnalyze($text) {
        $content = $this->normalizeForMatching($text);
        $toxic = ['idiot', 'stupide', 'imbecile', 'nul', 'haine', 'ta gueule', 'ferme la'];
        $sensitive = ['suicide', 'tuer', 'poison', 'overdose', 'violence', 'arme'];
        $medical = [
            'arrete ton traitement', 'arretez votre traitement', 'ne prends plus tes medicaments',
            'ne prends plus ton traitement', 'ne prenez plus votre insuline', 'double la dose',
            'doubler la dose', 'les vaccins tuent', 'insuline inutile', 'antibiotique pour virus',
            'remplace ton traitement par', 'surdosage', 'sans prevenir votre medecin'
        ];

        $toxicityHits = $this->countHits($content, $toxic);
        $sensitiveHits = $this->countHits($content, $sensitive);
        $medicalHits = $this->countHits($content, $medical);
        $toxicity = $this->clamp01($toxicityHits / 3);
        $sensitiveScore = $this->clamp01($sensitiveHits / 2);
        $medicalRisk = $this->clamp01($medicalHits / 2);
        $reasons = [];
        if ($toxicityHits > 0) $reasons[] = 'Langage agressif detecte';
        if ($sensitiveHits > 0) $reasons[] = 'Contenu sensible detecte';
        if ($medicalHits > 0) $reasons[] = 'Conseil medical dangereux detecte';

        return [
            'status' => $this->resolveStatus($toxicity, $sensitiveScore, $medicalRisk),
            'toxicity' => $toxicity,
            'sensitive' => $sensitiveScore,
            'medicalRisk' => $medicalRisk,
            'reasons' => $reasons,
            'source' => 'fallback',
        ];
    }

    private function resolveStatus($toxicity, $sensitive, $medicalRisk) {
        if ($toxicity >= 0.85 || $sensitive >= 0.90 || $medicalRisk >= 0.90) return 'blocked';
        if ($toxicity >= 0.55 || $sensitive >= 0.60 || $medicalRisk >= 0.60) return 'review';
        return 'safe';
    }

    private function getGeminiConfig() {
        $config = [
            'api_key' => getenv('GEMINI_API_KEY') ?: (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : ''),
            'model' => getenv('GEMINI_MODEL') ?: (defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-2.0-flash'),
        ];
        $path = __DIR__ . '/../config/gemini.local.php';
        if (is_file($path)) {
            $local = require $path;
            if (is_array($local)) {
                $config = array_merge($config, $local);
            }
        }
        return $config;
    }

    private function normalizeReasons($raw) {
        if (!is_array($raw)) return [];
        $reasons = [];
        foreach ($raw as $reason) {
            if (is_string($reason) && trim($reason) !== '') {
                $reasons[] = trim($reason);
            }
        }
        return array_slice(array_values(array_unique($reasons)), 0, 4);
    }

    private function countHits($content, $patterns) {
        $hits = 0;
        foreach ($patterns as $pattern) {
            if (str_contains($content, $this->normalizeForMatching($pattern))) {
                $hits++;
            }
        }
        return $hits;
    }

    private function normalizeForMatching($text) {
        $text = strtolower((string)$text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        return preg_replace('/\s+/', ' ', $text) ?? $text;
    }

    private function clamp01($value) {
        return max(0.0, min(1.0, (float)$value));
    }

    private function emptyAnalysis() {
        return [
            'status' => 'safe',
            'toxicity' => 0.0,
            'sensitive' => 0.0,
            'medicalRisk' => 0.0,
            'reasons' => [],
            'source' => 'fallback',
        ];
    }

    private static function ensureAvisSchema($pdo) {
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
                INDEX idx_avis_statut (statut)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    private function json($payload) {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
