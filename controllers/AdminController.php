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

        // Liste complète des consultations (avec patient et médecin)
        $stmt = $this->db->query("
            SELECT c.id_consultation, c.diagnostic, c.traitement, c.notes, c.date_creation,
                   r.date_rdv, r.heure_rdv, r.motif,
                   u_p.nom as patient_nom, u_p.prenom as patient_prenom,
                   u_m.nom as medecin_nom, u_m.prenom as medecin_prenom,
                   me.specialite
            FROM consultation c
            LEFT JOIN rendezvous r ON c.id_rdv = r.id_rdv
            LEFT JOIN patient pat ON r.id_patient = pat.id_patient
            LEFT JOIN utilisateur u_p ON pat.id_user = u_p.id_user
            LEFT JOIN medecin me ON r.id_medecin = me.id_medecin
            LEFT JOIN utilisateur u_m ON me.id_user = u_m.id_user
            ORDER BY c.date_creation DESC
        ");
        $listeConsultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Liste complète des suivis (avec patient et médecin)
        $stmt = $this->db->query("
            SELECT s.id_suivie, s.date_suivi, s.poids, s.tension, s.etat_general,
                   s.analyses_a_realiser, s.prochain_rdv, s.date_creation,
                   u_p.nom as patient_nom, u_p.prenom as patient_prenom,
                   u_m.nom as medecin_nom, u_m.prenom as medecin_prenom,
                   me.specialite
            FROM suivie s
            LEFT JOIN patient pat ON s.id_patient = pat.id_patient
            LEFT JOIN utilisateur u_p ON pat.id_user = u_p.id_user
            LEFT JOIN medecin me ON s.id_medecin = me.id_medecin
            LEFT JOIN utilisateur u_m ON me.id_user = u_m.id_user
            ORDER BY s.date_suivi DESC
        ");
        $listeSuivis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Liste des patients avec leur nombre de consultations
        $stmt = $this->db->query("
            SELECT u.nom, u.prenom, u.email,
                   COUNT(DISTINCT c.id_consultation) as nb_consultations,
                   COUNT(DISTINCT s.id_suivie) as nb_suivis
            FROM patient p
            JOIN utilisateur u ON p.id_user = u.id_user
            LEFT JOIN rendezvous r ON r.id_patient = p.id_patient
            LEFT JOIN consultation c ON c.id_rdv = r.id_rdv
            LEFT JOIN suivie s ON s.id_patient = p.id_patient
            GROUP BY p.id_patient, u.nom, u.prenom, u.email
            ORDER BY u.nom ASC
        ");
        $listePatients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/admin.php';
    }
}
?>
