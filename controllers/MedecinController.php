<?php
require_once 'models/ConsultationModel.php';
require_once 'models/SuivieModel.php';
require_once 'config/database.php';

class MedecinController {
    private $db;
    private $medecinId;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->medecinId = $_SESSION['medecin_id'] ?? 0;
    }

    // ── Dashboard médecin ──
    public function index() {
        $medecinId = $this->medecinId;

        // Stats : consultations faites par ce médecin
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM consultation c
            JOIN rendezvous r ON c.id_rdv = r.id_rdv
            WHERE r.id_medecin = ?
        ");
        $stmt->execute([$medecinId]);
        $nbConsultations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Stats : suivis faits par ce médecin
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM suivie WHERE id_medecin = ?");
        $stmt->execute([$medecinId]);
        $nbSuivis = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Stats : patients distincts suivis
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT id_patient) as total FROM suivie WHERE id_medecin = ?");
        $stmt->execute([$medecinId]);
        $nbPatients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Dernières consultations réalisées par ce médecin (5)
        $stmt = $this->db->prepare("
            SELECT c.id_consultation, c.diagnostic, c.traitement, c.date_creation,
                   r.date_rdv, r.motif,
                   u.nom as patient_nom, u.prenom as patient_prenom
            FROM consultation c
            JOIN rendezvous r ON c.id_rdv = r.id_rdv
            JOIN patient pat ON r.id_patient = pat.id_patient
            JOIN utilisateur u ON pat.id_user = u.id_user
            WHERE r.id_medecin = ?
            ORDER BY c.date_creation DESC
            LIMIT 5
        ");
        $stmt->execute([$medecinId]);
        $dernieresConsultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Derniers suivis réalisés par ce médecin (5)
        $stmt = $this->db->prepare("
            SELECT s.id_suivie, s.date_suivi, s.poids, s.tension, s.etat_general,
                   u.nom as patient_nom, u.prenom as patient_prenom
            FROM suivie s
            JOIN patient pat ON s.id_patient = pat.id_patient
            JOIN utilisateur u ON pat.id_user = u.id_user
            WHERE s.id_medecin = ?
            ORDER BY s.date_suivi DESC
            LIMIT 5
        ");
        $stmt->execute([$medecinId]);
        $derniersSuivis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/medecin/dashboard.php';
    }

    // ── Page ajouter/gérer consultations ──
    public function consultation() {
        $medecinId = $this->medecinId;

        $message = '';
        $messageType = '';

        // Traitement du formulaire POST (ajout)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add') {
                $id_rdv     = intval($_POST['id_rdv'] ?? 0);
                $diagnostic = trim($_POST['diagnostic'] ?? '');
                $traitement = trim($_POST['traitement'] ?? '');
                $notes      = trim($_POST['notes'] ?? '');

                if ($id_rdv && $diagnostic && $traitement) {
                    $model = new ConsultationModel();
                    $model->id_rdv     = $id_rdv;
                    $model->diagnostic = $diagnostic;
                    $model->traitement = $traitement;
                    $model->notes      = $notes;

                    if ($model->create()) {
                        $message = "Consultation ajoutée avec succès !";
                        $messageType = 'success';
                    } else {
                        $message = "Erreur lors de l'ajout.";
                        $messageType = 'error';
                    }
                } else {
                    $message = "Veuillez remplir tous les champs obligatoires.";
                    $messageType = 'error';
                }
            }

            if ($action === 'delete') {
                $id = intval($_POST['id_consultation'] ?? 0);
                if ($id) {
                    $model = new ConsultationModel();
                    $model->id_consultation = $id;
                    if ($model->delete()) {
                        $message = "Consultation supprimée.";
                        $messageType = 'success';
                    }
                }
            }
        }

        // Rendez-vous de ce médecin (pour le select)
        $stmt = $this->db->prepare("
            SELECT r.id_rdv, r.date_rdv, r.heure_rdv, r.motif,
                   u.nom as patient_nom, u.prenom as patient_prenom
            FROM rendezvous r
            JOIN patient pat ON r.id_patient = pat.id_patient
            JOIN utilisateur u ON pat.id_user = u.id_user
            WHERE r.id_medecin = ?
            ORDER BY r.date_rdv DESC
        ");
        $stmt->execute([$medecinId]);
        $rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consultations réalisées par ce médecin
        $stmt = $this->db->prepare("
            SELECT c.*, r.date_rdv, r.heure_rdv, r.motif,
                   u.nom as patient_nom, u.prenom as patient_prenom
            FROM consultation c
            JOIN rendezvous r ON c.id_rdv = r.id_rdv
            JOIN patient pat ON r.id_patient = pat.id_patient
            JOIN utilisateur u ON pat.id_user = u.id_user
            WHERE r.id_medecin = ?
            ORDER BY c.date_creation DESC
        ");
        $stmt->execute([$medecinId]);
        $consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/medecin/consultation.php';
    }

    // ── Page ajouter/gérer suivis ──
    public function suivie() {
        $medecinId = $this->medecinId;

        $message = '';
        $messageType = '';

        // Traitement POST (ajout)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add') {
                $id_patient          = intval($_POST['id_patient'] ?? 0);
                $id_consultation     = intval($_POST['id_consultation'] ?? 0) ?: null;
                $date_suivi          = trim($_POST['date_suivi'] ?? '');
                $poids               = $_POST['poids'] !== '' ? floatval($_POST['poids']) : null;
                $tension             = trim($_POST['tension'] ?? '');
                $etat_general        = trim($_POST['etat_general'] ?? '');
                $analyses            = trim($_POST['analyses_a_realiser'] ?? '');
                $regime              = trim($_POST['regime_alimentaire'] ?? '');
                $activite            = trim($_POST['activite_physique'] ?? '');
                $prochain_rdv        = trim($_POST['prochain_rdv'] ?? '');

                if (!$id_patient || !$date_suivi || !$etat_general) {
                    $missing = [];
                    if (!$id_patient) $missing[] = "patient";
                    if (!$date_suivi) $missing[] = "date";
                    if (!$etat_general) $missing[] = "état général";
                    $message = "Veuillez remplir les champs obligatoires : " . implode(", ", $missing) . ".";
                    $messageType = 'error';
                } else {
                    $model = new SuivieModel();
                    $model->id_patient          = $id_patient;
                    $model->id_medecin          = $medecinId;
                    $model->id_consultation     = $id_consultation;
                    $model->date_suivi          = $date_suivi;
                    $model->poids               = $poids;
                    $model->tension             = $tension;
                    $model->etat_general        = $etat_general;
                    $model->analyses_a_realiser = $analyses;
                    $model->regime_alimentaire  = $regime;
                    $model->activite_physique   = $activite;
                    $model->prochain_rdv        = $prochain_rdv;

                    if ($model->create()) {
                        $message = "Suivi ajouté avec succès !";
                        $messageType = 'success';
                    } else {
                        $message = "Erreur lors de l'ajout en base de données.";
                        $messageType = 'error';
                    }
                }
            }

            if ($action === 'delete') {
                $id = intval($_POST['id_suivie'] ?? 0);
                if ($id) {
                    $model = new SuivieModel();
                    $model->id_suivie = $id;
                    if ($model->delete()) {
                        $message = "Suivi supprimé.";
                        $messageType = 'success';
                    }
                }
            }
        }

        // Patients de ce médecin
        $stmt = $this->db->prepare("
            SELECT DISTINCT pat.id_patient, u.nom, u.prenom
            FROM rendezvous r
            JOIN patient pat ON r.id_patient = pat.id_patient
            JOIN utilisateur u ON pat.id_user = u.id_user
            WHERE r.id_medecin = ?
            ORDER BY u.nom ASC
        ");
        $stmt->execute([$medecinId]);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consultations de ce médecin (pour lier un suivi à une consultation)
        $stmt = $this->db->prepare("
            SELECT c.id_consultation, c.diagnostic, r.date_rdv,
                   u.nom as patient_nom, u.prenom as patient_prenom
            FROM consultation c
            JOIN rendezvous r ON c.id_rdv = r.id_rdv
            JOIN patient pat ON r.id_patient = pat.id_patient
            JOIN utilisateur u ON pat.id_user = u.id_user
            WHERE r.id_medecin = ?
            ORDER BY r.date_rdv DESC
        ");
        $stmt->execute([$medecinId]);
        $consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Suivis réalisés par ce médecin
        $stmt = $this->db->prepare("
            SELECT s.*, u.nom as patient_nom, u.prenom as patient_prenom
            FROM suivie s
            JOIN patient pat ON s.id_patient = pat.id_patient
            JOIN utilisateur u ON pat.id_user = u.id_user
            WHERE s.id_medecin = ?
            ORDER BY s.date_suivi DESC
        ");
        $stmt->execute([$medecinId]);
        $suivis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/medecin/suivie.php';
    }
}
?>
