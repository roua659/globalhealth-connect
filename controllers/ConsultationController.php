<?php
require_once 'models/ConsultationModel.php';

class ConsultationController {
    private $model;

    public function __construct() {
        $this->model = new ConsultationModel();
    }

    // Afficher la page principale
    public function index() {
        $consultations = $this->model->readAll();
        $stats = $this->model->getStats();
        $rendezvous = $this->model->getAllRendezVous();
        require_once 'views/consultation.php';
    }

    // Récupérer une consultation (API)
    public function get($id) {
        header('Content-Type: application/json');
        $consultation = $this->model->readOne($id);
        echo json_encode($consultation);
    }

    // Lister toutes les consultations (API)
    public function list() {
        header('Content-Type: application/json');
        $consultations = $this->model->readAll();
        echo json_encode($consultations);
    }

    // Créer une consultation (API)
    public function create() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents("php://input"));
        
        $this->model->diagnostic = $data->diagnostic ?? '';
        $this->model->traitement = $data->traitement ?? '';
        $this->model->notes = $data->notes ?? '';
        $this->model->id_rdv = $data->id_rdv ?? '';
        
        if($this->model->create()) {
            echo json_encode(['success' => true, 'message' => 'Consultation créée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
        }
    }

    // Mettre à jour une consultation (API)
    public function update() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents("php://input"));
        
        $this->model->id_consultation = $data->id_consultation ?? '';
        $this->model->diagnostic = $data->diagnostic ?? '';
        $this->model->traitement = $data->traitement ?? '';
        $this->model->notes = $data->notes ?? '';
        $this->model->id_rdv = $data->id_rdv ?? '';
        
        if($this->model->update()) {
            echo json_encode(['success' => true, 'message' => 'Consultation modifiée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
        }
    }

    // Supprimer une consultation (API)
    public function delete($id) {
        header('Content-Type: application/json');
        
        $this->model->id_consultation = $id;
        
        if($this->model->delete()) {
            echo json_encode(['success' => true, 'message' => 'Consultation supprimée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    }
}
?>