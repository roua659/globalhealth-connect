<?php
require_once 'config/database.php';

class ConsultationModel {
    private $conn;
    private $table_name = "consultation";

    public $id_consultation;
    public $diagnostic;
    public $traitement;
    public $notes;
    public $id_rdv;
    public $date_creation;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lire toutes les consultations
    public function readAll() {
        $query = "SELECT c.*, r.date_rdv, r.heure_rdv, 
                         p.nom as patient_nom, p.prenom as patient_prenom,
                         m.id_medecin, u.nom as medecin_nom, u.prenom as medecin_prenom
                  FROM " . $this->table_name . " c
                  LEFT JOIN rendezvous r ON c.id_rdv = r.id_rdv
                  LEFT JOIN patient pat ON r.id_patient = pat.id_patient
                  LEFT JOIN utilisateur p ON pat.id_user = p.id_user
                  LEFT JOIN medecin m ON r.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur u ON m.id_user = u.id_user
                  ORDER BY c.date_creation DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire une consultation par ID
    public function readOne($id) {
        $query = "SELECT c.*, r.date_rdv, r.heure_rdv, r.motif,
                         p.nom as patient_nom, p.prenom as patient_prenom,
                         u.nom as medecin_nom, u.prenom as medecin_prenom
                  FROM " . $this->table_name . " c
                  LEFT JOIN rendezvous r ON c.id_rdv = r.id_rdv
                  LEFT JOIN patient pat ON r.id_patient = pat.id_patient
                  LEFT JOIN utilisateur p ON pat.id_user = p.id_user
                  LEFT JOIN medecin m ON r.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur u ON m.id_user = u.id_user
                  WHERE c.id_consultation = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer une consultation
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET diagnostic = :diagnostic,
                      traitement = :traitement,
                      notes = :notes,
                      id_rdv = :id_rdv,
                      date_creation = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        $this->diagnostic = htmlspecialchars(strip_tags($this->diagnostic));
        $this->traitement = htmlspecialchars(strip_tags($this->traitement));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->id_rdv = htmlspecialchars(strip_tags($this->id_rdv));
        
        $stmt->bindParam(':diagnostic', $this->diagnostic);
        $stmt->bindParam(':traitement', $this->traitement);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':id_rdv', $this->id_rdv);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Mettre à jour une consultation
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET diagnostic = :diagnostic,
                      traitement = :traitement,
                      notes = :notes,
                      id_rdv = :id_rdv
                  WHERE id_consultation = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->diagnostic = htmlspecialchars(strip_tags($this->diagnostic));
        $this->traitement = htmlspecialchars(strip_tags($this->traitement));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->id_rdv = htmlspecialchars(strip_tags($this->id_rdv));
        $this->id_consultation = htmlspecialchars(strip_tags($this->id_consultation));
        
        $stmt->bindParam(':diagnostic', $this->diagnostic);
        $stmt->bindParam(':traitement', $this->traitement);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':id_rdv', $this->id_rdv);
        $stmt->bindParam(':id', $this->id_consultation);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer une consultation
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_consultation = :id";
        $stmt = $this->conn->prepare($query);
        $this->id_consultation = htmlspecialchars(strip_tags($this->id_consultation));
        $stmt->bindParam(':id', $this->id_consultation);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtenir tous les rendez-vous pour le select
    public function getAllRendezVous() {
        $query = "SELECT r.id_rdv, r.date_rdv, r.heure_rdv, 
                         p.nom as patient_nom, p.prenom as patient_prenom
                  FROM rendezvous r
                  LEFT JOIN patient pat ON r.id_patient = pat.id_patient
                  LEFT JOIN utilisateur p ON pat.id_user = p.id_user
                  ORDER BY r.date_rdv DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtenir les statistiques
    public function getStats() {
        $stats = [];
        
        // Total consultations
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Consultations par mois
        $query = "SELECT DATE_FORMAT(date_creation, '%Y-%m') as mois, COUNT(*) as count 
                  FROM " . $this->table_name . " 
                  GROUP BY DATE_FORMAT(date_creation, '%Y-%m')
                  ORDER BY mois DESC LIMIT 6";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['par_mois'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
}
?>