<?php
require_once __DIR__ . '/../models/DossierMedicalModel.php';
require_once __DIR__ . '/../config/functions.php';

class DossierMedicalController {
    private $model;
    
    public function __construct() {
        $this->model = new DossierMedicalModel();
    }
    
    public function index() {
        return $this->model->readAll();
    }
    
    public function create($data, $files = null) {
        $this->model->id_patient = sanitizeInput($data['id_patient']);
        $this->model->id_medecin = sanitizeInput($data['id_medecin']);
        $this->model->id_rdv = !empty($data['id_rdv']) ? sanitizeInput($data['id_rdv']) : null;
        $this->model->symptomes = sanitizeInput($data['symptomes']);
        $this->model->diagnostic = sanitizeInput($data['diagnostic']);
        $this->model->traitement = sanitizeInput($data['traitement']);
        $this->model->notes_medecin = sanitizeInput($data['notes_medecin']);
        
        if($this->model->create()) {
            return ['success' => true, 'message' => 'Dossier médical créé avec succès', 'id' => $this->model->id_dossier];
        }
        return ['success' => false, 'message' => 'Erreur lors de la création'];
    }
    
    public function update($id, $data) {
        $this->model->id_dossier = $id;
        $this->model->id_medecin = sanitizeInput($data['id_medecin']);
        $this->model->id_rdv = !empty($data['id_rdv']) ? sanitizeInput($data['id_rdv']) : null;
        $this->model->symptomes = sanitizeInput($data['symptomes']);
        $this->model->diagnostic = sanitizeInput($data['diagnostic']);
        $this->model->traitement = sanitizeInput($data['traitement']);
        $this->model->notes_medecin = sanitizeInput($data['notes_medecin']);
        
        if($this->model->update()) {
            return ['success' => true, 'message' => 'Dossier médical modifié avec succès'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la modification'];
    }
    
    public function delete($id) {
        $this->model->id_dossier = $id;
        if($this->model->delete()) {
            return ['success' => true, 'message' => 'Dossier médical supprimé avec succès'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la suppression'];
    }
    
    public function getOne($id) {
        $this->model->id_dossier = $id;
        return $this->model->readOne();
    }
    
    public function getStatistics() {
        return $this->model->getStatistics();
    }
    
    public function exportToPDF() {
        $data = $this->model->readAll();
        exportToPDF($data, 'Liste des dossiers médicaux', 'dossiers_medicaux_' . date('Y-m-d'));
    }
}
?>