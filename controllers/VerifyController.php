<?php
require_once 'config/database.php';

class VerifyController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function consultation() {
        $id = $_GET['id'] ?? null;
        $data = null;
        $error = null;

        if ($id) {
            $stmt = $this->db->prepare("
                SELECT c.id_consultation, c.date_creation, 
                       u_p.nom as patient_nom, u_p.prenom as patient_prenom,
                       u_m.nom as medecin_nom, u_m.prenom as medecin_prenom,
                       r.date_rdv
                FROM consultation c
                JOIN rendezvous r ON c.id_rdv = r.id_rdv
                JOIN patient pat ON r.id_patient = pat.id_patient
                JOIN utilisateur u_p ON pat.id_user = u_p.id_user
                JOIN medecin m ON r.id_medecin = m.id_medecin
                JOIN utilisateur u_m ON m.id_user = u_m.id_user
                WHERE c.id_consultation = ?
            ");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                $error = "Document introuvable ou ID invalide.";
            }
        } else {
            $error = "ID de document manquant.";
        }

        require_once 'views/verify.php';
    }

    public function suivie() {
        $id = $_GET['id'] ?? null;
        $data = null;
        $error = null;

        if ($id) {
            $stmt = $this->db->prepare("
                SELECT s.id_suivie, s.date_suivi, 
                       u_p.nom as patient_nom, u_p.prenom as patient_prenom,
                       u_m.nom as medecin_nom, u_m.prenom as medecin_prenom
                FROM suivie s
                JOIN patient pat ON s.id_patient = pat.id_patient
                JOIN utilisateur u_p ON pat.id_user = u_p.id_user
                JOIN medecin m ON s.id_medecin = m.id_medecin
                JOIN utilisateur u_m ON m.id_user = u_m.id_user
                WHERE s.id_suivie = ?
            ");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                $error = "Document introuvable ou ID invalide.";
            }
        } else {
            $error = "ID de document manquant.";
        }

        require_once 'views/verify.php';
    }
}
