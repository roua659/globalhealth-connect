<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Model.php';

/**
 * Commentaire Model
 * Represents a comment on a publication
 */
class Commentaire extends Model {
    protected $table = 'commentaire';
    protected $fillable = ['id_commentaire', 'contenu', 'date_publication', 'id_publication', 'id_user', 'statut', 'note', 'signalements'];

    // ==================== GETTERS ====================

    /**
     * Get comment ID
     */
    public function getId() {
        return $this->getAttribute('id_commentaire');
    }

    /**
     * Get comment content
     */
    public function getContenu() {
        return $this->getAttribute('contenu');
    }

    /**
     * Get comment date
     */
    public function getDatePublication() {
        return $this->getAttribute('date_publication');
    }

    /**
     * Get publication ID (parent)
     */
    public function getIdPublication() {
        return $this->getAttribute('id_publication');
    }

    /**
     * Get user ID (author)
     */
    public function getIdUser() {
        return $this->getAttribute('id_user');
    }

    /**
     * Get comment status
     */
    public function getStatut() {
        return $this->getAttribute('statut');
    }

    /**
     * Get comment rating/likes
     */
    public function getNote() {
        return $this->getAttribute('note') ?? 0;
    }

    /**
     * Get report count
     */
    public function getSignalements() {
        return $this->getAttribute('signalements') ?? 0;
    }

    /**
     * Get created date
     */
    public function getCreatedAt() {
        return $this->getAttribute('created_at');
    }

    /**
     * Get updated date
     */
    public function getUpdatedAt() {
        return $this->getAttribute('updated_at');
    }

    // ==================== SETTERS ====================

    /**
     * Set comment ID
     */
    public function setId($id) {
        return $this->setAttribute('id_commentaire', $id);
    }

    /**
     * Set comment content with validation
     */
    public function setContenu($contenu) {
        $contenu = trim($contenu);
        if (strlen($contenu) < 2) {
            throw new Exception('Comment must be at least 2 characters');
        }
        if (strlen($contenu) > 5000) {
            throw new Exception('Comment is too long (max 5000 characters)');
        }
        return $this->setAttribute('contenu', $contenu);
    }

    /**
     * Set comment date
     */
    public function setDatePublication($date) {
        if (!strtotime($date)) {
            throw new Exception('Invalid date format');
        }
        return $this->setAttribute('date_publication', $date);
    }

    /**
     * Set publication ID (parent publication)
     */
    public function setIdPublication($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new Exception('Invalid publication ID');
        }
        return $this->setAttribute('id_publication', $id);
    }

    /**
     * Set user ID (comment author)
     */
    public function setIdUser($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new Exception('Invalid user ID');
        }
        return $this->setAttribute('id_user', $id);
    }

    /**
     * Set comment status (pending, approved, rejected)
     */
    public function setStatut($statut) {
        $validStatuses = ['pending', 'approved', 'rejected'];
        if (!in_array($statut, $validStatuses)) {
            throw new Exception('Invalid status. Must be: ' . implode(', ', $validStatuses));
        }
        return $this->setAttribute('statut', $statut);
    }

    /**
     * Set comment rating/likes
     */
    public function setNote($note) {
        if (!is_numeric($note) || $note < 0) {
            throw new Exception('Note must be a positive number');
        }
        return $this->setAttribute('note', (int)$note);
    }

    /**
     * Set report count
     */
    public function setSignalements($count) {
        if (!is_numeric($count) || $count < 0) {
            throw new Exception('Signalements must be a positive number');
        }
        return $this->setAttribute('signalements', (int)$count);
    }

    /**
     * Set created date
     */
    public function setCreatedAt($date) {
        return $this->setAttribute('created_at', $date);
    }

    /**
     * Set updated date
     */
    public function setUpdatedAt($date) {
        return $this->setAttribute('updated_at', $date);
    }

    // ==================== QUERIES ====================

    /**
     * Find comment by ID
     */
    public function findById($id) {
        return $this->findByIdColumn('id_commentaire', $id);
    }

    /**
     * Get all comments for a publication
     */
    public function findByPublication($id_publication, $limit = 100, $offset = 0) {
        try {
            $query = "SELECT c.*, u.nom, u.prenom, u.email FROM {$this->table} c 
                      JOIN utilisateur u ON c.id_user = u.id 
                      WHERE c.id_publication = :id_publication 
                      ORDER BY c.date_publication DESC 
                      LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_publication', $id_publication, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get approved comments only
     */
    public function findApprovedByPublication($id_publication, $limit = 100, $offset = 0) {
        try {
            $query = "SELECT c.*, u.nom, u.prenom FROM {$this->table} c 
                      JOIN utilisateur u ON c.id_user = u.id 
                      WHERE c.id_publication = :id_publication AND c.statut = 'approved'
                      ORDER BY c.note DESC, c.date_publication DESC 
                      LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_publication', $id_publication, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get comments by user
     */
    public function findByUser($id_user, $limit = 100, $offset = 0) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id_user = :id_user 
                      ORDER BY date_publication DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get pending comments (for moderation)
     */
    public function findPending($limit = 50, $offset = 0) {
        try {
            $query = "SELECT c.*, u.nom, u.prenom, p.contenu as publication_contenu FROM {$this->table} c 
                      JOIN utilisateur u ON c.id_user = u.id 
                      JOIN publication p ON c.id_publication = p.id_publication
                      WHERE c.statut = 'pending' 
                      ORDER BY c.date_publication ASC 
                      LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get flagged comments (reports > 0)
     */
    public function findFlagged($limit = 50, $offset = 0) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE signalements > 0 
                      ORDER BY signalements DESC, date_publication DESC 
                      LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Increment like count
     */
    public function incrementNote() {
        $currentNote = $this->getNote();
        return $this->setNote($currentNote + 1);
    }

    /**
     * Increment report count
     */
    public function incrementSignalements() {
        $currentSignalements = $this->getSignalements();
        return $this->setSignalements($currentSignalements + 1);
    }

    /**
     * Approve comment
     */
    public function approve() {
        return $this->setStatut('approved');
    }

    /**
     * Reject comment
     */
    public function reject() {
        return $this->setStatut('rejected');
    }

    /**
     * Get comment with user details
     */
    public function getWithUser() {
        try {
            $id = $this->getId();
            if (!$id) {
                return null;
            }
            $query = "SELECT c.*, u.nom, u.prenom, u.email FROM {$this->table} c 
                      JOIN utilisateur u ON c.id_user = u.id 
                      WHERE c.id_commentaire = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
}
