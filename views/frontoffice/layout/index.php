<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Publication.php';

// Fetch only approved publications for frontoffice
$publications = [];
try {
    $pdo = config::getConnexion();
    try {
        $stmt = $pdo->prepare("SELECT * FROM publication WHERE statut = 'approved' ORDER BY date_publication DESC LIMIT 20");
        $stmt->execute();
        $publications = $stmt->fetchAll();
    } catch (Exception $e) {
        // Fallback if the statut column does not exist yet
        $pub = new Publication();
        $publications = $pub->getAll(20, 0);
    }
} catch (Exception $e) {
    $publications = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth Connect - Plateforme Médicale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --medical-white: #ffffff;
            --medical-blue: #2b7be4;
            --medical-light-blue: #e8f4ff;
            --medical-green: #2ecc71;
            --medical-light-green: #e8f8f0;
            --medical-dark: #1a2b3c;
            --medical-gray: #f5f7fa;
            --medical-text: #2c3e50;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--medical-gray);
            color: var(--medical-text);
            overflow-x: hidden;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        
        .animate-fade-up { animation: fadeInUp 0.8s ease-out forwards; }
        .animate-float { animation: float 4s ease-in-out infinite; }
        
        .navbar {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            padding: 1rem 0;
        }
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-medical {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border: none;
            padding: 10px 28px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(43,123,228,0.3);
        }
        .btn-medical:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(43,123,228,0.4); }
        .btn-outline-medical {
            background: transparent;
            border: 2px solid var(--medical-blue);
            color: var(--medical-blue);
            padding: 8px 24px;
            border-radius: 40px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-outline-medical:hover { background: var(--medical-blue); color: white; }
        
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #ffffff 0%, #e8f4ff 100%);
            display: flex;
            align-items: center;
            padding-top: 80px;
            position: relative;
            overflow: hidden;
        }
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .floating-card {
            background: white;
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            animation: float 5s ease-in-out infinite;
        }
        
        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            position: relative;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--medical-blue), var(--medical-green));
            border-radius: 2px;
        }
        
        .forum-card {
            background: white;
            border-radius: 24px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s;
        }
        .forum-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
        .forum-header {
            padding: 20px 25px;
            background: linear-gradient(135deg, var(--medical-light-blue), white);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .doctor-avatar {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        .forum-media {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 16px;
        }
        .forum-content { padding: 20px 25px; }
        .forum-stats {
            padding: 15px 25px;
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex;
            gap: 20px;
            background: var(--medical-gray);
        }
        .comment-card {
            background: var(--medical-gray);
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 12px;
        }
        .doctor-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.3s;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            text-align: center;
            padding: 25px 20px;
        }
        .doctor-card:hover { transform: translateY(-8px); }
        .doctor-avatar-lg {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--medical-light-blue), var(--medical-blue));
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }
        .rating-stars i { color: #ffc107; font-size: 0.9rem; }
        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 10px;
        }
        .rating-input input { display: none; }
        .rating-input label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s;
        }
        .rating-input label:hover,
        .rating-input label:hover ~ label,
        .rating-input input:checked ~ label { color: #ffc107; }
        
        .review-form-container {
            background: white;
            border-radius: 28px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .review-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .medical-folder-card {
            background: linear-gradient(135deg, var(--medical-light-blue), white);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(43,123,228,0.2);
        }
        .file-upload-area {
            border: 2px dashed var(--medical-blue);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-upload-area:hover {
            background: rgba(43,123,228,0.05);
            border-color: var(--medical-green);
        }
        .prescription-item {
            background: white;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .medical-record-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--medical-blue);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .badge-medical {
            background: var(--medical-light-blue);
            color: var(--medical-blue);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .consultation-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .consultation-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .video-placeholder {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        .video-placeholder:hover { transform: scale(1.02); }
        
        .followup-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .followup-textarea {
            width: 100%;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 15px;
            font-family: inherit;
            resize: vertical;
        }
        .followup-textarea:focus {
            outline: none;
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 3px rgba(43,123,228,0.1);
        }
        
        .chatbot-container {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 1000;
        }
        .chatbot-toggle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 25px rgba(43,123,228,0.4);
            transition: all 0.3s;
        }
        .chatbot-toggle:hover { transform: scale(1.05); }
        .chatbot-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 380px;
            height: 500px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }
        .chatbot-window.show { display: flex; }
        .chatbot-header {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            padding: 18px;
            text-align: center;
            color: white;
        }
        .chatbot-messages {
            flex: 1;
            padding: 18px;
            overflow-y: auto;
            background: var(--medical-gray);
        }
        .message {
            margin-bottom: 12px;
            padding: 10px 16px;
            border-radius: 20px;
            max-width: 85%;
        }
        .message.user {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .message.bot {
            background: white;
            color: var(--medical-text);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .chatbot-input {
            display: flex;
            padding: 12px;
            background: white;
            border-top: 1px solid #eee;
        }
        .chatbot-input input {
            flex: 1;
            border: 1px solid #e0e0e0;
            padding: 12px 16px;
            border-radius: 30px;
            outline: none;
        }
        .chatbot-input button {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border: none;
            border-radius: 50%;
            width: 45px;
            margin-left: 10px;
            color: white;
            cursor: pointer;
        }
        
        .notification-toast {
            position: fixed;
            bottom: 25px;
            left: 25px;
            background: white;
            padding: 14px 24px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateX(-450px);
            transition: transform 0.3s;
            z-index: 1001;
            border-left: 4px solid var(--medical-green);
        }
        .notification-toast.show { transform: translateX(0); }
        
        .form-control-medical {
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 12px 16px;
        }
        .auth-modal .modal-content {
            border-radius: 28px;
            border: none;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c7a8a;
        }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; opacity: 0.3; }
        
        /* Forum Post Card Styles */
        .forum-post-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            animation: slideIn 0.4s ease-out;
        }
        .forum-post-card:hover {
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .post-author {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }
        .post-author h5 {
            margin: 0;
            font-weight: 600;
            color: var(--medical-text);
        }
        .post-content {
            margin-bottom: 15px;
            line-height: 1.6;
            color: var(--medical-text);
        }
        .post-image, .post-video {
            margin: 15px 0;
            border-radius: 12px;
            overflow: hidden;
        }
        .post-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        .action-btn {
            flex: 1;
            background: var(--medical-light-blue);
            border: none;
            padding: 10px;
            border-radius: 8px;
            color: var(--medical-blue);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        .action-btn:hover {
            background: var(--medical-blue);
            color: white;
            transform: scale(1.05);
        }
        .post-crud-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .post-crud-btn.edit { color: var(--medical-blue); }
        .post-crud-btn.edit:hover { background: var(--medical-light-blue); }
        .post-crud-btn.del { color: #dc3545; }
        .post-crud-btn.del:hover { background: #fff0f0; }
        .pub-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .pub-modal-overlay.active { display: flex; }
        .pub-modal-box {
            background: white;
            border-radius: 20px;
            padding: 30px;
            width: 90%;
            max-width: 540px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
        .is-valid {
            border-color: #2ecc71 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%232ecc71' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        @media (max-width: 768px) {
            .hero-title { font-size: 2rem; }
            .chatbot-window { width: 320px; right: -50px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <div class="logo-icon"><i class="fas fa-heartbeat"></i></div>
            <div><span class="logo-text">GlobalHealth Connect</span></div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="#home">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="#consultation">Consultation</a></li>
                <li class="nav-item"><a class="nav-link" href="#teleconsultation">Téléconsultation</a></li>
                <li class="nav-item"><a class="nav-link" href="#suivi">Suivi</a></li>
                <li class="nav-item"><a class="nav-link" href="#medecins">Médecins</a></li>
                <li class="nav-item"><a class="nav-link" href="#forum">Forum</a></li>
                <li class="nav-item"><a class="nav-link" href="#dossier">Dossier</a></li>
            </ul>
            <div class="user-menu ms-3" id="userMenu" style="display: none;">
                <div class="dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" style="width:42px;height:42px;background:linear-gradient(135deg,var(--medical-blue),var(--medical-green));border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                        <i class="fas fa-user"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="showProfile()"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showAppointments()"><i class="fas fa-calendar me-2"></i>Mes RDV</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showMedicalFolder()"><i class="fas fa-folder-open me-2"></i>Mon dossier médical</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logoutPatient()"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="auth-buttons ms-3" id="authButtons">
                <button class="btn btn-outline-medical me-2" onclick="showSignInModal()">Se connecter</button>
                <button class="btn btn-medical" onclick="showSignUpModal()">S'inscrire</button>
            </div>
        </div>
    </div>
</nav>

<section id="home" class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 animate-fade-up">
                <div class="hero-badge" style="background:rgba(46,204,113,0.15);padding:8px 20px;border-radius:40px;display:inline-block;margin-bottom:20px;color:var(--medical-green);font-weight:600;">
                    <i class="fas fa-shield-alt me-2"></i>Soins 100% sécurisés
                </div>
                <h1 class="hero-title">Prenez soin de votre santé autrement</h1>
                <p class="lead mb-4" style="color: #5a6e7c;">Consultez des médecins qualifiés en ligne ou en présentiel. Partagez vos expériences et notez vos consultations.</p>
                <div class="d-flex gap-3">
                    <a href="#consultation" class="btn btn-medical btn-lg">Prendre rendez-vous <i class="fas fa-arrow-right ms-2"></i></a>
                    <a href="#teleconsultation" class="btn btn-outline-medical btn-lg">Téléconsultation <i class="fas fa-video ms-2"></i></a>
                </div>
            </div>
            <div class="col-lg-6 text-center animate-float">
                <div class="floating-card">
                    <i class="fas fa-stethoscope fa-4x" style="color: var(--medical-blue);"></i>
                    <h4 class="mt-3">Téléconsultation 24/7</h4>
                    <p class="text-muted">Consultation en visio avec nos experts</p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <span class="badge bg-success">✓ Remboursé</span>
                        <span class="badge bg-info">✓ Sécurisé</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="consultation" class="py-5" style="background: var(--medical-light-blue);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="review-form-container">
                    <h3 class="text-center mb-4"><i class="fas fa-calendar-plus me-2" style="color: var(--medical-blue);"></i>Prendre rendez-vous</h3>
                    <form id="appointmentForm">
                        <div class="row g-3">
                            <div class="col-md-6"><input type="text" class="form-control form-control-medical" id="patientName" placeholder="Nom complet" ></div>
                            <div class="col-md-6"><input type="email" class="form-control form-control-medical" id="patientEmail" placeholder="Email" ></div>
                            <div class="col-md-6"><input type="tel" class="form-control form-control-medical" id="patientPhone" placeholder="Téléphone" ></div>
                            <div class="col-md-6">
                                <select class="form-select form-control-medical" id="consultationType" >
                                    <option value="">Type de consultation</option>
                                    <option value="video">Visioconférence</option>
                                    <option value="presentiel">Présentiel</option>
                                </select>
                            </div>
                            <div class="col-12"><textarea class="form-control form-control-medical" id="symptoms" rows="3" placeholder="Décrivez vos symptômes..." ></textarea></div>
                            <div class="col-12"><button type="submit" class="btn btn-medical w-100 py-3">Prendre RDV <i class="fas fa-check-circle ms-2"></i></button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="teleconsultation" class="py-5" style="background: white;">
    <div class="container">
        <h2 class="section-title"><i class="fas fa-video me-2"></i>Téléconsultation</h2>
        <p class="text-center mb-5">Connectez-vous avec votre médecin en visioconférence</p>
        
        <div class="row">
            <div class="col-lg-6">
                <div class="consultation-card">
                    <h4><i class="fas fa-link me-2" style="color: var(--medical-blue);"></i>Rejoindre une consultation</h4>
                    <p class="text-muted mb-4">Entrez le lien de consultation fourni par votre médecin</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control form-control-medical" id="consultationLink" placeholder="https://meet.google.com/xxx-xxxx-xxx">
                        <button class="btn btn-medical" onclick="joinConsultation()">
                            <i class="fas fa-video me-2"></i>Rejoindre
                        </button>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Les liens de consultation sont généralement au format : meet.google.com/xxx-xxxx-xxx</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="consultation-card">
                    <h4><i class="fas fa-calendar-check me-2" style="color: var(--medical-green);"></i>Mes consultations à venir</h4>
                    <div id="upcomingConsultations">
                        <div class="empty-state">
                            <i class="fas fa-calendar-alt"></i>
                            <p>Aucune consultation prévue</p>
                            <small>Prenez rendez-vous pour voir vos consultations ici</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="suivi" class="py-5" style="background: var(--medical-gray);">
    <div class="container">
        <h2 class="section-title"><i class="fas fa-notes-medical me-2"></i>Suivi de consultation</h2>
        <p class="text-center mb-5">Documentez et suivez l'évolution de votre état de santé</p>
        
        <div class="row">
            <div class="col-lg-5">
                <div class="consultation-card">
                    <h4><i class="fas fa-plus-circle me-2" style="color: var(--medical-blue);"></i>Nouveau suivi</h4>
                    <form id="followupForm">
                        <div class="mb-3">
                            <label>Date de la consultation</label>
                            <input type="date" class="form-control form-control-medical" id="followupDate" >
                        </div>
                        <div class="mb-3">
                            <label>Médecin consulté</label>
                            <select class="form-select form-control-medical" id="followupDoctor" >
                                <option value="">Sélectionnez un médecin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Sujet / Motif</label>
                            <input type="text" class="form-control form-control-medical" id="followupSubject" placeholder="Ex: Consultation pour douleurs dorsales" >
                        </div>
                        <div class="mb-3">
                            <label>Compte-rendu détaillé</label>
                            <textarea class="followup-textarea" id="followupContent" rows="6" placeholder="Décrivez ce qui s'est passé pendant la consultation..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Documents joints</label>
                            <div class="file-upload-area" onclick="document.getElementById('followupFile').click()" style="padding: 15px;">
                                <i class="fas fa-paperclip me-2"></i>
                                <small>Cliquez pour joindre un fichier</small>
                                <input type="file" id="followupFile" style="display: none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="uploadFollowupFile(this)">
                            </div>
                            <div id="followupFileName" class="mt-2 small text-muted"></div>
                        </div>
                        <button type="submit" class="btn btn-medical w-100"><i class="fas fa-save me-2"></i>Enregistrer le suivi</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="consultation-card">
                    <h4><i class="fas fa-history me-2" style="color: var(--medical-green);"></i>Historique des suivis</h4>
                    <div id="followupList">
                        <div class="empty-state">
                            <i class="fas fa-notes-medical"></i>
                            <p>Aucun suivi enregistré</p>
                            <small>Créez votre premier suivi de consultation</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="medecins" class="py-5" style="background: white;">
    <div class="container">
        <h2 class="section-title">Nos médecins experts</h2>
        <div id="doctorsList" class="row">
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-user-md"></i>
                    <p>Aucun médecin disponible pour le moment.</p>
                    <small>Les médecins seront ajoutés prochainement.</small>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="dossier" class="py-5" style="background: linear-gradient(135deg, #e8f8f0, #e8f4ff);">
    <div class="container">
        <h2 class="section-title">Mon Dossier Médical</h2>
        <p class="text-center mb-4">Gérez l'ensemble de vos informations médicales en toute sécurité</p>
        
        <div class="medical-folder-card">
            <h5 class="mb-4"><i class="fas fa-notes-medical me-2" style="color: var(--medical-blue);"></i>Nouveau dossier médical</h5>
            <form id="medicalRecordForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label><i class="fas fa-user me-2 text-primary"></i>ID Patient</label>
                        <input type="text" class="form-control form-control-medical" id="id_patient" placeholder="ID Patient" readonly>
                    </div>
                    <div class="col-md-6">
                        <label><i class="fas fa-user-md me-2 text-primary"></i>Médecin traitant</label>
                        <select class="form-select form-control-medical" id="id_medecin" required>
                            <option value="">Sélectionnez un médecin</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label><i class="fas fa-calendar-check me-2 text-primary"></i>ID Rendez-vous</label>
                        <select class="form-select form-control-medical" id="id_rdv">
                            <option value="">Sélectionnez un rendez-vous (optionnel)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label><i class="fas fa-stethoscope me-2 text-primary"></i>Symptômes</label>
                        <textarea class="form-control form-control-medical" id="symptomes" rows="2" placeholder="Décrivez vos symptômes..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label><i class="fas fa-diagnoses me-2 text-primary"></i>Diagnostic</label>
                        <textarea class="form-control form-control-medical" id="diagnostic" rows="2" placeholder="Diagnostic médical..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label><i class="fas fa-pills me-2 text-primary"></i>Traitement</label>
                        <textarea class="form-control form-control-medical" id="traitement" rows="2" placeholder="Traitement prescrit..."></textarea>
                    </div>
                    <div class="col-12">
                        <label><i class="fas fa-file-prescription me-2 text-primary"></i>Ordonnance</label>
                        <div class="row g-2">
                            <div class="col-md-8">
                                <textarea class="form-control form-control-medical" id="ordonnance_texte" rows="3" placeholder="Ordonnance (texte) : Médicaments, posologie, durée..."></textarea>
                            </div>
                            <div class="col-md-4">
                                <div class="file-upload-area" onclick="document.getElementById('ordonnanceFile').click()" style="padding: 25px;">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: var(--medical-blue);"></i>
                                    <p class="mb-0"><small>ou télécharger un fichier</small></p>
                                    <input type="file" id="ordonnanceFile" style="display: none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="uploadOrdonnanceFile(this)">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label><i class="fas fa-comment-dots me-2 text-primary"></i>Notes du médecin</label>
                        <textarea class="form-control form-control-medical" id="notes_medecin" rows="2" placeholder="Notes complémentaires du médecin..."></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-medical"><i class="fas fa-save me-2"></i>Enregistrer le dossier</button>
                        <button type="button" class="btn btn-outline-medical ms-2" onclick="resetMedicalForm()"><i class="fas fa-undo-alt me-2"></i>Réinitialiser</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="medical-folder-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-folder-open me-2" style="color: var(--medical-blue);"></i>Mes dossiers médicaux</h5>
                <span class="badge-medical" id="recordCount">0 dossier(s)</span>
            </div>
            <div id="medicalRecordsList">
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>Aucun dossier médical</p>
                    <small>Créez votre premier dossier médical ci-dessus</small>
                </div>
            </div>
        </div>
        
        <div class="medical-folder-card" id="historyCard" style="display: none;">
            <h5 class="mb-3"><i class="fas fa-history me-2" style="color: var(--medical-blue);"></i>Historique des modifications</h5>
            <div id="historyList"></div>
        </div>
    </div>
</section>

<section id="forum" class="py-5" style="background: var(--medical-gray);">
    <div class="container">
        <h2 class="section-title">Forum Médical</h2>
        <p class="text-center mb-5">Les médecins partagent leurs publications, les patients commentent et notent les consultations</p>
        
        <div class="review-form-container" id="reviewFormContainer">
            <h4><i class="fas fa-star text-warning me-2"></i>Noter votre consultation</h4>
            <form id="submitReviewForm">
                <div class="row g-3">
                    <div class="col-md-6"><input type="text" class="form-control form-control-medical" id="reviewName" placeholder="Votre nom" ></div>
                    <div class="col-md-6">
                        <select class="form-select form-control-medical" id="reviewDoctorId">
                            <option value="">Sélectionnez un médecin</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="rating-input">
                            <input type="radio" name="reviewRating" value="5" id="rating5"><label for="rating5">★</label>
                            <input type="radio" name="reviewRating" value="4" id="rating4"><label for="rating4">★</label>
                            <input type="radio" name="reviewRating" value="3" id="rating3"><label for="rating3">★</label>
                            <input type="radio" name="reviewRating" value="2" id="rating2"><label for="rating2">★</label>
                            <input type="radio" name="reviewRating" value="1" id="rating1"><label for="rating1">★</label>
                        </div>
                    </div>
                    <div class="col-12"><textarea class="form-control form-control-medical" id="reviewComment" rows="3" placeholder="Partagez votre expérience avec le médecin..." ></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-medical"><i class="fas fa-paper-plane me-2"></i>Publier mon avis</button></div>
                </div>
            </form>
        </div>
        
        <!-- Bouton Nouvelle Publication -->
        <div class="text-end mb-4">
            <button class="btn btn-medical" onclick="openAddModal()">
                <i class="fas fa-plus me-2"></i>Nouvelle publication
            </button>
        </div>

        <!-- Modal Ajout / Modification Publication -->
        <div class="pub-modal-overlay" id="pubModalOverlay">
            <div class="pub-modal-box">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 id="pubModalTitle" style="margin:0;font-weight:700;color:var(--medical-dark);"><i class="fas fa-newspaper me-2"></i>Nouvelle publication</h4>
                    <button onclick="closeModal()" style="background:none;border:none;font-size:1.5rem;line-height:1;cursor:pointer;color:#666;">&times;</button>
                </div>
                <form id="pubForm">
                    <input type="hidden" id="pubFormId">
                    <div class="mb-3" id="pubFormDoctorWrap">
                        <label class="form-label fw-semibold">Médecin <span class="text-danger">*</span></label>
                        <select class="form-select form-control-medical" id="pubFormDoctor">
                            <option value="">Sélectionnez un médecin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contenu <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-medical" id="pubFormContent" rows="4" placeholder="Partagez votre expertise médicale..."></textarea>
                        <div class="invalid-feedback" id="pubContentError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image (URL)</label>
                        <input type="text" class="form-control form-control-medical" id="pubFormImage" placeholder="https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vidéo (URL)</label>
                        <input type="text" class="form-control form-control-medical" id="pubFormVideo" placeholder="https://...">
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-medical flex-fill" id="pubFormSubmitBtn">
                            <i class="fas fa-paper-plane me-2"></i>Publier
                        </button>
                        <button type="button" onclick="closeModal()" style="background:#f5f7fa;border:none;padding:10px 20px;border-radius:12px;cursor:pointer;font-weight:500;">Annuler</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="forumPostsList">
            <?php if(count($publications) > 0): ?>
                <?php foreach($publications as $pub): 
                    // Get doctor name
                    $doctorName = 'Dr. Médecin';
                    if(!empty($pub['id_medecin'])) {
                        try {
                            $pdo = config::getConnexion();
                            $stmt = $pdo->prepare("SELECT nom, prenom FROM utilisateur WHERE id = ?");
                            $stmt->execute([$pub['id_medecin']]);
                            $doctor = $stmt->fetch();
                            if($doctor) {
                                $doctorName = $doctor['nom'] . ' ' . $doctor['prenom'];
                            }
                        } catch(Exception $e) {
                            $doctorName = 'Dr. Médecin';
                        }
                    }
                ?>
                    <div class="forum-post-card"
                         data-pub-id="<?php echo (int)$pub['id_publication']; ?>"
                         data-content="<?php echo htmlspecialchars($pub['contenu'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                         data-image="<?php echo htmlspecialchars($pub['url_image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                         data-video="<?php echo htmlspecialchars($pub['url_video'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="post-header">
                            <div class="post-author">
                                <div class="author-avatar" style="background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));">
                                    <?php echo strtoupper(substr($doctorName, 0, 1)); ?>
                                </div>
                                <div>
                                    <h5><?php echo htmlspecialchars($doctorName); ?></h5>
                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($pub['date_publication'] ?? 'now')); ?></small>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="post-crud-btn edit" title="Modifier" onclick="openEditModal(this)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="post-crud-btn del" title="Supprimer" onclick="deletePublication(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="post-content">
                            <p><?php echo nl2br(htmlspecialchars(substr($pub['contenu'], 0, 300))); ?>
                            <?php if(strlen($pub['contenu'] ?? '') > 300): ?>
                                ...<a href="#" class="text-medical-blue" style="cursor: pointer;"> Lire plus</a>
                            <?php endif; ?>
                            </p>
                        </div>
                        <?php if(!empty($pub['url_image'])): ?>
                            <div class="post-image">
                                <img src="<?php echo htmlspecialchars($pub['url_image']); ?>" alt="Publication image" style="max-width: 100%; border-radius: 12px; max-height: 400px; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($pub['url_video'])): ?>
                            <div class="post-video">
                                <iframe width="100%" height="315" src="<?php echo htmlspecialchars($pub['url_video']); ?>" frameborder="0" allowfullscreen style="border-radius: 12px;"></iframe>
                            </div>
                        <?php endif; ?>
                        <div class="post-actions">
                            <button class="action-btn" onclick="toggleComments(<?php echo $pub['id_publication']; ?>)"><i class="fas fa-comment"></i> Commenter</button>
                            <button class="action-btn"><i class="fas fa-heart"></i> J'aime</button>
                            <button class="action-btn"><i class="fas fa-share"></i> Partager</button>
                        </div>

                        <!-- Comments Section -->
                        <div id="comments-section-<?php echo $pub['id_publication']; ?>" class="comments-section mt-4" style="display: none; background: #f9f9f9; padding: 20px; border-radius: 12px;">
                            <h6 class="mb-3"><i class="fas fa-comments me-2"></i>Commentaires (Pub ID: <?php echo $pub['id_publication']; ?>)</h6>
                            
                            <!-- Comment Form -->
                            <form class="mb-3" onsubmit="submitComment(event, <?php echo (int)$pub['id_publication']; ?>)" style="background: white; padding: 15px; border-radius: 10px;">
                                <div class="mb-2">
                                    <textarea class="form-control form-control-medical" id="comment-content-<?php echo $pub['id_publication']; ?>" placeholder="Écrivez votre commentaire..." rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-medical btn-sm">
                                    <i class="fas fa-paper-plane me-2"></i>Publier
                                </button>
                            </form>

                            <!-- Comments List -->
                            <div id="comments-list-<?php echo $pub['id_publication']; ?>" style="max-height: 400px; overflow-y: auto;">
                                <div class="text-center text-muted py-3">Chargement des commentaires...</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-newspaper"></i>
                    <p>Aucune publication pour le moment.</p>
                    <small>Les médecins publieront bientôt du contenu.</small>
                </div>
            <?php endif; ?>
        </div>
        
        <h3 class="mt-5 mb-4"><i class="fas fa-comments me-2"></i>Avis des patients</h3>
        <div id="reviewsList">
            <div class="empty-state">
                <i class="fas fa-star"></i>
                <p>Aucun avis pour le moment.</p>
                <small>Soyez le premier à donner votre avis !</small>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="signinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content auth-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-sign-in-alt me-2" style="color: var(--medical-blue);"></i>Connexion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="signinForm">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control form-control-medical" id="signinEmail" >
                    </div>
                    <div class="mb-3">
                        <label>Mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="signinPassword" >
                    </div>
                    <button type="submit" class="btn btn-medical w-100">Se connecter</button>
                    <div class="text-center mt-3">
                        <small>Pas encore de compte ? <a href="#" onclick="switchToSignUp()">S'inscrire</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="signupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content auth-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2" style="color: var(--medical-blue);"></i>Inscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="signupForm">
                    <div class="mb-3">
                        <label>Nom complet</label>
                        <input type="text" class="form-control form-control-medical" id="signupName" >
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control form-control-medical" id="signupEmail" >
                    </div>
                    <div class="mb-3">
                        <label>Téléphone</label>
                        <input type="tel" class="form-control form-control-medical" id="signupPhone">
                    </div>
                    <div class="mb-3">
                        <label>Mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="signupPassword" >
                        <div id="passwordStrength" class="mt-1 small"></div>
                    </div>
                    <div class="mb-3">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="signupConfirmPassword" >
                    </div>
                    <button type="submit" class="btn btn-medical w-100">S'inscrire</button>
                    <div class="text-center mt-3">
                        <small>Déjà inscrit ? <a href="#" onclick="switchToSignIn()">Se connecter</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="chatbot-container">
    <div class="chatbot-toggle" onclick="toggleChatbot()">
        <i class="fas fa-comment-medical fa-2x"></i>
    </div>
    <div class="chatbot-window" id="chatbotWindow">
        <div class="chatbot-header"><i class="fas fa-robot me-2"></i>Assistant GlobalHealth</div>
        <div class="chatbot-messages" id="chatMessages">
            <div class="message bot">👋 Bonjour ! Je suis votre assistant santé. Posez-moi vos questions.</div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chatInput" placeholder="Écrivez votre message...">
            <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<div class="notification-toast" id="notificationToast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="index.js"></script>

<script>
// Comment System Functions
function toggleComments(pubId) {
    const section = document.getElementById(`comments-section-${pubId}`);
    if (section.style.display === 'none') {
        section.style.display = 'block';
        loadComments(pubId);
    } else {
        section.style.display = 'none';
    }
}

async function loadComments(pubId) {
    const listDiv = document.getElementById(`comments-list-${pubId}`);
    listDiv.innerHTML = '<div class="text-center text-muted py-3">Chargement...</div>';

    try {
        const response = await fetch('../../backoffice/layout/backoffice.php?action=get-comments', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_publication: parseInt(pubId) })
        });

        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            listDiv.innerHTML = result.data.map(c => `
                <div style="background: white; padding: 12px; margin-bottom: 10px; border-radius: 8px; border-left: 3px solid var(--medical-blue);"
                     data-comment-id="${c.id_commentaire}" data-pub-id="${pubId}">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div style="font-weight:600; color:var(--medical-blue);">
                            ${escapeHtml(c.nom)} ${escapeHtml(c.prenom)}
                            <span style="font-size:0.85rem; color:#999; margin-left:10px; font-weight:400;">
                                ${formatDate(c.date_publication)}
                            </span>
                        </div>
                        <div class="comment-actions" style="display:flex; gap:2px; flex-shrink:0;">
                            <button onclick="startEditComment(this)" title="Modifier"
                                style="background:none;border:none;cursor:pointer;color:var(--medical-blue);padding:4px 8px;border-radius:6px;font-size:0.85rem;transition:background 0.2s;"
                                onmouseover="this.style.background='#e8f4ff'" onmouseout="this.style.background='none'">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteCommentFront(this)" title="Supprimer"
                                style="background:none;border:none;cursor:pointer;color:#dc3545;padding:4px 8px;border-radius:6px;font-size:0.85rem;transition:background 0.2s;"
                                onmouseover="this.style.background='#fff0f0'" onmouseout="this.style.background='none'">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="comment-text" style="margin-top:8px; color:#333; line-height:1.5;">
                        ${escapeHtml(c.contenu)}
                    </div>
                </div>
            `).join('');
        } else {
            listDiv.innerHTML = '<div class="text-center text-muted py-3">Aucun commentaire pour le moment.</div>';
        }
    } catch (error) {
        listDiv.innerHTML = `<div class="alert alert-danger mb-0">Erreur: ${error.message}</div>`;
        console.error('Error loading comments:', error);
    }
}

async function submitComment(event, pubId) {
    event.preventDefault();

    const contentInput = document.getElementById(`comment-content-${pubId}`);
    
    if (!contentInput.value.trim()) {
        alert('Veuillez écrire un commentaire');
        return;
    }

    let userId = 1;
    try {
        const userRes = await fetch('../../backoffice/layout/backoffice.php?action=get-users');
        const userData = await userRes.json();
        if (userData.success && userData.data && userData.data.length > 0) {
            userId = userData.data[0].id;
            console.log('Using user ID:', userId);
        }
    } catch (e) {
        console.warn('Could not fetch users, using default ID');
    }

    const commentData = {
        id_publication: parseInt(pubId),
        id_user: parseInt(userId),
        contenu: contentInput.value.trim()
    };
    
    console.log('Submitting comment:', commentData);

    try {
        const response = await fetch('../../backoffice/layout/backoffice.php?action=add-comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(commentData)
        });

        const result = await response.json();
        console.log('Response:', result);

        if (result.success) {
            contentInput.value = '';
            showFrontNotification('Commentaire publié avec succès !');
            loadComments(pubId);
        } else {
            showFrontNotification('Erreur : ' + (result.error || 'Erreur inconnue'), true);
        }
    } catch (error) {
        showFrontNotification('Erreur réseau : ' + error.message, true);
        console.error('Error submitting comment:', error);
    }
}

// ============ COMMENTAIRES CRUD ============

function startEditComment(btn) {
    const card = btn.closest('[data-comment-id]');
    const commentId = card.dataset.commentId;
    const pubId     = card.dataset.pubId;
    const textDiv   = card.querySelector('.comment-text');
    const original  = textDiv.textContent.trim();

    // Masquer les boutons pendant l'édition
    card.querySelector('.comment-actions').style.display = 'none';

    textDiv.innerHTML = `
        <textarea id="edit-comment-${commentId}" rows="3"
            style="width:100%;margin-top:8px;padding:10px;border:1px solid #ddd;border-radius:10px;font-family:inherit;font-size:0.95rem;resize:vertical;">${escapeHtml(original)}</textarea>
        <div style="display:flex;gap:8px;margin-top:8px;">
            <button onclick="saveEditComment('${commentId}','${pubId}')"
                style="background:linear-gradient(135deg,#2b7be4,#2ecc71);color:white;border:none;padding:6px 16px;border-radius:10px;cursor:pointer;font-size:0.85rem;font-weight:600;">
                <i class="fas fa-save me-1"></i>Enregistrer
            </button>
            <button onclick="cancelEditComment('${commentId}','${pubId}')"
                style="background:#f5f7fa;border:none;padding:6px 16px;border-radius:10px;cursor:pointer;font-size:0.85rem;">
                Annuler
            </button>
        </div>
    `;
    document.getElementById(`edit-comment-${commentId}`).focus();
}

async function saveEditComment(commentId, pubId) {
    const textarea = document.getElementById(`edit-comment-${commentId}`);
    const contenu  = textarea ? textarea.value.trim() : '';

    if (!contenu || contenu.length < 2) {
        showFrontNotification('Le commentaire est trop court.', true);
        return;
    }

    try {
        const r = await fetch('../../backoffice/layout/backoffice.php?action=update-comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(commentId), contenu })
        });
        const result = await r.json();
        if (result.success) {
            showFrontNotification('Commentaire modifié !');
            loadComments(pubId);
        } else {
            showFrontNotification(result.error || 'Erreur lors de la modification', true);
        }
    } catch (err) {
        showFrontNotification('Erreur réseau : ' + err.message, true);
    }
}

function cancelEditComment(commentId, pubId) {
    loadComments(pubId);
}

async function deleteCommentFront(btn) {
    const card      = btn.closest('[data-comment-id]');
    const commentId = card.dataset.commentId;
    const pubId     = card.dataset.pubId;

    if (!confirm('Supprimer ce commentaire ?')) return;

    try {
        const r = await fetch('../../backoffice/layout/backoffice.php?action=delete-comment-db', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(commentId) })
        });
        const result = await r.json();
        if (result.success) {
            showFrontNotification('Commentaire supprimé.');
            loadComments(pubId);
        } else {
            showFrontNotification(result.error || 'Erreur lors de la suppression', true);
        }
    } catch (err) {
        showFrontNotification('Erreur réseau : ' + err.message, true);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateString) {
    if (!dateString) return 'Date inconnue';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

// ============ PUBLICATIONS CRUD ============

async function loadDoctorsForForm() {
    const select = document.getElementById('pubFormDoctor');
    if (select.options.length > 1) return;
    select.innerHTML = '<option value="">Chargement...</option>';
    try {
        const r = await fetch('../../backoffice/layout/backoffice.php?action=get-doctors');
        const result = await r.json();
        if (result.success && result.data && result.data.length > 0) {
            select.innerHTML = '<option value="">Sélectionnez un médecin</option>' +
                result.data.map(d => `<option value="${d.id}">${escapeHtml(d.prenom)} ${escapeHtml(d.nom)}</option>`).join('');
        } else {
            select.innerHTML = '<option value="">Aucun médecin disponible</option>';
        }
    } catch (e) {
        select.innerHTML = '<option value="">Erreur de chargement</option>';
    }
}

function openAddModal() {
    document.getElementById('pubModalTitle').innerHTML = '<i class="fas fa-newspaper me-2"></i>Nouvelle publication';
    document.getElementById('pubFormSubmitBtn').innerHTML = '<i class="fas fa-paper-plane me-2"></i>Publier';
    document.getElementById('pubFormId').value = '';
    document.getElementById('pubFormContent').value = '';
    document.getElementById('pubFormImage').value = '';
    document.getElementById('pubFormVideo').value = '';
    document.getElementById('pubFormDoctorWrap').style.display = '';
    document.getElementById('pubFormContent').classList.remove('is-invalid');
    document.getElementById('pubModalOverlay').classList.add('active');
    loadDoctorsForForm();
}

function openEditModal(btn) {
    const card = btn.closest('.forum-post-card');
    const pubId   = card.dataset.pubId;
    const content = card.dataset.content;
    const image   = card.dataset.image;
    const video   = card.dataset.video;

    document.getElementById('pubModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Modifier la publication';
    document.getElementById('pubFormSubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
    document.getElementById('pubFormId').value = pubId;
    document.getElementById('pubFormContent').value = content;
    document.getElementById('pubFormImage').value = image || '';
    document.getElementById('pubFormVideo').value = video || '';
    document.getElementById('pubFormDoctorWrap').style.display = 'none';
    document.getElementById('pubFormContent').classList.remove('is-invalid');
    document.getElementById('pubModalOverlay').classList.add('active');
}

function closeModal() {
    document.getElementById('pubModalOverlay').classList.remove('active');
}

document.getElementById('pubModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('pubForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const pubId   = document.getElementById('pubFormId').value;
    const content = document.getElementById('pubFormContent').value.trim();
    const image   = document.getElementById('pubFormImage').value.trim();
    const video   = document.getElementById('pubFormVideo').value.trim();
    const doctorId = document.getElementById('pubFormDoctor').value;

    const contentEl = document.getElementById('pubFormContent');
    if (!content || content.length < 10) {
        contentEl.classList.add('is-invalid');
        document.getElementById('pubContentError').textContent = 'Le contenu doit contenir au moins 10 caractères.';
        return;
    }
    contentEl.classList.remove('is-invalid');

    if (!pubId && !doctorId) {
        showFrontNotification('Veuillez sélectionner un médecin.', true);
        return;
    }

    const submitBtn = document.getElementById('pubFormSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';

    try {
        let url, body;
        if (pubId) {
            url  = '../../backoffice/layout/backoffice.php?action=update-publication';
            body = { id: parseInt(pubId), contenu: content, url_image: image || null, url_video: video || null };
        } else {
            url  = '../../backoffice/layout/backoffice.php?action=add-publication';
            body = { id_medecin: parseInt(doctorId), contenu: content, url_image: image || null, url_video: video || null };
        }

        const r = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const result = await r.json();

        if (result.success) {
            closeModal();
            showFrontNotification(pubId ? 'Publication modifiée avec succès !' : 'Publication ajoutée avec succès !');
            setTimeout(() => window.location.reload(), 900);
        } else {
            showFrontNotification(result.error || 'Erreur lors de l\'opération', true);
            submitBtn.disabled = false;
            submitBtn.innerHTML = pubId
                ? '<i class="fas fa-save me-2"></i>Enregistrer'
                : '<i class="fas fa-paper-plane me-2"></i>Publier';
        }
    } catch (err) {
        showFrontNotification('Erreur réseau : ' + err.message, true);
        submitBtn.disabled = false;
    }
});

async function deletePublication(btn) {
    const pubId = btn.closest('.forum-post-card').dataset.pubId;
    if (!confirm('Supprimer cette publication ? Cette action est irréversible.')) return;
    try {
        const r = await fetch('../../backoffice/layout/backoffice.php?action=delete-publication', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(pubId) })
        });
        const result = await r.json();
        if (result.success) {
            showFrontNotification('Publication supprimée.');
            setTimeout(() => window.location.reload(), 900);
        } else {
            showFrontNotification(result.error || 'Erreur lors de la suppression', true);
        }
    } catch (err) {
        showFrontNotification('Erreur réseau : ' + err.message, true);
    }
}

function showFrontNotification(msg, isError = false) {
    const toast = document.getElementById('notificationToast');
    toast.textContent = msg;
    toast.style.borderLeftColor = isError ? '#dc3545' : 'var(--medical-green)';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
}
</script>
</body>
</html>

</body>
</html>
