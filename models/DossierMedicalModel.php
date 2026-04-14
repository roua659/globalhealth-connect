<?php
require_once __DIR__ . '/../config/database.php';

class DossierMedicalModel {
    private $conn;
    private $table_name = "dossier_medical";
    
    public $id_dossier;
    public $id_patient;
    public $id_medecin;
    public $id_rdv;
    public $symptomes;
    public $diagnostic;
    public $traitement;
    public $notes_medecin;
    public $date_creation;
    public $historique_modification;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function readAll() {
        $query = "SELECT d.*, 
                         CONCAT(up.nom, ' ', up.prenom) as patient_nom,
                         CONCAT(um.nom, ' ', um.prenom) as medecin_nom,
                         m.specialite,
                         r.date_rdv, r.heure_rdv
                  FROM " . $this->table_name . " d
                  LEFT JOIN patient p ON d.id_patient = p.id_patient
                  LEFT JOIN utilisateur up ON p.id_user = up.id_user
                  LEFT JOIN medecin m ON d.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur um ON m.id_user = um.id_user
                  LEFT JOIN rendezvous r ON d.id_rdv = r.id_rdv
                  ORDER BY d.date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function readOne() {
        $query = "SELECT d.*, 
                         CONCAT(up.nom, ' ', up.prenom) as patient_nom,
                         CONCAT(um.nom, ' ', um.prenom) as medecin_nom
                  FROM " . $this->table_name . " d
                  LEFT JOIN patient p ON d.id_patient = p.id_patient
                  LEFT JOIN utilisateur up ON p.id_user = up.id_user
                  LEFT JOIN medecin m ON d.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur um ON m.id_user = um.id_user
                  WHERE d.id_dossier = :id_dossier";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_dossier", $this->id_dossier);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create() {
        $this->date_creation = date('Y-m-d H:i:s');
        $this->historique_modification = "Créé le " . $this->date_creation;
        
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_patient=:id_patient, id_medecin=:id_medecin, id_rdv=:id_rdv,
                      symptomes=:symptomes, diagnostic=:diagnostic, traitement=:traitement,
                      notes_medecin=:notes_medecin, date_creation=:date_creation,
                      historique_modification=:historique_modification";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id_patient", $this->id_patient);
        $stmt->bindParam(":id_medecin", $this->id_medecin);
        $stmt->bindParam(":id_rdv", $this->id_rdv);
        $stmt->bindParam(":symptomes", $this->symptomes);
        $stmt->bindParam(":diagnostic", $this->diagnostic);
        $stmt->bindParam(":traitement", $this->traitement);
        $stmt->bindParam(":notes_medecin", $this->notes_medecin);
        $stmt->bindParam(":date_creation", $this->date_creation);
        $stmt->bindParam(":historique_modification", $this->historique_modification);
        
        if($stmt->execute()) {
            $this->id_dossier = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    public function update() {
        $this->historique_modification = date('Y-m-d H:i:s') . " - Modifié\n" . ($this->historique_modification ?? '');
        
        $query = "UPDATE " . $this->table_name . " 
                  SET id_medecin=:id_medecin, id_rdv=:id_rdv,
                      symptomes=:symptomes, diagnostic=:diagnostic, traitement=:traitement,
                      notes_medecin=:notes_medecin, historique_modification=:historique_modification
                  WHERE id_dossier = :id_dossier";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id_medecin", $this->id_medecin);
        $stmt->bindParam(":id_rdv", $this->id_rdv);
        $stmt->bindParam(":symptomes", $this->symptomes);
        $stmt->bindParam(":diagnostic", $this->diagnostic);
        $stmt->bindParam(":traitement", $this->traitement);
        $stmt->bindParam(":notes_medecin", $this->notes_medecin);
        $stmt->bindParam(":historique_modification", $this->historique_modification);
        $stmt->bindParam(":id_dossier", $this->id_dossier);
        
        return $stmt->execute();
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_dossier = :id_dossier";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_dossier", $this->id_dossier);
        return $stmt->execute();
    }
    
    public function getStatistics() {
        $stats = [];
        
        // Total dossiers
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Dossiers par médecin
        $query = "SELECT CONCAT(um.nom, ' ', um.prenom) as nom, COUNT(*) as count 
                  FROM " . $this->table_name . " d
                  JOIN medecin m ON d.id_medecin = m.id_medecin
                  JOIN utilisateur um ON m.id_user = um.id_user
                  GROUP BY d.id_medecin
                  ORDER BY count DESC LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_doctor'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
}
?>