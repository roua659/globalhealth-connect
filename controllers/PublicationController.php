<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Publication.php';

/**
 * PublicationController - Handles all publication-related operations
 */
class PublicationController extends BaseController {
    
    public function __construct() {
        parent::__construct('Publication');
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

            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            // Validation
            if (empty($data['contenu']) || strlen(trim($data['contenu'])) < 10) {
                return $this->jsonResponse(['success' => false, 'error' => 'Content must be at least 10 characters'], 400);
            }

            // Use provided id_medecin or fall back to session user ID
            $userId = $data['id_medecin'] ?? $_SESSION['user_id'] ?? null;
            
            if (empty($userId)) {
                return $this->jsonResponse(['success' => false, 'error' => 'User must be logged in'], 401);
            }

            $publication = new Publication();
            $publication->setIdMedecin($userId);
            $publication->setContenu($data['contenu']);
            $publication->setDatePublication(date('Y-m-d H:i:s'));
            
            if (!empty($data['url_image'])) {
                $publication->setUrlImage($data['url_image']);
            }
            if (!empty($data['url_video'])) {
                $publication->setUrlVideo($data['url_video']);
            }

            $result = $publication->create();

            if ($result['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Publication created successfully',
                    'id' => $result['id']
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
