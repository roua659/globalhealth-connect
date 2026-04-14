<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Model.php';

/**
 * Publication Model
 * Represents a medical publication/post
 */
class Publication extends Model {
    protected $table = 'publication';
    protected $fillable = ['id_publication', 'contenu', 'date_publication', 'url_video', 'url_image', 'id_medecin'];

    // ==================== GETTERS ====================

    /**
     * Get publication ID
     */
    public function getId() {
        return $this->getAttribute('id_publication');
    }

    /**
     * Get publication content
     */
    public function getContenu() {
        return $this->getAttribute('contenu');
    }

    /**
     * Get publication date
     */
    public function getDatePublication() {
        return $this->getAttribute('date_publication');
    }

    /**
     * Get video URL
     */
    public function getUrlVideo() {
        return $this->getAttribute('url_video');
    }

    /**
     * Get image URL
     */
    public function getUrlImage() {
        return $this->getAttribute('url_image');
    }

    /**
     * Get doctor ID
     */
    public function getIdMedecin() {
        return $this->getAttribute('id_medecin');
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
     * Set publication ID
     */
    public function setId($id) {
        return $this->setAttribute('id_publication', $id);
    }

    /**
     * Set publication content with validation
     */
    public function setContenu($contenu) {
        $contenu = trim($contenu);
        if (strlen($contenu) < 10) {
            throw new Exception('Content must be at least 10 characters');
        }
        return $this->setAttribute('contenu', $contenu);
    }

    /**
     * Set publication date
     */
    public function setDatePublication($date) {
        if (!strtotime($date)) {
            throw new Exception('Invalid date format');
        }
        return $this->setAttribute('date_publication', $date);
    }

    /**
     * Set video URL
     */
    public function setUrlVideo($url) {
        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid video URL format');
        }
        return $this->setAttribute('url_video', $url);
    }

    /**
     * Set image URL
     */
    public function setUrlImage($url) {
        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid image URL format');
        }
        return $this->setAttribute('url_image', $url);
    }

    /**
     * Set doctor ID
     */
    public function setIdMedecin($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new Exception('Invalid doctor ID');
        }
        return $this->setAttribute('id_medecin', $id);
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
     * Find publication by ID
     */
    public function findById($id) {
        return $this->findByIdColumn('id_publication', $id);
    }

    /**
     * Get all publications by doctor
     */
    public function findByMedecin($id_medecin, $limit = 10, $offset = 0) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id_medecin = :id_medecin 
                      ORDER BY date_publication DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get publication comments count
     */
    public function getCommentCount() {
        try {
            $idPub = $this->getId();
            if (!$idPub) {
                return 0;
            }
            $query = "SELECT COUNT(*) as count FROM commentaire WHERE id_publication = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['id' => $idPub]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get all comments for publication
     */
    public function getComments() {
        try {
            $idPub = $this->getId();
            if (!$idPub) {
                return [];
            }
            $query = "SELECT c.*, u.nom, u.prenom FROM commentaire c 
                      JOIN utilisateur u ON c.id_user = u.id 
                      WHERE c.id_publication = :id_publication 
                      ORDER BY c.date_publication DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['id_publication' => $idPub]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get approved comments only
     */
    public function getApprovedComments() {
        try {
            $idPub = $this->getId();
            if (!$idPub) {
                return [];
            }
            $query = "SELECT c.*, u.nom, u.prenom FROM commentaire c 
                      JOIN utilisateur u ON c.id_user = u.id 
                      WHERE c.id_publication = :id_publication AND c.statut = 'approved'
                      ORDER BY c.note DESC, c.date_publication DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['id_publication' => $idPub]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Search publications
     */
    public function search($keyword, $limit = 10, $offset = 0) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE contenu LIKE :keyword 
                      ORDER BY date_publication DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':keyword', "%{$keyword}%");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
