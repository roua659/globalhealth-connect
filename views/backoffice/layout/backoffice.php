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
    <title>GlobalHealth Connect - Backoffice Médical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --medical-white: #ffffff;
            --medical-blue: #2b7be4;
            --medical-light-blue: #e8f4ff;
            --medical-green: #2ecc71;
            --medical-gray: #f5f7fa;
            --medical-text: #2c3e50;
            --sidebar-width: 280px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--medical-gray);
            color: var(--medical-text);
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            box-shadow: 2px 0 20px rgba(0,0,0,0.05);
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 18px;
            border-radius: 16px;
            color: var(--medical-text);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            cursor: pointer;
        }
        .sidebar-menu a:hover { background: var(--medical-light-blue); color: var(--medical-blue); }
        .sidebar-menu a.active {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            color: white;
            box-shadow: 0 5px 15px rgba(43,123,228,0.3);
        }
        .sidebar-footer {
            position: absolute;
            bottom: 30px;
            left: 20px;
            right: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 25px 35px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 25px;
            border-radius: 20px;
        }
        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 18px;
        }
        .stat-number { font-size: 2.2rem; font-weight: 800; margin: 10px 0 5px; }
        .stat-label { color: #6c7a8a; font-size: 0.9rem; }
        
        .data-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            animation: slideIn 0.4s ease-out;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .data-table td a[href^="mailto:"] {
            color: var(--medical-blue);
            text-decoration: none;
            font-weight: 500;
        }
        .data-table td a[href^="mailto:"]:hover { text-decoration: underline; }
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-scroll .data-table { min-width: 920px; }
        .data-table th {
            font-weight: 600;
            color: var(--medical-blue);
            background: var(--medical-light-blue);
        }
        .status-badge {
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-approved { background: #e8f8f0; color: #2ecc71; }
        .status-pending { background: #fff3e0; color: #f39c12; }
        .status-reported { background: #fee; color: #e74c3c; }
        
        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px 12px;
            margin: 0 3px;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .icon-btn:hover { background: var(--medical-gray); transform: scale(1.05); }
        .icon-btn.approve { color: #2ecc71; }
        .icon-btn.delete { color: #e74c3c; }
        .icon-btn.edit { color: var(--medical-blue); }
        .icon-btn.flag { color: #f39c12; }
        
        .btn-medical {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-medical:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(43,123,228,0.3); }
        .btn-outline-medical {
            background: transparent;
            border: 2px solid var(--medical-blue);
            color: var(--medical-blue);
            padding: 8px 22px;
            border-radius: 40px;
            font-weight: 600;
        }
        
        .modal-custom .modal-content {
            border-radius: 28px;
            border: none;
            padding: 10px;
        }
        .form-control-custom {
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 12px 16px;
        }
        .form-control-custom:focus {
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 3px rgba(43,123,228,0.1);
        }
        
        .notification-toast {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: white;
            padding: 14px 24px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateX(450px);
            transition: transform 0.3s;
            z-index: 1001;
            border-left: 4px solid #2ecc71;
        }
        .notification-toast.show { transform: translateX(0); }
        
        .chart-bar {
            background: var(--medical-gray);
            border-radius: 12px;
            height: 40px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .chart-bar-fill {
            background: linear-gradient(90deg, var(--medical-blue), var(--medical-green));
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 15px;
            color: white;
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c7a8a;
        }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; opacity: 0.3; }
        
        .btn-group-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Style pour le suivi */
        .followup-card {
            background: var(--medical-gray);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--medical-blue);
        }
        .followup-card h6 {
            margin-bottom: 10px;
            color: var(--medical-blue);
        }
    </style>
</head>
<body>

<!-- Accès direct - Pas de login -->
<div id="mainContent">
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-icon"><i class="fas fa-heartbeat"></i></div>
                <div><strong style="font-size:1.3rem;">GlobalHealth</strong><br><small>BACKOFFICE</small></div>
            </div>
            <ul class="sidebar-menu">
                <li><a onclick="switchModule('dashboard');"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a onclick="switchModule('users');"><i class="fas fa-users"></i> Utilisateurs</a></li>
                <li><a onclick="switchModule('forum');"><i class="fas fa-newspaper"></i> Publications</a></li>
                <li><a onclick="switchModule('comments');"><i class="fas fa-comments"></i> Commentaires</a></li>
                <li><a onclick="switchModule('reviews');"><i class="fas fa-star"></i> Avis Patients</a></li>
                <li><a onclick="switchModule('appointments');"><i class="fas fa-calendar-check"></i> Rendez-vous</a></li>
                <li><a onclick="switchModule('consultations');"><i class="fas fa-stethoscope"></i> Consultation & Suivi</a></li>
            </ul>
            <div class="sidebar-footer">
                <!-- Bouton réinitialiser supprimé -->
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title" id="pageTitle">Dashboard</h1>
                <div><button class="btn-outline-medical" onclick="refreshModule()"><i class="fas fa-sync-alt"></i> Actualiser</button></div>
            </div>
            <div id="moduleContent"></div>
        </div>
    </div>
</div>

<div class="notification-toast" id="notificationToast"></div>

<!-- Modals CRUD -->
<div class="modal fade" id="addPostModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title"><i class="fas fa-newspaper me-2"></i>Ajouter une publication</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="addPostForm"><div class="mb-3"><label>Médecin</label><select class="form-select form-control-custom" id="postDoctorId" required></select></div>
<div class="mb-3"><label>Contenu</label><textarea class="form-control form-control-custom" id="postContent" rows="3" placeholder="Partagez votre expertise médicale..." required></textarea></div>
<div class="mb-3"><label>Image (URL)</label><input type="text" class="form-control form-control-custom" id="postImage" placeholder="https://..."></div>
<div class="mb-3"><label>Vidéo (URL)</label><input type="text" class="form-control form-control-custom" id="postVideo" placeholder="https://..."></div>
<button type="submit" class="btn btn-medical w-100">Publier</button></form></div></div></div></div>

<div class="modal fade" id="addUserModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title">Ajouter un utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="addUserForm" novalidate><div class="row g-3">
<div class="col-md-6"><label>Nom</label><input type="text" class="form-control form-control-custom" id="newUserNom" autocomplete="family-name" inputmode="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$" title="Lettres seulement" oninput="this.value = this.value.replace(/[0-9]/g, '')"></div>
<div class="col-md-6"><label>Prénom</label><input type="text" class="form-control form-control-custom" id="newUserPrenom" autocomplete="given-name" inputmode="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$" title="Lettres seulement" oninput="this.value = this.value.replace(/[0-9]/g, '')"></div>
<div class="col-md-4"><label>Age</label><input type="number" class="form-control form-control-custom" id="newUserAge" min="0" max="130"></div>
<div class="col-md-4"><label>Sexe</label><select class="form-select form-control-custom" id="newUserSexe"><option value="">Sélectionner</option><option value="Homme">Homme</option><option value="Femme">Femme</option></select></div>
<div class="col-md-4"><label>Date naissance</label><input type="date" class="form-control form-control-custom" id="newUserDateNaissance"></div>
<div class="col-md-6"><label>Poids (kg)</label><input type="number" class="form-control form-control-custom" id="newUserPoids" min="0" step="0.1"></div>
<div class="col-md-6"><label>Taille (m)</label><input type="number" class="form-control form-control-custom" id="newUserTaille" min="0" step="0.01"></div>
<div class="col-md-6"><label>Email</label><input type="email" class="form-control form-control-custom" id="newUserEmail"></div>
<div class="col-md-6"><label>Mot de passe</label><input type="password" class="form-control form-control-custom" id="newUserMotDePasse" minlength="6"></div>
<div class="col-md-6"><label>Cas social</label><input type="text" class="form-control form-control-custom" id="newUserCasSocial" placeholder="Ex: assuré CNSS"></div>
<div class="col-md-6"><label>Rôle</label><select class="form-select form-control-custom" id="newUserRole" onchange="toggleSpecialtyField()"><option value="patient">Patient</option><option value="medecin">Médecin</option><option value="admin">Admin</option></select></div>
<div class="col-12"><label>Adresse</label><textarea class="form-control form-control-custom" id="newUserAdresse" rows="2"></textarea></div>
<div class="col-12" id="specialtyField" style="display:none"><label>Spécialité</label><input type="text" class="form-control form-control-custom" id="newUserSpecialite" placeholder="Ex: Cardiologue"></div>
</div>
<button type="submit" class="btn btn-medical w-100 mt-3">Créer</button></form></div></div></div></div>

<div class="modal fade" id="editUserModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title" id="editUserModalTitle">Modifier utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="editUserForm">
<input type="hidden" id="editUserId">
<input type="hidden" id="editUserRole">
<div id="editPatientSection" style="display:none">
    <p class="text-muted small mb-3">Fiche patient — tous les champs correspondent à la table <code>utilisateur</code>.</p>
    <div class="row g-3">
        <div class="col-md-6"><label>Nom</label><input type="text" class="form-control form-control-custom" id="editPatientNom" autocomplete="family-name" inputmode="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$" title="Lettres seulement" oninput="this.value = this.value.replace(/[0-9]/g, '')"></div>
        <div class="col-md-6"><label>Prénom</label><input type="text" class="form-control form-control-custom" id="editPatientPrenom" autocomplete="given-name" inputmode="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$" title="Lettres seulement" oninput="this.value = this.value.replace(/[0-9]/g, '')"></div>
        <div class="col-md-4"><label>Âge</label><input type="number" class="form-control form-control-custom" id="editPatientAge" min="0" max="130"></div>
        <div class="col-md-4"><label>Sexe</label><select class="form-select form-control-custom" id="editPatientSexe"><option value="">Sélectionner</option><option value="Homme">Homme</option><option value="Femme">Femme</option></select></div>
        <div class="col-md-4"><label>Date de naissance</label><input type="date" class="form-control form-control-custom" id="editPatientDateNaissance"></div>
        <div class="col-md-6"><label>Poids (kg)</label><input type="number" class="form-control form-control-custom" id="editPatientPoids" min="0" step="0.1"></div>
        <div class="col-md-6"><label>Taille (m)</label><input type="number" class="form-control form-control-custom" id="editPatientTaille" min="0" step="0.01"></div>
        <div class="col-md-6"><label>Email</label><input type="email" class="form-control form-control-custom" id="editPatientEmail" autocomplete="email"></div>
        <div class="col-md-6"><label>Cas social</label><input type="text" class="form-control form-control-custom" id="editPatientCasSocial" placeholder="Ex: assuré CNSS"></div>
        <div class="col-12"><label>Adresse</label><textarea class="form-control form-control-custom" id="editPatientAdresse" rows="2"></textarea></div>
        <div class="col-12"><label>Nouveau mot de passe</label><input type="password" class="form-control form-control-custom" id="editPatientMotDePasse" minlength="6" autocomplete="new-password" placeholder="Laisser vide pour ne pas modifier"></div>
    </div>
</div>
<div id="editStaffSection" style="display:none">
    <div class="mb-3"><label>Nom complet</label><input type="text" class="form-control form-control-custom" id="editUserName" placeholder="Nom Prénom"></div>
    <div class="mb-3"><label>Email</label><input type="email" class="form-control form-control-custom" id="editUserEmail"></div>
    <div class="mb-3"><label>Cas social</label><input type="text" class="form-control form-control-custom" id="editUserPhone"></div>
    <div class="mb-3" id="editMedecinSpecialtyWrap" style="display:none"><label>Spécialité (médecin)</label><input type="text" class="form-control form-control-custom" id="editUserSpecialty" placeholder="Ex: Cardiologue"></div>
</div>
<button type="submit" class="btn btn-medical w-100 mt-3">Enregistrer les modifications</button>
</form></div></div></div></div>

<div class="modal fade" id="notifyReviewModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title">Notifier un patient</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="notifyReviewForm"><div class="mb-3"><label>Patient</label><select class="form-select form-control-custom" id="notifyPatientId" required></select></div>
<div class="mb-3"><label>Message</label><textarea class="form-control form-control-custom" id="notifyMessage" rows="3">📝 Nous espérons que votre consultation s'est bien passée ! N'oubliez pas de donner votre avis et de noter votre médecin sur 5 étoiles. 🌟</textarea></div>
<button type="submit" class="btn btn-medical w-100">Envoyer la notification</button></form></div></div></div></div>

<!-- Modal Consultation & Suivi -->
<div class="modal fade" id="addConsultationModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title"><i class="fas fa-stethoscope me-2"></i>Ajouter une consultation / suivi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="addConsultationForm"><div class="row g-3">
    <div class="col-md-6"><label>ID Patient</label><input type="text" class="form-control form-control-custom" id="consultation_id_patient" placeholder="ID Patient" required></div>
    <div class="col-md-6"><label>ID Médecin</label><input type="text" class="form-control form-control-custom" id="consultation_id_medecin" placeholder="ID Médecin" required></div>
    <div class="col-md-6"><label>ID Rendez-vous</label><input type="text" class="form-control form-control-custom" id="consultation_id_rdv" placeholder="ID Rendez-vous"></div>
    <div class="col-md-6"><label>Date de la consultation</label><input type="date" class="form-control form-control-custom" id="consultation_date" required></div>
    <div class="col-12"><label>Symptômes</label><textarea class="form-control form-control-custom" id="consultation_symptomes" rows="2" placeholder="Décrivez les symptômes..." required></textarea></div>
    <div class="col-12"><label>Diagnostic</label><textarea class="form-control form-control-custom" id="consultation_diagnostic" rows="2" placeholder="Diagnostic médical..." required></textarea></div>
    <div class="col-12"><label>Traitement prescrit</label><textarea class="form-control form-control-custom" id="consultation_traitement" rows="2" placeholder="Traitement prescrit..." required></textarea></div>
    <div class="col-12"><label>Ordonnance</label><textarea class="form-control form-control-custom" id="consultation_ordonnance" rows="3" placeholder="Ordonnance (médicaments, posologie, durée...)"></textarea></div>
    <div class="col-12"><label>Notes du médecin</label><textarea class="form-control form-control-custom" id="consultation_notes" rows="2" placeholder="Notes complémentaires..."></textarea></div>
    <div class="col-12"><label>Suivi / Évolution</label><textarea class="form-control form-control-custom" id="consultation_suivi" rows="3" placeholder="Suivi de l'évolution du patient..."></textarea></div>
</div>
<button type="submit" class="btn btn-medical w-100 mt-3">Enregistrer</button></form></div></div></div></div>

<div class="modal fade" id="editConsultationModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content modal-custom"><div class="modal-header border-0"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier consultation / suivi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><form id="editConsultationForm"><input type="hidden" id="editConsultationId"><div class="row g-3">
    <div class="col-md-6"><label>ID Patient</label><input type="text" class="form-control form-control-custom" id="edit_consultation_id_patient" required></div>
    <div class="col-md-6"><label>ID Médecin</label><input type="text" class="form-control form-control-custom" id="edit_consultation_id_medecin" required></div>
    <div class="col-md-6"><label>ID Rendez-vous</label><input type="text" class="form-control form-control-custom" id="edit_consultation_id_rdv"></div>
    <div class="col-md-6"><label>Date de la consultation</label><input type="date" class="form-control form-control-custom" id="edit_consultation_date" required></div>
    <div class="col-12"><label>Symptômes</label><textarea class="form-control form-control-custom" id="edit_consultation_symptomes" rows="2" required></textarea></div>
    <div class="col-12"><label>Diagnostic</label><textarea class="form-control form-control-custom" id="edit_consultation_diagnostic" rows="2" required></textarea></div>
    <div class="col-12"><label>Traitement prescrit</label><textarea class="form-control form-control-custom" id="edit_consultation_traitement" rows="2"></textarea></div>
    <div class="col-12"><label>Ordonnance</label><textarea class="form-control form-control-custom" id="edit_consultation_ordonnance" rows="3"></textarea></div>
    <div class="col-12"><label>Notes du médecin</label><textarea class="form-control form-control-custom" id="edit_consultation_notes" rows="2"></textarea></div>
    <div class="col-12"><label>Suivi / Évolution</label><textarea class="form-control form-control-custom" id="edit_consultation_suivi" rows="3"></textarea></div>
</div>
<button type="submit" class="btn btn-medical w-100 mt-3">Modifier</button></form></div></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ============================================
    // DONNÉES PERSISTANTES
    // ============================================
    let usersData = [];
    let forumPosts = [];
    let reviewsData = [];
    let appointmentsData = [];
    let consultationsData = [];
    let currentModule = 'dashboard';
    
    // Données de démo pour les consultations
    function initDemoConsultations() {
        if(consultationsData.length === 0) {
            consultationsData = [
                { id: 1, id_patient: "P001", id_medecin: "D001", id_rdv: "RDV001", date: "2024-01-15", symptomes: "Fièvre, toux, fatigue", diagnostic: "Grippe saisonnière", traitement: "Paracétamol, repos", ordonnance: "Doliprane 1000mg 3x/jour", notes_medecin: "Patient à surveiller", suivi: "Amélioration après 3 jours" },
                { id: 2, id_patient: "P002", id_medecin: "D002", id_rdv: "RDV002", date: "2024-01-20", symptomes: "Douleurs thoraciques", diagnostic: "Angine de poitrine", traitement: "Traitement cardiologique", ordonnance: "Aspirine, repos", notes_medecin: "Cardiologue consulté", suivi: "Stable sous traitement" }
            ];
            saveConsultations();
        }
    }
    
    const USERS_API_BASE = <?php echo json_encode($usersApiBase, JSON_THROW_ON_ERROR); ?>;

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

    async function loadUsersFromApi() {
        usersData = await apiRequest('list', 'GET');
    }

    async function loadAllData() {
        try {
            await loadUsersFromApi();
        } catch (error) {
            usersData = [];
            showNotification(`Erreur chargement utilisateurs: ${error.message}`, true);
        }
        
        const storedPosts = localStorage.getItem('globalhealthBack_posts');
        if(storedPosts) forumPosts = JSON.parse(storedPosts);
        else forumPosts = [];
        
        const storedReviews = localStorage.getItem('globalhealthBack_reviews');
        if(storedReviews) reviewsData = JSON.parse(storedReviews);
        else reviewsData = [];
        
        const storedAppointments = localStorage.getItem('globalhealthBack_appointments');
        if(storedAppointments) appointmentsData = JSON.parse(storedAppointments);
        else appointmentsData = [];
        
        const storedConsultations = localStorage.getItem('globalhealthBack_consultations');
        if(storedConsultations) consultationsData = JSON.parse(storedConsultations);
        else consultationsData = [];
        
        initDemoConsultations();
    }
    
    function savePosts() { localStorage.setItem('globalhealthBack_posts', JSON.stringify(forumPosts)); }
    function saveReviews() { localStorage.setItem('globalhealthBack_reviews', JSON.stringify(reviewsData)); }
    function saveAppointments() { localStorage.setItem('globalhealthBack_appointments', JSON.stringify(appointmentsData)); }
    function saveConsultations() { localStorage.setItem('globalhealthBack_consultations', JSON.stringify(consultationsData)); }
    
    function syncWithFrontoffice() {
        const doctors = usersData.filter(u => u.role === 'medecin').map(d => ({
            id: d.id || d.id_user,
            name: d.name || `${d.nom || ''} ${d.prenom || ''}`.trim(),
            specialty: d.specialite || 'Médecin généraliste',
            email: d.email,
            phone: d.cas_social || ''
        }));
        localStorage.setItem('globalhealth_doctors', JSON.stringify(doctors));
        localStorage.setItem('globalhealth_forumPosts', JSON.stringify(forumPosts));
        localStorage.setItem('globalhealth_reviews', JSON.stringify(reviewsData));
    }
    
    // ============================================
    // NAVIGATION
    // ============================================
    function switchModule(module) {
        currentModule = module;
        document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
        const activeLink = Array.from(document.querySelectorAll('.sidebar-menu a')).find(a => a.innerText.toLowerCase().includes(module));
        if(activeLink) activeLink.classList.add('active');
        
        const titles = {
            dashboard: 'Dashboard - Vue d\'ensemble',
            users: 'Gestion des Utilisateurs',
            forum: 'Forum - Publications des Médecins',
            comments: 'Gestion des Commentaires',
            reviews: 'Avis Patients - Modération',
            appointments: 'Rendez-vous & Paiements',
            consultations: 'Consultation & Suivi médical'
        };
        document.getElementById('pageTitle').innerHTML = titles[module] || module;
        loadModuleContent(module);
    }
    
    function loadModuleContent(module) {
        const body = document.getElementById('moduleContent');
        if(module === 'dashboard') body.innerHTML = renderDashboard();
        else if(module === 'users') body.innerHTML = renderUsers();
        else if(module === 'forum') body.innerHTML = renderForum();
        else if(module === 'comments') body.innerHTML = renderComments();
        else if(module === 'reviews') body.innerHTML = renderReviews();
        else if(module === 'appointments') body.innerHTML = renderAppointments();
        else if(module === 'consultations') body.innerHTML = renderConsultations();
    }
    
    function refreshModule() { loadModuleContent(currentModule); showNotification('Module actualisé'); }
    
    function showNotification(msg, isError=false) {
        const t = document.getElementById('notificationToast');
        if(t){
            t.textContent = msg;
            t.style.borderLeftColor = isError ? '#dc3545' : '#2ecc71';
            t.classList.add('show');
            setTimeout(()=>t.classList.remove('show'),3000);
        }
    }
    
    function escapeHtml(str) {
        if(!str) return '';
        return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));
    }

    function userId(u) {
        return u.id ?? u.id_user ?? 0;
    }

    function userFullName(u) {
        const n = (u.name || `${u.nom || ''} ${u.prenom || ''}`.trim()).trim();
        return n || '—';
    }

    function formatDateNaissance(iso) {
        if (!iso) return '—';
        const s = String(iso).slice(0, 10);
        const p = s.split('-');
        if (p.length !== 3) return String(iso);
        return `${p[2]}/${p[1]}/${p[0]}`;
    }

    function displayMetric(v, unit) {
        if (v === null || v === undefined || v === '') return '—';
        const n = Number(v);
        if (Number.isNaN(n)) return '—';
        return unit === 'kg' ? `${n} kg` : unit === 'm' ? `${n} m` : String(n);
    }
    
    function showStats(moduleName) {
        showNotification(`📊 Statistiques - ${moduleName} (Fonctionnalité à venir)`);
    }
    
    function exportToPDF(elementId, filename) {
        const element = document.getElementById(elementId);
        if(element && typeof html2pdf !== 'undefined') {
            html2pdf().from(element).set({ filename: filename }).save();
            showNotification('Export PDF en cours...');
        } else {
            showNotification('Export PDF');
        }
    }
    
    // ==================== DASHBOARD ====================
    function renderDashboard() {
        const totalUsers = usersData.length;
        const totalDoctors = usersData.filter(u => u.role === 'medecin').length;
        const totalPatients = usersData.filter(u => u.role === 'patient').length;
        const totalPosts = forumPosts.length;
        const allComments = forumPosts.flatMap(p => p.comments || []);
        const totalComments = allComments.length;
        const pendingComments = allComments.filter(c => c.status === 'pending').length;
        const totalReviews = reviewsData.length;
        const pendingReviews = reviewsData.filter(r => r.status === 'pending').length;
        const approvedReviews = reviewsData.filter(r => r.status === 'approved');
        const avgRating = approvedReviews.length ? (approvedReviews.reduce((s,r)=>s+r.rating,0)/approvedReviews.length).toFixed(1) : 0;
        const totalAppointments = appointmentsData.length;
        const totalConsultations = consultationsData.length;
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-icon" style="background:#e8f4ff;color:#2b7be4;"><i class="fas fa-users"></i></div><div class="stat-number">${totalUsers}</div><div class="stat-label">Utilisateurs</div><small>${totalDoctors} médecins, ${totalPatients} patients</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#e8f8f0;color:#2ecc71;"><i class="fas fa-newspaper"></i></div><div class="stat-number">${totalPosts}</div><div class="stat-label">Publications</div><small>forum médical</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#fff3e0;color:#f39c12;"><i class="fas fa-comments"></i></div><div class="stat-number">${totalComments}</div><div class="stat-label">Commentaires</div><small>${pendingComments} en attente</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#fee;color:#e74c3c;"><i class="fas fa-star"></i></div><div class="stat-number">${avgRating}</div><div class="stat-label">Note moyenne</div><small>${totalReviews} avis (${pendingReviews} en attente)</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#e8f4ff;color:#2b7be4;"><i class="fas fa-calendar-check"></i></div><div class="stat-number">${totalAppointments}</div><div class="stat-label">Rendez-vous</div><small>consultations</small></div>
                <div class="stat-card"><div class="stat-icon" style="background:#2ecc71;color:white;"><i class="fas fa-stethoscope"></i></div><div class="stat-number">${totalConsultations}</div><div class="stat-label">Consultations</div><small>suivis médicaux</small></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribution des notes des médecins</h5>
                    <div class="btn-group-actions">
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Dashboard')"><i class="fas fa-chart-line me-1"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('dashboardStats', 'dashboard-stats.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                <div id="dashboardStats">
                ${usersData.filter(u => u.role === 'medecin').map(doctor => {
                    const doctorReviews = reviewsData.filter(r => r.doctor_id === doctor.id && r.status === 'approved');
                    const avg = doctorReviews.length ? (doctorReviews.reduce((s,r)=>s+r.rating,0)/doctorReviews.length).toFixed(1) : 0;
                    return `<div class="chart-bar"><div class="chart-bar-fill" style="width:${(avg/5)*100}%">${doctor.name || `${doctor.nom || ''} ${doctor.prenom || ''}`.trim()}: ${avg}/5 ★</div></div>`;
                }).join('')}
                ${usersData.filter(u => u.role === 'medecin').length === 0 ? '<div class="empty-state"><i class="fas fa-chart-line"></i><p>Aucun médecin pour afficher les statistiques</p></div>' : ''}
                </div>
            </div>
        `;
    }
    
    // ==================== CONSULTATION & SUIVI ====================
    function renderConsultations() {
        if(consultationsData.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-stethoscope"></i><p>Aucune consultation</p><button class="btn btn-medical" onclick="showAddConsultationModal()"><i class="fas fa-plus"></i> Ajouter une consultation</button></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${consultationsData.length}</div><div class="stat-label">Total consultations</div></div>
                <div class="stat-card"><div class="stat-number">${consultationsData.filter(c => c.suivi && c.suivi !== '').length}</div><div class="stat-label">Avec suivi</div></div>
                <div class="stat-card"><div class="stat-number">${consultationsData.filter(c => !c.suivi || c.suivi === '').length}</div><div class="stat-label">Sans suivi</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Liste des consultations et suivis</h5>
                    <div class="btn-group-actions">
                        <button class="btn-medical btn-sm" onclick="showAddConsultationModal()"><i class="fas fa-plus"></i> Nouvelle consultation</button>
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Consultations')"><i class="fas fa-chart-line"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('consultationsTable', 'consultations-suivi.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                <div id="consultationsTable">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Patient</th><th>Médecin</th><th>Date</th><th>Diagnostic</th><th>Traitement</th><th>Suivi</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    ${consultationsData.map(c => `
                        <tr>
                            <td>${c.id}</td>
                            <td>${escapeHtml(c.id_patient)}</td>
                            <td>${escapeHtml(c.id_medecin)}</td>
                            <td>${c.date}</td>
                            <td>${escapeHtml(c.diagnostic.substring(0,30))}${c.diagnostic.length > 30 ? '...' : ''}</td>
                            <td>${escapeHtml(c.traitement ? c.traitement.substring(0,30) : '-')}${c.traitement && c.traitement.length > 30 ? '...' : ''}</td>
                            <td>${escapeHtml(c.suivi ? (c.suivi.substring(0,30) + (c.suivi.length > 30 ? '...' : '')) : '-')}</td>
                            <td>
                                <button class="icon-btn edit" onclick="editConsultation(${c.id})"><i class="fas fa-edit"></i></button>
                                <button class="icon-btn delete" onclick="deleteConsultation(${c.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `).join('')}
                    </tbody>
                </table>
                </div>
            </div>
            <div class="data-card">
                <h5><i class="fas fa-chart-line me-2"></i>Derniers suivis ajoutés</h5>
                ${consultationsData.slice(-3).reverse().map(c => `
                    <div class="followup-card">
                        <h6><i class="fas fa-calendar-alt me-2"></i> ${c.date} - Patient: ${escapeHtml(c.id_patient)}</h6>
                        <p><strong>Diagnostic:</strong> ${escapeHtml(c.diagnostic)}</p>
                        <p><strong>Suivi:</strong> ${escapeHtml(c.suivi || 'Aucun suivi pour le moment')}</p>
                        <small class="text-muted">Médecin: ${escapeHtml(c.id_medecin)}</small>
                    </div>
                `).join('')}
                ${consultationsData.length === 0 ? '<div class="empty-state"><i class="fas fa-chart-line"></i><p>Aucun suivi disponible</p></div>' : ''}
            </div>
        `;
    }
    
    function showAddConsultationModal() {
        document.getElementById('addConsultationForm').reset();
        new bootstrap.Modal(document.getElementById('addConsultationModal')).show();
    }
    
    document.getElementById('addConsultationForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const newConsultation = {
            id: Date.now(),
            id_patient: document.getElementById('consultation_id_patient').value,
            id_medecin: document.getElementById('consultation_id_medecin').value,
            id_rdv: document.getElementById('consultation_id_rdv').value || null,
            date: document.getElementById('consultation_date').value,
            symptomes: document.getElementById('consultation_symptomes').value,
            diagnostic: document.getElementById('consultation_diagnostic').value,
            traitement: document.getElementById('consultation_traitement').value,
            ordonnance: document.getElementById('consultation_ordonnance').value,
            notes_medecin: document.getElementById('consultation_notes').value,
            suivi: document.getElementById('consultation_suivi').value
        };
        consultationsData.push(newConsultation);
        saveConsultations();
        bootstrap.Modal.getInstance(document.getElementById('addConsultationModal')).hide();
        document.getElementById('addConsultationForm').reset();
        showNotification('Consultation ajoutée avec succès');
        refreshModule();
    });
    
    function editConsultation(id) {
        const consultation = consultationsData.find(c => c.id === id);
        if(!consultation) return;
        
        document.getElementById('editConsultationId').value = consultation.id;
        document.getElementById('edit_consultation_id_patient').value = consultation.id_patient;
        document.getElementById('edit_consultation_id_medecin').value = consultation.id_medecin;
        document.getElementById('edit_consultation_id_rdv').value = consultation.id_rdv || '';
        document.getElementById('edit_consultation_date').value = consultation.date;
        document.getElementById('edit_consultation_symptomes').value = consultation.symptomes;
        document.getElementById('edit_consultation_diagnostic').value = consultation.diagnostic;
        document.getElementById('edit_consultation_traitement').value = consultation.traitement || '';
        document.getElementById('edit_consultation_ordonnance').value = consultation.ordonnance || '';
        document.getElementById('edit_consultation_notes').value = consultation.notes_medecin || '';
        document.getElementById('edit_consultation_suivi').value = consultation.suivi || '';
        
        new bootstrap.Modal(document.getElementById('editConsultationModal')).show();
    }
    
    document.getElementById('editConsultationForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('editConsultationId').value);
        const index = consultationsData.findIndex(c => c.id === id);
        
        if(index !== -1) {
            consultationsData[index] = {
                ...consultationsData[index],
                id_patient: document.getElementById('edit_consultation_id_patient').value,
                id_medecin: document.getElementById('edit_consultation_id_medecin').value,
                id_rdv: document.getElementById('edit_consultation_id_rdv').value || null,
                date: document.getElementById('edit_consultation_date').value,
                symptomes: document.getElementById('edit_consultation_symptomes').value,
                diagnostic: document.getElementById('edit_consultation_diagnostic').value,
                traitement: document.getElementById('edit_consultation_traitement').value,
                ordonnance: document.getElementById('edit_consultation_ordonnance').value,
                notes_medecin: document.getElementById('edit_consultation_notes').value,
                suivi: document.getElementById('edit_consultation_suivi').value
            };
            saveConsultations();
            bootstrap.Modal.getInstance(document.getElementById('editConsultationModal')).hide();
            showNotification('Consultation modifiée avec succès');
            refreshModule();
        }
    });
    
    function deleteConsultation(id) {
        if(confirm('Supprimer cette consultation ?')) {
            consultationsData = consultationsData.filter(c => c.id !== id);
            saveConsultations();
            showNotification('Consultation supprimée');
            refreshModule();
        }
    }
    
    // ==================== UTILISATEURS ====================
    function renderUsers() {
        const doctors = usersData.filter(u => u.role === 'medecin');
        const patients = usersData.filter(u => u.role === 'patient');
        
        if(usersData.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-users"></i><p>Aucun utilisateur</p><button class="btn btn-medical" onclick="showAddUserModal()"><i class="fas fa-plus"></i> Ajouter un utilisateur</button></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${usersData.length}</div><div class="stat-label">Total utilisateurs</div></div>
                <div class="stat-card"><div class="stat-number">${doctors.length}</div><div class="stat-label">Médecins</div></div>
                <div class="stat-card"><div class="stat-number">${patients.length}</div><div class="stat-label">Patients</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Médecins (${doctors.length})</h5>
                    <div class="btn-group-actions">
                        <button class="btn-medical btn-sm" onclick="showAddUserModal()"><i class="fas fa-plus"></i> Ajouter</button>
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Utilisateurs')"><i class="fas fa-chart-line"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('usersTable', 'medecins-list.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                <div id="usersTable">
                <table class="data-table"><thead><tr><th>Nom</th><th>Email</th><th>Spécialité</th><th>Actions</th></tr></thead>
                <tbody>${doctors.map(d => {
                    const uid = userId(d);
                    const spec = (d.specialite && String(d.specialite).trim()) ? String(d.specialite).trim() : 'Généraliste';
                    return `<tr>
                    <td><strong>${escapeHtml(userFullName(d))}</strong></td>
                    <td><a href="mailto:${escapeHtml(d.email)}">${escapeHtml(d.email)}</a></td>
                    <td><span class="status-badge status-approved">${escapeHtml(spec)}</span></td>
                    <td>
                        <button type="button" class="icon-btn edit" onclick="editUser(${uid})" title="Modifier"><i class="fas fa-edit"></i></button>
                        <button type="button" class="icon-btn delete" onclick="deleteUser(${uid})" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
                }).join('')}</tbody>
                </table>
                </div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Patients (${patients.length})</h5>
                    <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('patientsTable', 'patients-list.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                </div>
                <div id="patientsTable" class="table-scroll">
                <table class="data-table"><thead><tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Âge</th>
                    <th>Date naissance</th>
                    <th>Poids</th>
                    <th>Taille</th>
                    <th>Cas social</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>${patients.map(p => {
                    const pid = userId(p);
                    const age = (p.age !== null && p.age !== undefined && p.age !== '') ? String(p.age) : '—';
                    return `<tr>
                    <td><strong>${escapeHtml(userFullName(p))}</strong></td>
                    <td><a href="mailto:${escapeHtml(p.email)}">${escapeHtml(p.email)}</a></td>
                    <td>${escapeHtml(age)}</td>
                    <td>${escapeHtml(formatDateNaissance(p.date_naissance))}</td>
                    <td>${escapeHtml(displayMetric(p.poids, 'kg'))}</td>
                    <td>${escapeHtml(displayMetric(p.taille, 'm'))}</td>
                    <td>${escapeHtml(p.cas_social && String(p.cas_social).trim() ? p.cas_social : '—')}</td>
                    <td>
                        <button type="button" class="icon-btn edit" onclick="editUser(${pid})" title="Modifier"><i class="fas fa-edit"></i></button>
                        <button type="button" class="icon-btn delete" onclick="deleteUser(${pid})" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
                }).join('')}</tbody>
                </table>
                </div>
            </div>
        `;
    }
    
    function toggleSpecialtyField() {
        const role = document.getElementById('newUserRole').value;
        document.getElementById('specialtyField').style.display = role === 'medecin' ? 'block' : 'none';
        if (role !== 'medecin') {
            clearFieldError(document.getElementById('newUserSpecialite'));
        }
    }
    
    function showAddUserModal() { new bootstrap.Modal(document.getElementById('addUserModal')).show(); }
    
    function getFormField(id) {
        return document.getElementById(id);
    }

    function getOrCreateFieldError(field) {
        if (!field) return null;
        let errorEl = field.parentElement.querySelector('.field-error');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'field-error text-danger small mt-1';
            field.parentElement.appendChild(errorEl);
        }
        return errorEl;
    }

    function clearFieldError(field) {
        if (!field) return;
        field.classList.remove('is-invalid');
        const errorEl = getOrCreateFieldError(field);
        if (errorEl) errorEl.textContent = '';
    }

    function setFieldError(field, message) {
        if (!field) return;
        field.classList.add('is-invalid');
        const errorEl = getOrCreateFieldError(field);
        if (errorEl) errorEl.textContent = message;
    }

    function attachAlphaInput(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        field.addEventListener('input', () => {
            field.value = field.value.replace(/[0-9]/g, '');
        });
    }

    attachAlphaInput('newUserNom');
    attachAlphaInput('newUserPrenom');
    attachAlphaInput('editPatientNom');
    attachAlphaInput('editPatientPrenom');

    function clearCreateUserErrors() {
        [
            'newUserNom', 'newUserPrenom', 'newUserAge', 'newUserSexe',
            'newUserDateNaissance', 'newUserPoids', 'newUserTaille',
            'newUserEmail', 'newUserMotDePasse', 'newUserCasSocial',
            'newUserRole', 'newUserAdresse', 'newUserSpecialite'
        ].forEach((fieldId) => clearFieldError(getFormField(fieldId)));
    }

    function validateCreateUserForm() {
        clearCreateUserErrors();
        let isValid = true;

        const nomField = getFormField('newUserNom');
        const prenomField = getFormField('newUserPrenom');
        const ageField = getFormField('newUserAge');
        const sexeField = getFormField('newUserSexe');
        const dateNaissanceField = getFormField('newUserDateNaissance');
        const poidsField = getFormField('newUserPoids');
        const tailleField = getFormField('newUserTaille');
        const emailField = getFormField('newUserEmail');
        const passwordField = getFormField('newUserMotDePasse');
        const casSocialField = getFormField('newUserCasSocial');
        const roleField = getFormField('newUserRole');
        const adresseField = getFormField('newUserAdresse');
        const specialiteField = getFormField('newUserSpecialite');

        const nom = nomField.value.trim();
        const prenom = prenomField.value.trim();
        const ageRaw = ageField.value.trim();
        const sexe = sexeField.value.trim();
        const dateNaissance = dateNaissanceField.value.trim();
        const poidsRaw = poidsField.value.trim();
        const tailleRaw = tailleField.value.trim();
        const email = emailField.value.trim();
        const motDePasse = passwordField.value;
        const casSocial = casSocialField.value.trim();
        const role = roleField.value.trim();
        const adresse = adresseField.value.trim();
        const specialite = specialiteField.value.trim();

        if (!nom) { setFieldError(nomField, 'Le nom est obligatoire.'); isValid = false; }
        if (!prenom) { setFieldError(prenomField, 'Le prénom est obligatoire.'); isValid = false; }
        if (nom && /\d/.test(nom)) { setFieldError(nomField, 'Le nom ne doit pas contenir de chiffres.'); isValid = false; }
        if (prenom && /\d/.test(prenom)) { setFieldError(prenomField, 'Le prénom ne doit pas contenir de chiffres.'); isValid = false; }

        if (!ageRaw) {
            setFieldError(ageField, "L'âge est obligatoire.");
            isValid = false;
        } else {
            const age = Number(ageRaw);
            if (Number.isNaN(age) || age < 0 || age > 130) {
                setFieldError(ageField, 'Âge invalide (0 à 130).');
                isValid = false;
            }
        }

        if (!sexe) { setFieldError(sexeField, 'Le sexe est obligatoire.'); isValid = false; }
        if (!dateNaissance) { setFieldError(dateNaissanceField, 'La date de naissance est obligatoire.'); isValid = false; }

        if (!poidsRaw) {
            setFieldError(poidsField, 'Le poids est obligatoire.');
            isValid = false;
        } else {
            const poids = Number(poidsRaw);
            if (Number.isNaN(poids) || poids <= 0) {
                setFieldError(poidsField, 'Poids invalide.');
                isValid = false;
            }
        }

        if (!tailleRaw) {
            setFieldError(tailleField, 'La taille est obligatoire.');
            isValid = false;
        } else {
            const taille = Number(tailleRaw);
            if (Number.isNaN(taille) || taille <= 0) {
                setFieldError(tailleField, 'Taille invalide.');
                isValid = false;
            }
        }

        if (!email) {
            setFieldError(emailField, "L'email est obligatoire.");
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            setFieldError(emailField, 'Format email invalide.');
            isValid = false;
        }

        if (!motDePasse) {
            setFieldError(passwordField, 'Le mot de passe est obligatoire.');
            isValid = false;
        } else if (motDePasse.length < 6) {
            setFieldError(passwordField, 'Minimum 6 caractères.');
            isValid = false;
        }

        if (!casSocial) { setFieldError(casSocialField, 'Le cas social est obligatoire.'); isValid = false; }
        if (!role || !['admin', 'medecin', 'patient'].includes(role)) {
            setFieldError(roleField, 'Le rôle est obligatoire.');
            isValid = false;
        }
        if (!adresse) { setFieldError(adresseField, "L'adresse est obligatoire."); isValid = false; }

        if (role === 'medecin' && !specialite) {
            setFieldError(specialiteField, 'La spécialité est obligatoire pour un médecin.');
            isValid = false;
        }

        if (!isValid) return null;

        return {
            nom,
            prenom,
            age: Number(ageRaw),
            sexe,
            poids: Number(poidsRaw),
            taille: Number(tailleRaw),
            email,
            mot_de_passe: motDePasse,
            cas_social: casSocial,
            date_naissance: dateNaissance,
            adresse,
            role,
            specialite: role === 'medecin' ? specialite : null,
            name: `${nom} ${prenom}`.trim(),
            status: 'active'
        };
    }

    function validateUserPayload(user) {
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

    document.getElementById('addUserForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newUser = validateCreateUserForm();
        if (!newUser) {
            showNotification('Veuillez corriger les champs en rouge.', true);
            return;
        }
        const error = validateUserPayload(newUser);
        if (error) {
            showNotification(error, true);
            return;
        }
        try {
            const createdUser = await apiRequest('create', 'POST', newUser);
            usersData.unshift(createdUser);
            syncWithFrontoffice();
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            document.getElementById('addUserForm').reset();
            clearCreateUserErrors();
            toggleSpecialtyField();
            showNotification(`Utilisateur ${createdUser.name} ajouté`);
            refreshModule();
        } catch (error) {
            showNotification(error.message, true);
        }
    });
    
    function editUser(id) {
        const user = usersData.find(u => (u.id || u.id_user) === id);
        if (!user) return;
        const uid = userId(user);
        document.getElementById('editUserId').value = String(uid);
        document.getElementById('editUserRole').value = user.role || '';

        const isPatient = user.role === 'patient';
        document.getElementById('editPatientSection').style.display = isPatient ? 'block' : 'none';
        document.getElementById('editStaffSection').style.display = isPatient ? 'none' : 'block';
        document.getElementById('editMedecinSpecialtyWrap').style.display = user.role === 'medecin' ? 'block' : 'none';
        const titleEl = document.getElementById('editUserModalTitle');
        if (titleEl) {
            titleEl.textContent = isPatient ? 'Modifier le patient' : (user.role === 'medecin' ? 'Modifier le médecin' : 'Modifier l\'utilisateur');
        }

        if (isPatient) {
            document.getElementById('editPatientNom').value = user.nom || '';
            document.getElementById('editPatientPrenom').value = user.prenom || '';
            document.getElementById('editPatientAge').value = user.age != null && user.age !== '' ? String(user.age) : '';
            document.getElementById('editPatientSexe').value = user.sexe || '';
            const dn = user.date_naissance ? String(user.date_naissance).slice(0, 10) : '';
            document.getElementById('editPatientDateNaissance').value = dn;
            document.getElementById('editPatientPoids').value = user.poids != null && user.poids !== '' ? String(user.poids) : '';
            document.getElementById('editPatientTaille').value = user.taille != null && user.taille !== '' ? String(user.taille) : '';
            document.getElementById('editPatientEmail').value = user.email || '';
            document.getElementById('editPatientCasSocial').value = user.cas_social || '';
            document.getElementById('editPatientAdresse').value = user.adresse || '';
            document.getElementById('editPatientMotDePasse').value = '';
        } else {
            document.getElementById('editUserName').value = user.name || `${user.nom || ''} ${user.prenom || ''}`.trim();
            document.getElementById('editUserEmail').value = user.email || '';
            document.getElementById('editUserPhone').value = user.cas_social || '';
            document.getElementById('editUserSpecialty').value = user.specialite || '';
        }
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }
    
    document.getElementById('editUserForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = parseInt(document.getElementById('editUserId').value, 10);
        const user = usersData.find(u => (u.id || u.id_user) === id);
        if (!user) return;
        const role = user.role;
        if (role === 'patient') {
            const editNomField = document.getElementById('editPatientNom');
            const editPrenomField = document.getElementById('editPatientPrenom');
            const editNom = editNomField ? editNomField.value.trim() : '';
            const editPrenom = editPrenomField ? editPrenomField.value.trim() : '';
            if (editNom && /\d/.test(editNom)) {
                if (editNomField) setFieldError(editNomField, 'Le nom ne doit pas contenir de chiffres.');
                showNotification('Veuillez corriger le nom.', true);
                return;
            }
            if (editPrenom && /\d/.test(editPrenom)) {
                if (editPrenomField) setFieldError(editPrenomField, 'Le prénom ne doit pas contenir de chiffres.');
                showNotification('Veuillez corriger le prénom.', true);
                return;
            }
        }
        try {
            let payload;
            if (role === 'patient') {
                payload = {
                    id_user: id,
                    nom: document.getElementById('editPatientNom').value.trim(),
                    prenom: document.getElementById('editPatientPrenom').value.trim(),
                    email: document.getElementById('editPatientEmail').value.trim(),
                    age: Number(document.getElementById('editPatientAge').value),
                    sexe: document.getElementById('editPatientSexe').value,
                    poids: Number(document.getElementById('editPatientPoids').value),
                    taille: Number(document.getElementById('editPatientTaille').value),
                    date_naissance: document.getElementById('editPatientDateNaissance').value,
                    adresse: document.getElementById('editPatientAdresse').value.trim(),
                    cas_social: document.getElementById('editPatientCasSocial').value.trim()
                };
                const np = document.getElementById('editPatientMotDePasse').value;
                if (np) payload.mot_de_passe = np;
            } else {
                payload = {
                    id_user: id,
                    name: document.getElementById('editUserName').value,
                    email: document.getElementById('editUserEmail').value,
                    cas_social: document.getElementById('editUserPhone').value,
                    specialite: role === 'medecin' ? document.getElementById('editUserSpecialty').value.trim() : ''
                };
            }
            const updated = await apiRequest('update', 'POST', payload);
            const idx = usersData.findIndex(u => (u.id || u.id_user) === id);
            if (idx !== -1 && updated) {
                usersData[idx] = { ...usersData[idx], ...updated };
            }
            syncWithFrontoffice();
            showNotification(`Utilisateur ${updated && updated.name ? updated.name : userFullName(user)} modifié`);
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            refreshModule();
        } catch (error) {
            showNotification(error.message, true);
        }
    });
    
    async function deleteUser(id) {
        if(confirm('Supprimer cet utilisateur ?')) {
            try {
                await apiRequest('delete', 'POST', { id_user: id });
                usersData = usersData.filter(u => (u.id || u.id_user) !== id);
                syncWithFrontoffice();
                showNotification('Utilisateur supprimé');
                refreshModule();
            } catch (error) {
                showNotification(error.message, true);
            }
        }
    }
    
    // ==================== FORUM PUBLICATIONS ====================
    function renderForum() {
        const approvedPosts = forumPosts.filter(p => p.status === 'approved').length;
        const pendingPosts = forumPosts.filter(p => p.status === 'pending').length;
        
        if(forumPosts.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-newspaper"></i><p>Aucune publication</p><button class="btn btn-medical" onclick="showAddPostModal()"><i class="fas fa-plus"></i> Nouvelle publication</button></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${forumPosts.length}</div><div class="stat-label">Total publications</div></div>
                <div class="stat-card"><div class="stat-number">${approvedPosts}</div><div class="stat-label">Approuvées</div></div>
                <div class="stat-card"><div class="stat-number">${pendingPosts}</div><div class="stat-label">En attente</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Publications des médecins</h5>
                    <div class="btn-group-actions">
                        <button class="btn-medical btn-sm" onclick="showAddPostModal()"><i class="fas fa-plus"></i> Nouvelle</button>
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Publications')"><i class="fas fa-chart-line"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('forumTable', 'forum-publications.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                <div id="forumTable">
                <table class="data-table"><thead><tr><th>Médecin</th><th>Contenu</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>${forumPosts.map(p => `<tr>
                    <td>${escapeHtml(p.doctor_name)}</td>
                    <td>${escapeHtml(p.content.substring(0,50))}...</td>
                    <td>${p.date}</td>
                    <td><span class="status-badge ${p.status==='approved'?'status-approved':'status-pending'}">${p.status}</span></td>
                    <td>
                        <button class="icon-btn edit" onclick="editPost(${p.id})"><i class="fas fa-edit"></i></button>
                        <button class="icon-btn ${p.status==='approved'?'flag':'approve'}" onclick="togglePostStatus(${p.id})"><i class="fas ${p.status==='approved'?'fa-ban':'fa-check-circle'}"></i></button>
                        <button class="icon-btn delete" onclick="deletePost(${p.id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('')}</tbody>
                </table>
                </div>
            </div>
        `;
    }
    
    function showAddPostModal() {
        const select = document.getElementById('postDoctorId');
        const doctors = usersData.filter(u => u.role === 'medecin');
        if(select) select.innerHTML = '<option value="">Sélectionner un médecin</option>' + doctors.map(d => `<option value="${d.id}">${escapeHtml(d.name || `${d.nom || ''} ${d.prenom || ''}`.trim())}</option>`).join('');
        if(doctors.length === 0) { showNotification('Veuillez d\'abord ajouter des médecins', true); return; }
        new bootstrap.Modal(document.getElementById('addPostModal')).show();
    }
    
    document.getElementById('addPostForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const doctorId = parseInt(document.getElementById('postDoctorId').value);
        const doctor = usersData.find(u => u.id === doctorId);
        if(!doctor) { showNotification('Veuillez sélectionner un médecin', true); return; }
        
        const newPost = {
            id: Date.now(),
            doctor_id: doctorId,
            doctor_name: doctor.name || `${doctor.nom || ''} ${doctor.prenom || ''}`.trim(),
            doctor_avatar: (doctor.name || `${doctor.nom || ''} ${doctor.prenom || ''}`.trim()).substring(0,2).toUpperCase(),
            content: document.getElementById('postContent').value,
            image: document.getElementById('postImage').value || null,
            video: document.getElementById('postVideo').value || null,
            date: new Date().toLocaleDateString('fr-FR'),
            status: 'pending',
            comments: []
        };
        forumPosts.push(newPost);
        savePosts();
        syncWithFrontoffice();
        bootstrap.Modal.getInstance(document.getElementById('addPostModal')).hide();
        document.getElementById('addPostForm').reset();
        showNotification('Publication ajoutée, en attente de validation');
        refreshModule();
    });
    
    function editPost(id) { showNotification('Fonctionnalité d\'édition à venir'); }
    function togglePostStatus(id) {
        const post = forumPosts.find(p => p.id === id);
        if(post) { post.status = post.status === 'approved' ? 'pending' : 'approved'; savePosts(); syncWithFrontoffice(); showNotification(`Publication ${post.status === 'approved' ? 'approuvée' : 'désapprouvée'}`); refreshModule(); }
    }
    function deletePost(id) {
        if(confirm('Supprimer cette publication ?')){ forumPosts = forumPosts.filter(p => p.id !== id); savePosts(); syncWithFrontoffice(); showNotification('Publication supprimée'); refreshModule(); }
    }
    
    // ==================== COMMENTAIRES ====================
    function renderComments() {
        const allComments = forumPosts.flatMap(p => (p.comments || []).map(c => ({ ...c, post_id: p.id, post_content: p.content.substring(0,30), doctor_name: p.doctor_name })));
        const pendingComments = allComments.filter(c => c.status === 'pending');
        const approvedComments = allComments.filter(c => c.status === 'approved');
        
        if(allComments.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-comments"></i><p>Aucun commentaire</p></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${allComments.length}</div><div class="stat-label">Total commentaires</div></div>
                <div class="stat-card"><div class="stat-number">${pendingComments.length}</div><div class="stat-label">En attente</div></div>
                <div class="stat-card"><div class="stat-number">${approvedComments.length}</div><div class="stat-label">Approuvés</div></div>
            </div>
            ${pendingComments.length ? `<div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="fas fa-clock me-2"></i>Commentaires en attente (${pendingComments.length})</h5>
                    <button class="btn-outline-medical btn-sm" onclick="showStats('Commentaires')"><i class="fas fa-chart-line"></i> Statistiques</button>
                </div>
                <div id="pendingCommentsTable">
                <table class="data-table"><thead><tr><th>Utilisateur</th><th>Commentaire</th><th>Médecin</th><th>Actions</th></tr></thead>
                <tbody>${pendingComments.map(c => `<tr>
                    <td>${escapeHtml(c.user_name)}</td>
                    <td>${escapeHtml(c.text)}</td>
                    <td>${escapeHtml(c.doctor_name)}</td>
                    <td>
                        <button class="icon-btn approve" onclick="approveComment(${c.id}, ${c.post_id})"><i class="fas fa-check-circle"></i></button>
                        <button class="icon-btn flag" onclick="reportComment(${c.id}, ${c.post_id})"><i class="fas fa-flag"></i></button>
                        <button class="icon-btn delete" onclick="deleteComment(${c.id}, ${c.post_id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('')}</tbody>
                </table>
                </div>
            </div>` : ''}
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="fas fa-check-circle me-2"></i>Commentaires approuvés (${approvedComments.length})</h5>
                    <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('approvedCommentsTable', 'commentaires-approuves.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                </div>
                <div id="approvedCommentsTable">
                <table class="data-table"><thead><tr><th>Utilisateur</th><th>Commentaire</th><th>Statut</th><th>Actions</th><tr></thead>
                <tbody>${approvedComments.map(c => `<tr>
                    <td>${escapeHtml(c.user_name)}</td>
                    <td>${escapeHtml(c.text)}</td>
                    <td><span class="status-badge status-approved">Approuvé</span></td>
                    <td><button class="icon-btn flag" onclick="reportComment(${c.id}, ${c.post_id})"><i class="fas fa-flag"></i></button><button class="icon-btn delete" onclick="deleteComment(${c.id}, ${c.post_id})"><i class="fas fa-trash"></i></button></td>
                </tr>`).join('')}</tbody>
                </table>
                </div>
            </div>
        `;
    }
    
    function findAndUpdateComment(commentId, postId, updateFn) {
        const post = forumPosts.find(p => p.id === postId);
        if(post && post.comments) {
            const commentIndex = post.comments.findIndex(c => c.id === commentId);
            if(commentIndex !== -1) { updateFn(post.comments[commentIndex]); savePosts(); syncWithFrontoffice(); refreshModule(); return true; }
        }
        return false;
    }
    
    function approveComment(commentId, postId) { findAndUpdateComment(commentId, postId, (c) => { c.status = 'approved'; }); showNotification('Commentaire approuvé'); }
    function reportComment(commentId, postId) { findAndUpdateComment(commentId, postId, (c) => { c.status = 'reported'; }); showNotification('Commentaire signalé'); }
    function deleteComment(commentId, postId) {
        if(confirm('Supprimer ce commentaire ?')) {
            const post = forumPosts.find(p => p.id === postId);
            if(post && post.comments) { post.comments = post.comments.filter(c => c.id !== commentId); savePosts(); syncWithFrontoffice(); showNotification('Commentaire supprimé'); refreshModule(); }
        }
    }
    
    // ==================== AVIS PATIENTS ====================
    function renderReviews() {
        const pendingReviews = reviewsData.filter(r => r.status === 'pending');
        const approvedReviews = reviewsData.filter(r => r.status === 'approved');
        const reportedReviews = reviewsData.filter(r => r.status === 'reported');
        const avgRating = approvedReviews.length ? (approvedReviews.reduce((s,r)=>s+r.rating,0)/approvedReviews.length).toFixed(1) : 0;
        const ratingCounts = {1:0,2:0,3:0,4:0,5:0};
        approvedReviews.forEach(r => ratingCounts[r.rating]++);
        
        if(reviewsData.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-star"></i><p>Aucun avis patient</p><button class="btn btn-medical" onclick="showNotifyReviewModal()"><i class="fas fa-bell"></i> Notifier un patient</button></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${avgRating}</div><div class="stat-label">Note moyenne</div></div>
                <div class="stat-card"><div class="stat-number">${reviewsData.length}</div><div class="stat-label">Total avis</div><small>${pendingReviews.length} en attente</small></div>
                <div class="stat-card"><div class="stat-number">${approvedReviews.length}</div><div class="stat-label">Approuvés</div><small>${reportedReviews.length} signalés</small></div>
            </div>
            <div class="data-card"><h6>Distribution des notes</h6>${[5,4,3,2,1].map(star => { const count = ratingCounts[star]; const pct = approvedReviews.length ? (count/approvedReviews.length*100) : 0; return `<div class="chart-bar"><div class="chart-bar-fill" style="width:${pct}%">${star}★ (${count})</div></div>`; }).join('')}</div>
            ${pendingReviews.length ? `<div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Avis en attente (${pendingReviews.length})</h5>
                    <button class="btn-outline-medical btn-sm" onclick="showStats('Avis Patients')"><i class="fas fa-chart-line"></i> Statistiques</button>
                </div>
                <div id="pendingReviewsTable">
                <table class="data-table"><thead><tr><th>Patient</th><th>Médecin</th><th>Note</th><th>Commentaire</th><th>Actions</th></tr></thead>
                <tbody>${pendingReviews.map(r => `<tr>
                    <td>${escapeHtml(r.patient_name)}</td>
                    <td>${escapeHtml(r.doctor_name)}</td>
                    <td>${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</td>
                    <td>${escapeHtml(r.comment)}</td>
                    <td>
                        <button class="icon-btn approve" onclick="approveReview(${r.id})"><i class="fas fa-check-circle"></i></button>
                        <button class="icon-btn flag" onclick="reportReview(${r.id})"><i class="fas fa-flag"></i></button>
                        <button class="icon-btn delete" onclick="deleteReview(${r.id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`).join('')}</tbody>
                </table>
                </div>
            </div>` : ''}
            <div class="data-card">
                <button class="btn-medical me-2" onclick="showNotifyReviewModal()"><i class="fas fa-bell"></i> Notifier un patient</button>
                <button class="btn-outline-medical" onclick="exportToPDF('pendingReviewsTable', 'avis-patients.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF avis</button>
                <button class="btn-outline-medical ms-2" onclick="sendAutoReviewNotification()"><i class="fas fa-clock"></i> Auto-notification</button>
            </div>
        `;
    }
    
    function approveReview(id) { const r = reviewsData.find(r => r.id === id); if(r){ r.status = 'approved'; saveReviews(); syncWithFrontoffice(); showNotification(`Avis approuvé`); refreshModule(); } }
    function reportReview(id) { const r = reviewsData.find(r => r.id === id); if(r){ r.status = 'reported'; saveReviews(); syncWithFrontoffice(); showNotification(`Avis signalé`); refreshModule(); } }
    function deleteReview(id) { if(confirm('Supprimer cet avis ?')){ reviewsData = reviewsData.filter(r => r.id !== id); saveReviews(); syncWithFrontoffice(); showNotification('Avis supprimé'); refreshModule(); } }
    
    function showNotifyReviewModal() {
        const select = document.getElementById('notifyPatientId');
        const patients = usersData.filter(u => u.role === 'patient');
        if(select) select.innerHTML = '<option value="">Sélectionner</option>' + patients.map(p => `<option value="${p.id}">${escapeHtml(p.name || `${p.nom || ''} ${p.prenom || ''}`.trim())}</option>`).join('');
        if(patients.length === 0) { showNotification('Aucun patient disponible', true); return; }
        new bootstrap.Modal(document.getElementById('notifyReviewModal')).show();
    }
    
    document.getElementById('notifyReviewForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const patient = usersData.find(u => u.id == document.getElementById('notifyPatientId').value);
        if(patient) { showNotification(`📧 Notification envoyée à ${patient.name || `${patient.nom || ''} ${patient.prenom || ''}`.trim()}`); bootstrap.Modal.getInstance(document.getElementById('notifyReviewModal')).hide(); }
        else showNotification('Veuillez sélectionner un patient', true);
    });
    
    function sendAutoReviewNotification() { showNotification(`🔔 Notification envoyée à ${usersData.filter(u=>u.role==='patient').length} patient(s)`); }
    
    // ==================== RENDEZ-VOUS ====================
    function renderAppointments() {
        const paidAppointments = appointmentsData.filter(a => a.payment_status === 'payé').length;
        const pendingPayments = appointmentsData.filter(a => a.payment_status === 'en attente').length;
        const totalAmount = appointmentsData.reduce((sum, a) => sum + (a.amount || 0), 0);
        
        if(appointmentsData.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-calendar-alt"></i><p>Aucun rendez-vous</p></div></div>`;
        }
        
        return `
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">${appointmentsData.length}</div><div class="stat-label">Total RDV</div></div>
                <div class="stat-card"><div class="stat-number">${paidAppointments}</div><div class="stat-label">Payés</div></div>
                <div class="stat-card"><div class="stat-number">${pendingPayments}</div><div class="stat-label">En attente</div></div>
                <div class="stat-card"><div class="stat-number">${totalAmount}€</div><div class="stat-label">CA total</div></div>
            </div>
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Liste des rendez-vous</h5>
                    <div class="btn-group-actions">
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Rendez-vous')"><i class="fas fa-chart-line"></i> Statistiques</button>
                        <span class="export-btn btn-outline-medical btn-sm" onclick="exportToPDF('appointmentsTable', 'rendez-vous.pdf')"><i class="fas fa-file-pdf"></i> Exporter PDF</span>
                    </div>
                </div>
                <div id="appointmentsTable">
                <table class="data-table"><thead><tr><th>Patient</th><th>Médecin</th><th>Date</th><th>Montant</th><th>Paiement</th><th>Actions</th></tr></thead>
                <tbody>${appointmentsData.map(a => `<tr>
                    <td>${escapeHtml(a.patient_name)}</td>
                    <td>${escapeHtml(a.doctor_name)}</td>
                    <td>${a.date}</td>
                    <td>${a.amount}€</td>
                    <td><span class="status-badge ${a.payment_status==='payé'?'status-approved':'status-pending'}">${a.payment_status}</span></td>
                    <td><button class="icon-btn" onclick="confirmPayment(${a.id})"><i class="fas fa-credit-card"></i></button><button class="icon-btn delete" onclick="deleteAppointment(${a.id})"><i class="fas fa-trash"></i></button></td>
                </tr>`).join('')}</tbody>
                </table>
                </div>
            </div>
        `;
    }
    
    function confirmPayment(id) { 
        const a = appointmentsData.find(a => a.id === id); 
        if(a){ a.payment_status = 'payé'; saveAppointments(); showNotification('Paiement confirmé'); refreshModule(); } 
    }
    function deleteAppointment(id) { 
        if(confirm('Annuler ce rendez-vous ?')){ appointmentsData = appointmentsData.filter(a => a.id !== id); saveAppointments(); showNotification('Rendez-vous annulé'); refreshModule(); } 
    }
    
    // ==================== INIT ====================
    async function initBackoffice() {
        await loadAllData();
        syncWithFrontoffice();
        switchModule('dashboard');
    }
    
    initBackoffice();
</script>
</body>
</html>