<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Publication.php';
require_once __DIR__ . '/../services/ForumModerationService.php';

/**
 * PublicationController - Handles all publication-related operations
 */
class PublicationController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all publications with optional filtering by doctor
     * GET /api/publications.php?action=list&limit=10&offset=0&id_medecin=1 (optional)
     */
    public function index() {
        try {
            $limit = (int)($_GET['limit'] ?? 10);
            $offset = (int)($_GET['offset'] ?? 0);
            $id_medecin = $_GET['id_medecin'] ?? null;

            $publication = new Publication();
            
            if ($id_medecin) {
                $data = $publication->findByMedecin($id_medecin, $limit, $offset);
            } else {
                $data = $publication->getAll($limit, $offset);
            }

            // Count total
            $countQuery = $id_medecin ? 
                "SELECT COUNT(*) as total FROM publication WHERE id_medecin = {$id_medecin}" :
                "SELECT COUNT(*) as total FROM publication";
            $pdo = config::getConnexion();
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];

            return $this->jsonResponse([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => (int)$total
                ]
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get single publication by ID with comments
     * GET /api/publications.php?action=show&id=1
     */
    public function show() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication ID required'], 400);
            }

            $publication = new Publication();
            if (!$publication->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication not found'], 404);
            }

            $comments = $publication->getComments();
            $commentCount = $publication->getCommentCount();

            return $this->jsonResponse([
                'success' => true,
                'publication' => $publication->toArray(),
                'comments' => $comments,
                'comment_count' => $commentCount
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Create new publication
     * POST /api/publications.php?action=store
     * Can be created by both doctors and patients
     */
    public function store() {
        try {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $data = !empty($_POST) ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);

            // Gestion upload image
            $url_image = null;
            if (!empty($_FILES['image']['tmp_name'])) {
                $uploadDir = __DIR__ . '/../uploads/publications/';
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowed)) {
                    return $this->jsonResponse(['success' => false, 'error' => 'Format image non supporté'], 400);
                }
                $filename = uniqid('pub_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $url_image = '/globalhealth-connect1/uploads/publications/' . $filename;
                }
            }

            // Validation
            if (empty($data['contenu']) || strlen(trim($data['contenu'])) < 10) {
                return $this->jsonResponse(['success' => false, 'error' => 'Content must be at least 10 characters'], 400);
            }

            $idUser = $data['id_medecin'] ?? $_SESSION['user_id'] ?? null;

            if (empty($idUser)) {
                return $this->jsonResponse(['success' => false, 'error' => 'User must be logged in'], 401);
            }

            // Résoudre id_medecin depuis la table medecin via id_user
            $pdo = config::getConnexion();
            ForumModerationService::ensurePublicationSchema($pdo);
            $stmt = $pdo->prepare("SELECT id_medecin FROM medecin WHERE id_user = :id_user LIMIT 1");
            $stmt->execute(['id_user' => $idUser]);
            $medecinRow = $stmt->fetch();

            if (!$medecinRow) {
                return $this->jsonResponse(['success' => false, 'error' => 'Aucun profil médecin trouvé pour cet utilisateur'], 403);
            }

            $idMedecin = $medecinRow['id_medecin'];

            $publication = new Publication();
            $publication->setIdMedecin($idMedecin);
            $publication->setContenu($data['contenu']);
            $publication->setDatePublication(date('Y-m-d H:i:s'));
            
            if ($url_image) {
                $publication->setUrlImage($url_image);
            } elseif (!empty($data['url_image'])) {
                $publication->setUrlImage($data['url_image']);
            }
            if (!empty($data['url_video'])) {
                $publication->setUrlVideo($data['url_video']);
            }

            $analysis = ForumModerationService::analyze((string)$data['contenu']);
            ForumModerationService::applyAnalysisToPublication($publication, $analysis);

            $result = $publication->create();

            if ($result['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Publication created successfully',
                    'id' => $result['id'],
                    'moderation' => $analysis
                ], 201);
            } else {
                return $this->jsonResponse(['success' => false, 'error' => $result['error'] ?? 'Failed to create publication'], 400);
            }
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update publication
     * POST /api/publications.php?action=update&id=1
     */
    public function update() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication ID required'], 400);
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $publication = new Publication();
            if (!$publication->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication not found'], 404);
            }

            if (!empty($data['contenu'])) {
                $publication->setContenu($data['contenu']);
            }
            if (isset($data['url_video'])) {
                $publication->setUrlVideo($data['url_video']);
            }
            if (isset($data['url_image'])) {
                $publication->setUrlImage($data['url_image']);
            }

            $result = $publication->update();
            return $this->jsonResponse([
                'success' => $result['success'],
                'message' => 'Publication updated successfully',
                'error' => $result['error'] ?? null
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete publication
     * POST /api/publications.php?action=destroy&id=1
     */
    public function destroy() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication ID required'], 400);
            }

            $publication = new Publication();
            if (!$publication->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication not found'], 404);
            }

            $result = $publication->delete();
            return $this->jsonResponse([
                'success' => $result['success'],
                'message' => 'Publication deleted successfully',
                'error' => $result['error'] ?? null
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Search publications
     * GET /api/publications.php?action=search&keyword=health&limit=10&offset=0
     */
    public function search() {
        try {
            $keyword = $_GET['keyword'] ?? '';
            $limit = (int)($_GET['limit'] ?? 10);
            $offset = (int)($_GET['offset'] ?? 0);

            if (strlen($keyword) < 2) {
                return $this->jsonResponse(['success' => false, 'error' => 'Search keyword must be at least 2 characters'], 400);
            }

            $publication = new Publication();
            $results = $publication->search($keyword, $limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'keyword' => $keyword
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Improve a publication draft with Gemini.
     * POST /api/publications.php?action=improve
     */
    public function improve() {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $content = trim((string)($data['contenu'] ?? ''));

            if (strlen($content) < 10) {
                return $this->jsonResponse(['success' => false, 'error' => 'Ecrivez au moins 10 caracteres avant d utiliser l IA'], 400);
            }

            $config = $this->getGeminiConfig();
            if (empty($config['api_key'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Cle Gemini manquante. Ajoutez config/gemini.local.php avec votre api_key.'], 500);
            }

            $model = $config['model'] ?? 'gemini-2.5-flash';
            $prompt = "Tu es assistant editorial pour une plateforme medicale appelee GlobalHealth Connect.\n"
                . "Ameliore cette publication en francais: rends-la claire, professionnelle, rassurante et utile pour des patients.\n"
                . "Garde le meme sens, n'invente pas de diagnostic, de traitement, de chiffres ou de promesse medicale.\n"
                . "Retourne uniquement le texte final, sans guillemets, sans markdown.\n\n"
                . "Texte a ameliorer:\n" . $content;

            $payload = [
                'contents' => [[
                    'parts' => [[
                        'text' => $prompt
                    ]]
                ]],
                'generationConfig' => [
                    'temperature' => 0.45,
                    'maxOutputTokens' => 700,
                ],
            ];

            $response = $this->callGemini($model, $config['api_key'], $payload);
            $improved = trim((string)($response['candidates'][0]['content']['parts'][0]['text'] ?? ''));

            if ($improved === '') {
                return $this->jsonResponse(['success' => false, 'error' => 'Gemini n a pas retourne de texte.'], 502);
            }

            return $this->jsonResponse([
                'success' => true,
                'original' => $content,
                'improved' => $improved
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    private function getGeminiConfig(): array {
        $config = [
            'api_key' => getenv('GEMINI_API_KEY') ?: '',
            'model' => getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash',
        ];

        $localConfigPath = __DIR__ . '/../config/gemini.local.php';
        if (is_file($localConfigPath)) {
            $localConfig = require $localConfigPath;
            if (is_array($localConfig)) {
                $config = array_merge($config, $localConfig);
            }
        }

        return $config;
    }

    private function callGemini(string $model, string $apiKey, array $payload): array {
        if (!function_exists('curl_init')) {
            throw new Exception('Extension PHP cURL non activee. Activez curl dans php.ini pour utiliser Gemini.');
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';
        $body = json_encode($payload);

        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Impossible d initialiser cURL.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 30,
        ]);

        $rawResponse = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false) {
            throw new Exception('Erreur reseau Gemini: ' . $curlError);
        }

        $decoded = json_decode($rawResponse, true);
        if (!is_array($decoded)) {
            throw new Exception('Reponse Gemini invalide.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = $decoded['error']['message'] ?? 'Erreur Gemini';
            throw new Exception($message);
        }

        return $decoded;
    }

    /**
     * Approved comments for a publication
     * GET /api/publications.php?action=approved-comments&id=1
     */
    public function approvedComments() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication ID required'], 400);
            }

            $publication = new Publication();
            if (!$publication->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication not found'], 404);
            }

            $comments = $publication->getApprovedComments();

            return $this->jsonResponse([
                'success' => true,
                'data' => $comments,
                'count' => count($comments)
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}
