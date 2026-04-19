<?php
require_once __DIR__ . '/../models/RendezVousModel.php';
require_once __DIR__ . '/../models/UtilisateurModel.php';
require_once __DIR__ . '/../config/functions.php';

class RendezVousController {
    private $model;
    private $utilisateurModel;
    
    public function __construct() {
        $this->model = new RendezVousModel();
        $this->utilisateurModel = new UtilisateurModel();
    }
    
    public function index() {
        return $this->model->readAll();
    }

    public function getByPatient($patientId) {
        return $this->model->readByPatient($patientId);
    }
    
    public function getPatients() {
        return $this->utilisateurModel->getPatients();
    }
    
    public function getMedecins() {
        return $this->utilisateurModel->getMedecins();
    }
    
    public function create($data) {
        $this->model->date_rdv = sanitizeInput($data['date_rdv']);
        $this->model->heure_rdv = sanitizeInput($data['heure_rdv']);
        $this->model->motif = sanitizeInput($data['motif']);
        $this->model->statut = 'en_attente';
        $this->model->type_consultation = sanitizeInput($data['type_consultation']);
        $this->model->lien_visio = $data['type_consultation'] == 'video' ? generateVideoLink() : null;
        $this->model->id_patient = sanitizeInput($data['id_patient']);
        $this->model->id_medecin = sanitizeInput($data['id_medecin']);
        
        if($this->model->create()) {
            if($this->model->type_consultation == 'video' && $this->model->lien_visio) {
                $this->sendVideoLink($this->model->id_rdv);
            }
            return ['success' => true, 'message' => 'Rendez-vous créé avec succès', 'id' => $this->model->id_rdv];
        }
        return ['success' => false, 'message' => 'Erreur lors de la création'];
    }
    
    public function update($id, $data) {
        $this->model->id_rdv = $id;
        $currentRdv = $this->model->readOne();
        if (!$currentRdv) {
            return ['success' => false, 'message' => 'Rendez-vous introuvable'];
        }
        if (($currentRdv['statut'] ?? '') === 'confirme') {
            return ['success' => false, 'message' => 'Un rendez-vous confirme ne peut pas etre modifie'];
        }
        $this->model->date_rdv = sanitizeInput($data['date_rdv']);
        $this->model->heure_rdv = sanitizeInput($data['heure_rdv']);
        $this->model->motif = sanitizeInput($data['motif']);
        $this->model->statut = sanitizeInput($data['statut']);
        $this->model->type_consultation = sanitizeInput($data['type_consultation']);
        $this->model->id_patient = sanitizeInput($data['id_patient']);
        $this->model->id_medecin = sanitizeInput($data['id_medecin']);
        $this->model->lien_visio = $currentRdv['lien_visio'] ?? null;

        if($this->model->update()) {
            return ['success' => true, 'message' => 'Rendez-vous modifié avec succès'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la modification'];
    }
    
    public function delete($id) {
        $this->model->id_rdv = $id;
        $currentRdv = $this->model->readOne();
        if (!$currentRdv) {
            return ['success' => false, 'message' => 'Rendez-vous introuvable'];
        }
        if (($currentRdv['statut'] ?? '') === 'confirme') {
            return ['success' => false, 'message' => 'Un rendez-vous confirme ne peut pas etre supprime'];
        }
        if($this->model->delete()) {
            return ['success' => true, 'message' => 'Rendez-vous supprimé avec succès'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la suppression'];
    }
    
    public function updateStatus($id, $statut) {
        $allowedStatuses = ['en_attente', 'confirme', 'termine', 'annule'];
        if (!in_array($statut, $allowedStatuses, true)) {
            return ['success' => false, 'message' => 'Statut invalide'];
        }

        $this->model->id_rdv = $id;
        $this->model->statut = $statut;

        if ($this->model->updateStatus()) {
            return ['success' => true, 'message' => 'Statut du rendez-vous mis a jour avec succes'];
        }

        return ['success' => false, 'message' => 'Erreur lors de la mise a jour du statut'];
    }

    public function getOne($id) {
        $this->model->id_rdv = $id;
        return $this->model->readOne();
    }
    
    public function getStatistics() {
        return $this->model->getStatistics();
    }
    
    public function exportToPDF() {
        $data = $this->model->readAll();
        exportToPDF($data, 'Liste des rendez-vous', 'rendez_vous_' . date('Y-m-d'));
    }
    
    public function sendVideoLink($id) {
        $this->model->id_rdv = $id;
        $rdv = $this->model->readOne();
        if($rdv && $rdv['lien_visio']) {
            sendVideoConferenceLink(
                $rdv['patient_email'] ?? '',
                $rdv['patient_nom'] ?? 'Patient',
                $rdv['medecin_nom'] ?? 'Médecin',
                $rdv['lien_visio'],
                $rdv['date_rdv'] . ' à ' . $rdv['heure_rdv']
            );
            return true;
        }
        return false;
    }
    
    public function getUpcomingReminders() {
        return $this->model->getUpcomingReminders();
    }
}
?>
