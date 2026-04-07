<?php
class LegacyController {
    
    // Stockage des données (similaire à votre localStorage)
    private $dataFile = __DIR__ . '/../../data/storage.json';
    
    public function __construct() {
        // Créer le dossier data s'il n'existe pas
        if (!file_exists(dirname($this->dataFile))) {
            mkdir(dirname($this->dataFile), 0777, true);
        }
        
        // Initialiser les données si fichier vide
        if (!file_exists($this->dataFile)) {
            $this->initData();
        }
    }
    
    private function initData() {
        $data = [
            'users' => [],
            'doctors' => [],
            'patients' => [],
            'appointments' => [],
            'reviews' => [],
            'payments' => [],
            'medical_records' => []
        ];
        file_put_contents($this->dataFile, json_encode($data));
    }
    
    private function getData() {
        $content = file_get_contents($this->dataFile);
        return json_decode($content, true);
    }
    
    private function saveData($data) {
        file_put_contents($this->dataFile, json_encode($data));
    }
    
    // ==================== AUTHENTIFICATION ====================
    public function login() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input['username'] === 'admin' && $input['password'] === '0000') {
            $_SESSION['user'] = ['username' => 'admin', 'role' => 'admin'];
            echo json_encode(['success' => true, 'redirect' => '/backoffice.html']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Identifiant incorrect']);
        }
    }
    
    public function logout() {
        session_destroy();
        echo json_encode(['success' => true]);
    }
    
    public function checkAuth() {
        header('Content-Type: application/json');
        echo json_encode(['loggedIn' => isset($_SESSION['user'])]);
    }
    
    // ==================== MÉDECINS ====================
    public function getDoctors() {
        header('Content-Type: application/json');
        $data = $this->getData();
        echo json_encode($data['doctors']);
    }
    
    public function addDoctor() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        $newDoctor = [
            'id' => time() . rand(1, 999),
            'name' => $input['name'],
            'specialty' => $input['specialty'],
            'email' => $input['email'],
            'phone' => $input['phone'] ?? '',
            'location' => $input['location'] ?? 'Paris',
            'price' => $input['price'] ?? 80,
            'rating' => 0,
            'image' => $input['image'] ?? 'https://randomuser.me/api/portraits/men/1.jpg',
            'consultations' => ['video', 'presentiel']
        ];
        
        $data['doctors'][] = $newDoctor;
        $this->saveData($data);
        echo json_encode(['success' => true, 'doctor' => $newDoctor]);
    }
    
    public function deleteDoctor() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        $data['doctors'] = array_filter($data['doctors'], function($d) use ($input) {
            return $d['id'] != $input['id'];
        });
        
        $this->saveData($data);
        echo json_encode(['success' => true]);
    }
    
    // ==================== PATIENTS ====================
    public function getPatients() {
        header('Content-Type: application/json');
        $data = $this->getData();
        echo json_encode($data['patients']);
    }
    
    public function addPatient() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        $newPatient = [
            'id' => time() . rand(1, 999),
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'] ?? '',
            'status' => 'active'
        ];
        
        $data['patients'][] = $newPatient;
        $this->saveData($data);
        echo json_encode(['success' => true, 'patient' => $newPatient]);
    }
    
    public function deletePatient() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        $data['patients'] = array_filter($data['patients'], function($p) use ($input) {
            return $p['id'] != $input['id'];
        });
        
        $this->saveData($data);
        echo json_encode(['success' => true]);
    }
    
    // ==================== RENDEZ-VOUS ====================
    public function submitAppointment() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        // Sélectionner un médecin aléatoire
        $doctors = $data['doctors'];
        $doctor = !empty($doctors) ? $doctors[array_rand($doctors)] : null;
        
        $newAppointment = [
            'id' => time() . rand(1, 999),
            'patient_name' => $input['patientName'],
            'patient_email' => $input['patientEmail'],
            'patient_phone' => $input['patientPhone'],
            'doctor_name' => $doctor ? $doctor['name'] : 'Dr. À venir',
            'doctor_id' => $doctor ? $doctor['id'] : null,
            'consultation_type' => $input['consultationType'],
            'symptoms' => $input['symptoms'],
            'appointment_date' => date('Y-m-d', strtotime('+7 days')),
            'status' => 'pending',
            'payment_status' => 'pending',
            'amount' => $doctor ? $doctor['price'] : 80,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $data['appointments'][] = $newAppointment;
        
        // Ajouter un paiement associé
        $data['payments'][] = [
            'id' => time() . rand(1, 999),
            'appointment_id' => $newAppointment['id'],
            'amount' => $newAppointment['amount'],
            'status' => 'pending'
        ];
        
        $this->saveData($data);
        echo json_encode(['success' => true, 'appointment' => $newAppointment]);
    }
    
    public function getAppointments() {
        header('Content-Type: application/json');
        $data = $this->getData();
        echo json_encode($data['appointments']);
    }
    
    public function confirmPayment() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        foreach ($data['appointments'] as &$apt) {
            if ($apt['id'] == $input['id']) {
                $apt['payment_status'] = 'paid';
                $apt['status'] = 'confirmed';
            }
        }
        
        foreach ($data['payments'] as &$pay) {
            if ($pay['appointment_id'] == $input['id']) {
                $pay['status'] = 'completed';
            }
        }
        
        $this->saveData($data);
        echo json_encode(['success' => true]);
    }
    
    // ==================== AVIS ====================
    public function submitReview() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        $newReview = [
            'id' => time() . rand(1, 999),
            'patient_name' => $input['patientName'],
            'doctor_id' => $input['doctorId'],
            'doctor_name' => $input['doctorName'],
            'rating' => $input['rating'],
            'comment' => $input['comment'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $data['reviews'][] = $newReview;
        $this->saveData($data);
        echo json_encode(['success' => true, 'review' => $newReview]);
    }
    
    public function getReviews() {
        header('Content-Type: application/json');
        $data = $this->getData();
        echo json_encode($data['reviews']);
    }
    
    public function approveReview() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        foreach ($data['reviews'] as &$r) {
            if ($r['id'] == $input['id']) {
                $r['status'] = 'approved';
            }
        }
        
        $this->saveData($data);
        echo json_encode(['success' => true]);
    }
    
    public function reportReview() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        foreach ($data['reviews'] as &$r) {
            if ($r['id'] == $input['id']) {
                $r['status'] = 'reported';
            }
        }
        
        $this->saveData($data);
        echo json_encode(['success' => true]);
    }
    
    public function deleteReview() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        $data['reviews'] = array_filter($data['reviews'], function($r) use ($input) {
            return $r['id'] != $input['id'];
        });
        
        $this->saveData($data);
        echo json_encode(['success' => true]);
    }
    
    public function notifyPatient() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Simuler l'envoi d'une notification
        echo json_encode([
            'success' => true,
            'message' => "Notification envoyée à {$input['patientName']}"
        ]);
    }
    
    // ==================== STATISTIQUES ====================
    public function getStats() {
        header('Content-Type: application/json');
        $data = $this->getData();
        
        $reviews = array_filter($data['reviews'], function($r) {
            return $r['status'] === 'approved';
        });
        
        $avgRating = 0;
        if (count($reviews) > 0) {
            $sum = array_sum(array_column($reviews, 'rating'));
            $avgRating = round($sum / count($reviews), 1);
        }
        
        echo json_encode([
            'totalDoctors' => count($data['doctors']),
            'totalPatients' => count($data['patients']),
            'totalAppointments' => count($data['appointments']),
            'totalReviews' => count($reviews),
            'avgRating' => $avgRating,
            'pendingReviews' => count(array_filter($data['reviews'], function($r) {
                return $r['status'] === 'pending';
            }))
        ]);
    }
    
    // ==================== DOSSIERS MÉDICAUX ====================
    public function getMedicalRecords() {
        header('Content-Type: application/json');
        $data = $this->getData();
        echo json_encode($data['medical_records']);
    }
    
    public function addMedicalRecord() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        $newRecord = [
            'id' => time() . rand(1, 999),
            'patient_id' => $input['patientId'],
            'patient_name' => $input['patientName'],
            'diagnostic' => $input['diagnostic'],
            'treatment' => $input['treatment'],
            'doctor_name' => $input['doctorName'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $data['medical_records'][] = $newRecord;
        $this->saveData($data);
        echo json_encode(['success' => true, 'record' => $newRecord]);
    }
    
    public function deleteMedicalRecord() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $data = $this->getData();
        
        $data['medical_records'] = array_filter($data['medical_records'], function($r) use ($input) {
            return $r['id'] != $input['id'];
        });
        
        $this->saveData($data);
        echo json_encode(['success' => true]);
    }
}