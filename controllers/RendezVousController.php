<?php
require_once __DIR__ . '/../models/RendezVousModel.php';
require_once __DIR__ . '/../models/UtilisateurModel.php';
require_once __DIR__ . '/../models/GoogleCalendarModel.php';
require_once __DIR__ . '/../config/functions.php';

class RendezVousController {
    private $model;
    private $utilisateurModel;
    private $googleCalendarModel;
    
    public function __construct() {
        $this->model = new RendezVousModel();
        $this->utilisateurModel = new UtilisateurModel();
        $this->googleCalendarModel = new GoogleCalendarModel();
    }
    
    public function index() {
        return $this->model->readAll();
    }

    public function getByPatient($patientId) {
        return $this->model->readByPatient($patientId);
    }

    public function getByMedecin($medecinId) {
        return $this->model->readByMedecin($medecinId);
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
            if (!empty($data['add_google_calendar'])) {
                $googleResult = $this->addToGoogleCalendar($this->model->id_rdv, $data['redirect_to'] ?? 'views/frontoffice.php#consultation');
                $result = ['success' => true, 'message' => 'Rendez-vous cree avec succes', 'id' => $this->model->id_rdv];

                if (!empty($googleResult['requires_auth']) && !empty($googleResult['auth_url'])) {
                    $result['message'] .= '. Connectez Google Agenda pour terminer l ajout au calendrier.';
                    $result['google_auth_url'] = $googleResult['auth_url'];
                } elseif (!empty($googleResult['success'])) {
                    $result['message'] .= '. Rendez-vous ajoute a Google Agenda.';
                } else {
                    $result['message'] .= '. Google Agenda: ' . ($googleResult['message'] ?? 'ajout impossible');
                }

                return $result;
            }
            return ['success' => true, 'message' => 'Rendez-vous créé avec succès', 'id' => $this->model->id_rdv];
        }
        return ['success' => false, 'message' => 'Erreur lors de la création'];
    }
    
    public function addToGoogleCalendar($id, $redirectTo = 'views/frontoffice.php#consultation') {
        $this->model->id_rdv = $id;
        $rdv = $this->model->readOne();
        if (!$rdv) {
            return ['success' => false, 'message' => 'Rendez-vous introuvable'];
        }

        if (!$this->googleCalendarModel->isConfigured()) {
            return ['success' => false, 'message' => 'Google Calendar n est pas configure'];
        }

        if (!$this->googleCalendarModel->isConnected()) {
            $this->googleCalendarModel->setPendingAppointment($id, $redirectTo);
            return [
                'success' => false,
                'requires_auth' => true,
                'auth_url' => $this->googleCalendarModel->getAuthUrl(),
                'message' => 'Connexion Google Agenda requise',
            ];
        }

        return $this->googleCalendarModel->createAppointmentEvent($rdv);
    }

    public function getGoogleCalendarAuthUrl() {
        if (!$this->googleCalendarModel->isConfigured()) {
            return null;
        }

        return $this->googleCalendarModel->getAuthUrl();
    }

    public function handleGoogleCalendarCallback($code, $state) {
        $result = $this->googleCalendarModel->handleCallback($code, $state);
        if (!$result['success']) {
            return $result;
        }

        $pendingRdvId = $this->googleCalendarModel->getPendingAppointmentId();
        if ($pendingRdvId) {
            $calendarResult = $this->addToGoogleCalendar($pendingRdvId, $this->googleCalendarModel->getPendingRedirect());
            $this->googleCalendarModel->clearPendingAppointment();

            if (!empty($calendarResult['success'])) {
                return ['success' => true, 'message' => 'Google Agenda connecte et rendez-vous ajoute.'];
            }

            return ['success' => false, 'message' => 'Google connecte, mais ajout du rendez-vous impossible: ' . ($calendarResult['message'] ?? 'erreur inconnue')];
        }

        return $result;
    }

    public function getGoogleCalendarPendingRedirect() {
        return $this->googleCalendarModel->getPendingRedirect();
    }

    public function disconnectGoogleCalendar() {
        $this->googleCalendarModel->disconnect();
        return ['success' => true, 'message' => 'Google Agenda deconnecte.'];
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

    public function updateStatusForMedecin($id, $medecinId, $statut) {
        $currentRdv = $this->getOne($id);
        if (!$currentRdv) {
            return ['success' => false, 'message' => 'Rendez-vous introuvable'];
        }

        if ((int)($currentRdv['medecin_id'] ?? 0) !== (int)$medecinId) {
            return ['success' => false, 'message' => 'Ce rendez-vous n appartient pas a ce medecin'];
        }

        return $this->updateStatus($id, $statut);
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
