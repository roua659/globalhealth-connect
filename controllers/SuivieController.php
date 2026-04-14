<?php
require_once 'models/SuivieModel.php';

class SuivieController {
    private $model;

    public function __construct() {
        $this->model = new SuivieModel();
    }

    // Afficher la page principale
    public function index() {
        $suivis = $this->model->readAll();
        $stats = $this->model->getStats();
        $patients = $this->model->getAllPatients();
        $medecins = $this->model->getAllMedecins();
        $consultations = $this->model->getAllConsultations();
        require_once 'views/suivie.php';
    }

    // Récupérer un suivi (API)
    public function get($id) {
        header('Content-Type: application/json');
        $suivi = $this->model->readOne($id);
        echo json_encode($suivi);
    }

    // Lister tous les suivis (API)
    public function list() {
        header('Content-Type: application/json');
        $suivis = $this->model->readAll();
        echo json_encode($suivis);
    }

    // Créer un suivi (API)
    public function create() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents("php://input"));
        
        $this->model->date_suivi = $data->date_suivi ?? '';
        $this->model->poids = $data->poids ?? null;
        $this->model->tension = $data->tension ?? '';
        $this->model->etat_general = $data->etat_general ?? '';
        $this->model->analyses_a_realiser = $data->analyses_a_realiser ?? '';
        $this->model->regime_alimentaire = $data->regime_alimentaire ?? '';
        $this->model->activite_physique = $data->activite_physique ?? '';
        $this->model->prochain_rdv = $data->prochain_rdv ?? '';
        $this->model->id_patient = $data->id_patient ?? '';
        $this->model->id_consultation = $data->id_consultation ?? '';
        $this->model->id_medecin = $data->id_medecin ?? '';
        
        if($this->model->create()) {
            echo json_encode(['success' => true, 'message' => 'Suivi créé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
        }
    }

    // Mettre à jour un suivi (API)
    public function update() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents("php://input"));
        
        $this->model->id_suivie = $data->id_suivie ?? '';
        $this->model->date_suivi = $data->date_suivi ?? '';
        $this->model->poids = $data->poids ?? null;
        $this->model->tension = $data->tension ?? '';
        $this->model->etat_general = $data->etat_general ?? '';
        $this->model->analyses_a_realiser = $data->analyses_a_realiser ?? '';
        $this->model->regime_alimentaire = $data->regime_alimentaire ?? '';
        $this->model->activite_physique = $data->activite_physique ?? '';
        $this->model->prochain_rdv = $data->prochain_rdv ?? '';
        $this->model->id_patient = $data->id_patient ?? '';
        $this->model->id_consultation = $data->id_consultation ?? '';
        $this->model->id_medecin = $data->id_medecin ?? '';
        
        if($this->model->update()) {
            echo json_encode(['success' => true, 'message' => 'Suivi modifié avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
        }
    }

    // Supprimer un suivi (API)
    public function delete($id) {
        header('Content-Type: application/json');
        
        $this->model->id_suivie = $id;
        
        if($this->model->delete()) {
            echo json_encode(['success' => true, 'message' => 'Suivi supprimé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    }
}
?>