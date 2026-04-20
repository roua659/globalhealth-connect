<?php
require_once 'config/database.php';

class SuivieModel {
    private $conn;
    private $table_name = "suivie";

    public $id_suivie;
    public $date_suivi;
    public $poids;
    public $tension;
    public $etat_general;
    public $analyses_a_realiser;
    public $regime_alimentaire;
    public $activite_physique;
    public $prochain_rdv;
    public $id_patient;
    public $id_consultation;
    public $id_medecin;
    public $date_creation;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Lire tous les suivis
    public function readAll() {
        $query = "SELECT s.*, 
                         p.nom as patient_nom, p.prenom as patient_prenom,
                         u.nom as medecin_nom, u.prenom as medecin_prenom,
                         c.diagnostic as consultation_diagnostic
                  FROM " . $this->table_name . " s
                  LEFT JOIN patient pat ON s.id_patient = pat.id_patient
                  LEFT JOIN utilisateur p ON pat.id_user = p.id_user
                  LEFT JOIN medecin m ON s.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur u ON m.id_user = u.id_user
                  LEFT JOIN consultation c ON s.id_consultation = c.id_consultation
                  ORDER BY s.date_suivi DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire un suivi par ID
    public function readOne($id) {
        $query = "SELECT s.*, 
                         p.nom as patient_nom, p.prenom as patient_prenom,
                         u.nom as medecin_nom, u.prenom as medecin_prenom,
                         c.diagnostic as consultation_diagnostic
                  FROM " . $this->table_name . " s
                  LEFT JOIN patient pat ON s.id_patient = pat.id_patient
                  LEFT JOIN utilisateur p ON pat.id_user = p.id_user
                  LEFT JOIN medecin m ON s.id_medecin = m.id_medecin
                  LEFT JOIN utilisateur u ON m.id_user = u.id_user
                  LEFT JOIN consultation c ON s.id_consultation = c.id_consultation
                  WHERE s.id_suivie = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un suivi
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET date_suivi = :date_suivi,
                      poids = :poids,
                      tension = :tension,
                      etat_general = :etat_general,
                      analyses_a_realiser = :analyses_a_realiser,
                      regime_alimentaire = :regime_alimentaire,
                      activite_physique = :activite_physique,
                      prochain_rdv = :prochain_rdv,
                      id_patient = :id_patient,
                      id_consultation = :id_consultation,
                      id_medecin = :id_medecin,
                      date_creation = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        $this->date_suivi = htmlspecialchars(strip_tags($this->date_suivi));
        $this->poids = htmlspecialchars(strip_tags($this->poids));
        $this->tension = htmlspecialchars(strip_tags($this->tension));
        $this->etat_general = htmlspecialchars(strip_tags($this->etat_general));
        $this->analyses_a_realiser = htmlspecialchars(strip_tags($this->analyses_a_realiser));
        $this->regime_alimentaire = htmlspecialchars(strip_tags($this->regime_alimentaire));
        $this->activite_physique = htmlspecialchars(strip_tags($this->activite_physique));
        $this->prochain_rdv = htmlspecialchars(strip_tags($this->prochain_rdv));
        $this->id_patient = htmlspecialchars(strip_tags($this->id_patient));
        $this->id_consultation = htmlspecialchars(strip_tags($this->id_consultation));
        $this->id_medecin = htmlspecialchars(strip_tags($this->id_medecin));
        
        $stmt->bindParam(':date_suivi', $this->date_suivi);
        $stmt->bindParam(':poids', $this->poids);
        $stmt->bindParam(':tension', $this->tension);
        $stmt->bindParam(':etat_general', $this->etat_general);
        $stmt->bindParam(':analyses_a_realiser', $this->analyses_a_realiser);
        $stmt->bindParam(':regime_alimentaire', $this->regime_alimentaire);
        $stmt->bindParam(':activite_physique', $this->activite_physique);
        $stmt->bindParam(':prochain_rdv', $this->prochain_rdv);
        $stmt->bindParam(':id_patient', $this->id_patient);
        $stmt->bindParam(':id_consultation', $this->id_consultation);
        $stmt->bindParam(':id_medecin', $this->id_medecin);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Mettre à jour un suivi
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET date_suivi = :date_suivi,
                      poids = :poids,
                      tension = :tension,
                      etat_general = :etat_general,
                      analyses_a_realiser = :analyses_a_realiser,
                      regime_alimentaire = :regime_alimentaire,
                      activite_physique = :activite_physique,
                      prochain_rdv = :prochain_rdv,
                      id_patient = :id_patient,
                      id_consultation = :id_consultation,
                      id_medecin = :id_medecin
                  WHERE id_suivie = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->date_suivi = htmlspecialchars(strip_tags($this->date_suivi));
        $this->poids = htmlspecialchars(strip_tags($this->poids));
        $this->tension = htmlspecialchars(strip_tags($this->tension));
        $this->etat_general = htmlspecialchars(strip_tags($this->etat_general));
        $this->analyses_a_realiser = htmlspecialchars(strip_tags($this->analyses_a_realiser));
        $this->regime_alimentaire = htmlspecialchars(strip_tags($this->regime_alimentaire));
        $this->activite_physique = htmlspecialchars(strip_tags($this->activite_physique));
        $this->prochain_rdv = htmlspecialchars(strip_tags($this->prochain_rdv));
        $this->id_patient = htmlspecialchars(strip_tags($this->id_patient));
        $this->id_consultation = htmlspecialchars(strip_tags($this->id_consultation));
        $this->id_medecin = htmlspecialchars(strip_tags($this->id_medecin));
        $this->id_suivie = htmlspecialchars(strip_tags($this->id_suivie));
        
        $stmt->bindParam(':date_suivi', $this->date_suivi);
        $stmt->bindParam(':poids', $this->poids);
        $stmt->bindParam(':tension', $this->tension);
        $stmt->bindParam(':etat_general', $this->etat_general);
        $stmt->bindParam(':analyses_a_realiser', $this->analyses_a_realiser);
        $stmt->bindParam(':regime_alimentaire', $this->regime_alimentaire);
        $stmt->bindParam(':activite_physique', $this->activite_physique);
        $stmt->bindParam(':prochain_rdv', $this->prochain_rdv);
        $stmt->bindParam(':id_patient', $this->id_patient);
        $stmt->bindParam(':id_consultation', $this->id_consultation);
        $stmt->bindParam(':id_medecin', $this->id_medecin);
        $stmt->bindParam(':id', $this->id_suivie);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer un suivi
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_suivie = :id";
        $stmt = $this->conn->prepare($query);
        $this->id_suivie = htmlspecialchars(strip_tags($this->id_suivie));
        $stmt->bindParam(':id', $this->id_suivie);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtenir tous les patients
    public function getAllPatients() {
        $query = "SELECT p.id_patient, u.nom, u.prenom, u.email
                  FROM patient p
                  LEFT JOIN utilisateur u ON p.id_user = u.id_user
                  ORDER BY u.nom ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtenir tous les médecins
    public function getAllMedecins() {
        $query = "SELECT m.id_medecin, u.nom, u.prenom, u.email, m.specialite
                  FROM medecin m
                  LEFT JOIN utilisateur u ON m.id_user = u.id_user
                  ORDER BY u.nom ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtenir toutes les consultations pour le select
    public function getAllConsultations() {
        $query = "SELECT c.id_consultation, c.diagnostic, r.date_rdv
                  FROM consultation c
                  LEFT JOIN rendezvous r ON c.id_rdv = r.id_rdv
                  ORDER BY c.date_creation DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtenir les statistiques
    public function getStats() {
        $stats = [];
        
        // Total suivis
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Moyenne poids
        $query = "SELECT AVG(poids) as moyenne_poids FROM " . $this->table_name . " WHERE poids IS NOT NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['moyenne_poids'] = round($stmt->fetch(PDO::FETCH_ASSOC)['moyenne_poids'], 1);
        
        return $stats;
    }

}
?>