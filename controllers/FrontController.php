<?php
require_once 'config/database.php';
require_once 'models/ConsultationModel.php';
require_once 'models/SuivieModel.php';

class FrontController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function index() {
        $message     = '';
        $messageType = '';
        $medecinId   = $_SESSION['medecin_id'] ?? 0;
        $isMedecin   = isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'medecin';

        // ── Traitement POST ──────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

            // Créer une consultation (médecin seulement)
            if ($_POST['action'] === 'create_consultation') {
                if ($isMedecin) {
                    $id_rdv     = intval($_POST['id_rdv']     ?? 0);
                    $diagnostic = trim($_POST['diagnostic']   ?? '');
                    $traitement = trim($_POST['traitement']   ?? '');
                    $notes      = trim($_POST['notes']        ?? '');

                    if ($id_rdv && $diagnostic && $traitement) {
                        $model             = new ConsultationModel();
                        $model->id_rdv     = $id_rdv;
                        $model->diagnostic = $diagnostic;
                        $model->traitement = $traitement;
                        $model->notes      = $notes;

                        if ($model->create()) {
                            $message     = "✅ Consultation créée avec succès !";
                            $messageType = 'success';
                        } else {
                            $message     = "❌ Erreur lors de la création de la consultation.";
                            $messageType = 'error';
                        }
                    } else {
                        $message     = "⚠️ Veuillez remplir tous les champs obligatoires (rendez-vous, diagnostic, traitement).";
                        $messageType = 'error';
                    }
                } else {
                    $message     = "⛔ Accès réservé aux médecins.";
                    $messageType = 'error';
                }
            }

            // Créer un suivi lié à une consultation (médecin seulement)
            if ($_POST['action'] === 'create_suivie') {
                if ($isMedecin) {
                    $id_consultation = intval($_POST['id_consultation'] ?? 0);
                    $date_suivi      = trim($_POST['date_suivi']        ?? '');
                    $poids           = (isset($_POST['poids']) && $_POST['poids'] !== '') ? floatval($_POST['poids']) : null;
                    $tension         = trim($_POST['tension']           ?? '');
                    $etat_general    = trim($_POST['etat_general']      ?? '');
                    $analyses        = trim($_POST['analyses_a_realiser']?? '');
                    $regime          = trim($_POST['regime_alimentaire'] ?? '');
                    $activite        = trim($_POST['activite_physique'] ?? '');
                    $prochain_rdv    = trim($_POST['prochain_rdv']      ?? '');

                    if ($id_consultation && $date_suivi && $etat_general) {
                        // Récupérer automatiquement l'ID du patient lié à cette consultation
                        $stmt = $this->db->prepare("
                            SELECT r.id_patient 
                            FROM consultation c 
                            JOIN rendezvous r ON c.id_rdv = r.id_rdv 
                            WHERE c.id_consultation = ?
                        ");
                        $stmt->execute([$id_consultation]);
                        $res = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($res) {
                            $model                      = new SuivieModel();
                            $model->id_patient          = $res['id_patient'];
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
                                $message     = "✅ Suivi créé avec succès pour le patient associé !";
                                $messageType = 'success';
                            } else {
                                $message     = "❌ Erreur lors de la création du suivi.";
                                $messageType = 'error';
                            }
                        } else {
                            $message = "❌ Consultation introuvable.";
                            $messageType = 'error';
                        }
                    } else {
                        $message     = "⚠️ Veuillez sélectionner une consultation, une date et l'état général.";
                        $messageType = 'error';
                    }
                } else {
                    $message     = "⛔ Accès réservé aux médecins.";
                    $messageType = 'error';
                }
            }
        }

        // ── Données publiques: Consultations avec leur nb de suivis (JOIN) ──
        try {
            $stmt = $this->db->query("
                SELECT c.id_consultation, c.diagnostic, c.traitement, c.date_creation,
                       u_p.nom AS patient_nom,  u_p.prenom AS patient_prenom,
                       u_m.nom AS medecin_nom,  u_m.prenom AS medecin_prenom,
                       COUNT(s.id_suivie) AS nb_suivis
                FROM consultation c
                LEFT JOIN rendezvous   r    ON c.id_rdv          = r.id_rdv
                LEFT JOIN patient      pat  ON r.id_patient       = pat.id_patient
                LEFT JOIN utilisateur  u_p  ON pat.id_user        = u_p.id_user
                LEFT JOIN medecin      me   ON r.id_medecin       = me.id_medecin
                LEFT JOIN utilisateur  u_m  ON me.id_user         = u_m.id_user
                LEFT JOIN suivie       s    ON s.id_consultation  = c.id_consultation
                GROUP BY c.id_consultation, c.diagnostic, c.traitement, c.date_creation,
                         u_p.nom, u_p.prenom, u_m.nom, u_m.prenom
                ORDER BY c.date_creation DESC
                LIMIT 10
            ");
            $consultations = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            $consultations = [];
        }

        // ── Données publiques: Suivis liés à leurs consultations (INNER JOIN) ──
        try {
            $stmt = $this->db->query("
                SELECT s.id_suivie, s.date_suivi, s.etat_general, s.poids, s.tension, s.prochain_rdv,
                       c.id_consultation, c.diagnostic AS consultation_diagnostic, c.date_creation AS consultation_date,
                       u_p.nom AS patient_nom,  u_p.prenom AS patient_prenom,
                       u_m.nom AS medecin_nom,  u_m.prenom AS medecin_prenom
                FROM suivie s
                INNER JOIN consultation  c    ON s.id_consultation = c.id_consultation
                LEFT JOIN  patient       pat  ON s.id_patient      = pat.id_patient
                LEFT JOIN  utilisateur   u_p  ON pat.id_user       = u_p.id_user
                LEFT JOIN  medecin       me   ON s.id_medecin      = me.id_medecin
                LEFT JOIN  utilisateur   u_m  ON me.id_user        = u_m.id_user
                ORDER BY s.date_suivi DESC
                LIMIT 10
            ");
            $suivis = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            $suivis = [];
        }

        // ── Données pour formulaires médecin ──
        $rendezvous             = [];
        $consultationsForSuivie = [];
        $patients               = [];

        if ($isMedecin && $medecinId) {
            // Rendez-vous du médecin (pour formulaire consultation)
            $stmt = $this->db->prepare("
                SELECT r.id_rdv, r.date_rdv, r.heure_rdv, r.motif,
                       u.nom AS patient_nom, u.prenom AS patient_prenom
                FROM rendezvous r
                JOIN patient      pat ON r.id_patient = pat.id_patient
                JOIN utilisateur  u   ON pat.id_user  = u.id_user
                WHERE r.id_medecin = ?
                ORDER BY r.date_rdv DESC
            ");
            $stmt->execute([$medecinId]);
            $rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Consultations du médecin (pour le select du formulaire suivie — JOIN FK)
            $stmt = $this->db->prepare("
                SELECT c.id_consultation, c.diagnostic, r.date_rdv,
                       u.nom AS patient_nom, u.prenom AS patient_prenom,
                       pat.id_patient
                FROM consultation c
                JOIN rendezvous  r   ON c.id_rdv       = r.id_rdv
                JOIN patient     pat ON r.id_patient    = pat.id_patient
                JOIN utilisateur u   ON pat.id_user     = u.id_user
                WHERE r.id_medecin = ?
                ORDER BY r.date_rdv DESC
            ");
            $stmt->execute([$medecinId]);
            $consultationsForSuivie = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Patients du médecin (pour formulaire suivie)
            $stmt = $this->db->prepare("
                SELECT DISTINCT pat.id_patient, u.nom, u.prenom
                FROM rendezvous  r
                JOIN patient     pat ON r.id_patient = pat.id_patient
                JOIN utilisateur u   ON pat.id_user  = u.id_user
                WHERE r.id_medecin = ?
                ORDER BY u.nom ASC
            ");
            $stmt->execute([$medecinId]);
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require_once 'views/front.php';
    }
}
?>
