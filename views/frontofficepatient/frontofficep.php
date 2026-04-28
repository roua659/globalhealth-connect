<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Publication.php';

// Récupérer uniquement les publications approuvées
$publications = [];
try {
    $pdo = config::getConnexion();
    try {
        $stmt = $pdo->prepare("SELECT * FROM publication WHERE statut = 'approved' ORDER BY date_publication DESC LIMIT 20");
        $stmt->execute();
        $publications = $stmt->fetchAll();
    } catch (Exception $e) {
        // Fallback si la colonne statut n'existe pas encore
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
        /* ========== STYLES COMPLETS (identiques à l'original) ========== */
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
        .medical-folder-card {
            background: linear-gradient(135deg, var(--medical-light-blue), white);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(43,123,228,0.2);
        }
        .consultation-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .followup-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(0,0,0,0.05);
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
        }
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
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c7a8a;
        }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; opacity: 0.3; }
        .is-invalid { border-color: #dc3545 !important; }
        .invalid-feedback { display: block; color: #dc3545; font-size: 0.875rem; }
        @media (max-width: 768px) {
            .hero-title { font-size: 2rem; }
            .chatbot-window { width: 320px; right: -50px; }
        }
    </style>
</head>
<body>

<!-- ========== NAVBAR ========== -->
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
                        <li><a class="dropdown-item" href="#" onclick="showProfile()">Mon profil</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showAppointments()">Mes RDV</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showMedicalFolder()">Mon dossier médical</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logoutPatient()">Déconnexion</a></li>
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

<!-- ========== SECTION HERO ========== -->
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

<!-- ========== SECTION CONSULTATION ========== -->
<section id="consultation" class="py-5" style="background: var(--medical-light-blue);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="review-form-container">
                    <h3 class="text-center mb-4"><i class="fas fa-calendar-plus me-2" style="color: var(--medical-blue);"></i>Prendre rendez-vous</h3>
                    <form id="appointmentForm" novalidate>
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

<!-- ========== SECTION TÉLÉCONSULTATION ========== -->
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
                        <button class="btn btn-medical" type="button" onclick="joinConsultation()"><i class="fas fa-video me-2"></i>Rejoindre</button>
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
                    <div id="upcomingConsultations"><div class="empty-state"><i class="fas fa-calendar-alt"></i><p>Aucune consultation prévue</p><small>Prenez rendez-vous pour voir vos consultations ici</small></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== SECTION SUIVI ========== -->
<section id="suivi" class="py-5" style="background: var(--medical-gray);">
    <div class="container">
        <h2 class="section-title"><i class="fas fa-notes-medical me-2"></i>Suivi de consultation</h2>
        <p class="text-center mb-5">Documentez et suivez l'évolution de votre état de santé</p>
        <div class="row">
            <div class="col-lg-5">
                <div class="consultation-card">
                    <h4><i class="fas fa-plus-circle me-2"></i>Nouveau suivi</h4>
                    <form id="followupForm" novalidate>
                        <div class="mb-3"><label>Date de la consultation</label><input type="date" class="form-control form-control-medical" id="followupDate" required></div>
                        <div class="mb-3"><label>Médecin consulté</label><select class="form-select form-control-medical" id="followupDoctor" required><option value="">Sélectionnez un médecin</option></select></div>
                        <div class="mb-3"><label>Sujet / Motif</label><input type="text" class="form-control form-control-medical" id="followupSubject" placeholder="Ex: Consultation pour douleurs dorsales" required></div>
                        <div class="mb-3"><label>Compte-rendu détaillé</label><textarea class="followup-textarea" id="followupContent" rows="6" placeholder="Décrivez ce qui s'est passé..." required></textarea></div>
                        <div class="mb-3"><label>Documents joints</label><div class="file-upload-area" onclick="document.getElementById('followupFile').click()"><i class="fas fa-paperclip me-2"></i><small>Cliquez pour joindre un fichier</small><input type="file" id="followupFile" style="display:none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="uploadFollowupFile(this)"></div><div id="followupFileName" class="mt-2 small text-muted"></div></div>
                        <button type="submit" class="btn btn-medical w-100"><i class="fas fa-save me-2"></i>Enregistrer le suivi</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="consultation-card"><h4><i class="fas fa-history me-2"></i>Historique des suivis</h4><div id="followupList"><div class="empty-state"><i class="fas fa-notes-medical"></i><p>Aucun suivi enregistré</p><small>Créez votre premier suivi de consultation</small></div></div></div>
            </div>
        </div>
    </div>
</section>

<!-- ========== SECTION MÉDECINS ========== -->
<section id="medecins" class="py-5" style="background: white;">
    <div class="container"><h2 class="section-title">Nos médecins experts</h2><div id="doctorsList" class="row"><div class="col-12"><div class="empty-state"><i class="fas fa-user-md"></i><p>Aucun médecin disponible pour le moment.</p></div></div></div></div>
</section>

<!-- ========== SECTION DOSSIER MÉDICAL ========== -->
<section id="dossier" class="py-5" style="background: linear-gradient(135deg, #e8f8f0, #e8f4ff);">
    <div class="container">
        <h2 class="section-title">Mon Dossier Médical</h2>
        <p class="text-center mb-4">Gérez l'ensemble de vos informations médicales en toute sécurité</p>
        <div class="medical-folder-card">
            <h5 class="mb-4">Nouveau dossier médical</h5>
            <form id="medicalRecordForm" novalidate>
                <div class="row g-3">
                    <div class="col-md-6"><label>ID Patient</label><input type="text" class="form-control form-control-medical" id="id_patient" readonly></div>
                    <div class="col-md-6"><label>Médecin traitant</label><select class="form-select form-control-medical" id="id_medecin" required><option value="">Sélectionnez un médecin</option></select></div>
                    <div class="col-md-6"><label>ID Rendez-vous</label><select class="form-select form-control-medical" id="id_rdv"><option value="">Sélectionnez un rendez-vous (optionnel)</option></select></div>
                    <div class="col-md-6"><label>Symptômes</label><textarea class="form-control form-control-medical" id="symptomes" rows="2" placeholder="Décrivez vos symptômes..."></textarea></div>
                    <div class="col-md-6"><label>Diagnostic</label><textarea class="form-control form-control-medical" id="diagnostic" rows="2" placeholder="Diagnostic médical..."></textarea></div>
                    <div class="col-md-6"><label>Traitement</label><textarea class="form-control form-control-medical" id="traitement" rows="2" placeholder="Traitement prescrit..."></textarea></div>
                    <div class="col-12"><label>Ordonnance</label><div class="row g-2"><div class="col-md-8"><textarea class="form-control form-control-medical" id="ordonnance_texte" rows="3" placeholder="Ordonnance (texte) : Médicaments, posologie..."></textarea></div><div class="col-md-4"><div class="file-upload-area" onclick="document.getElementById('ordonnanceFile').click()"><i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><p class="mb-0"><small>ou télécharger un fichier</small></p><input type="file" id="ordonnanceFile" style="display:none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="uploadOrdonnanceFile(this)"></div></div></div></div>
                    <div class="col-12"><label>Notes du médecin</label><textarea class="form-control form-control-medical" id="notes_medecin" rows="2" placeholder="Notes complémentaires..."></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-medical">Enregistrer le dossier</button><button type="button" class="btn btn-outline-medical ms-2" onclick="resetMedicalForm()">Réinitialiser</button></div>
                </div>
            </form>
        </div>
        <div class="medical-folder-card"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Mes dossiers médicaux</h5><span class="badge-medical" id="recordCount">0 dossier(s)</span></div><div id="medicalRecordsList"><div class="empty-state"><i class="fas fa-folder-open"></i><p>Aucun dossier médical</p><small>Créez votre premier dossier médical ci-dessus</small></div></div></div>
        <div class="medical-folder-card" id="historyCard" style="display:none;"><h5 class="mb-3">Historique des modifications</h5><div id="historyList"></div></div>
    </div>
</section>

<!-- ========== SECTION FORUM ========== -->
<section id="forum" class="py-5" style="background: var(--medical-gray);">
    <div class="container">
        <h2 class="section-title">Forum Médical</h2>
        <p class="text-center mb-5">Les médecins partagent leurs publications, les patients commentent et notent les consultations</p>
        
        <!-- Formulaire pour noter un médecin -->
        <div class="review-form-container" id="reviewFormContainer">
            <h4><i class="fas fa-star text-warning me-2"></i>Noter votre consultation</h4>
            <form id="submitReviewForm" novalidate>
                <div class="row g-3">
                    <div class="col-md-6"><input type="text" class="form-control form-control-medical" id="reviewName" placeholder="Votre nom" required></div>
                    <div class="col-md-6"><select class="form-select form-control-medical" id="reviewDoctorId" required><option value="">Sélectionnez un médecin</option></select></div>
                    <div class="col-12"><div class="rating-input"><input type="radio" name="reviewRating" value="5" id="rating5"><label for="rating5">★</label><input type="radio" name="reviewRating" value="4" id="rating4"><label for="rating4">★</label><input type="radio" name="reviewRating" value="3" id="rating3"><label for="rating3">★</label><input type="radio" name="reviewRating" value="2" id="rating2"><label for="rating2">★</label><input type="radio" name="reviewRating" value="1" id="rating1"><label for="rating1">★</label></div></div>
                    <div class="col-12"><textarea class="form-control form-control-medical" id="reviewComment" rows="3" placeholder="Partagez votre expérience avec le médecin..." required></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-medical"><i class="fas fa-paper-plane me-2"></i>Publier mon avis</button></div>
                </div>
            </form>
        </div>

        <!-- Liste des publications (sans aucun bouton d'ajout/modification) -->
        <div id="forumPostsList">
            <?php if(count($publications) > 0): ?>
                <?php foreach($publications as $pub):
                    // Récupérer le nom du médecin
                    $doctorName = 'Dr. Médecin';
                    if(!empty($pub['id_medecin'])) {
                        try {
                            $pdo = config::getConnexion();
                            $stmt = $pdo->prepare("
                                SELECT u.nom, u.prenom
                                FROM medecin m
                                INNER JOIN utilisateur u ON u.id_user = m.id_user
                                WHERE m.id_medecin = ?
                            ");
                            $stmt->execute([$pub['id_medecin']]);
                            $doctor = $stmt->fetch();
                            if($doctor) $doctorName = $doctor['nom'] . ' ' . $doctor['prenom'];
                        } catch(Exception $e) {}
                    }
                ?>
                    <div class="forum-post-card" data-pub-id="<?= $pub['id_publication'] ?>">
                        <div class="post-header">
                            <div class="post-author">
                                <div class="author-avatar" style="background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));">
                                    <?= strtoupper(substr($doctorName,0,1)) ?>
                                </div>
                                <div>
                                    <h5><?= htmlspecialchars($doctorName) ?></h5>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($pub['date_publication'] ?? 'now')) ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="post-content">
                            <p><?= nl2br(htmlspecialchars(substr($pub['contenu'], 0, 300))) ?>
                            <?php if(strlen($pub['contenu'] ?? '') > 300): ?>
                                ...<a href="#" class="read-more" data-fulltext="<?= htmlspecialchars($pub['contenu']) ?>"> Lire plus</a>
                            <?php endif; ?>
                            </p>
                        </div>
                        <?php if(!empty($pub['url_image'])): ?>
                            <div class="post-image"><img src="<?= htmlspecialchars($pub['url_image']) ?>" alt="Image" style="max-width:100%; border-radius:12px;"></div>
                        <?php endif; ?>
                        <?php if(!empty($pub['url_video'])): ?>
                            <div class="post-video"><iframe width="100%" height="315" src="<?= htmlspecialchars($pub['url_video']) ?>" frameborder="0" allowfullscreen></iframe></div>
                        <?php endif; ?>
                        <div class="post-actions">
                            <button class="action-btn" onclick="toggleComments(<?= $pub['id_publication'] ?>)"><i class="fas fa-comment"></i> Commenter</button>
                            <button class="action-btn"><i class="fas fa-heart"></i> J'aime</button>
                            <button class="action-btn"><i class="fas fa-share"></i> Partager</button>
                        </div>
                        <!-- Section commentaires -->
                        <div id="comments-section-<?= $pub['id_publication'] ?>" class="comments-section mt-4" style="display: none; background: #f9f9f9; padding: 20px; border-radius: 12px;">
                            <h6 class="mb-3">Commentaires</h6>
                            <form class="mb-3" onsubmit="submitComment(event, <?= $pub['id_publication'] ?>)" style="background:white; padding:15px; border-radius:10px;">
                                <textarea class="form-control form-control-medical" id="comment-content-<?= $pub['id_publication'] ?>" rows="3" placeholder="Écrivez votre commentaire..." required></textarea>
                                <button type="submit" class="btn btn-medical btn-sm mt-2">Publier</button>
                            </form>
                            <div id="comments-list-<?= $pub['id_publication'] ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state"><i class="fas fa-newspaper"></i><p>Aucune publication pour le moment.</p><small>Les médecins publieront bientôt du contenu.</small></div>
            <?php endif; ?>
        </div>
        
        <h3 class="mt-5 mb-4">Avis des patients</h3>
        <div id="reviewsList"><div class="empty-state"><i class="fas fa-star"></i><p>Aucun avis pour le moment.</p><small>Soyez le premier à donner votre avis !</small></div></div>
    </div>
</section>

<!-- ========== MODALES CONNEXION / INSCRIPTION ========== -->
<div class="modal fade" id="signinModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content auth-modal"><div class="modal-header border-0"><h5 class="modal-title"><i class="fas fa-sign-in-alt me-2"></i>Connexion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="signinForm" novalidate><div class="mb-3"><label>Email</label><input type="email" class="form-control form-control-medical" id="signinEmail" required></div><div class="mb-3"><label>Mot de passe</label><input type="password" class="form-control form-control-medical" id="signinPassword" required></div><button type="submit" class="btn btn-medical w-100">Se connecter</button><div class="text-center mt-3"><small>Pas encore de compte ? <a href="#" onclick="switchToSignUp()">S'inscrire</a></small></div></form></div></div></div></div>
<div class="modal fade" id="signupModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content auth-modal"><div class="modal-header border-0"><h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Inscription</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="signupForm" novalidate><div class="mb-3"><label>Nom complet</label><input type="text" class="form-control form-control-medical" id="signupName" required></div><div class="mb-3"><label>Email</label><input type="email" class="form-control form-control-medical" id="signupEmail" required></div><div class="mb-3"><label>Téléphone</label><input type="tel" class="form-control form-control-medical" id="signupPhone" required></div><div class="mb-3"><label>Mot de passe</label><input type="password" class="form-control form-control-medical" id="signupPassword" required><div id="passwordStrength" class="mt-1 small"></div></div><div class="mb-3"><label>Confirmer le mot de passe</label><input type="password" class="form-control form-control-medical" id="signupConfirmPassword" required></div><button type="submit" class="btn btn-medical w-100">S'inscrire</button><div class="text-center mt-3"><small>Déjà inscrit ? <a href="#" onclick="switchToSignIn()">Se connecter</a></small></div></form></div></div></div></div>

<!-- Chatbot & Toast -->
<div class="chatbot-container"><div class="chatbot-toggle" onclick="toggleChatbot()"><i class="fas fa-comment-medical fa-2x"></i></div><div class="chatbot-window" id="chatbotWindow"><div class="chatbot-header"><i class="fas fa-robot me-2"></i>Assistant GlobalHealth</div><div class="chatbot-messages" id="chatMessages"><div class="message bot">👋 Bonjour ! Je suis votre assistant santé. Posez-moi vos questions.</div></div><div class="chatbot-input"><input type="text" id="chatInput" placeholder="Écrivez votre message..."><button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button></div></div></div>
<div class="notification-toast" id="notificationToast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===================== SYSTÈME DE COMMENTAIRES =====================
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
                <div style="background: white; padding: 12px; margin-bottom: 10px; border-radius: 8px; border-left: 3px solid var(--medical-blue);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div style="font-weight:600; color:var(--medical-blue);">
                            ${escapeHtml(c.nom)} ${escapeHtml(c.prenom)}
                            <span style="font-size:0.85rem; color:#999; margin-left:10px;">${formatDate(c.date_publication)}</span>
                        </div>
                    </div>
                    <div style="margin-top:8px;">${escapeHtml(c.contenu)}</div>
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
        showFrontNotification('Veuillez écrire un commentaire', true);
        return;
    }
    const rawCurrentPatient = localStorage.getItem('globalhealth_currentPatient');
    const currentPatient = rawCurrentPatient ? JSON.parse(rawCurrentPatient) : null;
    const userId = Number(currentPatient?.id || 0);
    if (!userId) {
        showFrontNotification('Veuillez vous connecter pour commenter', true);
        return;
    }
    const commentData = { id_publication: parseInt(pubId), id_user: parseInt(userId), contenu: contentInput.value.trim() };
    try {
        const response = await fetch('../../backoffice/layout/backoffice.php?action=add-comment', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(commentData)
        });
        const result = await response.json();
        if (result.success) {
            contentInput.value = '';
            showFrontNotification('Commentaire publié avec succès !');
            loadComments(pubId);
        } else {
            showFrontNotification('Erreur : ' + (result.error || 'Erreur inconnue'), true);
        }
    } catch (error) {
        showFrontNotification('Erreur réseau : ' + error.message, true);
        console.error(error);
    }
}
function escapeHtml(text) { if (!text) return ''; const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }; return text.replace(/[&<>"']/g, m => map[m]); }
function formatDate(dateString) { if (!dateString) return ''; const date = new Date(dateString); return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' }); }
function showFrontNotification(msg, isError = false) { const toast = document.getElementById('notificationToast'); toast.textContent = msg; toast.style.borderLeftColor = isError ? '#dc3545' : 'var(--medical-green)'; toast.classList.add('show'); setTimeout(() => toast.classList.remove('show'), 3500); }

// ===================== AUTRES FONCTIONS (connexion, etc.) =====================
function showSignInModal() { new bootstrap.Modal(document.getElementById('signinModal')).show(); }
function showSignUpModal() { new bootstrap.Modal(document.getElementById('signupModal')).show(); }
function switchToSignUp() { bootstrap.Modal.getInstance(document.getElementById('signinModal')).hide(); showSignUpModal(); }
function switchToSignIn() { bootstrap.Modal.getInstance(document.getElementById('signupModal')).hide(); showSignInModal(); }
function showProfile() { alert("Fonctionnalité à venir"); }
function showAppointments() { alert("Aucun rendez-vous pour le moment"); }
function showMedicalFolder() { document.getElementById('dossier').scrollIntoView({ behavior: 'smooth' }); }
function logoutPatient() { location.reload(); }
function toggleChatbot() { document.getElementById('chatbotWindow').classList.toggle('show'); }
function sendMessage() { const input = document.getElementById('chatInput'); const msg = input.value.trim(); if(!msg) return; const messagesDiv = document.getElementById('chatMessages'); messagesDiv.innerHTML += `<div class="message user">${escapeHtml(msg)}</div>`; input.value = ''; setTimeout(() => { let response = "🤖 Merci pour votre message. Notre équipe médicale vous répondra dans les plus brefs délais."; if(msg.toLowerCase().includes("symptome") || msg.toLowerCase().includes("douleur")) response = "🤖 Je vous conseille de consulter un médecin rapidement."; else if(msg.toLowerCase().includes("rendez-vous") || msg.toLowerCase().includes("rdv")) response = "🤖 Pour prendre un rendez-vous, rendez-vous dans la section 'Consultation'."; else if(msg.toLowerCase().includes("teleconsultation") || msg.toLowerCase().includes("visio")) response = "🤖 Pour une téléconsultation, allez dans la section 'Téléconsultation'."; messagesDiv.innerHTML += `<div class="message bot">${response}</div>`; messagesDiv.scrollTop = messagesDiv.scrollHeight; }, 500); messagesDiv.scrollTop = messagesDiv.scrollHeight; }
function joinConsultation() { const link = document.getElementById('consultationLink').value.trim(); if(!link) { showFrontNotification('Veuillez entrer un lien', true); return; } if(!link.startsWith('http://') && !link.startsWith('https://')) { showFrontNotification('Lien invalide', true); return; } window.open(link, '_blank'); showFrontNotification('Ouverture de la consultation...'); }
function uploadFollowupFile(input) { if(input.files && input.files[0]) { document.getElementById('followupFileName').innerHTML = `<i class="fas fa-check-circle text-success me-1"></i>${input.files[0].name}`; } }
function uploadOrdonnanceFile(input) { if(input.files && input.files[0]) { showFrontNotification(`Fichier "${input.files[0].name}" chargé`); } }
function resetMedicalForm() { document.getElementById('medicalRecordForm').reset(); }

// Soumission des formulaires (simples)
document.getElementById('appointmentForm')?.addEventListener('submit', (e) => { e.preventDefault(); showFrontNotification('Votre demande de rendez-vous a été envoyée !'); e.target.reset(); });
document.getElementById('signupForm')?.addEventListener('submit', (e) => { e.preventDefault(); const pwd = document.getElementById('signupPassword').value, confirm = document.getElementById('signupConfirmPassword').value; if(pwd !== confirm) { showFrontNotification('Les mots de passe ne correspondent pas', true); return; } showFrontNotification('Inscription réussie ! Connectez-vous.'); bootstrap.Modal.getInstance(document.getElementById('signupModal')).hide(); showSignInModal(); });
document.getElementById('signinForm')?.addEventListener('submit', (e) => { e.preventDefault(); showFrontNotification('Connexion réussie !'); bootstrap.Modal.getInstance(document.getElementById('signinModal')).hide(); document.getElementById('userMenu').style.display = 'flex'; document.getElementById('authButtons').style.display = 'none'; });
document.getElementById('submitReviewForm')?.addEventListener('submit', (e) => { e.preventDefault(); const rating = document.querySelector('input[name="reviewRating"]:checked'); if(!rating) { showFrontNotification('Veuillez sélectionner une note', true); return; } showFrontNotification('Merci pour votre avis !'); e.target.reset(); document.querySelectorAll('input[name="reviewRating"]').forEach(r => r.checked = false); });
document.getElementById('followupForm')?.addEventListener('submit', (e) => { e.preventDefault(); showFrontNotification('Suivi enregistré avec succès'); e.target.reset(); document.getElementById('followupFileName').innerHTML = ''; });
document.getElementById('medicalRecordForm')?.addEventListener('submit', (e) => { e.preventDefault(); showFrontNotification('Dossier médical enregistré avec succès'); e.target.reset(); });

// Chargement des médecins (simulation)
function loadDoctors() {
    const doctors = [{ id:1, name:"Dr. Martin Dupont", specialite:"Cardiologue" }, { id:2, name:"Dr. Sophie Lefevre", specialite:"Généraliste" }, { id:3, name:"Dr. Jean Pierre", specialite:"Dermatologue" }];
    const selects = ['reviewDoctorId', 'followupDoctor', 'id_medecin'];
    selects.forEach(id => { const sel = document.getElementById(id); if(sel) sel.innerHTML = '<option value="">Sélectionnez un médecin</option>' + doctors.map(d => `<option value="${d.id}">${d.name} - ${d.specialite}</option>`).join(''); });
    const doctorsList = document.getElementById('doctorsList');
    if(doctorsList) doctorsList.innerHTML = doctors.map(d => `<div class="col-md-4"><div class="doctor-card"><div class="doctor-avatar-lg"><i class="fas fa-user-md"></i></div><h5>${d.name}</h5><p class="text-muted">${d.specialite}</p><button class="btn btn-outline-medical w-100 mt-3" onclick="document.getElementById('reviewDoctorId').value='${d.id}'; document.getElementById('reviewFormContainer').scrollIntoView({behavior:'smooth'});">Donner mon avis</button></div></div>`).join('');
}
document.addEventListener('DOMContentLoaded', () => { loadDoctors(); console.log("✅ index.js chargé - sans publications CRUD"); });
</script>
</body>
</html>
