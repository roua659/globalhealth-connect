<?php
require_once 'models/ConsultationModel.php';
require_once 'models/SuivieModel.php';
require_once 'config/database.php';

class AdminController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function index() {
        $consultationModel = new ConsultationModel();
        $statsConsultation = $consultationModel->getStats();

        $suivieModel = new SuivieModel();
        $statsSuivis = $suivieModel->getStats();

        // Nombre de patients
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM patient");
        $statsPatients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Nombre de médecins
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM medecin");
        $statsMedecins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Consultations des 6 derniers mois (pour graphique)
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(date_creation, '%b %Y') as mois,
                   COUNT(*) as total
            FROM consultation
            GROUP BY DATE_FORMAT(date_creation, '%Y-%m')
            ORDER BY date_creation DESC
            LIMIT 6
        ");
        $consultationsMois = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

        // Dernières consultations (5)
        $stmt = $this->db->query("
            SELECT c.id_consultation, c.diagnostic, c.date_creation,
                   u_p.nom as patient_nom, u_p.prenom as patient_prenom,
                   u_m.nom as medecin_nom, u_m.prenom as medecin_prenom
            FROM consultation c
            LEFT JOIN rendezvous r ON c.id_rdv = r.id_rdv
            LEFT JOIN patient pat ON r.id_patient = pat.id_patient
            LEFT JOIN utilisateur u_p ON pat.id_user = u_p.id_user
            LEFT JOIN medecin m ON r.id_medecin = m.id_medecin
            LEFT JOIN utilisateur u_m ON m.id_user = u_m.id_user
            ORDER BY c.date_creation DESC
            LIMIT 5
        ");
        $dernieresConsultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Derniers suivis (5)
        $stmt = $this->db->query("
            SELECT s.id_suivie, s.date_suivi, s.etat_general, s.poids,
                   u_p.nom as patient_nom, u_p.prenom as patient_prenom,
                   u_m.nom as medecin_nom, u_m.prenom as medecin_prenom
            FROM suivie s
            LEFT JOIN patient pat ON s.id_patient = pat.id_patient
            LEFT JOIN utilisateur u_p ON pat.id_user = u_p.id_user
            LEFT JOIN medecin m ON s.id_medecin = m.id_medecin
            LEFT JOIN utilisateur u_m ON m.id_user = u_m.id_user
            ORDER BY s.date_suivi DESC
            LIMIT 5
        ");
        $derniersSuivis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Liste des médecins
        $stmt = $this->db->query("
            SELECT m.*, u.nom, u.prenom, u.email
            FROM medecin m
            JOIN utilisateur u ON m.id_user = u.id_user
            ORDER BY u.nom ASC
        ");
        $listeMedecins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Liste des administrateurs
        $stmt = $this->db->query("
            SELECT u.id_user, u.nom, u.prenom, u.email
            FROM utilisateur u
            JOIN role r ON u.id_role = r.id_role
            WHERE r.type_role = 'admin'
            ORDER BY u.nom ASC
        ");
        $listeAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/admin.php';
    }
}
?>
