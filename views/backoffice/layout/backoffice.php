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
            --sidebar-width: 240px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            font-family: 'Inter', sans-serif;
            background: var(--medical-gray);
            color: var(--medical-text);
            overflow-x: hidden;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .dashboard-container { display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background: white;
            box-shadow: 2px 0 20px rgba(0,0,0,0.05);
            padding: 24px 16px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            padding-bottom: 18px;
            border-bottom: 1px solid #eee;
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
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu li { margin-bottom: 4px; }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 14px;
            color: var(--medical-text);
            text-decoration: none;
            transition: all 0.25s;
            font-weight: 500;
            font-size: 0.88rem;
            cursor: pointer;
        }
        .sidebar-menu a:hover { background: var(--medical-light-blue); color: var(--medical-blue); }
        .sidebar-menu a.active {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            color: white;
            box-shadow: 0 4px 12px rgba(43,123,228,0.3);
        }
        .sidebar-footer {
            position: absolute;
            bottom: 24px;
            left: 16px;
            right: 16px;
            padding-top: 16px;
            border-top: 1px solid #eee;
        }

        /* ── Main content ── */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px 24px;
            min-width: 0;
            overflow-x: hidden;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            background: white;
            padding: 14px 22px;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .page-title {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ── Stats grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.08); }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 14px;
        }
        .stat-number { font-size: 2rem; font-weight: 800; margin: 8px 0 4px; }
        .stat-label { color: #6c7a8a; font-size: 0.85rem; }

        /* ── Data card ── */
        .data-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 22px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            animation: slideIn 0.35s ease-out;
            overflow: hidden;
        }

        /* ── Table ── */
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .data-table th, .data-table td {
            padding: 11px 10px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            white-space: nowrap;
        }
        .data-table td { white-space: normal; word-break: break-word; max-width: 180px; }
        .data-table td a[href^="mailto:"] {
            color: var(--medical-blue);
            text-decoration: none;
            font-weight: 500;
        }
        .data-table td a[href^="mailto:"]:hover { text-decoration: underline; }
        .data-table th {
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--medical-blue);
            background: var(--medical-light-blue);
        }

        /* ── Colonnes triables ── */
        .data-table th.sortable {
            cursor: pointer;
            user-select: none;
            transition: background .15s;
        }
        .data-table th.sortable:hover { background: #d0e8ff; }
        .data-table th.sortable.sort-active { background: #c2dfff; }
        .sort-icon { font-size: 0.7rem; margin-left: 4px; opacity: 0.5; }
        .sort-icon.active { opacity: 1; color: #1a5fc8; }

        /* ── Badges ── */
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap;
        }
        .status-approved { background: #e8f8f0; color: #27ae60; }
        .status-pending  { background: #fff3e0; color: #e67e22; }
        .status-reported { background: #fde8e8; color: #e74c3c; }

        /* ── Buttons ── */
        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px 9px;
            margin: 0 2px;
            border-radius: 10px;
            transition: all 0.2s;
            font-size: 0.85rem;
        }
        .icon-btn:hover { background: var(--medical-gray); transform: scale(1.08); }
        .icon-btn.approve { color: #27ae60; }
        .icon-btn.delete  { color: #e74c3c; }
        .icon-btn.edit    { color: var(--medical-blue); }
        .icon-btn.flag    { color: #f39c12; }

        .btn-medical {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border: none;
            padding: 9px 20px;
            border-radius: 30px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-medical:hover { transform: translateY(-2px); box-shadow: 0 5px 14px rgba(43,123,228,0.35); }
        .btn-medical.btn-sm { padding: 7px 16px; font-size: 0.8rem; }

        .btn-outline-medical {
            background: transparent;
            border: 2px solid var(--medical-blue);
            color: var(--medical-blue);
            padding: 7px 18px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.25s;
        }
        .btn-outline-medical:hover { background: var(--medical-light-blue); }
        .btn-outline-medical.btn-sm { padding: 6px 14px; font-size: 0.8rem; }

        .btn-group-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        /* ── Search bar ── */
        .search-bar-card { padding: 18px 20px; }
        .search-bar-card .section-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--medical-text);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .search-row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
        .search-field {
            flex: 1;
            min-width: 130px;
        }
        .search-field label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c7a8a;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .search-field input,
        .search-field select {
            width: 100%;
            border: 1.5px solid #e0e6ef;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            color: var(--medical-text);
            background: #fafbfc;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .search-field input:focus,
        .search-field select:focus {
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 3px rgba(43,123,228,0.1);
            background: #fff;
        }
        .search-actions { display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap; }
        .search-result-count {
            font-size: 0.8rem;
            color: #6c7a8a;
            padding: 4px 10px;
            background: var(--medical-gray);
            border-radius: 20px;
            white-space: nowrap;
        }

        /* ── Modal ── */
        .modal-custom .modal-content {
            border-radius: 24px;
            border: none;
            padding: 8px;
        }
        .form-control-custom {
            border: 1.5px solid #e0e6ef;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 0.88rem;
        }
        .form-control-custom:focus {
            border-color: var(--medical-blue);
            box-shadow: 0 0 0 3px rgba(43,123,228,0.1);
        }

        /* ── Toast ── */
        .notification-toast {
            position: fixed;
            bottom: 22px;
            right: 22px;
            background: white;
            padding: 12px 22px;
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.14);
            transform: translateX(420px);
            transition: transform 0.3s;
            z-index: 2000;
            border-left: 4px solid #2ecc71;
            font-size: 0.88rem;
            max-width: 340px;
        }
        .notification-toast.show { transform: translateX(0); }

        /* ── Chart bars ── */
        .chart-bar {
            background: var(--medical-gray);
            border-radius: 10px;
            height: 36px;
            margin-bottom: 10px;
            overflow: hidden;
        }
        .chart-bar-fill {
            background: linear-gradient(90deg, var(--medical-blue), var(--medical-green));
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 12px;
            color: white;
            font-weight: 500;
            font-size: 0.82rem;
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6c7a8a;
        }
        .empty-state i { font-size: 3.5rem; margin-bottom: 16px; opacity: 0.25; display: block; }

        /* ── Followup ── */
        .followup-card {
            background: var(--medical-gray);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
            border-left: 4px solid var(--medical-blue);
        }
        .followup-card h6 { margin-bottom: 8px; color: var(--medical-blue); font-size: 0.88rem; }

        /* ── Donut stats ── */
        .stat-donut-wrap { display: flex; justify-content: center; margin-bottom: 10px; }
        .stat-donut-wrap svg { filter: drop-shadow(0 2px 6px rgba(0,0,0,.07)); }
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
<div class="col-md-6"><label>Sexe</label><select class="form-select form-control-custom" id="newUserSexe"><option value="">Sélectionner</option><option value="Homme">Homme</option><option value="Femme">Femme</option></select></div>
<div class="col-md-6"><label>Date naissance</label><input type="date" class="form-control form-control-custom" id="newUserDateNaissance"></div>
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
        <div class="col-md-6"><label>Sexe</label><select class="form-select form-control-custom" id="editPatientSexe"><option value="">Sélectionner</option><option value="Homme">Homme</option><option value="Femme">Femme</option></select></div>
        <div class="col-md-6"><label>Date de naissance</label><input type="date" class="form-control form-control-custom" id="editPatientDateNaissance"></div>
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
    
    // =========================================================
    // ÉTAT GLOBAL : filtres et tri actifs (partagés entre search + export)
    // =========================================================
    let activeFilters   = {};
    let activeSortField = 'id_user';
    let activeSortDir   = 'DESC';

    // =========================================================
    // STATISTIQUES — appel API réel
    // =========================================================
    async function showStats(moduleName) {
        if (moduleName !== 'Utilisateurs') {
            showNotification(`📊 Statistiques - ${moduleName}`);
            return;
        }
        try {
            const stats = await apiRequest('stats', 'GET');
            renderUserStatsModal(stats);
        } catch (err) {
            showNotification(`Erreur stats: ${err.message}`, true);
        }
    }

    // ── Helpers SVG donut ──────────────────────────────────────
    function buildDonutSVG(segments, size = 120) {
        // segments = [{value, color, label}]
        const total = segments.reduce((s, x) => s + x.value, 0);
        if (total === 0) return `<svg width="${size}" height="${size}"><text x="50%" y="54%" text-anchor="middle" fill="#aaa" font-size="12">—</text></svg>`;
        const r = 46, cx = size / 2, cy = size / 2;
        const circ = 2 * Math.PI * r;
        let offset = 0;
        let paths = '';
        segments.forEach(seg => {
            const pct   = seg.value / total;
            const dash  = pct * circ;
            const gap   = circ - dash;
            paths += `<circle cx="${cx}" cy="${cy}" r="${r}"
                fill="none" stroke="${seg.color}" stroke-width="22"
                stroke-dasharray="${dash.toFixed(2)} ${gap.toFixed(2)}"
                stroke-dashoffset="${(-offset * circ).toFixed(2)}"
                transform="rotate(-90 ${cx} ${cy})"/>`;
            offset += pct;
        });
        return `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
            <circle cx="${cx}" cy="${cy}" r="${r}" fill="none" stroke="#f0f0f0" stroke-width="22"/>
            ${paths}
            <text x="${cx}" y="${cy - 6}" text-anchor="middle" font-size="13" font-weight="700" fill="#2c3e50">${total}</text>
            <text x="${cx}" y="${cy + 12}" text-anchor="middle" font-size="9" fill="#6c7a8a">total</text>
        </svg>`;
    }

    function buildLegend(segments, total) {
        return segments.map(s => {
            const pct = total > 0 ? Math.round(s.value / total * 100) : 0;
            return `<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <span style="width:12px;height:12px;border-radius:50%;background:${s.color};flex-shrink:0;display:inline-block;"></span>
                <span style="font-size:13px;flex:1;">${escapeHtml(s.label)}</span>
                <strong style="font-size:13px;">${s.value}</strong>
                <span style="font-size:11px;color:#6c7a8a;">(${pct}%)</span>
            </div>`;
        }).join('');
    }

    function renderUserStatsModal(s) {
        document.getElementById('userStatsModal')?.remove();

        const COLORS = ['#2b7be4','#2ecc71','#f39c12','#e74c3c','#9b59b6','#1abc9c','#e67e22','#34495e'];

        // Donut rôles
        const roleSegs = Object.entries(s.par_role || {}).map(([k, v], i) => ({
            value: v, color: COLORS[i % COLORS.length], label: k
        }));

        // Donut sexe
        const sexeSegs = Object.entries(s.par_sexe || {}).map(([k, v], i) => ({
            value: v, color: i === 0 ? '#2b7be4' : '#e74c3c', label: k || 'Non renseigné'
        }));

        // Donut cas social (top 6)
        const socialEntries = Object.entries(s.par_cas_social || {}).slice(0, 6);
        const socialSegs = socialEntries.map(([k, v], i) => ({
            value: v, color: COLORS[i % COLORS.length], label: k
        }));

        const totalRole   = s.total_utilisateurs || 0;
        const totalSexe   = Object.values(s.par_sexe || {}).reduce((a, b) => a + b, 0);
        const totalSocial = socialEntries.reduce((a, [, v]) => a + v, 0);

        const html = `
        <div class="modal fade" id="userStatsModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content modal-custom">
              <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-chart-pie me-2"></i>Statistiques Utilisateurs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body" id="statsModalBody">

                <!-- KPI cards -->
                <div class="stats-grid mb-4">
                  <div class="stat-card text-center">
                    <div class="stat-icon mx-auto" style="background:#e8f4ff;color:#2b7be4;"><i class="fas fa-users"></i></div>
                    <div class="stat-number">${s.total_utilisateurs}</div>
                    <div class="stat-label">Total utilisateurs</div>
                  </div>
                  <div class="stat-card text-center">
                    <div class="stat-icon mx-auto" style="background:#e8f8f0;color:#2ecc71;"><i class="fas fa-user-injured"></i></div>
                    <div class="stat-number">${s.total_patients}</div>
                    <div class="stat-label">Patients</div>
                  </div>
                  <div class="stat-card text-center">
                    <div class="stat-icon mx-auto" style="background:#fff3e0;color:#f39c12;"><i class="fas fa-user-md"></i></div>
                    <div class="stat-number">${s.total_medecins}</div>
                    <div class="stat-label">Médecins</div>
                  </div>
                  <div class="stat-card text-center">
                    <div class="stat-icon mx-auto" style="background:#fee;color:#e74c3c;"><i class="fas fa-user-shield"></i></div>
                    <div class="stat-number">${s.total_admins}</div>
                    <div class="stat-label">Admins</div>
                  </div>
                </div>

                <!-- Donuts row -->
                <div class="row g-3 mb-3">

                  <!-- Donut rôles -->
                  <div class="col-md-4">
                    <div class="data-card p-3 text-center h-100">
                      <h6 class="mb-3"><i class="fas fa-users me-1"></i>Répartition par rôle</h6>
                      <div style="display:flex;justify-content:center;margin-bottom:12px;">
                        ${buildDonutSVG(roleSegs, 130)}
                      </div>
                      <div style="text-align:left;">${buildLegend(roleSegs, totalRole)}</div>
                    </div>
                  </div>

                  <!-- Donut sexe -->
                  <div class="col-md-4">
                    <div class="data-card p-3 text-center h-100">
                      <h6 class="mb-3"><i class="fas fa-venus-mars me-1"></i>Répartition par sexe</h6>
                      <div style="display:flex;justify-content:center;margin-bottom:12px;">
                        ${buildDonutSVG(sexeSegs, 130)}
                      </div>
                      <div style="text-align:left;">${buildLegend(sexeSegs, totalSexe)}</div>
                    </div>
                  </div>

                  <!-- Donut cas social -->
                  <div class="col-md-4">
                    <div class="data-card p-3 text-center h-100">
                      <h6 class="mb-3"><i class="fas fa-id-card me-1"></i>Cas social</h6>
                      <div style="display:flex;justify-content:center;margin-bottom:12px;">
                        ${buildDonutSVG(socialSegs, 130)}
                      </div>
                      <div style="text-align:left;">${buildLegend(socialSegs, totalSocial)}</div>
                    </div>
                  </div>

                </div>

                <!-- Moyennes patients -->
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="data-card p-3">
                      <h6><i class="fas fa-weight me-2"></i>Moyennes patients</h6>
                      <div style="display:flex;gap:20px;margin-top:12px;">
                        <div style="flex:1;text-align:center;">
                          ${buildDonutSVG([
                              {value: s.avg_poids_patients ? Math.round(s.avg_poids_patients) : 0, color:'#2b7be4', label:'Poids'},
                              {value: s.avg_poids_patients ? Math.max(0, 150 - Math.round(s.avg_poids_patients)) : 150, color:'#f0f0f0', label:''}
                          ], 100)}
                          <div style="font-size:13px;margin-top:4px;"><strong>${s.avg_poids_patients ?? '—'} kg</strong><br><span style="color:#6c7a8a;font-size:11px;">Poids moyen</span></div>
                        </div>
                        <div style="flex:1;text-align:center;">
                          ${buildDonutSVG([
                              {value: s.avg_taille_patients ? Math.round(s.avg_taille_patients * 100) : 0, color:'#2ecc71', label:'Taille'},
                              {value: s.avg_taille_patients ? Math.max(0, 220 - Math.round(s.avg_taille_patients * 100)) : 220, color:'#f0f0f0', label:''}
                          ], 100)}
                          <div style="font-size:13px;margin-top:4px;"><strong>${s.avg_taille_patients ?? '—'} m</strong><br><span style="color:#6c7a8a;font-size:11px;">Taille moyenne</span></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="data-card p-3">
                      <h6><i class="fas fa-id-card me-2"></i>Détail cas social</h6>
                      <div style="max-height:160px;overflow-y:auto;margin-top:8px;">
                        ${Object.entries(s.par_cas_social || {}).map(([k, v]) =>
                            `<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f0f0f0;font-size:13px;">
                              <span>${escapeHtml(k)}</span><strong>${v}</strong>
                            </div>`
                        ).join('') || '<p style="color:#aaa;font-size:13px;">Aucune donnée</p>'}
                      </div>
                    </div>
                  </div>
                </div>

              </div>
              <div class="modal-footer border-0">
                <button class="btn-medical" onclick="exportStatsPDF()"><i class="fas fa-file-pdf me-1"></i>Exporter PDF</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
              </div>
            </div>
          </div>
        </div>`;

        document.body.insertAdjacentHTML('beforeend', html);
        new bootstrap.Modal(document.getElementById('userStatsModal')).show();
    }

    function exportStatsPDF() {
        const el = document.getElementById('statsModalBody');
        if (!el) return;
        if (typeof html2pdf === 'undefined') { showNotification('html2pdf non chargé', true); return; }
        showNotification('Export PDF statistiques en cours…');
        html2pdf().from(el).set({
            filename: `stats-utilisateurs-${new Date().toISOString().slice(0,10)}.pdf`,
            margin: [10, 8, 10, 8],
            html2canvas: { scale: 2, useCORS: true, logging: false },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        }).save();
    }

    // =========================================================
    // EXPORT PDF — données filtrées + triées via API
    // =========================================================
    async function exportToPDF(elementId, filename) {
        if (elementId === 'usersTable' || elementId === 'patientsTable') {
            await exportUsersPDF(filename);
            return;
        }
        const element = document.getElementById(elementId);
        if (!element) { showNotification('Élément introuvable pour l\'export', true); return; }
        if (typeof html2pdf === 'undefined') { showNotification('html2pdf non chargé', true); return; }
        showNotification('Export PDF en cours…');
        html2pdf().from(element).set({
            filename: filename,
            margin: [8, 6, 8, 6],
            html2canvas: { scale: 2, useCORS: true, logging: false },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
        }).save();
    }

    async function exportUsersPDF(filename) {
        if (typeof html2pdf === 'undefined') { showNotification('html2pdf non chargé', true); return; }
        showNotification('Préparation de l\'export PDF…');
        try {
            const result = await apiRequest('export-pdf', 'POST', {
                filters:    activeFilters,
                sort_field: activeSortField,
                sort_dir:   activeSortDir,
            });

            const filterDesc = Object.entries(activeFilters).length
                ? 'Filtres : ' + Object.entries(activeFilters).map(([k, v]) => `${k}="${v}"`).join(', ')
                : 'Aucun filtre actif';

            const roleLabel = { patient: 'Patient', medecin: 'Médecin', admin: 'Admin' };
            const rows = result.map((u, i) => {
                const bg = i % 2 === 0 ? '#ffffff' : '#f7faff';
                return `<tr style="background:${bg};">
                  <td style="padding:5px 6px;">${escapeHtml(String(u.id_user))}</td>
                  <td style="padding:5px 6px;font-weight:600;">${escapeHtml(u.nom)} ${escapeHtml(u.prenom)}</td>
                  <td style="padding:5px 6px;">${escapeHtml(u.email)}</td>
                  <td style="padding:5px 6px;">${escapeHtml(roleLabel[u.role] || u.role)}</td>
                  <td style="padding:5px 6px;">${escapeHtml(u.sexe || '—')}</td>
                  <td style="padding:5px 6px;text-align:center;">${u.poids ? u.poids + ' kg' : '—'}</td>
                  <td style="padding:5px 6px;text-align:center;">${u.taille ? u.taille + ' m' : '—'}</td>
                  <td style="padding:5px 6px;">${escapeHtml(u.cas_social || '—')}</td>
                  <td style="padding:5px 6px;">${escapeHtml(u.specialite || '—')}</td>
                </tr>`;
            }).join('');

            const pdfContent = `
                <div style="font-family:Arial,Helvetica,sans-serif;padding:12px;color:#2c3e50;">
                  <div style="display:flex;align-items:center;margin-bottom:10px;border-bottom:3px solid #2b7be4;padding-bottom:8px;">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#2b7be4,#2ecc71);border-radius:10px;margin-right:12px;"></div>
                    <div>
                      <div style="font-size:16px;font-weight:800;color:#2b7be4;">GlobalHealth Connect</div>
                      <div style="font-size:10px;color:#6c7a8a;">Liste des utilisateurs — Backoffice Médical</div>
                    </div>
                    <div style="margin-left:auto;text-align:right;font-size:10px;color:#6c7a8a;">
                      Généré le ${new Date().toLocaleString('fr-FR')}<br>
                      ${filterDesc}<br>
                      Tri : <strong>${activeSortField}</strong> ${activeSortDir} &nbsp;|&nbsp; <strong>${result.length}</strong> résultat(s)
                    </div>
                  </div>
                  <table style="width:100%;border-collapse:collapse;font-size:9.5px;">
                    <thead>
                      <tr style="background:#2b7be4;color:white;">
                        <th style="padding:6px 6px;text-align:left;">ID</th>
                        <th style="padding:6px 6px;text-align:left;">Nom Prénom</th>
                        <th style="padding:6px 6px;text-align:left;">Email</th>
                        <th style="padding:6px 6px;text-align:left;">Rôle</th>
                        <th style="padding:6px 6px;text-align:left;">Sexe</th>
                        <th style="padding:6px 6px;text-align:center;">Poids</th>
                        <th style="padding:6px 6px;text-align:center;">Taille</th>
                        <th style="padding:6px 6px;text-align:left;">Cas social</th>
                        <th style="padding:6px 6px;text-align:left;">Spécialité</th>
                      </tr>
                    </thead>
                    <tbody>${rows || '<tr><td colspan="10" style="text-align:center;padding:20px;color:#aaa;">Aucun résultat</td></tr>'}</tbody>
                  </table>
                  <div style="margin-top:10px;font-size:9px;color:#aaa;text-align:right;">
                    GlobalHealth Connect — Document confidentiel
                  </div>
                </div>`;

            // Conteneur hors-écran pour éviter tout flash visuel
            const wrapper = document.createElement('div');
            wrapper.style.cssText = 'position:fixed;left:-9999px;top:0;width:297mm;background:#fff;';
            wrapper.innerHTML = pdfContent;
            document.body.appendChild(wrapper);

            await html2pdf().from(wrapper).set({
                filename: filename || `utilisateurs-${new Date().toISOString().slice(0,10)}.pdf`,
                margin: [8, 6, 8, 6],
                html2canvas: { scale: 2, useCORS: true, logging: false, windowWidth: 1122 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
            }).save();

            document.body.removeChild(wrapper);
            showNotification(`✅ PDF généré — ${result.length} utilisateur(s)`);
        } catch (err) {
            showNotification(`Erreur export PDF: ${err.message}`, true);
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
        const doctors  = usersData.filter(u => u.role === 'medecin');
        const patients = usersData.filter(u => u.role === 'patient');

        if (usersData.length === 0) {
            return `<div class="data-card"><div class="empty-state"><i class="fas fa-users"></i><p>Aucun utilisateur</p><button class="btn-medical" onclick="showAddUserModal()"><i class="fas fa-plus"></i> Ajouter un utilisateur</button></div></div>`;
        }

        return `
            <!-- KPI -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#e8f4ff;color:#2b7be4;"><i class="fas fa-users"></i></div>
                    <div class="stat-number">${usersData.length}</div>
                    <div class="stat-label">Total utilisateurs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fff3e0;color:#f39c12;"><i class="fas fa-user-md"></i></div>
                    <div class="stat-number">${doctors.length}</div>
                    <div class="stat-label">Médecins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#e8f8f0;color:#27ae60;"><i class="fas fa-user-injured"></i></div>
                    <div class="stat-number">${patients.length}</div>
                    <div class="stat-label">Patients</div>
                </div>
            </div>

            <!-- BARRE RECHERCHE AVANCÉE -->
            <div class="data-card search-bar-card">
                <div class="section-title">
                    <i class="fas fa-search" style="color:var(--medical-blue);"></i>
                    Recherche avancée &amp; Tri dynamique
                </div>
                <form id="searchUsersForm" onsubmit="return false;">
                    <!-- Ligne 1 : filtres texte + sélecteurs -->
                    <div class="search-row">
                        <div class="search-field">
                            <label>Nom</label>
                            <input type="text" id="filterNom" placeholder="Ex : Dupont"
                                   value="${escapeHtml(activeFilters.nom || '')}">
                        </div>
                        <div class="search-field">
                            <label>Prénom</label>
                            <input type="text" id="filterPrenom" placeholder="Ex : Marie"
                                   value="${escapeHtml(activeFilters.prenom || '')}">
                        </div>
                        <div class="search-field" style="flex:2;min-width:180px;">
                            <label>Email</label>
                            <input type="text" id="filterEmail" placeholder="Ex : marie@email.com"
                                   value="${escapeHtml(activeFilters.email || '')}">
                        </div>
                        <div class="search-field">
                            <label>Sexe</label>
                            <select id="filterSexe">
                                <option value="">Tous</option>
                                <option value="Homme" ${activeFilters.sexe === 'Homme' ? 'selected' : ''}>Homme</option>
                                <option value="Femme" ${activeFilters.sexe === 'Femme' ? 'selected' : ''}>Femme</option>
                            </select>
                        </div>
                        <div class="search-field">
                            <label>Rôle</label>
                            <select id="filterRole">
                                <option value="">Tous</option>
                                <option value="patient" ${activeFilters.role === 'patient' ? 'selected' : ''}>Patient</option>
                                <option value="medecin" ${activeFilters.role === 'medecin' ? 'selected' : ''}>Médecin</option>
                                <option value="admin"   ${activeFilters.role === 'admin'   ? 'selected' : ''}>Admin</option>
                            </select>
                        </div>
                        <div class="search-field">
                            <label>Cas social</label>
                            <input type="text" id="filterCasSocial" placeholder="Ex : CNSS"
                                   value="${escapeHtml(activeFilters.cas_social || '')}">
                        </div>
                    </div>
                    <!-- Ligne 2 : tri + actions -->
                    <div class="search-row" style="align-items:flex-end;">
                        <div class="search-field" style="max-width:180px;">
                            <label>Trier par</label>
                            <select id="sortField">
                                <option value="id_user"        ${activeSortField === 'id_user'        ? 'selected' : ''}>ID</option>
                                <option value="nom"            ${activeSortField === 'nom'            ? 'selected' : ''}>Nom</option>
                                <option value="prenom"         ${activeSortField === 'prenom'         ? 'selected' : ''}>Prénom</option>
                                <option value="poids"          ${activeSortField === 'poids'          ? 'selected' : ''}>Poids</option>
                                <option value="taille"         ${activeSortField === 'taille'         ? 'selected' : ''}>Taille</option>
                                <option value="date_naissance" ${activeSortField === 'date_naissance' ? 'selected' : ''}>Date naissance</option>
                            </select>
                        </div>
                        <div class="search-field" style="max-width:120px;">
                            <label>Ordre</label>
                            <select id="sortDir">
                                <option value="ASC"  ${activeSortDir === 'ASC'  ? 'selected' : ''}>↑ Croissant</option>
                                <option value="DESC" ${activeSortDir === 'DESC' ? 'selected' : ''}>↓ Décroissant</option>
                            </select>
                        </div>
                        <div class="search-actions">
                            <button type="button" class="btn-medical" onclick="applySearchAndSort()">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                            <button type="button" class="btn-outline-medical" onclick="resetSearchAndSort()">
                                <i class="fas fa-times"></i> Réinitialiser
                            </button>
                            <span id="searchResultCount" class="search-result-count" style="display:none;"></span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TABLEAU RÉSULTATS -->
            <div class="data-card" style="padding:18px 20px;">
                <div class="d-flex justify-content-between align-items-center mb-3" style="flex-wrap:wrap;gap:10px;">
                    <h5 class="mb-0" style="font-size:1rem;font-weight:700;">
                        <i class="fas fa-users me-2" style="color:var(--medical-blue);"></i>
                        Liste des utilisateurs
                        <span id="tableCount" style="font-size:0.8rem;color:#6c7a8a;font-weight:400;margin-left:8px;">(${usersData.length})</span>
                    </h5>
                    <div class="btn-group-actions">
                        <button class="btn-medical btn-sm" onclick="showAddUserModal()">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                        <button class="btn-outline-medical btn-sm" onclick="showStats('Utilisateurs')">
                            <i class="fas fa-chart-pie"></i> Statistiques
                        </button>
                        <button class="btn-outline-medical btn-sm" onclick="exportUsersTablePDF()">
                            <i class="fas fa-file-pdf"></i> Exporter PDF
                        </button>
                    </div>
                </div>
                <div class="table-scroll">
                    <div id="usersTable">
                        ${buildUsersTableHTML(usersData)}
                    </div>
                </div>
            </div>
        `;
    }

    function buildUsersTableHTML(data) {
        if (!data || data.length === 0) {
            return `<div class="empty-state"><i class="fas fa-search"></i><p>Aucun résultat correspondant aux critères</p></div>`;
        }

        // Définition des colonnes (label, champ API pour tri, largeur indicative)
        const cols = [
            { label: 'Nom Prénom',     field: 'nom',            w: '150px' },
            { label: 'Email',          field: null,             w: '180px' },
            { label: 'Rôle',           field: null,             w: '90px'  },
            { label: 'Sexe',           field: null,             w: '70px'  },
            { label: 'Poids',          field: 'poids',          w: '75px'  },
            { label: 'Taille',         field: 'taille',         w: '75px'  },
            { label: 'Date naissance', field: 'date_naissance', w: '120px' },
            { label: 'Cas social',     field: null,             w: '110px' },
            { label: 'Spécialité',     field: null,             w: '110px' },
            { label: 'Actions',        field: null,             w: '80px'  },
        ];

        const thCells = cols.map(c => {
            const style = `min-width:${c.w};`;
            if (!c.field) return `<th style="${style}">${c.label}</th>`;
            const isActive = activeSortField === c.field;
            const nextDir  = (isActive && activeSortDir === 'ASC') ? 'DESC' : 'ASC';
            const iconHtml = isActive
                ? `<span class="sort-icon active">${activeSortDir === 'ASC' ? '▲' : '▼'}</span>`
                : `<span class="sort-icon">⇅</span>`;
            return `<th class="sortable${isActive ? ' sort-active' : ''}" style="${style}"
                        onclick="quickSort('${c.field}','${nextDir}')"
                        title="Trier par ${c.label}">
                      ${c.label}${iconHtml}
                    </th>`;
        }).join('');

        const rows = data.map((u, i) => {
            const uid  = userId(u);
            const role = u.role || 'patient';
            const spec = (u.specialite && String(u.specialite).trim()) ? String(u.specialite).trim() : '—';
            const roleBadge = role === 'medecin'
                ? `<span class="status-badge status-approved">Médecin</span>`
                : role === 'admin'
                    ? `<span class="status-badge status-reported">Admin</span>`
                    : `<span class="status-badge status-pending">Patient</span>`;
            const rowBg = i % 2 === 0 ? '' : 'style="background:#fafbff;"';
            return `<tr ${rowBg}>
                <td><strong>${escapeHtml(userFullName(u))}</strong></td>
                <td style="font-size:0.82rem;">${escapeHtml(u.email || '—')}</td>
                <td>${roleBadge}</td>
                <td>${escapeHtml(u.sexe || '—')}</td>
                <td style="text-align:center;">${escapeHtml(displayMetric(u.poids, 'kg'))}</td>
                <td style="text-align:center;">${escapeHtml(displayMetric(u.taille, 'm'))}</td>
                <td>${escapeHtml(formatDateNaissance(u.date_naissance))}</td>
                <td>${escapeHtml(u.cas_social && String(u.cas_social).trim() ? u.cas_social : '—')}</td>
                <td>${escapeHtml(spec)}</td>
                <td style="white-space:nowrap;">
                    <button type="button" class="icon-btn edit"   onclick="editUser(${uid})"   title="Modifier"><i class="fas fa-edit"></i></button>
                    <button type="button" class="icon-btn delete" onclick="deleteUser(${uid})" title="Supprimer"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('');

        return `<table class="data-table" id="usersDataTable">
            <thead><tr>${thCells}</tr></thead>
            <tbody>${rows}</tbody>
        </table>`;
    }

    // ── Tri rapide via clic sur en-tête ──────────────────────
    async function quickSort(field, dir) {
        activeSortField = field;
        activeSortDir   = dir;
        const sf = document.getElementById('sortField');
        const sd = document.getElementById('sortDir');
        if (sf) sf.value = field;
        if (sd) sd.value = dir;
        try {
            const results = await apiRequest('search', 'POST', {
                filters:    activeFilters,
                sort_field: field,
                sort_dir:   dir,
            });
            const wrapper = document.getElementById('usersTable');
            if (wrapper) wrapper.innerHTML = buildUsersTableHTML(results);
            updateTableCount(results.length);
        } catch (err) {
            showNotification(`Erreur tri : ${err.message}`, true);
        }
    }

    function updateTableCount(n) {
        const el = document.getElementById('tableCount');
        if (el) el.textContent = `(${n})`;
        const rc = document.getElementById('searchResultCount');
        if (rc) { rc.textContent = `${n} résultat(s)`; rc.style.display = 'inline-block'; }
    }

    // ── Export PDF : tableau identique à l'écran ─────────────
    async function exportUsersTablePDF() {
        showNotification('Génération du PDF…');
        let rows;
        try {
            rows = await apiRequest('export-pdf', 'POST', {
                filters:    activeFilters,
                sort_field: activeSortField,
                sort_dir:   activeSortDir,
            });
        } catch (err) {
            showNotification(`Erreur export : ${err.message}`, true);
            return;
        }

        if (!rows || rows.length === 0) {
            showNotification('Aucune donnée à exporter', true);
            return;
        }

        const roleLabel = { patient: 'Patient', medecin: 'Médecin', admin: 'Admin' };
        const now = new Date().toLocaleString('fr-FR');
        const filterDesc = Object.entries(activeFilters).length
            ? Object.entries(activeFilters).map(([k, v]) => `${k}="${v}"`).join(', ')
            : 'Aucun filtre';

        // ── Construire le HTML du PDF ──────────────────────────
        let tbodyHtml = '';
        rows.forEach((u, i) => {
            const bg = i % 2 === 0 ? '#ffffff' : '#f4f7fb';
            const dn = u.date_naissance
                ? String(u.date_naissance).slice(0, 10).split('-').reverse().join('/')
                : '—';
            tbodyHtml += `<tr style="background:${bg};">
              <td>${escapeHtml(u.nom || '')} ${escapeHtml(u.prenom || '')}</td>
              <td>${escapeHtml(u.email || '—')}</td>
              <td>${escapeHtml(roleLabel[u.role] || u.role || '—')}</td>
              <td>${escapeHtml(u.sexe || '—')}</td>
              <td style="text-align:center;">${u.poids ? u.poids + ' kg' : '—'}</td>
              <td style="text-align:center;">${u.taille ? u.taille + ' m' : '—'}</td>
              <td>${dn}</td>
              <td>${escapeHtml(u.cas_social || '—')}</td>
              <td>${escapeHtml(u.specialite || '—')}</td>
            </tr>`;
        });

        const pdfHtml = `<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; color: #1a2b3c; }
  .wrap { padding: 18px 20px; }
  /* En-tête */
  .header { display: flex; align-items: center; margin-bottom: 14px;
            border-bottom: 3px solid #c0392b; padding-bottom: 10px; }
  .logo-box { width: 36px; height: 36px; background: linear-gradient(135deg,#2b7be4,#2ecc71);
              border-radius: 8px; margin-right: 12px; flex-shrink: 0; }
  .header-title { flex: 1; }
  .header-title h1 { font-size: 14px; font-weight: 800; color: #2b7be4; margin: 0 0 2px; }
  .header-title p  { font-size: 9px; color: #6c7a8a; margin: 0; }
  .header-meta { text-align: right; font-size: 8.5px; color: #6c7a8a; line-height: 1.7; }
  .header-meta strong { color: #1a2b3c; }
  /* Tableau */
  table { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 4px; }
  thead tr { background: #c0392b; color: #ffffff; }
  thead th { padding: 7px 6px; text-align: left; font-weight: 700;
             border-right: 1px solid rgba(255,255,255,0.2); }
  thead th:last-child { border-right: none; }
  tbody td { padding: 6px 6px; border-bottom: 1px solid #e8ecf0;
             border-right: 1px solid #e8ecf0; }
  tbody td:last-child { border-right: none; }
  tbody tr:last-child td { border-bottom: none; }
  /* Pied */
  .footer { margin-top: 12px; font-size: 8px; color: #aaa;
            display: flex; justify-content: space-between; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo-box"></div>
    <div class="header-title">
      <h1>GlobalHealth Connect — Liste des Utilisateurs</h1>
      <p>Backoffice Médical — Document confidentiel</p>
    </div>
    <div class="header-meta">
      <div>Généré le <strong>${now}</strong></div>
      <div>Filtres : <strong>${escapeHtml(filterDesc)}</strong></div>
      <div>Tri : <strong>${activeSortField}</strong> ${activeSortDir === 'ASC' ? '↑' : '↓'}
           &nbsp;|&nbsp; <strong>${rows.length}</strong> utilisateur(s)</div>
    </div>
  </div>
  <table>
    <thead>
      <tr>
        <th>Nom Prénom</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Sexe</th>
        <th style="text-align:center;">Poids</th>
        <th style="text-align:center;">Taille</th>
        <th>Date naiss.</th>
        <th>Cas social</th>
        <th>Spécialité</th>
      </tr>
    </thead>
    <tbody>${tbodyHtml}</tbody>
  </table>
  <div class="footer">
    <span>GlobalHealth Connect — Confidentiel</span>
    <span>Exporté depuis le Backoffice Médical</span>
  </div>
</div>
</body>
</html>`;

        // ── Ouvrir dans un nouvel onglet et déclencher l'impression ──
        const win = window.open('', '_blank', 'width=1100,height=800');
        if (!win) {
            showNotification('Autorisez les popups pour exporter le PDF', true);
            return;
        }
        win.document.open();
        win.document.write(pdfHtml);
        win.document.close();

        // Attendre le rendu puis imprimer (Ctrl+P → Enregistrer en PDF)
        win.onload = () => {
            setTimeout(() => {
                win.focus();
                win.print();
            }, 400);
        };

        showNotification(`✅ PDF prêt — ${rows.length} utilisateur(s) — utilisez "Enregistrer en PDF" dans la boîte d'impression`);
    }

    async function applySearchAndSort() {
        const filters = {};
        const nom       = document.getElementById('filterNom')?.value.trim();
        const prenom    = document.getElementById('filterPrenom')?.value.trim();
        const email     = document.getElementById('filterEmail')?.value.trim();
        const sexe      = document.getElementById('filterSexe')?.value;
        const role      = document.getElementById('filterRole')?.value;
        const casSocial = document.getElementById('filterCasSocial')?.value.trim();

        if (nom)       filters.nom        = nom;
        if (prenom)    filters.prenom     = prenom;
        if (email)     filters.email      = email;
        if (sexe)      filters.sexe       = sexe;
        if (role)      filters.role       = role;
        if (casSocial) filters.cas_social = casSocial;

        const sortField = document.getElementById('sortField')?.value || 'id_user';
        const sortDir   = document.getElementById('sortDir')?.value   || 'DESC';

        activeFilters   = filters;
        activeSortField = sortField;
        activeSortDir   = sortDir;

        try {
            const results = await apiRequest('search', 'POST', {
                filters,
                sort_field: sortField,
                sort_dir:   sortDir,
            });

            const wrapper = document.getElementById('usersTable');
            if (wrapper) wrapper.innerHTML = buildUsersTableHTML(results);
            updateTableCount(results.length);
            showNotification(`${results.length} utilisateur(s) trouvé(s)`);
        } catch (err) {
            showNotification(`Erreur recherche : ${err.message}`, true);
        }
    }

    async function resetSearchAndSort() {
        activeFilters   = {};
        activeSortField = 'id_user';
        activeSortDir   = 'DESC';

        // Recharge la liste complète depuis l'API
        try {
            await loadUsersFromApi();
        } catch (e) { /* silencieux */ }

        // Re-render le module pour réinitialiser les champs du formulaire
        loadModuleContent('users');
        showNotification('Filtres réinitialisés');
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
            'newUserNom', 'newUserPrenom', 'newUserSexe',
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
        const requiredFields = ['nom', 'prenom', 'sexe', 'poids', 'taille', 'email', 'mot_de_passe', 'date_naissance', 'adresse', 'role'];
        const missing = requiredFields.find((field) => !user[field] && user[field] !== 0);
        if (missing) return `Champ obligatoire manquant: ${missing}`;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(user.email)) return 'Email invalide';
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