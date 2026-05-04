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

        // Calcul des alertes critiques (Tension >= 140/90)
        $alertesCritiques = 0;
        $stmt = $this->db->prepare("SELECT tension FROM suivie WHERE id_medecin = ?");
        $stmt->execute([$medecinId]);
        $allTensions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($allTensions as $t) {
            if(!empty($t['tension'])) {
                $parts = explode('/', $t['tension']);
                if(count($parts) == 2) {
                    if((int)$parts[0] >= 140 || (int)$parts[1] >= 90) {
                        $alertesCritiques++;
                    }
                }
            }
        }

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

        // --- DONNÉES POUR LES GRAPHIQUES ---
        
        // 1. Répartition par Diagnostic (Top 5)
        $stmt = $this->db->prepare("
            SELECT diagnostic, COUNT(*) as nb
            FROM consultation c
            JOIN rendezvous r ON c.id_rdv = r.id_rdv
            WHERE r.id_medecin = ?
            GROUP BY diagnostic
            ORDER BY nb DESC
            LIMIT 5
        ");
        $stmt->execute([$medecinId]);
        $statsDiag = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Répartition de l'état des patients (basé sur les suivis)
        $stmt = $this->db->prepare("
            SELECT etat_general, COUNT(*) as nb 
            FROM suivie 
            WHERE id_medecin = ? 
            GROUP BY etat_general 
            ORDER BY nb DESC 
            LIMIT 5
        ");
        $stmt->execute([$medecinId]);
        $statsEtat = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

            if ($action === 'edit') {
                $id         = intval($_POST['id_consultation'] ?? 0);
                $id_rdv     = intval($_POST['id_rdv'] ?? 0);
                $diagnostic = trim($_POST['diagnostic'] ?? '');
                $traitement = trim($_POST['traitement'] ?? '');
                $notes      = trim($_POST['notes'] ?? '');

                if ($id && $id_rdv && $diagnostic && $traitement) {
                    $model = new ConsultationModel();
                    $model->id_consultation = $id;
                    $model->id_rdv         = $id_rdv;
                    $model->diagnostic     = $diagnostic;
                    $model->traitement     = $traitement;
                    $model->notes          = $notes;

                    if ($model->update()) {
                        $message = "Consultation mise à jour !";
                        $messageType = 'success';
                    } else {
                        $message = "Erreur lors de la mise à jour.";
                        $messageType = 'error';
                    }
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

        // Récupérer la signature du médecin
        $stmt = $this->db->prepare("SELECT signature FROM medecin WHERE id_medecin = ?");
        $stmt->execute([$medecinId]);
        $signature = $stmt->fetch(PDO::FETCH_ASSOC)['signature'] ?? null;

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
                $id_consultation     = intval($_POST['id_consultation'] ?? 0);
                $date_suivi          = trim($_POST['date_suivi'] ?? '');
                $poids               = $_POST['poids'] !== '' ? floatval($_POST['poids']) : null;
                $tension             = trim($_POST['tension'] ?? '');
                $etat_general        = trim($_POST['etat_general'] ?? '');
                $analyses            = trim($_POST['analyses_a_realiser'] ?? '');
                $regime              = trim($_POST['regime_alimentaire'] ?? '');
                $activite            = trim($_POST['activite_physique'] ?? '');
                $prochain_rdv        = trim($_POST['prochain_rdv'] ?? '');

                // Validation serveur
                $errors = [];
                if (!$id_consultation) $errors[] = "La consultation est obligatoire.";
                if (empty($date_suivi)) $errors[] = "La date du suivi est obligatoire.";
                if (empty($etat_general)) $errors[] = "L'état général doit être renseigné.";
                
                // Vérification format date
                if (!empty($date_suivi) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_suivi)) {
                    $errors[] = "Format de date invalide (AAAA-MM-JJ attendu).";
                }

                // Vérification poids
                if ($poids !== null && ($poids <= 0 || $poids > 500)) {
                    $errors[] = "Le poids doit être compris entre 0 et 500 kg.";
                }

                if (!empty($errors)) {
                    $message = implode(" | ", $errors);
                    $messageType = 'error';
                } else {
                    // Récupérer le patient lié à la consultation
                    $stmt = $this->db->prepare("SELECT r.id_patient FROM consultation c JOIN rendezvous r ON c.id_rdv = r.id_rdv WHERE c.id_consultation = ?");
                    $stmt->execute([$id_consultation]);
                    $res = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($res) {
                        $model = new SuivieModel();
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
                            $message = "Suivi ajouté avec succès (lié à la consultation N°$id_consultation) !";
                            $messageType = 'success';
                        } else {
                            $message = "Erreur technique lors de l'enregistrement.";
                            $messageType = 'error';
                        }
                    } else {
                        $message = "L'ID de consultation sélectionné est invalide ou n'existe pas.";
                        $messageType = 'error';
                    }
                }
            }

            if ($action === 'edit') {
                $id_suivie           = intval($_POST['id_suivie'] ?? 0);
                $id_consultation     = intval($_POST['id_consultation'] ?? 0);
                $date_suivi          = trim($_POST['date_suivi'] ?? '');
                $poids               = $_POST['poids'] !== '' ? floatval($_POST['poids']) : null;
                $tension             = trim($_POST['tension'] ?? '');
                $etat_general        = trim($_POST['etat_general'] ?? '');
                $analyses            = trim($_POST['analyses_a_realiser'] ?? '');
                $regime              = trim($_POST['regime_alimentaire'] ?? '');
                $activite            = trim($_POST['activite_physique'] ?? '');
                $prochain_rdv        = trim($_POST['prochain_rdv'] ?? '');

                if ($id_suivie && $id_consultation && $date_suivi) {
                    $stmt = $this->db->prepare("SELECT r.id_patient FROM consultation c JOIN rendezvous r ON c.id_rdv = r.id_rdv WHERE c.id_consultation = ?");
                    $stmt->execute([$id_consultation]);
                    $res = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($res) {
                        $model = new SuivieModel();
                        $model->id_suivie           = $id_suivie;
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

                        if ($model->update()) {
                            $message = "Suivi mis à jour !";
                            $messageType = 'success';
                        } else {
                            $message = "Erreur lors de la mise à jour.";
                            $messageType = 'error';
                        }
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

        // Récupérer la signature du médecin
        $stmt = $this->db->prepare("SELECT signature FROM medecin WHERE id_medecin = ?");
        $stmt->execute([$medecinId]);
        $signature = $stmt->fetch(PDO::FETCH_ASSOC)['signature'] ?? null;

        require_once 'views/medecin/suivie.php';
    }

    // ── Profil & Signature ──
    public function profile() {
        $medecinId = $this->medecinId;
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['signature'])) {
            $targetDir = "uploads/signatures/";
            $fileExtension = strtolower(pathinfo($_FILES["signature"]["name"], PATHINFO_EXTENSION));
            $newFileName = "sig_" . $medecinId . "_" . time() . "." . $fileExtension;
            $targetFile = $targetDir . $newFileName;

            // Validation simple
            $check = getimagesize($_FILES["signature"]["tmp_name"]);
            if($check !== false) {
                if (move_uploaded_file($_FILES["signature"]["tmp_name"], $targetFile)) {
                    // Mettre à jour la BDD
                    $stmt = $this->db->prepare("UPDATE medecin SET signature = ? WHERE id_medecin = ?");
                    if ($stmt->execute([$newFileName, $medecinId])) {
                        $message = "Signature mise à jour avec succès !";
                        $messageType = 'success';
                    } else {
                        $message = "Erreur lors de la mise à jour en base de données.";
                        $messageType = 'error';
                    }
                } else {
                    $message = "Erreur lors de l'upload du fichier.";
                    $messageType = 'error';
                }
            } else {
                $message = "Le fichier n'est pas une image valide.";
                $messageType = 'error';
            }
        }

        // Récupérer les infos du médecin
        $stmt = $this->db->prepare("
            SELECT m.*, u.nom, u.prenom, u.email 
            FROM medecin m 
            JOIN utilisateur u ON m.id_user = u.id_user 
            WHERE m.id_medecin = ?
        ");
        $stmt->execute([$medecinId]);
        $medecin = $stmt->fetch(PDO::FETCH_ASSOC);

        require_once 'views/medecin/profile.php';
    }
}
?>
