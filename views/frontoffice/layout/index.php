<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../config/paths.php';
$usersApiBase = gh_users_api_base();
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
        .profile-avatar-preview {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            background-size: cover;
            background-position: center;
            border: 4px solid rgba(255,255,255,0.75);
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
        
        /* Styles pour la téléconsultation */
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
        
        /* Styles pour le suivi */
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
        
        @media (max-width: 768px) {
            .hero-title { font-size: 2rem; }
            .chatbot-window { width: 320px; right: -50px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
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
                    <div class="user-avatar" id="navUserAvatar" data-bs-toggle="dropdown" style="width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;background:linear-gradient(135deg,var(--medical-blue),var(--medical-green));color:white;background-size:cover;background-position:center;">
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
                            <div class="col-md-6"><input type="text" class="form-control form-control-medical" id="patientName" placeholder="Nom complet" required></div>
                            <div class="col-md-6"><input type="email" class="form-control form-control-medical" id="patientEmail" placeholder="Email" required></div>
                            <div class="col-md-6"><input type="tel" class="form-control form-control-medical" id="patientPhone" placeholder="Téléphone" required></div>
                            <div class="col-md-6">
                                <select class="form-select form-control-medical" id="consultationType" required>
                                    <option value="">Type de consultation</option>
                                    <option value="video">Visioconférence</option>
                                    <option value="presentiel">Présentiel</option>
                                </select>
                            </div>
                            <div class="col-12"><textarea class="form-control form-control-medical" id="symptoms" rows="3" placeholder="Décrivez vos symptômes..." required></textarea></div>
                            <div class="col-12"><button type="submit" class="btn btn-medical w-100 py-3">Prendre RDV <i class="fas fa-check-circle ms-2"></i></button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION TÉLÉCONSULTATION -->
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

<!-- SECTION SUIVI DE CONSULTATION -->
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
                            <input type="date" class="form-control form-control-medical" id="followupDate" required>
                        </div>
                        <div class="mb-3">
                            <label>Médecin consulté</label>
                            <select class="form-select form-control-medical" id="followupDoctor" required>
                                <option value="">Sélectionnez un médecin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Sujet / Motif</label>
                            <input type="text" class="form-control form-control-medical" id="followupSubject" placeholder="Ex: Consultation pour douleurs dorsales" required>
                        </div>
                        <div class="mb-3">
                            <label>Compte-rendu détaillé</label>
                            <textarea class="followup-textarea" id="followupContent" rows="6" placeholder="Décrivez ce qui s'est passé pendant la consultation :
- Symptômes présentés
- Diagnostic du médecin
- Traitement prescrit
- Conseils et recommandations
- Prochaines étapes..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Documents joints</label>
                            <div class="file-upload-area" onclick="document.getElementById('followupFile').click()" style="padding: 15px;">
                                <i class="fas fa-paperclip me-2"></i>
                                <small>Cliquez pour joindre un fichier (ordonnance, compte-rendu...)</small>
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
                    <div class="col-md-6"><input type="text" class="form-control form-control-medical" id="reviewName" placeholder="Votre nom" required></div>
                    <div class="col-md-6">
                        <select class="form-select form-control-medical" id="reviewDoctorId" required>
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
                    <div class="col-12"><textarea class="form-control form-control-medical" id="reviewComment" rows="3" placeholder="Partagez votre expérience avec le médecin..." required></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-medical"><i class="fas fa-paper-plane me-2"></i>Publier mon avis</button></div>
                </div>
            </form>
        </div>
        
        <div id="forumPostsList">
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <p>Aucune publication pour le moment.</p>
                <small>Les médecins publieront bientôt du contenu.</small>
            </div>
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

<!-- Modals Connexion / Inscription -->
<div class="modal fade" id="signinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content auth-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-sign-in-alt me-2" style="color: var(--medical-blue);"></i>Connexion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="signinForm" novalidate>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control form-control-medical" id="signinEmail">
                    </div>
                    <div class="mb-3">
                        <label>Mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="signinPassword">
                    </div>
                    <button type="submit" class="btn btn-medical w-100">Se connecter</button>
                    <div class="text-center mt-3">
                        <small><a href="#" onclick="switchToForgotPassword()">Mot de passe oublié ?</a></small>
                    </div>
                    <div class="text-center mt-2">
                        <small>Pas encore de compte ? <a href="#" onclick="switchToSignUp()">S'inscrire</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content auth-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-key me-2" style="color: var(--medical-blue);"></i>Réinitialiser le mot de passe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="forgotPasswordForm" novalidate>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control form-control-medical" id="forgotEmail">
                    </div>
                    <div class="mb-3">
                        <label>Nouveau mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="forgotPassword">
                    </div>
                    <div class="mb-3">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="forgotConfirmPassword">
                    </div>
                    <button type="submit" class="btn btn-medical w-100">Réinitialiser</button>
                    <div class="text-center mt-3">
                        <small>Retour à la connexion ? <a href="#" onclick="switchToSignInFromForgot()">Se connecter</a></small>
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
                <form id="signupForm" novalidate>
                    <div class="mb-3">
                        <label>Nom complet</label>
                        <input type="text" class="form-control form-control-medical" id="signupName">
                    </div>
                    <div class="row gx-3">
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control form-control-medical" id="signupEmail">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Rôle</label>
                            <select class="form-select form-control-medical" id="signupRole" onchange="toggleSignupSpecialty()">
                                <option value="patient">Patient</option>
                                <option value="medecin">Médecin</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="signupSpecialtyField" style="display:none;">
                        <label>Spécialité</label>
                        <input type="text" class="form-control form-control-medical" id="signupSpecialite" placeholder="Ex: Cardiologue">
                    </div>
                    <div class="row gx-3">
                        <div class="col-md-4 mb-3">
                            <label>Âge</label>
                            <input type="number" min="0" class="form-control form-control-medical" id="signupAge">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Sexe</label>
                            <select class="form-select form-control-medical" id="signupSexe">
                                <option value="">Sélectionner</option>
                                <option value="Homme">Homme</option>
                                <option value="Femme">Femme</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Date de naissance</label>
                            <input type="date" class="form-control form-control-medical" id="signupDateNaissance">
                        </div>
                    </div>
                    <div class="row gx-3">
                        <div class="col-md-6 mb-3">
                            <label>Poids (kg)</label>
                            <input type="number" step="0.1" min="0" class="form-control form-control-medical" id="signupPoids">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Taille (cm)</label>
                            <input type="number" step="0.1" min="0" class="form-control form-control-medical" id="signupTaille">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Adresse</label>
                        <input type="text" class="form-control form-control-medical" id="signupAdresse">
                    </div>
                    <div class="mb-3">
                        <label>Mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="signupPassword">
                    </div>
                    <div class="mb-3">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="signupConfirmPassword">
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

<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content auth-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-user-circle me-2" style="color: var(--medical-blue);"></i>Mon profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="profileForm" novalidate>
                    <div class="text-center mb-4">
                        <div id="profileAvatarPreview" class="profile-avatar-preview mx-auto mb-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <button type="button" class="btn btn-outline-medical btn-sm" onclick="document.getElementById('profilePhotoInput').click()">Modifier la photo</button>
                        <input type="file" id="profilePhotoInput" accept="image/*" style="display:none" onchange="handleProfilePhotoUpload(this)">
                    </div>
                    <div class="mb-3">
                        <label>Nom complet</label>
                        <input type="text" class="form-control form-control-medical" id="profileName" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="text" class="form-control form-control-medical" id="profileEmail" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Rôle</label>
                        <input type="text" class="form-control form-control-medical" id="profileRole" readonly>
                    </div>
                    <div class="mb-3" id="profileSpecialiteRow" style="display:none;">
                        <label>Spécialité</label>
                        <input type="text" class="form-control form-control-medical" id="profileSpecialite" readonly>
                    </div>
                    <div class="row gx-3">
                        <div class="col-md-6 mb-3">
                            <label>Âge</label>
                            <input type="text" class="form-control form-control-medical" id="profileAge" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Sexe</label>
                            <input type="text" class="form-control form-control-medical" id="profileSexe" readonly>
                        </div>
                    </div>
                    <div class="row gx-3">
                        <div class="col-md-6 mb-3">
                            <label>Poids (kg)</label>
                            <input type="text" class="form-control form-control-medical" id="profilePoids" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Taille (cm)</label>
                            <input type="text" class="form-control form-control-medical" id="profileTaille" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Date de naissance</label>
                        <input type="text" class="form-control form-control-medical" id="profileDateNaissance" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Adresse</label>
                        <input type="text" class="form-control form-control-medical" id="profileAdresse" readonly>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-medical w-100" data-bs-dismiss="modal">Fermer</button>
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
<script>
    // ============================================
    // AUTHENTIFICATION PATIENT
    // ============================================
    const USERS_API_BASE = <?php echo json_encode($usersApiBase, JSON_THROW_ON_ERROR); ?>;
    let patientsData = [];
    let currentPatient = null;
    
    async function apiRequest(endpoint, method = 'GET', payload = null) {
        const options = { method, headers: {} };
        if (payload !== null) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }
        const url = `${USERS_API_BASE}/${endpoint}`;
        const response = await fetch(url, options);
        let result;
        try {
            result = await response.json();
        } catch (e) {
            throw new Error(`Réponse invalide (${response.status}). Vérifiez l'URL: ${url}`);
        }
        if (!response.ok || !result.success) {
            throw new Error(result.message || `Erreur API (${response.status})`);
        }
        return result.data;
    }

    function toggleSignupSpecialty() {
        const isMedecin = document.getElementById('signupRole').value === 'medecin';
        document.getElementById('signupSpecialtyField').style.display = isMedecin ? 'block' : 'none';
    }

    function validateSignupUser(user) {
        const requiredFields = ['nom', 'prenom', 'age', 'sexe', 'poids', 'taille', 'email', 'mot_de_passe', 'date_naissance', 'adresse', 'role'];
        const missing = requiredFields.find((field) => !user[field] && user[field] !== 0);
        if (missing) return `Champ obligatoire manquant: ${missing}`;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(user.email)) return 'Email invalide';
        if (Number(user.age) < 0 || Number(user.age) > 130) return 'Age invalide';
        if (Number(user.poids) <= 0 || Number(user.taille) <= 0) return 'Poids/Taille invalides';
        if (!['admin', 'medecin', 'patient'].includes(user.role)) return 'Rôle invalide';
        if (user.role === 'medecin' && !user.specialite) return 'La spécialité est obligatoire pour un médecin';
        if (user.mot_de_passe.length < 6) return 'Mot de passe trop court (min 6)';
        return null;
    }


    async function loadPatients() {
        patientsData = [];
    }
    
    function renderUserAvatar() {
        const avatarEl = document.getElementById('navUserAvatar');
        if (!avatarEl) return;
        if (currentPatient?.avatar) {
            avatarEl.style.backgroundImage = `url(${currentPatient.avatar})`;
            avatarEl.style.backgroundColor = 'transparent';
            avatarEl.innerHTML = '';
        } else {
            avatarEl.style.backgroundImage = '';
            avatarEl.style.background = 'linear-gradient(135deg,var(--medical-blue),var(--medical-green))';
            avatarEl.innerHTML = currentPatient?.name
                ? escapeHtml(currentPatient.name.split(/\s+/).map((part) => part[0]).slice(0,2).join('').toUpperCase())
                : '<i class="fas fa-user"></i>';
        }
    }

    function updateProfileAvatarPreview() {
        const preview = document.getElementById('profileAvatarPreview');
        if (!preview) return;
        if (currentPatient?.avatar) {
            preview.style.backgroundImage = `url(${currentPatient.avatar})`;
            preview.innerHTML = '';
        } else {
            preview.style.backgroundImage = '';
            preview.innerHTML = currentPatient?.name
                ? escapeHtml(currentPatient.name.split(/\s+/).map((part) => part[0]).slice(0,2).join('').toUpperCase())
                : '<i class="fas fa-user"></i>';
        }
    }

    function handleProfilePhotoUpload(input) {
        if (!input.files || !input.files[0] || !currentPatient) return;
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function (e) {
            currentPatient.avatar = e.target.result;
            localStorage.setItem('globalhealth_currentPatient', JSON.stringify(currentPatient));
            renderUserAvatar();
            updateProfileAvatarPreview();
        };
        reader.readAsDataURL(file);
    }

    function showProfile() {
        if (!currentPatient) {
            showNotification('Veuillez vous connecter pour voir votre profil', true);
            return;
        }
        document.getElementById('profileName').value = currentPatient.name || '';
        document.getElementById('profileEmail').value = currentPatient.email || '';
        document.getElementById('profileRole').value = currentPatient.role || 'patient';
        const specialiteRow = document.getElementById('profileSpecialiteRow');
        if (currentPatient.role === 'medecin') {
            specialiteRow.style.display = 'block';
            document.getElementById('profileSpecialite').value = currentPatient.specialite || 'Non renseigné';
        } else {
            specialiteRow.style.display = 'none';
        }
        
        // Calculer l'âge à partir de la date de naissance
        let age = '';
        if (currentPatient.date_naissance) {
            const birthDate = new Date(currentPatient.date_naissance);
            const today = new Date();
            age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            age = age.toString();
        }
        document.getElementById('profileAge').value = age;
        document.getElementById('profileSexe').value = currentPatient.sexe || '';
        document.getElementById('profilePoids').value = currentPatient.poids || '';
        document.getElementById('profileTaille').value = currentPatient.taille || '';
        document.getElementById('profileDateNaissance').value = currentPatient.date_naissance || '';
        document.getElementById('profileAdresse').value = currentPatient.adresse || '';
        
        updateProfileAvatarPreview();
        new bootstrap.Modal(document.getElementById('profileModal')).show();
    }

    function saveProfileChanges() {
        if (!currentPatient) return;
        localStorage.setItem('globalhealth_currentPatient', JSON.stringify(currentPatient));
        showNotification('Profil mis à jour');
        renderUserAvatar();
    }

    function savePatients() {
        return;
    }
    
    function showSignInModal() { new bootstrap.Modal(document.getElementById('signinModal')).show(); }
    function showSignUpModal() { new bootstrap.Modal(document.getElementById('signupModal')).show(); }
    function switchToSignUp() { bootstrap.Modal.getInstance(document.getElementById('signinModal')).hide(); showSignUpModal(); }
    function switchToSignIn() { bootstrap.Modal.getInstance(document.getElementById('signupModal')).hide(); showSignInModal(); }
    
    document.getElementById('signupForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const password = document.getElementById('signupPassword').value;
        const confirm = document.getElementById('signupConfirmPassword').value;
        
        if(password !== confirm) {
            showNotification('Les mots de passe ne correspondent pas', true);
            return;
        }
        
        const fullName = document.getElementById('signupName').value.trim();
        const nameParts = fullName.split(/\s+/);
        const nom = nameParts.shift() || '';
        const prenom = nameParts.join(' ') || 'Patient';
        const email = document.getElementById('signupEmail').value.trim();
        const role = document.getElementById('signupRole').value;
        const age = parseInt(document.getElementById('signupAge').value, 10) || 0;
        const sexe = document.getElementById('signupSexe').value;
        const poids = parseFloat(document.getElementById('signupPoids').value) || 0;
        const taille = parseFloat(document.getElementById('signupTaille').value) || 0;
        const dateNaissance = document.getElementById('signupDateNaissance').value;
        const adresse = document.getElementById('signupAdresse').value.trim();
        const specialite = role === 'medecin' ? document.getElementById('signupSpecialite').value.trim() : null;

        const userData = {
            nom,
            prenom,
            age,
            sexe,
            poids,
            taille,
            email,
            mot_de_passe: password,
            date_naissance: dateNaissance,
            adresse,
            role,
            specialite,
            name: `${nom} ${prenom}`.trim()
        };

        const error = validateSignupUser(userData);
        if (error) {
            showNotification(error, true);
            return;
        }

        try {
            const newUser = await apiRequest('create', 'POST', userData);

            currentPatient = { id: newUser.id_user, name: newUser.name, email: newUser.email };
            localStorage.setItem('globalhealth_currentPatient', JSON.stringify(currentPatient));

            bootstrap.Modal.getInstance(document.getElementById('signupModal')).hide();
            document.getElementById('signupForm').reset();
            updateUIForConnectedPatient();
            loadMedicalRecords();
            loadFollowups();
            showNotification(`Bienvenue ${newUser.name} ! Vous êtes maintenant connecté.`);
        } catch (error) {
            showNotification(error.message, true);
        }
    });
    
    document.getElementById('signinForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('signinEmail').value.trim();
        const password = document.getElementById('signinPassword').value;

        if (email === '' || password === '') {
            showNotification('Email et mot de passe sont obligatoires', true);
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showNotification('Email invalide', true);
            return;
        }

        try {
            const patient = await apiRequest('login-patient', 'POST', {
                email,
                mot_de_passe: password
            });
            currentPatient = {
                id: patient.id_user,
                name: patient.name,
                email: patient.email,
                role: patient.role || 'patient',
                specialite: patient.specialite || null,
                avatar: patient.avatar || null,
                age: patient.age || null,
                sexe: patient.sexe || null,
                poids: patient.poids || null,
                taille: patient.taille || null,
                date_naissance: patient.date_naissance || null,
                adresse: patient.adresse || null
            };
            localStorage.setItem('globalhealth_currentPatient', JSON.stringify(currentPatient));
            bootstrap.Modal.getInstance(document.getElementById('signinModal')).hide();
            document.getElementById('signinForm').reset();
            updateUIForConnectedPatient();
            loadMedicalRecords();
            loadFollowups();
            showNotification(`Bon retour ${patient.name} !`);
        } catch (error) {
            showNotification(error.message, true);
        }
    });

    document.getElementById('forgotPasswordForm').addEventListener('submit', async (event) => {
        event.preventDefault();
        const email = document.getElementById('forgotEmail').value.trim();
        const password = document.getElementById('forgotPassword').value;
        const confirmPassword = document.getElementById('forgotConfirmPassword').value;

        if (email === '' || password === '' || confirmPassword === '') {
            showNotification('Tous les champs sont obligatoires', true);
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showNotification('Email invalide', true);
            return;
        }
        if (password.length < 6) {
            showNotification('Le mot de passe doit contenir au moins 6 caractères', true);
            return;
        }
        if (password !== confirmPassword) {
            showNotification('Les mots de passe ne correspondent pas', true);
            return;
        }

        try {
            await apiRequest('reset-password', 'POST', {
                email,
                mot_de_passe: password
            });
            bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
            document.getElementById('forgotPasswordForm').reset();
            showNotification('Mot de passe réinitialisé. Vous pouvez maintenant vous connecter.');
            showSignInModal();
        } catch (error) {
            showNotification(error.message, true);
        }
    });

    function switchToForgotPassword() {
        bootstrap.Modal.getInstance(document.getElementById('signinModal')).hide();
        new bootstrap.Modal(document.getElementById('forgotPasswordModal')).show();
    }

    function switchToSignInFromForgot() {
        bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
        showSignInModal();
    }
    
    function logoutPatient() {
        currentPatient = null;
        localStorage.removeItem('globalhealth_currentPatient');
        updateUIForConnectedPatient();
        showNotification('Vous avez été déconnecté');
    }
    
    function refreshCurrentPatient() {
        if (!currentPatient || !currentPatient.id) return;
        
        apiRequest('get-current-user', 'POST', { id_user: currentPatient.id })
            .then(userData => {
                // Mettre à jour currentPatient avec toutes les données
                currentPatient = {
                    id: userData.id_user,
                    name: userData.name,
                    email: userData.email,
                    role: userData.role || 'patient',
                    specialite: userData.specialite || null,
                    avatar: currentPatient.avatar || null, // Garder l'avatar existant
                    age: userData.age || null,
                    sexe: userData.sexe || null,
                    poids: userData.poids || null,
                    taille: userData.taille || null,
                    date_naissance: userData.date_naissance || null,
                    adresse: userData.adresse || null
                };
                // Sauvegarder dans localStorage
                localStorage.setItem('globalhealth_currentPatient', JSON.stringify(currentPatient));
            })
            .catch(error => {
                console.error('Erreur lors du rafraîchissement des données utilisateur:', error);
            });
    }
    
    function updateUIForConnectedPatient() {
        const userMenu = document.getElementById('userMenu');
        const authButtons = document.getElementById('authButtons');
        
        if(currentPatient) {
            userMenu.style.display = 'flex';
            authButtons.style.display = 'none';
            document.getElementById('id_patient').value = currentPatient.id;
            renderUserAvatar();
            // Rafraîchir les données complètes de l'utilisateur
            refreshCurrentPatient();
        } else {
            userMenu.style.display = 'none';
            authButtons.style.display = 'flex';
            document.getElementById('id_patient').value = '';
            renderUserAvatar();
        }
    }
    
    // ============================================
    // TÉLÉCONSULTATION
    // ============================================
    function joinConsultation() {
        const link = document.getElementById('consultationLink').value.trim();
        if(!link) {
            showNotification('Veuillez entrer un lien de consultation', true);
            return;
        }
        
        if(!link.startsWith('http://') && !link.startsWith('https://')) {
            showNotification('Lien invalide. Le lien doit commencer par http:// ou https://', true);
            return;
        }
        
        // Ouvrir la consultation dans une nouvelle fenêtre
        window.open(link, '_blank');
        showNotification('Ouverture de la consultation...');
        
        // Sauvegarder la consultation dans l'historique
        if(currentPatient) {
            const consultations = JSON.parse(localStorage.getItem(`globalhealth_consultations_${currentPatient.id}`) || '[]');
            consultations.push({
                id: Date.now(),
                link: link,
                date: new Date().toLocaleString('fr-FR'),
                status: 'effectuée'
            });
            localStorage.setItem(`globalhealth_consultations_${currentPatient.id}`, JSON.stringify(consultations));
            loadUpcomingConsultations();
        }
    }
    
    function loadUpcomingConsultations() {
        const container = document.getElementById('upcomingConsultations');
        if(!container || !currentPatient) return;
        
        const consultations = JSON.parse(localStorage.getItem(`globalhealth_consultations_${currentPatient.id}`) || '[]');
        
        if(consultations.length === 0) {
            container.innerHTML = `<div class="empty-state"><i class="fas fa-calendar-alt"></i><p>Aucune consultation prévue</p><small>Prenez rendez-vous pour voir vos consultations ici</small></div>`;
            return;
        }
        
        container.innerHTML = consultations.slice(-5).reverse().map(c => `
            <div class="followup-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong><i class="fas fa-video me-2" style="color: var(--medical-blue);"></i>Consultation</strong><br>
                        <small class="text-muted">${c.date}</small>
                    </div>
                    <span class="badge-medical">${c.status}</span>
                </div>
                <div class="mt-2">
                    <small>Lien: <a href="${c.link}" target="_blank">${c.link.substring(0,50)}...</a></small>
                </div>
            </div>
        `).join('');
    }
    
    // ============================================
    // SUIVI DE CONSULTATION
    // ============================================
    let followups = [];
    let tempFollowupFile = null;
    let tempFollowupFileName = null;
    
    function loadFollowups() {
        if(!currentPatient) return;
        const stored = localStorage.getItem(`globalhealth_followups_${currentPatient.id}`);
        if(stored) followups = JSON.parse(stored);
        else followups = [];
        renderFollowups();
    }
    
    function saveFollowups() {
        if(currentPatient) localStorage.setItem(`globalhealth_followups_${currentPatient.id}`, JSON.stringify(followups));
    }
    
    function uploadFollowupFile(input) {
        if(input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                tempFollowupFile = e.target.result;
                tempFollowupFileName = file.name;
                document.getElementById('followupFileName').innerHTML = `<i class="fas fa-check-circle text-success me-1"></i>${file.name}`;
                showNotification(`Fichier "${file.name}" chargé`);
            };
            reader.readAsDataURL(file);
        }
    }
    
    document.getElementById('followupForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        
        if(!currentPatient) {
            showNotification('Veuillez vous connecter pour créer un suivi', true);
            showSignInModal();
            return;
        }
        
        const newFollowup = {
            id: Date.now(),
            patient_id: currentPatient.id,
            patient_name: currentPatient.name,
            date: document.getElementById('followupDate').value,
            doctor_id: parseInt(document.getElementById('followupDoctor').value),
            doctor_name: document.getElementById('followupDoctor').options[document.getElementById('followupDoctor').selectedIndex]?.text || '',
            subject: document.getElementById('followupSubject').value,
            content: document.getElementById('followupContent').value,
            attachment: tempFollowupFile || null,
            attachment_name: tempFollowupFileName || null,
            created_at: new Date().toLocaleString('fr-FR')
        };
        
        followups.unshift(newFollowup);
        saveFollowups();
        renderFollowups();
        
        // Réinitialiser le formulaire
        document.getElementById('followupForm').reset();
        document.getElementById('followupFileName').innerHTML = '';
        tempFollowupFile = null;
        tempFollowupFileName = null;
        document.getElementById('followupFile').value = '';
        
        showNotification('Suivi enregistré avec succès');
    });
    
    function renderFollowups() {
        const container = document.getElementById('followupList');
        if(!container) return;
        
        if(followups.length === 0) {
            container.innerHTML = `<div class="empty-state"><i class="fas fa-notes-medical"></i><p>Aucun suivi enregistré</p><small>Créez votre premier suivi de consultation</small></div>`;
            return;
        }
        
        container.innerHTML = followups.map(f => `
            <div class="followup-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong><i class="fas fa-stethoscope me-2" style="color: var(--medical-blue);"></i>${escapeHtml(f.subject)}</strong>
                        <br><small class="text-muted">${f.date} - Dr. ${escapeHtml(f.doctor_name)}</small>
                    </div>
                    <small class="text-muted">${f.created_at}</small>
                </div>
                <div class="mt-2">
                    <p class="mb-2">${escapeHtml(f.content.substring(0,200))}${f.content.length > 200 ? '...' : ''}</p>
                    ${f.attachment ? `<a href="#" onclick="viewFollowupAttachment(${f.id})" class="small"><i class="fas fa-paperclip me-1"></i>${escapeHtml(f.attachment_name)}</a>` : ''}
                    ${f.content.length > 200 ? `<button class="btn btn-link btn-sm p-0 ms-2" onclick="expandFollowup(${f.id})">Voir plus</button>` : ''}
                </div>
                <div class="mt-2">
                    <button class="icon-btn edit" onclick="editFollowup(${f.id})"><i class="fas fa-edit"></i></button>
                    <button class="icon-btn delete" onclick="deleteFollowup(${f.id})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `).join('');
    }
    
    function expandFollowup(id) {
        const followup = followups.find(f => f.id === id);
        if(followup) {
            showNotification(followup.content);
        }
    }
    
    function viewFollowupAttachment(id) {
        const followup = followups.find(f => f.id === id);
        if(followup && followup.attachment) {
            const win = window.open();
            win.document.write(`<iframe src="${followup.attachment}" style="width:100%;height:100%;border:none;"></iframe>`);
        } else {
            showNotification('Aucun fichier attaché', true);
        }
    }
    
    function editFollowup(id) {
        const followup = followups.find(f => f.id === id);
        if(followup) {
            document.getElementById('followupDate').value = followup.date;
            document.getElementById('followupDoctor').value = followup.doctor_id;
            document.getElementById('followupSubject').value = followup.subject;
            document.getElementById('followupContent').value = followup.content;
            showNotification(`Modification du suivi du ${followup.date}`);
            // Supprimer l'ancien après modification
            followups = followups.filter(f => f.id !== id);
            saveFollowups();
            renderFollowups();
        }
    }
    
    function deleteFollowup(id) {
        if(confirm('Supprimer ce suivi ?')) {
            followups = followups.filter(f => f.id !== id);
            saveFollowups();
            renderFollowups();
            showNotification('Suivi supprimé');
        }
    }
    
    // ============================================
    // DOSSIER MÉDICAL
    // ============================================
    let medicalRecords = [];
    let currentEditId = null;
    let tempOrdonnanceFile = null;
    let tempOrdonnanceFileName = null;
    
    function loadMedicalRecords() {
        if(!currentPatient) return;
        const stored = localStorage.getItem(`globalhealth_medicalRecords_${currentPatient.id}`);
        if(stored) medicalRecords = JSON.parse(stored);
        else medicalRecords = [];
        renderMedicalRecords();
    }
    
    function saveMedicalRecords() {
        if(currentPatient) localStorage.setItem(`globalhealth_medicalRecords_${currentPatient.id}`, JSON.stringify(medicalRecords));
    }
    
    function renderMedicalRecords() {
        const container = document.getElementById('medicalRecordsList');
        const countSpan = document.getElementById('recordCount');
        
        if(!container) return;
        
        if(medicalRecords.length === 0) {
            container.innerHTML = `<div class="empty-state"><i class="fas fa-folder-open"></i><p>Aucun dossier médical</p><small>Créez votre premier dossier médical ci-dessus</small></div>`;
            if(countSpan) countSpan.textContent = '0 dossier(s)';
            return;
        }
        
        if(countSpan) countSpan.textContent = `${medicalRecords.length} dossier(s)`;
        
        container.innerHTML = medicalRecords.map(record => `
            <div class="medical-record-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge-medical"><i class="fas fa-folder-open me-1"></i> Dossier #${record.id_dossier}</span>
                        <span class="badge-medical ms-2"><i class="far fa-calendar-alt me-1"></i> ${record.date_creation}</span>
                    </div>
                    <div>
                        <button class="icon-btn edit" onclick="editMedicalRecord(${record.id_dossier})"><i class="fas fa-edit"></i></button>
                        <button class="icon-btn delete" onclick="deleteMedicalRecord(${record.id_dossier})"><i class="fas fa-trash"></i></button>
                        <button class="icon-btn" onclick="viewHistory(${record.id_dossier})"><i class="fas fa-history"></i></button>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6"><strong><i class="fas fa-stethoscope"></i> Symptômes:</strong><br><small>${escapeHtml(record.symptomes || '-')}</small></div>
                    <div class="col-md-6"><strong><i class="fas fa-diagnoses"></i> Diagnostic:</strong><br><small>${escapeHtml(record.diagnostic || '-')}</small></div>
                    <div class="col-md-6"><strong><i class="fas fa-pills"></i> Traitement:</strong><br><small>${escapeHtml(record.traitement || '-')}</small></div>
                    <div class="col-md-6"><strong><i class="fas fa-file-prescription"></i> Ordonnance:</strong><br>
                        ${record.ordonnance_type === 'file' ? `<a href="#" onclick="viewOrdonnanceFile(${record.id_dossier})"><i class="fas fa-file-pdf"></i> ${escapeHtml(record.ordonnance_filename)}</a>` : `<small>${escapeHtml(record.ordonnance || '-')}</small>`}
                    </div>
                    <div class="col-12"><strong><i class="fas fa-comment-dots"></i> Notes médecin:</strong><br><small>${escapeHtml(record.notes_medecin || '-')}</small></div>
                </div>
            </div>
        `).join('');
    }
    
    function uploadOrdonnanceFile(input) {
        if(input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                tempOrdonnanceFile = e.target.result;
                tempOrdonnanceFileName = file.name;
                showNotification(`Fichier "${file.name}" chargé`);
            };
            reader.readAsDataURL(file);
        }
    }
    
    function viewOrdonnanceFile(id) {
        const record = medicalRecords.find(r => r.id_dossier === id);
        if(record && record.ordonnance_type === 'file' && record.ordonnance) {
            const win = window.open();
            win.document.write(`<iframe src="${record.ordonnance}" style="width:100%;height:100%;border:none;"></iframe>`);
        } else {
            showNotification('Aucun fichier d\'ordonnance disponible', true);
        }
    }
    
    function addHistoryEntry(recordId, action, details) {
        const record = medicalRecords.find(r => r.id_dossier === recordId);
        if(record) {
            if(!record.historique_modification) record.historique_modification = [];
            record.historique_modification.push({
                date: new Date().toLocaleString('fr-FR'),
                action: action,
                details: details
            });
            saveMedicalRecords();
        }
    }
    
    function viewHistory(id) {
        const record = medicalRecords.find(r => r.id_dossier === id);
        if(record && record.historique_modification && record.historique_modification.length > 0) {
            const historyCard = document.getElementById('historyCard');
            const historyList = document.getElementById('historyList');
            historyList.innerHTML = `
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Action</th><th>Détails</th></tr></thead>
                    <tbody>
                        ${record.historique_modification.map(h => `
                            <tr><td>${h.date}</td><td>${h.action}</td><td>${escapeHtml(h.details)}</td></tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            historyCard.style.display = 'block';
            historyCard.scrollIntoView({ behavior: 'smooth' });
        } else {
            showNotification('Aucun historique pour ce dossier');
        }
    }
    
    document.getElementById('medicalRecordForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        
        if(!currentPatient) {
            showNotification('Veuillez vous connecter pour créer un dossier médical', true);
            showSignInModal();
            return;
        }
        
        const medecinId = document.getElementById('id_medecin').value;
        if(!medecinId) {
            showNotification('Veuillez sélectionner un médecin', true);
            return;
        }
        
        const now = new Date().toLocaleDateString('fr-FR');
        
        if(currentEditId) {
            const index = medicalRecords.findIndex(r => r.id_dossier === currentEditId);
            if(index !== -1) {
                medicalRecords[index] = {
                    ...medicalRecords[index],
                    id_medecin: parseInt(medecinId),
                    symptomes: document.getElementById('symptomes').value,
                    diagnostic: document.getElementById('diagnostic').value,
                    traitement: document.getElementById('traitement').value,
                    ordonnance: tempOrdonnanceFile || document.getElementById('ordonnance_texte').value,
                    ordonnance_type: tempOrdonnanceFile ? 'file' : 'text',
                    ordonnance_filename: tempOrdonnanceFileName || null,
                    notes_medecin: document.getElementById('notes_medecin').value
                };
                addHistoryEntry(currentEditId, 'MODIFICATION', 'Dossier médical modifié');
                showNotification('Dossier médical modifié avec succès');
            }
            currentEditId = null;
        } else {
            const newRecord = {
                id_dossier: Date.now(),
                id_patient: currentPatient.id,
                id_medecin: parseInt(medecinId),
                id_rdv: document.getElementById('id_rdv').value ? parseInt(document.getElementById('id_rdv').value) : null,
                symptomes: document.getElementById('symptomes').value,
                diagnostic: document.getElementById('diagnostic').value,
                traitement: document.getElementById('traitement').value,
                ordonnance: tempOrdonnanceFile || document.getElementById('ordonnance_texte').value,
                ordonnance_type: tempOrdonnanceFile ? 'file' : 'text',
                ordonnance_filename: tempOrdonnanceFileName || null,
                notes_medecin: document.getElementById('notes_medecin').value,
                date_creation: now,
                historique_modification: [{
                    date: now,
                    action: 'CRÉATION',
                    details: 'Dossier médical créé'
                }]
            };
            medicalRecords.push(newRecord);
            showNotification('Dossier médical créé avec succès');
        }
        
        saveMedicalRecords();
        renderMedicalRecords();
        resetMedicalForm();
        
        tempOrdonnanceFile = null;
        tempOrdonnanceFileName = null;
        document.getElementById('ordonnanceFile').value = '';
    });
    
    function editMedicalRecord(id) {
        const record = medicalRecords.find(r => r.id_dossier === id);
        if(record) {
            currentEditId = id;
            document.getElementById('id_medecin').value = record.id_medecin;
            document.getElementById('id_rdv').value = record.id_rdv || '';
            document.getElementById('symptomes').value = record.symptomes || '';
            document.getElementById('diagnostic').value = record.diagnostic || '';
            document.getElementById('traitement').value = record.traitement || '';
            if(record.ordonnance_type === 'text') {
                document.getElementById('ordonnance_texte').value = record.ordonnance || '';
            } else {
                document.getElementById('ordonnance_texte').value = '';
                showNotification(`Fichier existant: ${record.ordonnance_filename}`);
            }
            document.getElementById('notes_medecin').value = record.notes_medecin || '';
            document.getElementById('dossier').scrollIntoView({ behavior: 'smooth' });
            showNotification(`Modification du dossier #${id}`);
        }
    }
    
    function deleteMedicalRecord(id) {
        if(confirm('Supprimer définitivement ce dossier médical ?')) {
            medicalRecords = medicalRecords.filter(r => r.id_dossier !== id);
            saveMedicalRecords();
            renderMedicalRecords();
            showNotification('Dossier médical supprimé');
            if(currentEditId === id) resetMedicalForm();
        }
    }
    
    function resetMedicalForm() {
        currentEditId = null;
        document.getElementById('medicalRecordForm').reset();
        document.getElementById('id_medecin').value = '';
        document.getElementById('id_rdv').value = '';
        tempOrdonnanceFile = null;
        tempOrdonnanceFileName = null;
        document.getElementById('ordonnanceFile').value = '';
    }
    
    // ============================================
    // DONNÉES PUBLIQUES
    // ============================================
    let doctorsData = [];
    let forumPosts = [];
    let reviewsData = [];
    let appointmentsData = [];
    
    async function loadPublicData() {
        try {
            doctorsData = await apiRequest('doctors', 'GET');
        } catch (error) {
            doctorsData = [];
            showNotification(`Erreur chargement médecins: ${error.message}`, true);
        }
        
        const storedPosts = localStorage.getItem('globalhealth_forumPosts');
        if(storedPosts) forumPosts = JSON.parse(storedPosts);
        else forumPosts = [];
        
        const storedReviews = localStorage.getItem('globalhealth_reviews');
        if(storedReviews) reviewsData = JSON.parse(storedReviews);
        else reviewsData = [];
        
        const storedAppointments = localStorage.getItem('globalhealthBack_appointments');
        if(storedAppointments) appointmentsData = JSON.parse(storedAppointments);
        else appointmentsData = [];
        
        renderDoctors();
        renderForumPosts();
        renderReviews();
        updateDoctorSelect();
        updateMedecinSelect();
        updateRdvSelect();
        updateFollowupDoctorSelect();
        loadUpcomingConsultations();
    }
    
    function updateMedecinSelect() {
        const select = document.getElementById('id_medecin');
        if(select) {
            select.innerHTML = '<option value="">Sélectionnez un médecin</option>' + 
                doctorsData.map(d => `<option value="${d.id}">${escapeHtml(d.name)} - ${escapeHtml(d.specialite || 'Généraliste')}</option>`).join('');
        }
    }
    
    function updateFollowupDoctorSelect() {
        const select = document.getElementById('followupDoctor');
        if(select) {
            select.innerHTML = '<option value="">Sélectionnez un médecin</option>' + 
                doctorsData.map(d => `<option value="${d.id}">${escapeHtml(d.name)} - ${escapeHtml(d.specialite || 'Généraliste')}</option>`).join('');
        }
    }
    
    function updateRdvSelect() {
        const select = document.getElementById('id_rdv');
        if(select && currentPatient) {
            const patientRdvs = appointmentsData.filter(a => a.patient_id === currentPatient.id);
            select.innerHTML = '<option value="">Sélectionnez un rendez-vous (optionnel)</option>' + 
                patientRdvs.map(a => `<option value="${a.id}">${a.date} - Dr. ${a.doctor_name}</option>`).join('');
        }
    }
    
    function renderDoctors() {
        const container = document.getElementById('doctorsList');
        if(!container) return;
        
        if(doctorsData.length === 0) {
            container.innerHTML = `<div class="col-12"><div class="empty-state"><i class="fas fa-user-md"></i><p>Aucun médecin disponible pour le moment.</p><small>Les médecins seront ajoutés prochainement.</small></div></div>`;
            return;
        }
        
        container.innerHTML = doctorsData.map(doctor => {
            const doctorReviews = reviewsData.filter(r => r.doctor_id === doctor.id && r.status === 'approved');
            const avgRating = doctorReviews.length > 0 ? doctorReviews.reduce((s,r) => s + r.rating, 0) / doctorReviews.length : 0;
            const ratingCount = doctorReviews.length;
            
            return `
            <div class="col-md-3">
                <div class="doctor-card">
                    <div class="doctor-avatar-lg"><i class="fas fa-user-md"></i></div>
                    <h5>${escapeHtml(doctor.name)}</h5>
                    <p class="text-muted">${escapeHtml(doctor.specialite || 'Médecin généraliste')}</p>
                    <div class="doctor-rating mb-2">
                        ${renderStarsStatic(avgRating)}
                        <span class="ms-2">${avgRating.toFixed(1)} (${ratingCount} avis)</span>
                    </div>
                    <button class="btn btn-outline-medical w-100 mt-3" onclick="selectDoctorForReview(${doctor.id}, '${escapeHtml(doctor.name)}')">Donner mon avis</button>
                </div>
            </div>`;
        }).join('');
    }
    
    function renderStarsStatic(rating) {
        let s = '';
        for(let i=1; i<=5; i++) {
            if(i <= Math.round(rating)) s += '<i class="fas fa-star"></i>';
            else s += '<i class="far fa-star"></i>';
        }
        return `<span class="rating-stars">${s}</span>`;
    }
    
    function renderStars(rating) {
        let s = '';
        for(let i=1; i<=5; i++) {
            if(i <= rating) s += '<i class="fas fa-star"></i>';
            else s += '<i class="far fa-star"></i>';
        }
        return s;
    }
    
    function updateDoctorSelect() {
        const select = document.getElementById('reviewDoctorId');
        if(select) {
            select.innerHTML = '<option value="">Sélectionnez un médecin</option>' + 
                doctorsData.map(d => `<option value="${d.id}">${escapeHtml(d.name)} - ${escapeHtml(d.specialite || 'Généraliste')}</option>`).join('');
        }
    }
    
    function renderForumPosts() {
        const container = document.getElementById('forumPostsList');
        if(!container) return;
        
        const approvedPosts = forumPosts.filter(p => p.status === 'approved');
        if(approvedPosts.length === 0) {
            container.innerHTML = `<div class="empty-state"><i class="fas fa-newspaper"></i><p>Aucune publication pour le moment.</p><small>Les médecins publieront bientôt du contenu.</small></div>`;
            return;
        }
        
        container.innerHTML = approvedPosts.map(post => `
            <div class="forum-card">
                <div class="forum-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="doctor-avatar">${post.doctor_avatar || post.doctor_name.substring(0,2).toUpperCase()}</div>
                        <div>
                            <h5 class="mb-0">${escapeHtml(post.doctor_name)}</h5>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>${post.date}</small>
                        </div>
                    </div>
                    <span class="badge" style="background: var(--medical-green);"><i class="fas fa-check-circle"></i> Médecin</span>
                </div>
                <div class="forum-content">
                    <p>${escapeHtml(post.content)}</p>
                    ${post.image ? `<img src="${post.image}" class="forum-media mt-2" alt="Image">` : ''}
                    ${post.video ? `<video class="forum-media mt-2" controls><source src="${post.video}" type="video/mp4"></video>` : ''}
                </div>
                <div class="forum-stats">
                    <span><i class="far fa-comment me-1"></i> ${(post.comments || []).filter(c => c.status === 'approved').length} commentaires</span>
                </div>
                <div class="forum-content pt-0">
                    <h6 class="mb-3"><i class="fas fa-comments me-2"></i>Commentaires</h6>
                    ${(post.comments || []).filter(c => c.status === 'approved').map(comment => `
                        <div class="comment-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div><strong>${escapeHtml(comment.user_name)}</strong><br><small>${comment.date}</small></div>
                            </div>
                            <p class="mb-0 mt-2">${escapeHtml(comment.text)}</p>
                        </div>
                    `).join('')}
                    <div class="mt-3">
                        <form onsubmit="addComment(${post.id}, this); return false;">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Ajouter un commentaire..." name="commentText" required>
                                <button class="btn btn-medical" type="submit">Envoyer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    function addComment(postId, form) {
        if(!currentPatient) {
            showNotification('Veuillez vous connecter pour commenter', true);
            showSignInModal();
            return;
        }
        
        const input = form.querySelector('input[name="commentText"]');
        const text = input.value.trim();
        if(!text) return;
        const post = forumPosts.find(p => p.id === postId);
        if(post) {
            if(!post.comments) post.comments = [];
            post.comments.push({
                id: Date.now(),
                user_name: currentPatient.name,
                text: text,
                status: "pending",
                date: new Date().toLocaleDateString('fr-FR')
            });
            localStorage.setItem('globalhealth_forumPosts', JSON.stringify(forumPosts));
            showNotification("Commentaire ajouté, en attente de modération");
            renderForumPosts();
            input.value = '';
        }
    }
    
    function renderReviews() {
        const container = document.getElementById('reviewsList');
        if(!container) return;
        
        const approvedReviews = reviewsData.filter(r => r.status === 'approved');
        if(approvedReviews.length === 0) {
            container.innerHTML = `<div class="empty-state"><i class="fas fa-star"></i><p>Aucun avis pour le moment.</p><small>Soyez le premier à donner votre avis !</small></div>`;
            return;
        }
        
        container.innerHTML = `<div class="row">${approvedReviews.map(r => `
            <div class="col-md-6">
                <div class="review-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${escapeHtml(r.patient_name)}</strong>
                            <div class="mt-1">${renderStars(r.rating)}</div>
                        </div>
                        <small class="text-muted">${r.date}</small>
                    </div>
                    <p class="mt-2 mb-0">${escapeHtml(r.comment)}</p>
                    <small class="text-muted">Consultation avec ${escapeHtml(r.doctor_name)}</small>
                </div>
            </div>
        `).join('')}</div>`;
    }
    
    function escapeHtml(str) {
        if(!str) return '';
        return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));
    }
    
    function selectDoctorForReview(doctorId, doctorName) {
        if(!currentPatient) {
            showNotification('Veuillez vous connecter pour donner votre avis', true);
            showSignInModal();
            return;
        }
        const select = document.getElementById('reviewDoctorId');
        if(select) select.value = doctorId;
        document.getElementById('reviewFormContainer').scrollIntoView({ behavior: 'smooth' });
    }
    
    document.getElementById('appointmentForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        if(!currentPatient) {
            showNotification('Veuillez vous connecter pour prendre rendez-vous', true);
            showSignInModal();
            return;
        }
        showNotification('Votre demande de rendez-vous a été envoyée ! Un médecin vous contactera.');
        e.target.reset();
    });
    
    document.getElementById('submitReviewForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        if(!currentPatient) {
            showNotification('Veuillez vous connecter pour donner votre avis', true);
            showSignInModal();
            return;
        }
        
        const selectedRating = document.querySelector('input[name="reviewRating"]:checked');
        const rating = selectedRating ? parseInt(selectedRating.value) : 0;
        if(rating === 0) { showNotification('Veuillez sélectionner une note', true); return; }
        
        const doctorId = parseInt(document.getElementById('reviewDoctorId').value);
        const doctor = doctorsData.find(d => d.id === doctorId);
        if(!doctor) { showNotification('Veuillez sélectionner un médecin', true); return; }
        
        const newReview = {
            id: Date.now(),
            patient_name: currentPatient.name,
            doctor_id: doctorId,
            doctor_name: doctor.name,
            rating: rating,
            comment: document.getElementById('reviewComment').value,
            date: new Date().toLocaleDateString('fr-FR'),
            status: 'pending'
        };
        reviewsData.push(newReview);
        localStorage.setItem('globalhealth_reviews', JSON.stringify(reviewsData));
        showNotification('Merci pour votre avis ! Il sera publié après modération par l\'administrateur.');
        e.target.reset();
        document.querySelectorAll('input[name="reviewRating"]').forEach(r => r.checked = false);
        renderReviews();
        renderDoctors();
    });
    
    function showAppointments() { showNotification('Aucun rendez-vous pour le moment'); }
    function showMedicalFolder() { document.getElementById('dossier').scrollIntoView({ behavior: 'smooth' }); }
    
    function toggleChatbot() { document.getElementById('chatbotWindow').classList.toggle('show'); }
    function sendMessage() {
        const input = document.getElementById('chatInput');
        const msg = input.value.trim();
        if(!msg) return;
        const messagesDiv = document.getElementById('chatMessages');
        messagesDiv.innerHTML += `<div class="message user">${escapeHtml(msg)}</div>`;
        input.value = '';
        setTimeout(() => {
            let response = "🤖 Merci pour votre message. Notre équipe médicale vous répondra dans les plus brefs délais.";
            if(msg.toLowerCase().includes("symptome") || msg.toLowerCase().includes("douleur")) {
                response = "🤖 Je vous conseille de consulter un médecin rapidement. Vous pouvez prendre rendez-vous sur notre plateforme.";
            } else if(msg.toLowerCase().includes("rendez-vous") || msg.toLowerCase().includes("rdv")) {
                response = "🤖 Pour prendre un rendez-vous, rendez-vous dans la section 'Consultation' ci-dessus.";
            } else if(msg.toLowerCase().includes("teleconsultation") || msg.toLowerCase().includes("visio")) {
                response = "🤖 Pour une téléconsultation, allez dans la section 'Téléconsultation' et entrez le lien fourni par votre médecin.";
            }
            messagesDiv.innerHTML += `<div class="message bot">${response}</div>`;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }, 500);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
    
    function showNotification(msg, isError=false) {
        const t = document.getElementById('notificationToast');
        if(t){
            t.textContent = msg;
            t.style.borderLeftColor = isError ? '#dc3545' : '#2ecc71';
            t.classList.add('show');
            setTimeout(()=>t.classList.remove('show'),3000);
        }
    }
    
    document.addEventListener('DOMContentLoaded', async () => {
        const savedPatient = localStorage.getItem('globalhealth_currentPatient');
        if(savedPatient){ 
            try{ 
                currentPatient = JSON.parse(savedPatient); 
                updateUIForConnectedPatient();
                loadMedicalRecords();
                loadFollowups();
            }catch(e){} 
        }
        await loadPatients();
        await loadPublicData();
        if(currentPatient) {
            loadMedicalRecords();
            loadFollowups();
        }
    });
</script>
</body>
</html>