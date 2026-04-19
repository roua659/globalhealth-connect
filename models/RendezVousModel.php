<?php
require_once __DIR__ . '/../config/database.php';

class RendezVousModel {
    private $conn;
    private $table_name = "rendezvous";
    
    public $id_rdv;
    public $date_rdv;
    public $heure_rdv;
    public $motif;
    public $statut;
    public $type_consultation;
    public $lien_visio;
    public $date_creation;
    public $id_patient;
    public $id_medecin;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function readAll() {
        $query = "SELECT r.*, 
                         CONCAT(up.nom, ' ', up.prenom) as patient_nom,
                         CONCAT(um.nom, ' ', um.prenom) as medecin_nom,
                         m.specialite
                  FROM " . $this->table_name . " r
                  LEFT JOIN patient p ON r.id_patient = p.id_patient
                  LEFT JOIN utilisateur up ON p.id_user = up.id_user
                  LEFT JOIN medecin m ON r.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur um ON m.id_user = um.id_user
                  ORDER BY r.date_rdv DESC, r.heure_rdv DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByPatient($patientId) {
        $query = "SELECT r.*, 
                         CONCAT(up.nom, ' ', up.prenom) as patient_nom,
                         CONCAT(um.nom, ' ', um.prenom) as medecin_nom,
                         m.specialite
                  FROM " . $this->table_name . " r
                  LEFT JOIN patient p ON r.id_patient = p.id_patient
                  LEFT JOIN utilisateur up ON p.id_user = up.id_user
                  LEFT JOIN medecin m ON r.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur um ON m.id_user = um.id_user
                  WHERE r.id_patient = :id_patient
                  ORDER BY r.date_rdv DESC, r.heure_rdv DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_patient", $patientId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function readOne() {
        $query = "SELECT r.*, 
                         p.id_patient as patient_id, up.nom as patient_nom, up.prenom as patient_prenom,
                         m.id_medecin as medecin_id, um.nom as medecin_nom, um.prenom as medecin_prenom
                  FROM " . $this->table_name . " r
                  LEFT JOIN patient p ON r.id_patient = p.id_patient
                  LEFT JOIN utilisateur up ON p.id_user = up.id_user
                  LEFT JOIN medecin m ON r.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur um ON m.id_user = um.id_user
                  WHERE r.id_rdv = :id_rdv";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_rdv", $this->id_rdv);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create() {
        $this->date_creation = date('Y-m-d H:i:s');
        
        $query = "INSERT INTO " . $this->table_name . " 
                  SET date_rdv=:date_rdv, heure_rdv=:heure_rdv, motif=:motif,
                      statut=:statut, type_consultation=:type_consultation,
                      lien_visio=:lien_visio, date_creation=:date_creation,
                      id_patient=:id_patient, id_medecin=:id_medecin";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":date_rdv", $this->date_rdv);
        $stmt->bindParam(":heure_rdv", $this->heure_rdv);
        $stmt->bindParam(":motif", $this->motif);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":type_consultation", $this->type_consultation);
        $stmt->bindParam(":lien_visio", $this->lien_visio);
        $stmt->bindParam(":date_creation", $this->date_creation);
        $stmt->bindParam(":id_patient", $this->id_patient);
        $stmt->bindParam(":id_medecin", $this->id_medecin);
        
        if($stmt->execute()) {
            $this->id_rdv = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET date_rdv=:date_rdv, heure_rdv=:heure_rdv, motif=:motif,
                      statut=:statut, type_consultation=:type_consultation,
                      lien_visio=:lien_visio, id_patient=:id_patient, id_medecin=:id_medecin
                  WHERE id_rdv = :id_rdv";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":date_rdv", $this->date_rdv);
        $stmt->bindParam(":heure_rdv", $this->heure_rdv);
        $stmt->bindParam(":motif", $this->motif);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":type_consultation", $this->type_consultation);
        $stmt->bindParam(":lien_visio", $this->lien_visio);
        $stmt->bindParam(":id_patient", $this->id_patient);
        $stmt->bindParam(":id_medecin", $this->id_medecin);
        $stmt->bindParam(":id_rdv", $this->id_rdv);
        
        return $stmt->execute();
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                  SET statut = :statut
                  WHERE id_rdv = :id_rdv";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":id_rdv", $this->id_rdv);

        return $stmt->execute();
    }
    
    public function delete() {
        $nullify = $this->conn->prepare("UPDATE dossier_medical SET id_rdv = NULL WHERE id_rdv = :id_rdv");
        $nullify->bindParam(":id_rdv", $this->id_rdv);
        $nullify->execute();

        $query = "DELETE FROM " . $this->table_name . " WHERE id_rdv = :id_rdv";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_rdv", $this->id_rdv);
        return $stmt->execute();
    }
    
    public function getStatistics() {
        $stats = [];
        
        // Total RDV
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // RDV par statut
        $query = "SELECT statut, COUNT(*) as count FROM " . $this->table_name . " GROUP BY statut";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // RDV par type
        $query = "SELECT type_consultation, COUNT(*) as count FROM " . $this->table_name . " GROUP BY type_consultation";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top médecins par nombre de rendez-vous
        $query = "SELECT CONCAT(um.nom, ' ', um.prenom) as nom, COUNT(*) as count
                  FROM " . $this->table_name . " r
                  JOIN medecin m ON r.id_medecin = m.id_medecin
                  JOIN utilisateur um ON m.id_user = um.id_user
                  GROUP BY r.id_medecin, um.nom, um.prenom
                  ORDER BY count DESC
                  LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_doctor'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // RDV par mois
        $query = "SELECT DATE_FORMAT(date_rdv, '%Y-%m') as mois, COUNT(*) as count 
                  FROM " . $this->table_name . " 
                  GROUP BY DATE_FORMAT(date_rdv, '%Y-%m') 
                  ORDER BY mois DESC LIMIT 6";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    public function getUpcomingReminders() {
        $now = date('Y-m-d H:i:s');
        $query = "SELECT r.*, 
                         CONCAT(up.nom, ' ', up.prenom) as patient_nom, up.email as patient_email,
                         CONCAT(um.nom, ' ', um.prenom) as medecin_nom
                  FROM " . $this->table_name . " r
                  LEFT JOIN patient p ON r.id_patient = p.id_patient
                  LEFT JOIN utilisateur up ON p.id_user = up.id_user
                  LEFT JOIN medecin m ON r.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur um ON m.id_user = um.id_user
                  WHERE CONCAT(r.date_rdv, ' ', r.heure_rdv) BETWEEN :now AND DATE_ADD(:now, INTERVAL 1 HOUR)
                  AND r.statut = 'confirme'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":now", $now);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
