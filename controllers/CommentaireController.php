<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Commentaire.php';

/**
 * CommentaireController - Handles all comment-related operations
 */
class CommentaireController extends BaseController {
    
    public function __construct() {
        parent::__construct('Commentaire');
    }

    /**
     * Get all comments for a publication
     * GET /api/commentaires.php?action=index&id_publication=1&limit=100&offset=0
     */
    public function index() {
        try {
            $id_publication = $_GET['id_publication'] ?? null;
            $limit = (int)($_GET['limit'] ?? 100);
            $offset = (int)($_GET['offset'] ?? 0);

            if (!$id_publication) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication ID required'], 400);
            }

            $commentaire = new Commentaire();
            $comments = $commentaire->findByPublication($id_publication, $limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'data' => $comments,
                'count' => count($comments)
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get single comment
     * GET /api/commentaires.php?action=show&id=1
     */
    public function show() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment ID required'], 400);
            }

            $commentaire = new Commentaire();
            if (!$commentaire->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment not found'], 404);
            }

            $commentWithUser = $commentaire->getWithUser();

            return $this->jsonResponse([
                'success' => true,
                'data' => $commentWithUser ?? $commentaire->toArray()
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get approved comments only
     * GET /api/commentaires.php?action=approved&id_publication=1
     */
    public function approved() {
        try {
            $id_publication = $_GET['id_publication'] ?? null;
            $limit = (int)($_GET['limit'] ?? 100);
            $offset = (int)($_GET['offset'] ?? 0);

            if (!$id_publication) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication ID required'], 400);
            }

            $commentaire = new Commentaire();
            $comments = $commentaire->findApprovedByPublication($id_publication, $limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'data' => $comments,
                'count' => count($comments)
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get comments by user
     * GET /api/commentaires.php?action=by-user&id_user=1
     */
    public function byUser() {
        try {
            $id_user = $_GET['id_user'] ?? null;
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);

            if (!$id_user) {
                return $this->jsonResponse(['success' => false, 'error' => 'User ID required'], 400);
            }

            $commentaire = new Commentaire();
            $comments = $commentaire->findByUser($id_user, $limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'data' => $comments,
                'count' => count($comments)
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get pending comments for moderation
     * GET /api/commentaires.php?action=pending
     */
    public function pending() {
        try {
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);

            $commentaire = new Commentaire();
            $comments = $commentaire->findPending($limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'data' => $comments,
                'count' => count($comments)
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get flagged comments (reported)
     * GET /api/commentaires.php?action=flagged
     */
    public function flagged() {
        try {
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);

            $commentaire = new Commentaire();
            $comments = $commentaire->findFlagged($limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'data' => $comments,
                'count' => count($comments)
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Create new comment
     * POST /api/commentaires.php?action=store
     */
    public function store() {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            // Validation
            if (empty($data['contenu']) || strlen(trim($data['contenu'])) < 2) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment must be at least 2 characters'], 400);
            }

            if (empty($data['id_publication'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Publication ID is required'], 400);
            }

            if (empty($data['id_user'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'User ID is required'], 400);
            }

            $commentaire = new Commentaire();
            $commentaire->setContenu($data['contenu']);
            $commentaire->setIdPublication($data['id_publication']);
            $commentaire->setIdUser($data['id_user']);
            $commentaire->setDatePublication(date('Y-m-d H:i:s'));
            $commentaire->setStatut($data['statut'] ?? 'pending');
            $commentaire->setNote(0);
            $commentaire->setSignalements(0);

            $result = $commentaire->create();

            if ($result['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Comment created successfully',
                    'id' => $result['id']
                ], 201);
            } else {
                return $this->jsonResponse(['success' => false, 'error' => $result['error'] ?? 'Failed to create comment'], 400);
            }
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update comment
     * POST /api/commentaires.php?action=update&id=1
     */
    public function update() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment ID required'], 400);
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $commentaire = new Commentaire();
            if (!$commentaire->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment not found'], 404);
            }

            if (!empty($data['contenu'])) {
                $commentaire->setContenu($data['contenu']);
            }
            if (isset($data['statut']) && !empty($data['statut'])) {
                $commentaire->setStatut($data['statut']);
            }
            if (isset($data['note'])) {
                $commentaire->setNote($data['note']);
            }
            if (isset($data['signalements'])) {
                $commentaire->setSignalements($data['signalements']);
            }

            $result = $commentaire->update();
            return $this->jsonResponse([
                'success' => $result['success'],
                'message' => 'Comment updated successfully',
                'error' => $result['error'] ?? null
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Approve comment
     * POST /api/commentaires.php?action=approve&id=1
     */
    public function approve() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment ID required'], 400);
            }

            $commentaire = new Commentaire();
            if (!$commentaire->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment not found'], 404);
            }

            $commentaire->approve();
            $result = $commentaire->update();

            return $this->jsonResponse([
                'success' => $result['success'],
                'message' => 'Comment approved successfully',
                'error' => $result['error'] ?? null
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Reject comment
     * POST /api/commentaires.php?action=reject&id=1
     */
    public function reject() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment ID required'], 400);
            }

            $commentaire = new Commentaire();
            if (!$commentaire->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment not found'], 404);
            }

            $commentaire->reject();
            $result = $commentaire->update();

            return $this->jsonResponse([
                'success' => $result['success'],
                'message' => 'Comment rejected successfully',
                'error' => $result['error'] ?? null
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Like/upvote comment (increment note)
     * POST /api/commentaires.php?action=like&id=1
     */
    public function like() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment ID required'], 400);
            }

            $commentaire = new Commentaire();
            if (!$commentaire->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment not found'], 404);
            }

            $commentaire->incrementNote();
            $result = $commentaire->update();

            return $this->jsonResponse([
                'success' => $result['success'],
                'message' => 'Comment liked',
                'note' => $commentaire->getNote(),
                'error' => $result['error'] ?? null
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Report/flag comment (increment signalements)
     * POST /api/commentaires.php?action=report&id=1
     */
    public function report() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment ID required'], 400);
            }

            $commentaire = new Commentaire();
            if (!$commentaire->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment not found'], 404);
            }

            $commentaire->incrementSignalements();
            $result = $commentaire->update();

            return $this->jsonResponse([
                'success' => $result['success'],
                'message' => 'Comment reported',
                'signalements' => $commentaire->getSignalements(),
                'error' => $result['error'] ?? null
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete comment
     * POST /api/commentaires.php?action=destroy&id=1
     */
    public function destroy() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment ID required'], 400);
            }

            $commentaire = new Commentaire();
            if (!$commentaire->findById($id)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Comment not found'], 404);
            }

            $result = $commentaire->delete();
            return $this->jsonResponse([
                'success' => $result['success'],
                'message' => 'Comment deleted successfully',
                'error' => $result['error'] ?? null
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}
