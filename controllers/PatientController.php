<?php
require_once 'models/ConsultationModel.php';
require_once 'models/SuivieModel.php';
require_once 'config/database.php';

class PatientController {
    private $db;
    private $patientId;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->patientId = $_SESSION['patient_id'] ?? 0;
    }

    // ── Dashboard patient ──
    public function index() {
        $patientId = $this->patientId;

        // Stats : nb consultations
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM consultation c
            JOIN rendezvous r ON c.id_rdv = r.id_rdv
            WHERE r.id_patient = ?
        ");
        $stmt->execute([$patientId]);
        $nbConsultations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Stats : nb suivis
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM suivie WHERE id_patient = ?");
        $stmt->execute([$patientId]);
        $nbSuivis = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Stats : nb RDV
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM rendezvous WHERE id_patient = ?");
        $stmt->execute([$patientId]);
        $nbRdv = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Dernières consultations (3)
        $stmt = $this->db->prepare("
            SELECT c.id_consultation, c.diagnostic, c.traitement, c.notes, c.date_creation,
                   r.date_rdv, r.heure_rdv,
                   u.nom as medecin_nom, u.prenom as medecin_prenom,
                   m.specialite
            FROM consultation c
            JOIN rendezvous r ON c.id_rdv = r.id_rdv
            JOIN medecin m ON r.id_medecin = m.id_medecin
            JOIN utilisateur u ON m.id_user = u.id_user
            WHERE r.id_patient = ?
            ORDER BY c.date_creation DESC
            LIMIT 3
        ");
        $stmt->execute([$patientId]);
        $dernieresConsultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Dernier suivi
        $stmt = $this->db->prepare("
            SELECT s.*, u.nom as medecin_nom, u.prenom as medecin_prenom, m.specialite
            FROM suivie s
            JOIN medecin m ON s.id_medecin = m.id_medecin
            JOIN utilisateur u ON m.id_user = u.id_user
            WHERE s.id_patient = ?
            ORDER BY s.date_suivi DESC
            LIMIT 1
        ");
        $stmt->execute([$patientId]);
        $dernierSuivi = $stmt->fetch(PDO::FETCH_ASSOC);

        // Prochain RDV
        $stmt = $this->db->prepare("
            SELECT r.*, u.nom as medecin_nom, u.prenom as medecin_prenom, m.specialite
            FROM rendezvous r
            JOIN medecin m ON r.id_medecin = m.id_medecin
            JOIN utilisateur u ON m.id_user = u.id_user
            WHERE r.id_patient = ? AND r.date_rdv >= CURDATE()
            ORDER BY r.date_rdv ASC
            LIMIT 1
        ");
        $stmt->execute([$patientId]);
        $prochainRdv = $stmt->fetch(PDO::FETCH_ASSOC);

        require_once 'views/patient/dashboard.php';
    }

    // ── Mes consultations ──
    public function consultations() {
        $patientId = $this->patientId;

        $stmt = $this->db->prepare("
            SELECT c.id_consultation, c.diagnostic, c.traitement, c.notes, c.date_creation,
                   r.date_rdv, r.heure_rdv, r.motif,
                   u.nom as medecin_nom, u.prenom as medecin_prenom,
                   m.specialite
            FROM consultation c
            JOIN rendezvous r ON c.id_rdv = r.id_rdv
            JOIN medecin m ON r.id_medecin = m.id_medecin
            JOIN utilisateur u ON m.id_user = u.id_user
            WHERE r.id_patient = ?
            ORDER BY c.date_creation DESC
        ");
        $stmt->execute([$patientId]);
        $consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/patient/mes_consultations.php';
    }

    // ── Mes suivis ──
    public function suivis() {
        $patientId = $this->patientId;
        $message = '';
        $messageType = '';

        // Traitement de la mise à jour par le patient
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_suivi') {
            $id_suivie = intval($_POST['id_suivie'] ?? 0);
            $poids = !empty($_POST['poids']) ? floatval($_POST['poids']) : null;
            $tension = trim($_POST['tension'] ?? '');
            $resultats = trim($_POST['resultat_analyses'] ?? '');

            if ($id_suivie) {
                $stmt = $this->db->prepare("UPDATE suivie SET poids = ?, tension = ?, resultat_analyses = ? WHERE id_suivie = ? AND id_patient = ?");
                if ($stmt->execute([$poids, $tension, $resultats, $id_suivie, $patientId])) {
                    $message = "Données de suivi mises à jour avec succès !";
                    $messageType = 'success';
                } else {
                    $message = "Erreur lors de la mise à jour.";
                    $messageType = 'error';
                }
            }
        }

        $stmt = $this->db->prepare("
            SELECT s.*, u.nom as medecin_nom, u.prenom as medecin_prenom, m.specialite
            FROM suivie s
            JOIN medecin m ON s.id_medecin = m.id_medecin
            JOIN utilisateur u ON m.id_user = u.id_user
            WHERE s.id_patient = ?
            ORDER BY s.date_suivi DESC
        ");
        $stmt->execute([$patientId]);
        $suivis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/patient/mes_suivis.php';
    }
}
?>
