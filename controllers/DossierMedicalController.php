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

    public function getByPatient($patientId) {
        return $this->model->readByPatient($patientId);
    }
    
    public function create($data, $files = null) {
        $this->model->id_patient = sanitizeInput($data['id_patient']);
        $this->model->id_medecin = sanitizeInput($data['id_medecin']);
        $this->model->id_rdv = !empty($data['id_rdv']) ? sanitizeInput($data['id_rdv']) : null;
        $this->model->symptomes = sanitizeInput($data['symptomes']);
        $this->model->diagnostic = sanitizeInput($data['diagnostic']);
        $this->model->traitement = sanitizeInput($data['traitement']);
        $this->model->ordonnance_texte = sanitizeInput($data['ordonnance_texte'] ?? '');
        $this->model->ordonnance_fichier = null;
        $this->model->notes_medecin = sanitizeInput($data['notes_medecin']);

        if (!empty($files['ordonnance_file']['name'])) {
            $uploadResult = uploadFile($files['ordonnance_file']);
            if (!$uploadResult['success']) {
                return ['success' => false, 'message' => $uploadResult['message']];
            }
            $this->model->ordonnance_fichier = $uploadResult['filename'];
        }
        
        if($this->model->create()) {
            return ['success' => true, 'message' => 'Dossier medical cree avec succes', 'id' => $this->model->id_dossier];
        }
        return ['success' => false, 'message' => 'Erreur lors de la creation'];
    }
    
    public function update($id, $data, $files = null) {
        $this->model->id_dossier = $id;
        $existingDossier = $this->model->readOne();

        if (!$existingDossier) {
            return ['success' => false, 'message' => 'Dossier medical introuvable'];
        }

        $this->model->id_medecin = sanitizeInput($data['id_medecin']);
        $this->model->id_rdv = !empty($data['id_rdv']) ? sanitizeInput($data['id_rdv']) : null;
        $this->model->symptomes = sanitizeInput($data['symptomes']);
        $this->model->diagnostic = sanitizeInput($data['diagnostic']);
        $this->model->traitement = sanitizeInput($data['traitement']);
        $this->model->ordonnance_texte = sanitizeInput($data['ordonnance_texte'] ?? '');
        $this->model->ordonnance_fichier = $existingDossier['ordonnance_fichier'] ?? null;
        $this->model->notes_medecin = sanitizeInput($data['notes_medecin']);

        if (!empty($data['remove_ordonnance_file']) && $this->model->ordonnance_fichier) {
            deleteFile($this->model->ordonnance_fichier);
            $this->model->ordonnance_fichier = null;
        }

        if (!empty($files['ordonnance_file']['name'])) {
            $uploadResult = uploadFile($files['ordonnance_file']);
            if (!$uploadResult['success']) {
                return ['success' => false, 'message' => $uploadResult['message']];
            }

            if (!empty($existingDossier['ordonnance_fichier'])) {
                deleteFile($existingDossier['ordonnance_fichier']);
            }

            $this->model->ordonnance_fichier = $uploadResult['filename'];
        }
        
        if($this->model->update()) {
            return ['success' => true, 'message' => 'Dossier medical modifie avec succes'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la modification'];
    }
    
    public function delete($id) {
        $this->model->id_dossier = $id;
        $existingDossier = $this->model->readOne();

        if (!$existingDossier) {
            return ['success' => false, 'message' => 'Dossier medical introuvable'];
        }

        if (!empty($existingDossier['ordonnance_fichier'])) {
            deleteFile($existingDossier['ordonnance_fichier']);
        }

        if($this->model->delete()) {
            return ['success' => true, 'message' => 'Dossier medical supprime avec succes'];
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
        exportToPDF($data, 'Liste des dossiers medicaux', 'dossiers_medicaux_' . date('Y-m-d'));
    }
}
?>
