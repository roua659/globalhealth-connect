<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/UtilisateurModel.php';
require_once __DIR__ . '/../controllers/ForumController.php';

// Récupérer les listes pour les formulaires
$utilisateurModel = new UtilisateurModel();
$users = $utilisateurModel->readAll();
$patients = $utilisateurModel->getPatients();
$medecins = $utilisateurModel->getMedecins();
$userStats = $utilisateurModel->getStatistics();

$pageTitle = "Backoffice - Administration";
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Récupérer les messages flash
$successMessage = Session::getFlash('success');
$errorMessage = Session::getFlash('error');
?>

<?php include __DIR__ . '/partials/header.php'; ?>
<?php include __DIR__ . '/partials/navbar.php'; ?>

<style>
    .sidebar {
        min-height: calc(100vh - 70px);
        background: white;
        box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        position: fixed;
        top: 70px;
        left: 0;
        width: 260px;
        z-index: 100;
        transition: all 0.3s;
    }
    
    .sidebar-menu {
        padding: 20px 0;
    }
    
    .sidebar-menu-item {
        padding: 12px 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--medical-text);
        text-decoration: none;
        transition: all 0.3s;
        border-left: 3px solid transparent;
    }
    
    .sidebar-menu-item:hover, .sidebar-menu-item.active {
        background: var(--medical-light-blue);
        border-left-color: var(--medical-blue);
        color: var(--medical-blue);
    }
    
    .sidebar-menu-item i {
        width: 22px;
        font-size: 1.1rem;
    }
    
    .main-content {
        margin-left: 260px;
        padding: 20px;
        margin-top: 70px;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }
        .sidebar.show {
            transform: translateX(0);
        }
        .main-content {
            margin-left: 0;
        }
    }
    
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .action-buttons button, .action-buttons a {
        padding: 5px 10px;
        font-size: 0.8rem;
        border-radius: 8px;
    }

    .advanced-search-card {
        background: white;
        border-radius: 20px;
        padding: 22px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .advanced-search-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #1a2b3c;
        font-weight: 800;
        margin-bottom: 18px;
    }

    .advanced-search-card label {
        color: #53657a;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .sortable-user-header {
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }

    .sortable-user-header:hover {
        color: var(--medical-blue);
    }

    .sort-indicator {
        color: #8fa1b5;
        font-size: 0.75rem;
        margin-left: 5px;
    }

    .sort-indicator.active {
        color: var(--medical-blue);
    }

    .search-result-count {
        color: #53657a;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .comments-card {
        border-radius: 18px;
        overflow: hidden;
        padding: 0;
    }

    .comments-table {
        margin-bottom: 0;
        table-layout: auto;
        min-width: 1180px;
    }

    .comments-table thead th {
        background: #f6f9fc;
        border-bottom: 1px solid #e3ebf4;
        color: #1a2b3c;
        font-size: 0.9rem;
        font-weight: 700;
        padding: 15px 16px;
        white-space: nowrap;
    }

    .comments-table tbody td {
        border-color: #eef2f6;
        padding: 14px 16px;
        vertical-align: middle;
    }

    .comments-table tbody tr:hover {
        background: #fbfdff;
    }

    .comment-user {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .comment-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        background: linear-gradient(135deg, var(--medical-blue), #2fc58d);
        color: #fff;
        font-weight: 800;
        font-size: 0.9rem;
    }

    .comment-user-name {
        font-weight: 600;
        color: #1a2b3c;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .comment-text-cell {
        color: #1a2b3c;
        line-height: 1.45;
        word-break: break-word;
        max-width: 380px;
    }

    .comment-publication {
        color: #53657a;
        line-height: 1.45;
        word-break: break-word;
        max-width: 320px;
    }

    .comment-date {
        color: #53657a;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .comment-status {
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 0.75rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .comment-status.publie {
        background: #e8f8f0;
        color: #128653;
    }

    .comment-status.supprime {
        background: #fff0f2;
        color: #c82333;
    }

    .comment-status.en_attente {
        background: #fff8e6;
        color: #9a6700;
    }

    .comment-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-start;
        flex-wrap: nowrap;
        min-width: 132px;
    }

    .comment-action-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        padding: 0 !important;
    }

    .comment-empty {
        padding: 42px 20px;
        text-align: center;
        color: #6c7a89;
    }
</style>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-menu">
        <a href="?page=dashboard" class="sidebar-menu-item <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="?page=rendezvous" class="sidebar-menu-item <?php echo $currentPage == 'rendezvous' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Rendez-vous</span>
        </a>
        <a href="?page=consultations" class="sidebar-menu-item <?php echo $currentPage == 'consultations' ? 'active' : ''; ?>">
            <i class="fas fa-stethoscope"></i>
            <span>Consultations</span>
        </a>
        <a href="?page=suivis" class="sidebar-menu-item <?php echo $currentPage == 'suivis' ? 'active' : ''; ?>">
            <i class="fas fa-heart-pulse"></i>
            <span>Suivis patients</span>
        </a>
        <a href="?page=dossiers" class="sidebar-menu-item <?php echo $currentPage == 'dossiers' ? 'active' : ''; ?>">
            <i class="fas fa-folder-open"></i>
            <span>Dossiers médicaux</span>
        </a>
        <a href="?page=users" class="sidebar-menu-item <?php echo $currentPage == 'users' ? 'active' : ''; ?>">
            <i class="fas fa-user-cog"></i>
            <span>Utilisateurs</span>
        </a>
        <a href="?page=patients" class="sidebar-menu-item <?php echo $currentPage == 'patients' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Patients</span>
        </a>
        <a href="?page=medecins" class="sidebar-menu-item <?php echo $currentPage == 'medecins' ? 'active' : ''; ?>">
            <i class="fas fa-user-md"></i>
            <span>Médecins</span>
        </a>
        <a href="?page=statistiques" class="sidebar-menu-item <?php echo $currentPage == 'statistiques' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Statistiques</span>
        </a>
        <a href="?page=confirmation-rdv" class="sidebar-menu-item <?php echo $currentPage == 'confirmation-rdv' ? 'active' : ''; ?>">
            <i class="fas fa-circle-check"></i>
            <span>Confirmer RDV</span>
        </a>
        <a href="?page=publications" class="sidebar-menu-item <?php echo $currentPage == 'publications' ? 'active' : ''; ?>">
            <i class="fas fa-newspaper"></i>
            <span>Publications</span>
        </a>
        <a href="?page=moderation" class="sidebar-menu-item <?php echo $currentPage == 'moderation' ? 'active' : ''; ?>">
            <i class="fas fa-shield-alt"></i>
            <span>Modération IA</span>
        </a>
        <a href="?page=commentaires" class="sidebar-menu-item <?php echo $currentPage == 'commentaires' ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i>
            <span>Commentaires</span>
        </a>
        <a href="?page=avis" class="sidebar-menu-item <?php echo $currentPage == 'avis' ? 'active' : ''; ?>">
            <i class="fas fa-star"></i>
            <span>Avis patients</span>
        </a>
        <hr class="my-3">
        <a href="?action=logout" class="sidebar-menu-item text-danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Messages Flash -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Bouton menu mobile -->
    <button class="btn btn-medical d-md-none mb-3" onclick="document.getElementById('sidebar').classList.toggle('show')">
        <i class="fas fa-bars"></i> Menu
    </button>
    
    <?php
    // Chargement du contenu selon la page
    switch ($currentPage) {
        case 'dashboard':
            includeContent('dashboard');
            break;
        case 'rendezvous':
            includeContent('rendezvous');
            break;
        case 'consultations':
            includeContent('consultations');
            break;
        case 'suivis':
            includeContent('suivis');
            break;
        case 'dossiers':
            includeContent('dossiers');
            break;
        case 'users':
            includeContent('users');
            break;
        case 'patients':
            includeContent('patients');
            break;
        case 'medecins':
            includeContent('medecins');
            break;
        case 'statistiques':
            includeContent('statistiques');
            break;
        case 'confirmation-rdv':
            includeContent('confirmation-rdv');
            break;
        case 'publications':
            includeContent('publications');
            break;
        case 'moderation':
            includeContent('moderation');
            break;
        case 'commentaires':
            includeContent('commentaires');
            break;
        case 'avis':
            includeContent('avis');
            break;
        default:
            includeContent('dashboard');
            break;
    }
    
    function includeContent($page) {
        global $users, $userStats, $patients, $medecins, $rendez_vous, $stats, $dossiers, $rdvStats, $dossierStats;
        global $consultations, $consultationStats, $consultationRendezVous;
        global $suivis, $suiviStats, $suiviPatients, $suiviMedecins, $suiviConsultations;

        switch ($page) {
            case 'dashboard':
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tachometer-alt me-2" style="color: var(--medical-blue);"></i>Dashboard</h2>
                    <button class="btn btn-outline-medical" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </button>
                </div>
                
                <!-- Cartes Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-2">Total Rendez-vous</h6>
                                    <h2 class="mb-0" id="statTotalRdv">0</h2>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-success" id="statRdvConfirmes">0 confirmés</span>
                                <span class="badge bg-warning" id="statRdvAttente">0 en attente</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-2">Dossiers médicaux</h6>
                                    <h2 class="mb-0" id="statTotalDossiers">0</h2>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-2">Patients actifs</h6>
                                    <h2 class="mb-0" id="statTotalPatients">0</h2>
                                </div>
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted mb-2">Médecins</h6>
                                    <h2 class="mb-0" id="statTotalMedecins">0</h2>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-user-md"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Graphique -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="stat-card">
                            <h5 class="mb-3">Évolution des rendez-vous</h5>
                            <canvas id="statsChart" height="300"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h5 class="mb-3">Répartition par type</h5>
                            <canvas id="typeChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Derniers rendez-vous -->
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Derniers rendez-vous</h5>
                        <a href="?page=rendezvous" class="btn btn-sm btn-outline-medical">Voir tout</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="lastRdvTable">
                            <thead>
                                <tr><th>Patient</th><th>Médecin</th><th>Date</th><th>Heure</th><th>Statut</th><th>Action</th></tr>
                            </thead>
                            <tbody id="lastRdvList"></tbody>
                        </table>
                    </div>
                </div>
                
                <script>
                // Charger les statistiques
                fetch('index.php?page=statistiques&action=getStats')
                    .then(res => res.json())
                    .then(data => {
                        if (data.rdv) {
                            document.getElementById('statTotalRdv').textContent = data.rdv.total || 0;
                            document.getElementById('statRdvConfirmes').textContent = (data.rdv.confirmes || 0) + ' confirmés';
                            document.getElementById('statRdvAttente').textContent = (data.rdv.attente || 0) + ' en attente';
                        }
                        if (data.dossiers) {
                            document.getElementById('statTotalDossiers').textContent = data.dossiers.total || 0;
                        }
                        if (data.patients) {
                            document.getElementById('statTotalPatients').textContent = data.patients.total || 0;
                        }
                        if (data.medecins) {
                            document.getElementById('statTotalMedecins').textContent = data.medecins.total || 0;
                        }
                        
                        // Graphique évolution
                        if (data.rdv && data.rdv.by_month && typeof Chart !== 'undefined') {
                            const ctx = document.getElementById('statsChart');
                            if (ctx) {
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: data.rdv.by_month.map(m => m.mois),
                                        datasets: [{
                                            label: 'Rendez-vous',
                                            data: data.rdv.by_month.map(m => m.count),
                                            borderColor: '#2b7be4',
                                            backgroundColor: 'rgba(43,123,228,0.1)',
                                            tension: 0.4,
                                            fill: true
                                        }]
                                    },
                                    options: { responsive: true, maintainAspectRatio: true }
                                });
                            }
                        }
                        
                        // Graphique type
                        if (data.rdv && data.rdv.by_type && typeof Chart !== 'undefined') {
                            const typeCtx = document.getElementById('typeChart');
                            if (typeCtx) {
                                new Chart(typeCtx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: data.rdv.by_type.map(t => t.type_consultation === 'video' ? 'Téléconsultation' : 'Présentiel'),
                                        datasets: [{
                                            data: data.rdv.by_type.map(t => t.count),
                                            backgroundColor: ['#2b7be4', '#2ecc71']
                                        }]
                                    },
                                    options: { responsive: true, maintainAspectRatio: true }
                                });
                            }
                        }
                    });
                
                // Charger les derniers rendez-vous
                fetch('index.php?page=rendezvous&action=getLast&limit=5')
                    .then(res => res.json())
                    .then(data => {
                        const tbody = document.getElementById('lastRdvList');
                        if (tbody && data.length) {
                            tbody.innerHTML = data.map(rdv => `
                                <tr>
                                    <td>${rdv.patient_nom || '-'}</td>
                                    <td>Dr. ${rdv.medecin_nom || '-'}</td>
                                    <td>${rdv.date_rdv || '-'}</td>
                                    <td>${rdv.heure_rdv || '-'}</td>
                                    <td><span class="badge status-badge-${rdv.statut}">${rdv.statut}</span></td>
                                    <td><a href="?page=rendezvous&edit=${rdv.id_rdv}" class="btn btn-sm btn-primary">Voir</a></td>
                                </tr>
                            `).join('');
                        }
                    });
                </script>
                </div>
                <?php
                break;
                
            case 'rendezvous':
                global $rendez_vous, $stats;
                $rdvTotal = (int)($stats['total'] ?? 0);
                $statusCounts = [
                    'en_attente' => 0,
                    'confirme' => 0,
                    'termine' => 0,
                    'annule' => 0
                ];
                foreach (($stats['by_status'] ?? []) as $row) {
                    $statut = $row['statut'] ?? '';
                    if (array_key_exists($statut, $statusCounts)) {
                        $statusCounts[$statut] = (int)($row['count'] ?? 0);
                    }
                }
                $confirmPercent = $rdvTotal > 0 ? round(($statusCounts['confirme'] / $rdvTotal) * 100) : 0;
                $pendingPercent = $rdvTotal > 0 ? round(($statusCounts['en_attente'] / $rdvTotal) * 100) : 0;
                ?>
                <style>
                .rdv-stats-strip {
                    display: grid;
                    grid-template-columns: minmax(260px, 320px) 1fr;
                    gap: 20px;
                    margin-bottom: 24px;
                }

                .rdv-circle-card {
                    position: relative;
                    overflow: hidden;
                    border-radius: 28px;
                    padding: 24px;
                    min-height: 240px;
                    color: #fff;
                    background:
                        radial-gradient(circle at 20% 20%, rgba(255,255,255,0.22), transparent 28%),
                        linear-gradient(135deg, #0f5cc0 0%, #2b7be4 50%, #2ecc71 100%);
                    box-shadow: 0 20px 45px rgba(22, 97, 191, 0.22);
                }

                .rdv-circle-card::after {
                    content: '';
                    position: absolute;
                    inset: auto -50px -50px auto;
                    width: 180px;
                    height: 180px;
                    border-radius: 50%;
                    background: rgba(255,255,255,0.08);
                }

                .rdv-circle-label {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 7px 14px;
                    border-radius: 999px;
                    background: rgba(255,255,255,0.16);
                    font-size: 0.85rem;
                    font-weight: 600;
                }

                .rdv-progress-wrap {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 18px 0 14px;
                }

                .rdv-progress-circle {
                    --size: 142px;
                    --thickness: 14px;
                    width: var(--size);
                    height: var(--size);
                    border-radius: 50%;
                    display: grid;
                    place-items: center;
                    background:
                        radial-gradient(closest-side, rgba(16,50,92,0.92) calc(100% - var(--thickness)), transparent calc(100% - var(--thickness) + 1px)),
                        conic-gradient(#ffffff 0 calc(var(--value) * 1%), rgba(255,255,255,0.18) 0 100%);
                    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
                }

                .rdv-progress-inner {
                    text-align: center;
                }

                .rdv-progress-inner strong {
                    display: block;
                    font-size: 2rem;
                    line-height: 1;
                    font-weight: 800;
                    color: #fff;
                }

                .rdv-progress-inner span {
                    font-size: 0.82rem;
                    color: rgba(255,255,255,0.78);
                }

                .rdv-circle-footer {
                    display: flex;
                    justify-content: space-between;
                    gap: 12px;
                    font-size: 0.92rem;
                    color: rgba(255,255,255,0.88);
                }

                .rdv-circle-footer strong {
                    display: block;
                    color: #fff;
                    font-size: 1.05rem;
                }

                .rdv-summary-card {
                    background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
                    border: 1px solid rgba(43, 123, 228, 0.08);
                    border-radius: 28px;
                    padding: 24px;
                    box-shadow: 0 14px 35px rgba(24, 52, 84, 0.08);
                }

                .rdv-summary-grid {
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 16px;
                    margin-top: 18px;
                }

                .rdv-summary-item {
                    border-radius: 20px;
                    padding: 18px;
                    background: #f4f8fd;
                }

                .rdv-summary-item strong {
                    display: block;
                    font-size: 1.6rem;
                    color: #163a5c;
                    line-height: 1.1;
                }

                .rdv-summary-item span {
                    color: #657b91;
                    font-size: 0.9rem;
                }

                .rdv-summary-item small {
                    display: inline-block;
                    margin-top: 8px;
                    color: #2b7be4;
                    font-weight: 600;
                }

                @media (max-width: 992px) {
                    .rdv-stats-strip {
                        grid-template-columns: 1fr;
                    }
                }

                @media (max-width: 768px) {
                    .rdv-summary-grid {
                        grid-template-columns: 1fr;
                    }
                }
                </style>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-calendar-alt me-2" style="color: var(--medical-blue);"></i>Gestion des rendez-vous</h2>
                    <div class="d-flex gap-2">
                        <a class="btn btn-success" href="?page=rendezvous&action=exportCSV">
                            <i class="fas fa-file-excel me-2"></i>Export CSV
                        </a>
                        <a class="btn btn-danger" href="?page=rendezvous&action=exportPDF">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </a>
                        <button class="btn btn-medical" data-bs-toggle="modal" data-bs-target="#rdvModal" onclick="openRdvModal()">
                            <i class="fas fa-plus me-2"></i>Nouveau rendez-vous
                        </button>
                    </div>
                </div>

                <div class="rdv-stats-strip">
                    <button type="button" class="rdv-circle-card border-0 text-start">
                        <span class="rdv-circle-label">
                            <i class="fas fa-chart-donut"></i>
                            Statistique rapide
                        </span>
                        <div class="rdv-progress-wrap">
                            <div class="rdv-progress-circle" style="--value: <?php echo $confirmPercent; ?>;">
                                <div class="rdv-progress-inner">
                                    <strong><?php echo $confirmPercent; ?>%</strong>
                                    <span>confirmes</span>
                                </div>
                            </div>
                        </div>
                        <div class="rdv-circle-footer">
                            <div>
                                <strong><?php echo $statusCounts['confirme']; ?></strong>
                                RDV valides
                            </div>
                            <div class="text-end">
                                <strong><?php echo $rdvTotal; ?></strong>
                                Total RDV
                            </div>
                        </div>
                    </button>

                    <div class="rdv-summary-card">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <h4 class="mb-1" style="color:#163a5c;">Vue statistique des rendez-vous</h4>
                                <p class="mb-0" style="color:#6d8298;">Un aperçu immédiat des rendez-vous confirmés, en attente et terminés.</p>
                            </div>
                            <a href="?page=statistiques" class="btn btn-outline-medical">
                                <i class="fas fa-chart-pie me-2"></i>Voir statistiques
                            </a>
                        </div>
                        <div class="rdv-summary-grid">
                            <div class="rdv-summary-item">
                                <strong><?php echo $statusCounts['en_attente']; ?></strong>
                                <span>En attente</span>
                                <small><?php echo $pendingPercent; ?>% du total</small>
                            </div>
                            <div class="rdv-summary-item">
                                <strong><?php echo $statusCounts['termine']; ?></strong>
                                <span>Consultations terminees</span>
                                <small>Suivi des rendez-vous finalises</small>
                            </div>
                            <div class="rdv-summary-item">
                                <strong><?php echo $statusCounts['annule']; ?></strong>
                                <span>Annulations</span>
                                <small>Controle de la charge planifiee</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtres -->
                <div class="stat-card mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" id="searchRdv" class="form-control" placeholder="Rechercher...">
                        </div>
                        <div class="col-md-2">
                            <select id="filterStatus" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="en_attente">En attente</option>
                                <option value="confirme">Confirmé</option>
                                <option value="termine">Terminé</option>
                                <option value="annule">Annulé</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="filterDate" class="form-control" placeholder="Date">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary" onclick="filterRdv()"><i class="fas fa-search"></i> Filtrer</button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-secondary" onclick="resetFilters()"><i class="fas fa-undo"></i> Réinitialiser</button>
                        </div>
                    </div>
                </div>
                
                <!-- Tableau des rendez-vous -->
                <div class="stat-card">
                    <div class="table-responsive">
                        <table class="table table-hover datatable" id="rdvTable">
                            <thead>
                                <tr><th>ID</th><th>Patient</th><th>Médecin</th><th>Date</th><th>Heure</th><th>Type</th><th>Statut</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if (isset($rendez_vous) && $rendez_vous): ?>
                                    <?php foreach ($rendez_vous as $rdv): ?>
                                        <tr>
                                            <td><?php echo $rdv['id_rdv']; ?></td>
                                            <td><?php echo htmlspecialchars($rdv['patient_nom'] ?? '-'); ?></td>
                                            <td>Dr. <?php echo htmlspecialchars($rdv['medecin_nom'] ?? '-'); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?></td>
                                            <td><?php echo $rdv['heure_rdv']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $rdv['type_consultation'] == 'video' ? 'bg-info' : 'bg-secondary'; ?>">
                                                    <?php echo $rdv['type_consultation'] == 'video' ? 'Visio' : 'Présentiel'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge status-badge-<?php echo $rdv['statut']; ?>">
                                                    <?php echo $rdv['statut']; ?>
                                                </span>
                                            </td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-primary" onclick="editRdv(<?php echo $rdv['id_rdv']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteRdv(<?php echo $rdv['id_rdv']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php if ($rdv['type_consultation'] == 'video' && $rdv['lien_visio']): ?>
                                                    <button class="btn btn-sm btn-success" onclick="sendVideoLink(<?php echo $rdv['id_rdv']; ?>)">
                                                        <i class="fas fa-video"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center">Aucun rendez-vous trouvé</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php include __DIR__ . '/modals/rdv_modal.php'; ?>
                
                <script>
                function openRdvModal(id = null) {
                    if (id) {
                        document.getElementById('rdvModalTitle').textContent = 'Modifier le rendez-vous';
                        document.getElementById('rdvAction').value = 'update';
                        document.getElementById('rdvId').value = id;
                        // Charger les données
                        fetch(`index.php?page=rendezvous&action=getOne&id=${id}`)
                            .then(res => res.json())
                            .then(data => {
                                document.getElementById('rdvPatient').value = data.id_patient;
                                document.getElementById('rdvMedecin').value = data.id_medecin;
                                document.getElementById('rdvDate').value = data.date_rdv;
                                document.getElementById('rdvHeure').value = data.heure_rdv;
                                document.getElementById('rdvType').value = data.type_consultation;
                                document.getElementById('rdvStatut').value = data.statut;
                                document.getElementById('rdvMotif').value = data.motif || '';
                                document.getElementById('rdvSymptomes').value = data.symptomes || '';
                            });
                    } else {
                        document.getElementById('rdvModalTitle').textContent = 'Nouveau rendez-vous';
                        document.getElementById('rdvAction').value = 'create';
                        document.getElementById('rdvId').value = '';
                        document.getElementById('rdvForm').reset();
                    }
                    new bootstrap.Modal(document.getElementById('rdvModal')).show();
                }
                
                function editRdv(id) { openRdvModal(id); }
                
                function deleteRdv(id) {
                    if (confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?')) {
                        window.location.href = `index.php?page=rendezvous&action=delete&id=${id}`;
                    }
                }
                
                function filterRdv() {
                    const search = document.getElementById('searchRdv').value.toLowerCase();
                    const status = document.getElementById('filterStatus').value;
                    const date = document.getElementById('filterDate').value;
                    const rows = document.querySelectorAll('#rdvTable tbody tr');
                    
                    rows.forEach(row => {
                        let show = true;
                        const text = row.textContent.toLowerCase();
                        const rowStatus = row.cells[6]?.textContent.trim();
                        const rowDate = row.cells[3]?.textContent.split('/').reverse().join('-');
                        
                        if (search && !text.includes(search)) show = false;
                        if (status && rowStatus !== status) show = false;
                        if (date && rowDate !== date) show = false;
                        
                        row.style.display = show ? '' : 'none';
                    });
                }
                
                function resetFilters() {
                    document.getElementById('searchRdv').value = '';
                    document.getElementById('filterStatus').value = '';
                    document.getElementById('filterDate').value = '';
                    document.querySelectorAll('#rdvTable tbody tr').forEach(row => row.style.display = '');
                }
                </script>
                <?php
                break;

            case 'consultations':
                $consultations = $consultations ?? [];
                $consultationStats = $consultationStats ?? ['total' => count($consultations)];
                $consultationRendezVous = $consultationRendezVous ?? [];
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-stethoscope me-2" style="color: var(--medical-blue);"></i>Gestion des consultations</h2>
                    <button class="btn btn-medical" onclick="openConsultationModal()">
                        <i class="fas fa-plus me-2"></i>Nouvelle consultation
                    </button>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Total consultations</h6>
                            <h2 class="mb-0" id="consultationTotal"><?php echo (int)($consultationStats['total'] ?? count($consultations)); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Rendez-vous disponibles</h6>
                            <h2 class="mb-0"><?php echo count($consultationRendezVous); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Dernière mise à jour</h6>
                            <h2 class="mb-0" style="font-size:1.4rem;"><?php echo date('d/m/Y'); ?></h2>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <input type="text" id="consultationSearch" class="form-control" placeholder="Rechercher par patient, médecin, diagnostic ou traitement...">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-medical w-100" onclick="resetConsultationSearch()">
                                <i class="fas fa-rotate-left me-2"></i>Réinitialiser
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="consultationsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Médecin</th>
                                    <th>Date RDV</th>
                                    <th>Diagnostic</th>
                                    <th>Traitement</th>
                                    <th>Créée le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="consultationsTableBody">
                                <?php if ($consultations): ?>
                                    <?php foreach ($consultations as $consultation): ?>
                                        <tr>
                                            <td>#<?php echo (int)($consultation['id_consultation'] ?? 0); ?></td>
                                            <td><?php echo htmlspecialchars(trim(($consultation['patient_nom'] ?? '') . ' ' . ($consultation['patient_prenom'] ?? '')) ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars(trim(($consultation['medecin_nom'] ?? '') . ' ' . ($consultation['medecin_prenom'] ?? '')) ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($consultation['date_rdv'] ?? '-'); ?></td>
                                            <td><?php $diagnosticPreview = (string)($consultation['diagnostic'] ?? '-'); echo htmlspecialchars(strlen($diagnosticPreview) > 48 ? substr($diagnosticPreview, 0, 48) . '...' : $diagnosticPreview); ?></td>
                                            <td><?php $traitementPreview = (string)($consultation['traitement'] ?? '-'); echo htmlspecialchars(strlen($traitementPreview) > 48 ? substr($traitementPreview, 0, 48) . '...' : $traitementPreview); ?></td>
                                            <td><?php echo htmlspecialchars($consultation['date_creation'] ?? '-'); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-warning" onclick="editConsultation(<?php echo (int)($consultation['id_consultation'] ?? 0); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteConsultation(<?php echo (int)($consultation['id_consultation'] ?? 0); ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center">Aucune consultation trouvée</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal fade" id="consultationModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="consultationModalTitle">Nouvelle consultation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="consultationForm">
                                    <input type="hidden" id="consultationId">
                                    <div class="mb-3">
                                        <label>Rendez-vous associé <span class="text-danger">*</span></label>
                                        <select class="form-select" id="consultationRdvId" required>
                                            <option value="">Sélectionner un rendez-vous</option>
                                            <?php foreach ($consultationRendezVous as $rdv): ?>
                                                <option value="<?php echo (int)$rdv['id_rdv']; ?>">
                                                    #<?php echo (int)$rdv['id_rdv']; ?> - <?php echo htmlspecialchars(trim(($rdv['patient_nom'] ?? '') . ' ' . ($rdv['patient_prenom'] ?? ''))); ?> - <?php echo htmlspecialchars($rdv['date_rdv'] ?? ''); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label>Diagnostic <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="consultationDiagnostic" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label>Traitement <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="consultationTraitement" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-0">
                                        <label>Notes complémentaires</label>
                                        <textarea class="form-control" id="consultationNotes" rows="2"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-medical" onclick="submitConsultation()">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function openConsultationModal() {
                    document.getElementById('consultationForm').reset();
                    document.getElementById('consultationId').value = '';
                    document.getElementById('consultationModalTitle').textContent = 'Nouvelle consultation';
                    new bootstrap.Modal(document.getElementById('consultationModal')).show();
                }

                async function editConsultation(id) {
                    const response = await fetch(`index.php?page=consultations&action=get&id=${id}`);
                    const data = await response.json();
                    document.getElementById('consultationId').value = data.id_consultation || '';
                    document.getElementById('consultationRdvId').value = data.id_rdv || '';
                    document.getElementById('consultationDiagnostic').value = data.diagnostic || '';
                    document.getElementById('consultationTraitement').value = data.traitement || '';
                    document.getElementById('consultationNotes').value = data.notes || '';
                    document.getElementById('consultationModalTitle').textContent = 'Modifier consultation';
                    new bootstrap.Modal(document.getElementById('consultationModal')).show();
                }

                async function submitConsultation() {
                    const id = document.getElementById('consultationId').value;
                    const payload = {
                        id_consultation: id,
                        id_rdv: document.getElementById('consultationRdvId').value,
                        diagnostic: document.getElementById('consultationDiagnostic').value.trim(),
                        traitement: document.getElementById('consultationTraitement').value.trim(),
                        notes: document.getElementById('consultationNotes').value.trim()
                    };
                    if (!payload.id_rdv || !payload.diagnostic || !payload.traitement) {
                        alert('Veuillez remplir les champs obligatoires.');
                        return;
                    }
                    const response = await fetch(`index.php?page=consultations&action=${id ? 'update' : 'create'}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        alert(result.message || 'Erreur lors de l enregistrement');
                        return;
                    }
                    location.reload();
                }

                async function deleteConsultation(id) {
                    if (!confirm('Supprimer cette consultation ?')) return;
                    const response = await fetch(`index.php?page=consultations&action=delete&id=${id}`, { method: 'DELETE' });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        alert(result.message || 'Erreur lors de la suppression');
                        return;
                    }
                    location.reload();
                }

                function filterConsultationsTable() {
                    const search = (document.getElementById('consultationSearch')?.value || '').toLowerCase();
                    document.querySelectorAll('#consultationsTable tbody tr').forEach(row => {
                        row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
                    });
                }

                function resetConsultationSearch() {
                    document.getElementById('consultationSearch').value = '';
                    filterConsultationsTable();
                }

                document.getElementById('consultationSearch')?.addEventListener('input', filterConsultationsTable);
                </script>
                <?php
                break;

            case 'suivis':
                $suivis = $suivis ?? [];
                $suiviStats = $suiviStats ?? ['total' => count($suivis), 'moyenne_poids' => 0];
                $suiviPatients = $suiviPatients ?? [];
                $suiviMedecins = $suiviMedecins ?? [];
                $suiviConsultations = $suiviConsultations ?? [];
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-heart-pulse me-2" style="color: var(--medical-blue);"></i>Suivis patients</h2>
                    <button class="btn btn-medical" onclick="openSuiviModal()">
                        <i class="fas fa-plus me-2"></i>Nouveau suivi
                    </button>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Total suivis</h6>
                            <h2 class="mb-0"><?php echo (int)($suiviStats['total'] ?? count($suivis)); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Poids moyen</h6>
                            <h2 class="mb-0"><?php echo htmlspecialchars((string)($suiviStats['moyenne_poids'] ?? 0)); ?> kg</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Patients suivis</h6>
                            <h2 class="mb-0"><?php echo count($suiviPatients); ?></h2>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <input type="text" id="suiviSearch" class="form-control" placeholder="Rechercher par patient, médecin, état général ou tension...">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-medical w-100" onclick="resetSuiviSearch()">
                                <i class="fas fa-rotate-left me-2"></i>Réinitialiser
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="suivisTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Médecin</th>
                                    <th>Date suivi</th>
                                    <th>Poids</th>
                                    <th>Tension</th>
                                    <th>État général</th>
                                    <th>Prochain RDV</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($suivis): ?>
                                    <?php foreach ($suivis as $suivi): ?>
                                        <tr>
                                            <td>#<?php echo (int)($suivi['id_suivie'] ?? 0); ?></td>
                                            <td><?php echo htmlspecialchars(trim(($suivi['patient_nom'] ?? '') . ' ' . ($suivi['patient_prenom'] ?? '')) ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars(trim(($suivi['medecin_nom'] ?? '') . ' ' . ($suivi['medecin_prenom'] ?? '')) ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($suivi['date_suivi'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($suivi['poids'] ? $suivi['poids'] . ' kg' : '-'); ?></td>
                                            <td><?php echo htmlspecialchars($suivi['tension'] ?? '-'); ?></td>
                                            <td><?php $etatPreview = (string)($suivi['etat_general'] ?? '-'); echo htmlspecialchars(strlen($etatPreview) > 48 ? substr($etatPreview, 0, 48) . '...' : $etatPreview); ?></td>
                                            <td><?php echo htmlspecialchars($suivi['prochain_rdv'] ?? '-'); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-warning" onclick="editSuivi(<?php echo (int)($suivi['id_suivie'] ?? 0); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteSuivi(<?php echo (int)($suivi['id_suivie'] ?? 0); ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center">Aucun suivi trouvé</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal fade" id="suiviModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="suiviModalTitle">Nouveau suivi</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="suiviForm">
                                    <input type="hidden" id="suiviId">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label>Patient <span class="text-danger">*</span></label>
                                            <select class="form-select" id="suiviPatientId" required>
                                                <option value="">Sélectionner un patient</option>
                                                <?php foreach ($suiviPatients as $patient): ?>
                                                    <option value="<?php echo (int)$patient['id_patient']; ?>"><?php echo htmlspecialchars(trim(($patient['nom'] ?? '') . ' ' . ($patient['prenom'] ?? ''))); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Médecin <span class="text-danger">*</span></label>
                                            <select class="form-select" id="suiviMedecinId" required>
                                                <option value="">Sélectionner un médecin</option>
                                                <?php foreach ($suiviMedecins as $medecin): ?>
                                                    <option value="<?php echo (int)$medecin['id_medecin']; ?>"><?php echo htmlspecialchars(trim(($medecin['nom'] ?? '') . ' ' . ($medecin['prenom'] ?? '') . ' - ' . ($medecin['specialite'] ?? ''))); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Consultation associée</label>
                                            <select class="form-select" id="suiviConsultationId">
                                                <option value="">Aucune consultation associée</option>
                                                <?php foreach ($suiviConsultations as $consultation): ?>
                                                    <option value="<?php echo (int)$consultation['id_consultation']; ?>">#<?php echo (int)$consultation['id_consultation']; ?> - <?php echo htmlspecialchars($consultation['diagnostic'] ?? 'Consultation'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Date du suivi <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="suiviDate" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Poids (kg)</label>
                                            <input type="number" step="0.1" class="form-control" id="suiviPoids">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Tension</label>
                                            <input type="text" class="form-control" id="suiviTension" placeholder="Ex : 12/8">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Prochain RDV</label>
                                            <input type="date" class="form-control" id="suiviProchainRdv">
                                        </div>
                                        <div class="col-12">
                                            <label>État général <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="suiviEtatGeneral" rows="2" required></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Analyses à réaliser</label>
                                            <textarea class="form-control" id="suiviAnalyses" rows="2"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Régime alimentaire</label>
                                            <textarea class="form-control" id="suiviRegime" rows="2"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label>Activité physique</label>
                                            <textarea class="form-control" id="suiviActivite" rows="2"></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-medical" onclick="submitSuivi()">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function openSuiviModal() {
                    document.getElementById('suiviForm').reset();
                    document.getElementById('suiviId').value = '';
                    document.getElementById('suiviDate').value = new Date().toISOString().slice(0, 10);
                    document.getElementById('suiviModalTitle').textContent = 'Nouveau suivi';
                    new bootstrap.Modal(document.getElementById('suiviModal')).show();
                }

                async function editSuivi(id) {
                    const response = await fetch(`index.php?page=suivis&action=get&id=${id}`);
                    const data = await response.json();
                    document.getElementById('suiviId').value = data.id_suivie || '';
                    document.getElementById('suiviPatientId').value = data.id_patient || '';
                    document.getElementById('suiviMedecinId').value = data.id_medecin || '';
                    document.getElementById('suiviConsultationId').value = data.id_consultation || '';
                    document.getElementById('suiviDate').value = data.date_suivi || '';
                    document.getElementById('suiviPoids').value = data.poids || '';
                    document.getElementById('suiviTension').value = data.tension || '';
                    document.getElementById('suiviProchainRdv').value = data.prochain_rdv || '';
                    document.getElementById('suiviEtatGeneral').value = data.etat_general || '';
                    document.getElementById('suiviAnalyses').value = data.analyses_a_realiser || '';
                    document.getElementById('suiviRegime').value = data.regime_alimentaire || '';
                    document.getElementById('suiviActivite').value = data.activite_physique || '';
                    document.getElementById('suiviModalTitle').textContent = 'Modifier suivi';
                    new bootstrap.Modal(document.getElementById('suiviModal')).show();
                }

                async function submitSuivi() {
                    const id = document.getElementById('suiviId').value;
                    const payload = {
                        id_suivie: id,
                        id_patient: document.getElementById('suiviPatientId').value,
                        id_medecin: document.getElementById('suiviMedecinId').value,
                        id_consultation: document.getElementById('suiviConsultationId').value || null,
                        date_suivi: document.getElementById('suiviDate').value,
                        poids: document.getElementById('suiviPoids').value || null,
                        tension: document.getElementById('suiviTension').value.trim(),
                        prochain_rdv: document.getElementById('suiviProchainRdv').value || null,
                        etat_general: document.getElementById('suiviEtatGeneral').value.trim(),
                        analyses_a_realiser: document.getElementById('suiviAnalyses').value.trim(),
                        regime_alimentaire: document.getElementById('suiviRegime').value.trim(),
                        activite_physique: document.getElementById('suiviActivite').value.trim()
                    };
                    if (!payload.id_patient || !payload.id_medecin || !payload.date_suivi || !payload.etat_general) {
                        alert('Veuillez remplir les champs obligatoires.');
                        return;
                    }
                    const response = await fetch(`index.php?page=suivis&action=${id ? 'update' : 'create'}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        alert(result.message || 'Erreur lors de l enregistrement');
                        return;
                    }
                    location.reload();
                }

                async function deleteSuivi(id) {
                    if (!confirm('Supprimer ce suivi ?')) return;
                    const response = await fetch(`index.php?page=suivis&action=delete&id=${id}`, { method: 'DELETE' });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        alert(result.message || 'Erreur lors de la suppression');
                        return;
                    }
                    location.reload();
                }

                function filterSuivisTable() {
                    const search = (document.getElementById('suiviSearch')?.value || '').toLowerCase();
                    document.querySelectorAll('#suivisTable tbody tr').forEach(row => {
                        row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
                    });
                }

                function resetSuiviSearch() {
                    document.getElementById('suiviSearch').value = '';
                    filterSuivisTable();
                }

                document.getElementById('suiviSearch')?.addEventListener('input', filterSuivisTable);
                </script>
                <?php
                break;

            case 'users':
            case 'patients':
            case 'medecins':
                $userPage = $page;
                $userList = $users ?? [];
                if ($userPage === 'patients') {
                    $userList = array_values(array_filter($userList, function ($user) {
                        return ($user['type_role'] ?? '') === 'patient';
                    }));
                } elseif ($userPage === 'medecins') {
                    $userList = array_values(array_filter($userList, function ($user) {
                        return ($user['type_role'] ?? '') === 'medecin';
                    }));
                }
                $pageTitles = [
                    'users' => 'Gestion des utilisateurs',
                    'patients' => 'Gestion des patients',
                    'medecins' => 'Gestion des medecins'
                ];
                $buttonLabels = [
                    'users' => 'Nouvel utilisateur',
                    'patients' => 'Nouveau patient',
                    'medecins' => 'Nouveau medecin'
                ];
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-cog me-2" style="color: var(--medical-blue);"></i><?php echo $pageTitles[$userPage] ?? 'Utilisateurs'; ?></h2>
                    <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-success" href="?page=<?php echo urlencode($userPage); ?>&action=exportCSV">
                            <i class="fas fa-file-csv me-2"></i>CSV
                        </a>
                        <a class="btn btn-danger" href="?page=<?php echo urlencode($userPage); ?>&action=exportPDF" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </a>
                        <button class="btn btn-medical" onclick="openUserModal()">
                            <i class="fas fa-plus me-2"></i><?php echo $buttonLabels[$userPage] ?? 'Nouvel utilisateur'; ?>
                        </button>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Total</h6>
                            <h2 class="mb-0"><?php echo (int)($userStats['total'] ?? 0); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Patients</h6>
                            <h2 class="mb-0"><?php echo (int)($userStats['patients'] ?? 0); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Medecins</h6>
                            <h2 class="mb-0"><?php echo (int)($userStats['medecins'] ?? 0); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6 class="text-muted mb-2">Admins</h6>
                            <h2 class="mb-0"><?php echo (int)($userStats['admins'] ?? 0); ?></h2>
                        </div>
                    </div>
                </div>

                <div class="advanced-search-card">
                    <div class="advanced-search-title">
                        <i class="fas fa-search" style="color: var(--medical-blue);"></i>
                        Recherche avancée &amp; Tri dynamique
                    </div>
                    <form id="advancedUserSearchForm" onsubmit="applyUserSearchAndSort(); return false;">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="filterNom">Nom</label>
                                <input type="text" id="filterNom" class="form-control" placeholder="Ex : Dupont">
                            </div>
                            <div class="col-md-2">
                                <label for="filterPrenom">Prénom</label>
                                <input type="text" id="filterPrenom" class="form-control" placeholder="Ex : Marie">
                            </div>
                            <div class="col-md-3">
                                <label for="filterEmail">Email</label>
                                <input type="text" id="filterEmail" class="form-control" placeholder="Ex : marie@email.com">
                            </div>
                            <div class="col-md-2">
                                <label for="filterSexe">Sexe</label>
                                <select id="filterSexe" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="Homme">Homme</option>
                                    <option value="Femme">Femme</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filterRole">Rôle</label>
                                <select id="filterRole" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="patient">Patient</option>
                                    <option value="medecin">Médecin</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label for="filterCasSocial">Cas social</label>
                                <input type="text" id="filterCasSocial" class="form-control" placeholder="CNSS">
                            </div>
                            <div class="col-md-2">
                                <label for="sortField">Trier par</label>
                                <select id="sortField" class="form-select">
                                    <option value="id_user">ID</option>
                                    <option value="nom">Nom</option>
                                    <option value="prenom">Prénom</option>
                                    <option value="email">Email</option>
                                    <option value="age">Âge</option>
                                    <option value="poids">Poids</option>
                                    <option value="taille">Taille</option>
                                    <option value="date_naissance">Date naissance</option>
                                    <option value="role">Rôle</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="sortDir">Ordre</label>
                                <select id="sortDir" class="form-select">
                                    <option value="DESC">↓ Décroissant</option>
                                    <option value="ASC">↑ Croissant</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end gap-2 flex-wrap">
                                <button type="submit" class="btn btn-medical">
                                    <i class="fas fa-search me-2"></i>Rechercher
                                </button>
                                <button type="button" class="btn btn-outline-medical" onclick="resetUserSearchAndSort()">
                                    <i class="fas fa-times me-2"></i>Réinitialiser
                                </button>
                                <span id="searchResultCount" class="search-result-count"></span>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2" style="color: var(--medical-blue);"></i>
                            Liste des utilisateurs <span id="tableCount" class="text-muted fs-6">(<?php echo count($userList); ?>)</span>
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th class="sortable-user-header" data-sort="id_user">ID <span class="sort-indicator">⇅</span></th>
                                    <th class="sortable-user-header" data-sort="nom">Nom complet <span class="sort-indicator">⇅</span></th>
                                    <th class="sortable-user-header" data-sort="email">Email <span class="sort-indicator">⇅</span></th>
                                    <th class="sortable-user-header" data-sort="role">Rôle <span class="sort-indicator">⇅</span></th>
                                    <th>Spécialité</th>
                                    <th>Sexe</th>
                                    <th>Cas social</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <?php if ($userList): ?>
                                    <?php foreach ($userList as $user): ?>
                                        <tr data-role="<?php echo htmlspecialchars($user['type_role'] ?? ''); ?>" data-sexe="<?php echo htmlspecialchars($user['sexe'] ?? ''); ?>">
                                            <td><?php echo (int)($user['id_user'] ?? 0); ?></td>
                                            <td><?php echo htmlspecialchars(trim(($user['nom'] ?? '') . ' ' . ($user['prenom'] ?? ''))); ?></td>
                                            <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($user['type_role'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($user['specialite'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($user['sexe'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($user['cas_social'] ?? '-'); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-warning" onclick='openUserModal(<?php echo json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo (int)($user['id_user'] ?? 0); ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center">Aucun utilisateur trouvé</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal fade" id="userModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="userModalTitle">Nouvel utilisateur</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="userForm">
                                    <input type="hidden" id="userId">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label>Nom</label>
                                            <input type="text" class="form-control" id="userNom" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Prenom</label>
                                            <input type="text" class="form-control" id="userPrenom" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Age</label>
                                            <input type="number" class="form-control" id="userAge" min="0" max="130" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Sexe</label>
                                            <select class="form-select" id="userSexe" required>
                                                <option value="">Selectionnez</option>
                                                <option value="Homme">Homme</option>
                                                <option value="Femme">Femme</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Date naissance</label>
                                            <input type="date" class="form-control" id="userDateNaissance" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Poids</label>
                                            <input type="number" class="form-control" id="userPoids" min="0" step="0.1" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Taille</label>
                                            <input type="number" class="form-control" id="userTaille" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Email</label>
                                            <input type="email" class="form-control" id="userEmail" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Mot de passe</label>
                                            <input type="password" class="form-control" id="userPassword" placeholder="Laisser vide en modification">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Cas social</label>
                                            <input type="text" class="form-control" id="userCasSocial">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Role</label>
                                            <select class="form-select" id="userRole" onchange="toggleUserSpecialiteField()" required>
                                                <option value="patient">Patient</option>
                                                <option value="medecin">Medecin</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </div>
                                        <div class="col-12" id="userSpecialiteWrap" style="display:none;">
                                            <label>Specialite</label>
                                            <input type="text" class="form-control" id="userSpecialite">
                                        </div>
                                        <div class="col-12">
                                            <label>Adresse</label>
                                            <textarea class="form-control" id="userAdresse" rows="2" required></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-medical" onclick="submitUserForm()">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                const currentUserManagementPage = <?php echo json_encode($userPage); ?>;
                let activeUserFilters = {};
                let activeUserSortField = 'id_user';
                let activeUserSortDir = 'DESC';
                let managedUsers = <?php echo json_encode(array_values($userList), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

                function escapeHtml(value) {
                    return String(value ?? '').replace(/[&<>"']/g, char => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    }[char]));
                }

                function normalizeManagedUser(user) {
                    return {
                        ...user,
                        type_role: user.type_role || user.role || 'patient'
                    };
                }

                function userFullName(user) {
                    return `${user.nom || ''} ${user.prenom || ''}`.trim() || '-';
                }

                function roleLabel(role) {
                    return { patient: 'Patient', medecin: 'Médecin', admin: 'Admin' }[role] || role || '-';
                }

                function renderUsersTable(users) {
                    managedUsers = (users || []).map(normalizeManagedUser);
                    const tbody = document.getElementById('usersTableBody');
                    if (!tbody) return;

                    if (!managedUsers.length) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Aucun résultat correspondant aux critères</td></tr>';
                    } else {
                        tbody.innerHTML = managedUsers.map(user => {
                            const id = Number(user.id_user || user.id || 0);
                            const role = user.type_role || user.role || '';
                            return `<tr data-role="${escapeHtml(role)}" data-sexe="${escapeHtml(user.sexe || '')}">
                                <td>${id}</td>
                                <td>${escapeHtml(userFullName(user))}</td>
                                <td>${escapeHtml(user.email || '')}</td>
                                <td>${escapeHtml(roleLabel(role))}</td>
                                <td>${escapeHtml(user.specialite || '-')}</td>
                                <td>${escapeHtml(user.sexe || '-')}</td>
                                <td>${escapeHtml(user.cas_social || '-')}</td>
                                <td class="action-buttons">
                                    <button class="btn btn-sm btn-warning" onclick="editUserFromCache(${id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                        }).join('');
                    }

                    document.getElementById('tableCount').textContent = `(${managedUsers.length})`;
                    document.getElementById('searchResultCount').textContent = `${managedUsers.length} résultat(s)`;
                    updateUserSortIndicators();
                }

                function editUserFromCache(id) {
                    const user = managedUsers.find(item => Number(item.id_user || item.id || 0) === Number(id));
                    if (user) openUserModal(normalizeManagedUser(user));
                }

                function collectUserSearchFilters() {
                    const filters = {};
                    const fields = {
                        nom: 'filterNom',
                        prenom: 'filterPrenom',
                        email: 'filterEmail',
                        sexe: 'filterSexe',
                        role: 'filterRole',
                        cas_social: 'filterCasSocial'
                    };
                    Object.entries(fields).forEach(([key, id]) => {
                        const value = document.getElementById(id)?.value.trim();
                        if (value) filters[key] = value;
                    });
                    if (currentUserManagementPage === 'patients') {
                        filters.role = 'patient';
                    } else if (currentUserManagementPage === 'medecins') {
                        filters.role = 'medecin';
                    }
                    return filters;
                }

                async function fetchUserSearchResults(filters, sortField, sortDir) {
                    const response = await fetch('index.php?page=users&action=search', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            filters,
                            sort_field: sortField,
                            sort_dir: sortDir
                        })
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Erreur recherche utilisateurs');
                    }
                    return result.data || [];
                }

                async function applyUserSearchAndSort() {
                    activeUserFilters = collectUserSearchFilters();
                    activeUserSortField = document.getElementById('sortField')?.value || 'id_user';
                    activeUserSortDir = document.getElementById('sortDir')?.value || 'DESC';

                    try {
                        const users = await fetchUserSearchResults(activeUserFilters, activeUserSortField, activeUserSortDir);
                        renderUsersTable(users);
                    } catch (error) {
                        alert(error.message);
                    }
                }

                async function quickUserSort(field) {
                    activeUserSortDir = activeUserSortField === field && activeUserSortDir === 'ASC' ? 'DESC' : 'ASC';
                    activeUserSortField = field;
                    document.getElementById('sortField').value = field;
                    document.getElementById('sortDir').value = activeUserSortDir;
                    try {
                        const users = await fetchUserSearchResults(activeUserFilters, activeUserSortField, activeUserSortDir);
                        renderUsersTable(users);
                    } catch (error) {
                        alert(error.message);
                    }
                }

                function updateUserSortIndicators() {
                    document.querySelectorAll('.sortable-user-header').forEach(th => {
                        const field = th.dataset.sort;
                        const indicator = th.querySelector('.sort-indicator');
                        if (!indicator) return;
                        const active = field === activeUserSortField;
                        indicator.textContent = active ? (activeUserSortDir === 'ASC' ? '▲' : '▼') : '⇅';
                        indicator.classList.toggle('active', active);
                    });
                }

                function resetUserSearchAndSort() {
                    document.getElementById('advancedUserSearchForm')?.reset();
                    activeUserFilters = {};
                    activeUserSortField = 'id_user';
                    activeUserSortDir = 'DESC';
                    document.getElementById('sortField').value = activeUserSortField;
                    document.getElementById('sortDir').value = activeUserSortDir;
                    applyUserSearchAndSort();
                }

                function applyUserPageDefaults(isEditMode = false) {
                    const roleField = document.getElementById('userRole');
                    if (!roleField) return;

                    if (currentUserManagementPage === 'patients') {
                        roleField.value = 'patient';
                        roleField.disabled = true;
                    } else if (currentUserManagementPage === 'medecins') {
                        roleField.value = 'medecin';
                        roleField.disabled = true;
                    } else {
                        roleField.disabled = isEditMode;
                    }

                    toggleUserSpecialiteField();
                }

                function toggleUserSpecialiteField() {
                    const role = document.getElementById('userRole').value;
                    document.getElementById('userSpecialiteWrap').style.display = role === 'medecin' ? '' : 'none';
                }

                function openUserModal(user = null) {
                    document.getElementById('userForm').reset();
                    document.getElementById('userId').value = '';
                    document.getElementById('userModalTitle').textContent = 'Nouvel utilisateur';
                    document.getElementById('userPassword').value = '';
                    applyUserPageDefaults(false);

                    if (user) {
                        document.getElementById('userModalTitle').textContent = 'Modifier utilisateur';
                        document.getElementById('userId').value = user.id_user || '';
                        document.getElementById('userNom').value = user.nom || '';
                        document.getElementById('userPrenom').value = user.prenom || '';
                        document.getElementById('userAge').value = user.age || '';
                        document.getElementById('userSexe').value = user.sexe || '';
                        document.getElementById('userDateNaissance').value = user.date_naissance || '';
                        document.getElementById('userPoids').value = user.poids || '';
                        document.getElementById('userTaille').value = user.taille || '';
                        document.getElementById('userEmail').value = user.email || '';
                        document.getElementById('userCasSocial').value = user.cas_social || '';
                        document.getElementById('userRole').value = user.type_role || 'patient';
                        document.getElementById('userSpecialite').value = user.specialite || '';
                        document.getElementById('userAdresse').value = user.adresse || '';
                        applyUserPageDefaults(true);
                    }

                    toggleUserSpecialiteField();
                    new bootstrap.Modal(document.getElementById('userModal')).show();
                }

                async function submitUserForm() {
                    const id = document.getElementById('userId').value;
                    const payload = {
                        id_user: id ? parseInt(id, 10) : undefined,
                        nom: document.getElementById('userNom').value.trim(),
                        prenom: document.getElementById('userPrenom').value.trim(),
                        age: parseInt(document.getElementById('userAge').value, 10) || 0,
                        sexe: document.getElementById('userSexe').value,
                        date_naissance: document.getElementById('userDateNaissance').value,
                        poids: parseFloat(document.getElementById('userPoids').value) || 0,
                        taille: parseFloat(document.getElementById('userTaille').value) || 0,
                        email: document.getElementById('userEmail').value.trim(),
                        mot_de_passe: document.getElementById('userPassword').value,
                        cas_social: document.getElementById('userCasSocial').value.trim(),
                        role: document.getElementById('userRole').value,
                        specialite: document.getElementById('userSpecialite').value.trim(),
                        adresse: document.getElementById('userAdresse').value.trim()
                    };

                    try {
                        const response = await fetch(`index.php?page=users&action=${id ? 'update' : 'create'}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        const result = await response.json();
                        if (!response.ok || !result.success) {
                            throw new Error(result.message || 'Erreur lors de l enregistrement');
                        }
                        location.reload();
                    } catch (error) {
                        alert(error.message);
                    }
                }

                async function deleteUser(id) {
                    if (!confirm('Supprimer cet utilisateur ?')) return;

                    try {
                        const response = await fetch('index.php?page=users&action=delete', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_user: id })
                        });
                        const result = await response.json();
                        if (!response.ok || !result.success) {
                            throw new Error(result.message || 'Erreur lors de la suppression');
                        }
                        location.reload();
                    } catch (error) {
                        alert(error.message);
                    }
                }

                document.querySelectorAll('.sortable-user-header').forEach(th => {
                    th.addEventListener('click', () => quickUserSort(th.dataset.sort));
                });
                updateUserSortIndicators();
                </script>
                <?php
                break;

            case 'publications':
                $publicationsForum = ForumController::publications();
                $publishedCount = count(array_filter($publicationsForum, fn($p) => ($p['statut'] ?? 'approved') === 'approved'));
                $blockedCount = count($publicationsForum) - $publishedCount;
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-newspaper me-2" style="color: var(--medical-blue);"></i>Publications</h2>
                    <a href="views/frontoffice.php#forum" class="btn btn-outline-medical">
                        <i class="fas fa-eye me-2"></i>Voir le forum
                    </a>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">Total</h6><h2><?php echo count($publicationsForum); ?></h2></div></div>
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">Publiees</h6><h2><?php echo $publishedCount; ?></h2></div></div>
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">Bloquees</h6><h2><?php echo $blockedCount; ?></h2></div></div>
                </div>
                <div class="stat-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Medecin</th><th>Contenu</th><th>Commentaires</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($publicationsForum as $pub): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(trim($pub['medecin_nom'] ?? '') ?: 'Medecin'); ?></td>
                                    <td style="max-width:420px;"><?php $text = (string)($pub['contenu'] ?? ''); echo htmlspecialchars(strlen($text) > 130 ? substr($text, 0, 130) . '...' : $text); ?></td>
                                    <td><?php echo (int)($pub['commentaires_count'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars($pub['date_publication'] ?? ''); ?></td>
                                    <td><span class="badge <?php echo ($pub['statut'] ?? 'approved') === 'approved' ? 'bg-success' : 'bg-danger'; ?>"><?php echo htmlspecialchars($pub['statut'] ?? 'approved'); ?></span></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary" onclick="togglePublicationStatus(<?php echo (int)$pub['id_publication']; ?>)">
                                            <i class="fas fa-shield-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteForumItem('delete-publication', <?php echo (int)$pub['id_publication']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                async function togglePublicationStatus(id) {
                    await fetch('index.php?page=forum&action=toggle-publication-status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    location.reload();
                }
                async function deleteForumItem(action, id) {
                    if (!confirm('Confirmer la suppression ?')) return;
                    await fetch(`index.php?page=forum&action=${action}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    location.reload();
                }
                </script>
                <?php
                break;

            case 'moderation':
                $moderationPosts = ForumController::moderationPublications();
                $reviewCount = count(array_filter($moderationPosts, fn($p) => ($p['moderation_status'] ?? '') === 'review'));
                $blockedCount = count(array_filter($moderationPosts, fn($p) => ($p['moderation_status'] ?? '') === 'blocked'));
                $percentScore = function ($value) {
                    return (int)round(((float)$value) * 100) . '%';
                };
                $badgeClass = function ($status) {
                    if ($status === 'blocked') return 'bg-danger bg-opacity-10 text-danger';
                    if ($status === 'review') return 'bg-warning bg-opacity-10 text-warning';
                    return 'bg-success bg-opacity-10 text-success';
                };
                $label = function ($status) {
                    if ($status === 'blocked') return 'Bloque';
                    if ($status === 'review') return 'A verifier';
                    return 'Valide';
                };
                ?>
                <style>
                    .moderation-hero {
                        background: white;
                        border-radius: 20px;
                        padding: 24px 28px;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
                    }
                    .moderation-item {
                        background: #fff;
                        border: 1px solid rgba(43,123,228,0.08);
                        border-radius: 18px;
                        padding: 22px 28px;
                        margin-bottom: 18px;
                        box-shadow: 0 12px 28px rgba(29,57,90,0.06);
                    }
                    .moderation-score {
                        color: #5d6d7e;
                        font-size: 0.95rem;
                    }
                    .moderation-reason {
                        color: #5d6d7e;
                        font-size: 0.92rem;
                    }
                    @media (max-width: 768px) {
                        .moderation-item { padding: 18px; }
                    }
                </style>
                <div class="moderation-hero d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <h2 class="mb-0">
                        <i class="fas fa-shield-alt me-2" style="color: var(--medical-blue);"></i>
                        Modération IA - Forum
                    </h2>
                    <button class="btn btn-outline-medical" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </button>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">Publications signalees</h6><h2><?php echo count($moderationPosts); ?></h2></div></div>
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">A verifier</h6><h2><?php echo $reviewCount; ?></h2></div></div>
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">Bloquees par IA</h6><h2><?php echo $blockedCount; ?></h2></div></div>
                </div>
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1"><i class="fas fa-shield-alt me-2"></i>Modération IA - Forum</h5>
                            <p class="text-muted mb-0">Vérifiez les publications détectées automatiquement avant leur affichage public.</p>
                        </div>
                        <button class="btn btn-outline-medical btn-sm" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                    </div>
                    <?php if (empty($moderationPosts)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shield-check"></i>
                            <p>Aucune publication à vérifier.</p>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($moderationPosts as $post): ?>
                        <div class="moderation-item">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div style="min-width:260px;flex:1;">
                                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                        <h5 class="mb-0"><?php echo htmlspecialchars(trim($post['doctor_name'] ?? '') ?: 'Medecin'); ?></h5>
                                        <span class="badge <?php echo $badgeClass($post['moderation_status'] ?? 'review'); ?>">
                                            <?php echo $label($post['moderation_status'] ?? 'review'); ?>
                                        </span>
                                        <span class="text-muted small">Source: <?php echo htmlspecialchars($post['moderation_source'] ?? 'fallback'); ?></span>
                                    </div>
                                    <p class="text-muted small mb-2"><?php echo htmlspecialchars(substr((string)($post['date_publication'] ?? ''), 0, 16)); ?></p>
                                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($post['contenu'] ?? '')); ?></p>
                                    <p class="moderation-reason mb-0">
                                        <?php echo htmlspecialchars($post['moderation_reason'] ?: 'Aucune raison detaillee.'); ?>
                                    </p>
                                </div>
                                <div style="min-width:280px;">
                                    <div class="moderation-score mb-3">
                                        Toxicite: <strong><?php echo $percentScore($post['toxicity_score'] ?? 0); ?></strong> ·
                                        Sensible: <strong><?php echo $percentScore($post['sensitive_score'] ?? 0); ?></strong> ·
                                        Risque medical: <strong><?php echo $percentScore($post['medical_risk_score'] ?? 0); ?></strong>
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        <button class="btn btn-success btn-sm" onclick="setPublicationModerationStatus(<?php echo (int)$post['id_publication']; ?>, 'safe')">
                                            <i class="fas fa-check-circle me-1"></i>Approuver
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="setPublicationModerationStatus(<?php echo (int)$post['id_publication']; ?>, 'blocked')">
                                            <i class="fas fa-ban me-1"></i>Bloquer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <script>
                async function setPublicationModerationStatus(id, status) {
                    const response = await fetch('index.php?page=forum&action=set-publication-moderation-status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, status })
                    });
                    const result = await response.json();
                    if (!result.success) {
                        alert(result.error || 'Erreur moderation');
                        return;
                    }
                    location.reload();
                }
                </script>
                <?php
                break;

            case 'commentaires':
                $commentsForum = ForumController::comments();
                $pendingComments = count(array_filter($commentsForum, fn($c) => ($c['statut'] ?? '') === 'en_attente'));
                $publishedComments = count(array_filter($commentsForum, fn($c) => ($c['statut'] ?? '') === 'publie'));
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-comments me-2" style="color: var(--medical-blue);"></i>Commentaires</h2>
                    <button class="btn btn-outline-medical" onclick="location.reload()"><i class="fas fa-sync-alt me-2"></i>Actualiser</button>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">Total</h6><h2><?php echo count($commentsForum); ?></h2></div></div>
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">Publies</h6><h2><?php echo $publishedComments; ?></h2></div></div>
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">En attente</h6><h2><?php echo $pendingComments; ?></h2></div></div>
                </div>
                <div class="stat-card comments-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle comments-table">
                            <colgroup>
                                <col style="width: 19%;">
                                <col style="width: 31%;">
                                <col style="width: 25%;">
                                <col style="width: 13%;">
                                <col style="width: 8%;">
                                <col style="width: 140px;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Commentaire</th>
                                    <th>Publication</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($commentsForum)): ?>
                                <tr>
                                    <td colspan="6" class="comment-empty">
                                        <i class="fas fa-comments fa-2x mb-3 d-block text-muted"></i>
                                        Aucun commentaire pour le moment.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($commentsForum as $comment): 
                                $userName = trim($comment['user_name'] ?? '') ?: 'Utilisateur';
                                $initial = strtoupper(substr($userName, 0, 1));
                                $commentText = (string)($comment['contenu'] ?? '');
                                $publicationText = (string)($comment['publication_excerpt'] ?? '');
                                $status = (string)($comment['statut'] ?? 'en_attente');
                                $statusIcon = $status === 'publie' ? 'fa-check-circle' : ($status === 'supprime' ? 'fa-ban' : 'fa-clock');
                                $displayDate = !empty($comment['date_publication']) ? date('d/m/Y H:i', strtotime($comment['date_publication'])) : '';
                            ?>
                                <tr>
                                    <td>
                                        <div class="comment-user">
                                            <span class="comment-avatar"><?php echo htmlspecialchars($initial); ?></span>
                                            <span class="comment-user-name" title="<?php echo htmlspecialchars($userName); ?>">
                                                <?php echo htmlspecialchars($userName); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="comment-text-cell" title="<?php echo htmlspecialchars($commentText); ?>">
                                            <?php echo htmlspecialchars(strlen($commentText) > 150 ? substr($commentText, 0, 150) . '...' : $commentText); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="comment-publication" title="<?php echo htmlspecialchars($publicationText); ?>">
                                            <?php echo htmlspecialchars(strlen($publicationText) > 120 ? substr($publicationText, 0, 120) . '...' : $publicationText); ?>
                                        </div>
                                    </td>
                                    <td><span class="comment-date"><?php echo htmlspecialchars($displayDate); ?></span></td>
                                    <td>
                                        <span class="comment-status <?php echo htmlspecialchars($status); ?>">
                                            <i class="fas <?php echo $statusIcon; ?>"></i>
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="comment-actions">
                                            <button class="btn btn-outline-success comment-action-btn" title="Publier" onclick="updateCommentStatus(<?php echo (int)$comment['id_commentaire']; ?>, 'publie')"><i class="fas fa-check"></i></button>
                                            <button class="btn btn-outline-warning comment-action-btn" title="Bloquer" onclick="updateCommentStatus(<?php echo (int)$comment['id_commentaire']; ?>, 'supprime')"><i class="fas fa-ban"></i></button>
                                            <button class="btn btn-outline-danger comment-action-btn" title="Supprimer" onclick="deleteForumItem('delete-comment-db', <?php echo (int)$comment['id_commentaire']; ?>)"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                async function updateCommentStatus(id, statut) {
                    await fetch('index.php?page=forum&action=update-comment-status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, statut })
                    });
                    location.reload();
                }
                async function deleteForumItem(action, id) {
                    if (!confirm('Confirmer la suppression ?')) return;
                    await fetch(`index.php?page=forum&action=${action}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    location.reload();
                }
                </script>
                <?php
                break;

            case 'avis':
                $reviewsForum = ForumController::reviews();
                $pendingReviews = count(array_filter($reviewsForum, fn($r) => ($r['statut'] ?? '') === 'pending'));
                $approvedReviews = count(array_filter($reviewsForum, fn($r) => ($r['statut'] ?? '') === 'approved'));
                $avgReview = $approvedReviews > 0
                    ? round(array_sum(array_map(fn($r) => ($r['statut'] ?? '') === 'approved' ? (int)$r['rating'] : 0, $reviewsForum)) / $approvedReviews, 1)
                    : 0;
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-star me-2" style="color: #ffc107;"></i>Avis patients</h2>
                    <button class="btn btn-outline-medical" onclick="location.reload()"><i class="fas fa-sync-alt me-2"></i>Actualiser</button>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">Note moyenne</h6><h2><?php echo $avgReview; ?>/5</h2></div></div>
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">Approuves</h6><h2><?php echo $approvedReviews; ?></h2></div></div>
                    <div class="col-md-4"><div class="stat-card"><h6 class="text-muted">En attente</h6><h2><?php echo $pendingReviews; ?></h2></div></div>
                </div>
                <div class="stat-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Patient</th><th>Medecin</th><th>Note</th><th>Avis</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($reviewsForum as $review): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($review['patient_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(trim($review['doctor_name'] ?? '') ?: 'Medecin'); ?></td>
                                    <td><?php echo str_repeat('★', (int)$review['rating']); ?></td>
                                    <td style="max-width:340px;"><?php $text = (string)($review['commentaire'] ?? ''); echo htmlspecialchars(strlen($text) > 120 ? substr($text, 0, 120) . '...' : $text); ?></td>
                                    <td><?php echo htmlspecialchars($review['created_at'] ?? ''); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($review['statut'] ?? ''); ?></span></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-outline-success" onclick="updateReviewStatus(<?php echo (int)$review['id_avis']; ?>, 'approved')"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="updateReviewStatus(<?php echo (int)$review['id_avis']; ?>, 'reported')"><i class="fas fa-flag"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(<?php echo (int)$review['id_avis']; ?>)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                async function updateReviewStatus(id, statut) {
                    await fetch('index.php?page=forum&action=admin-update-review-status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, statut })
                    });
                    location.reload();
                }
                async function deleteReview(id) {
                    if (!confirm('Supprimer cet avis ?')) return;
                    await fetch('index.php?page=forum&action=admin-delete-review', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    location.reload();
                }
                </script>
                <?php
                break;
                
            case 'dossiers':
                global $dossiers;
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-folder-open me-2" style="color: var(--medical-blue);"></i>Dossiers médicaux</h2>
                    <div class="d-flex gap-2">
                        <a class="btn btn-success" href="?page=dossiers&action=exportCSV">
                            <i class="fas fa-file-excel me-2"></i>Export CSV
                        </a>
                        <a class="btn btn-danger" href="?page=dossiers&action=exportPDF">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </a>
                        <button class="btn btn-medical" data-bs-toggle="modal" data-bs-target="#dossierModal" onclick="openDossierModal()">
                            <i class="fas fa-plus me-2"></i>Nouveau dossier
                        </button>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" id="searchDossier" class="form-control" placeholder="Rechercher par patient ou diagnostic...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="dossierTable">
                            <thead>
                                <tr><th>ID</th><th>Patient</th><th>Médecin</th><th>Diagnostic</th><th>Date création</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if (isset($dossiers) && $dossiers): ?>
                                    <?php foreach ($dossiers as $dossier): ?>
                                        <tr>
                                            <td><?php echo $dossier['id_dossier']; ?></td>
                                            <td><?php echo htmlspecialchars($dossier['patient_nom'] ?? '-'); ?></td>
                                            <td>Dr. <?php echo htmlspecialchars($dossier['medecin_nom'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars(substr($dossier['diagnostic'] ?? '', 0, 50)); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($dossier['date_creation'])); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-primary" onclick="viewDossier(<?php echo $dossier['id_dossier']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editDossier(<?php echo $dossier['id_dossier']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteDossier(<?php echo $dossier['id_dossier']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">Aucun dossier médical trouvé</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php include __DIR__ . '/modals/dossier_modal.php'; ?>
                
                <script>
                function openDossierModal(id = null) {
                    if (id) {
                        document.getElementById('dossierModalTitle').textContent = 'Modifier le dossier médical';
                        document.getElementById('dossierAction').value = 'update';
                        document.getElementById('dossierId').value = id;
                        fetch(`index.php?page=dossiers&action=getOne&id=${id}`)
                            .then(res => res.json())
                            .then(data => {
                                document.getElementById('dossierPatient').value = data.id_patient;
                                document.getElementById('dossierMedecin').value = data.id_medecin;
                                document.getElementById('dossierRdv').value = data.id_rdv || '';
                                document.getElementById('dossierSymptomes').value = data.symptomes || '';
                                document.getElementById('dossierDiagnostic').value = data.diagnostic || '';
                                document.getElementById('dossierTraitement').value = data.traitement || '';
                                document.getElementById('dossierOrdonnance').value = data.ordonnance || '';
                                document.getElementById('dossierNotes').value = data.notes_medecin || '';
                            });
                    } else {
                        document.getElementById('dossierModalTitle').textContent = 'Nouveau dossier médical';
                        document.getElementById('dossierAction').value = 'create';
                        document.getElementById('dossierId').value = '';
                        document.getElementById('dossierForm').reset();
                    }
                    new bootstrap.Modal(document.getElementById('dossierModal')).show();
                }
                
                function editDossier(id) { openDossierModal(id); }
                
                function viewDossier(id) {
                    fetch(`index.php?page=dossiers&action=getOne&id=${id}`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('dossierModalTitle').textContent = 'Détails du dossier médical #' + id;
                            document.getElementById('dossierAction').value = '';
                            document.getElementById('dossierId').value = id;
                            document.getElementById('dossierPatient').value = data.id_patient;
                            document.getElementById('dossierMedecin').value = data.id_medecin;
                            document.getElementById('dossierRdv').value = data.id_rdv || '';
                            document.getElementById('dossierSymptomes').value = data.symptomes || '';
                            document.getElementById('dossierDiagnostic').value = data.diagnostic || '';
                            document.getElementById('dossierTraitement').value = data.traitement || '';
                            document.getElementById('dossierOrdonnance').value = data.ordonnance_texte || '';
                            document.getElementById('dossierNotes').value = data.notes_medecin || '';
                            // Mode lecture seule
                            document.querySelectorAll('#dossierForm select, #dossierForm textarea, #dossierForm input:not([type=hidden])').forEach(el => el.setAttribute('disabled', true));
                            document.querySelector('#dossierModal .modal-footer button[type=submit]').style.display = 'none';
                            const modal = new bootstrap.Modal(document.getElementById('dossierModal'));
                            modal._element.addEventListener('hidden.bs.modal', function restoreForm() {
                                document.querySelectorAll('#dossierForm select, #dossierForm textarea, #dossierForm input:not([type=hidden])').forEach(el => el.removeAttribute('disabled'));
                                document.querySelector('#dossierModal .modal-footer button[type=submit]').style.display = '';
                                modal._element.removeEventListener('hidden.bs.modal', restoreForm);
                            }, { once: true });
                            modal.show();
                        });
                }
                
                function deleteDossier(id) {
                    if (confirm('Êtes-vous sûr de vouloir supprimer ce dossier médical ?')) {
                        window.location.href = `index.php?page=dossiers&action=delete&id=${id}`;
                    }
                }
                
                document.getElementById('searchDossier')?.addEventListener('keyup', function() {
                    const search = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#dossierTable tbody tr');
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(search) ? '' : 'none';
                    });
                });
                </script>
                <?php
                break;

            case 'confirmation-rdv':
                global $rendez_vous, $stats;
                $rdvTotal = is_array($rendez_vous ?? null) ? count($rendez_vous) : 0;
                ?>
                <style>
                .confirm-rdv-hero {
                    padding: 28px;
                    border-radius: 28px;
                    margin-bottom: 24px;
                    color: #fff;
                    background:
                        radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent 28%),
                        linear-gradient(135deg, #1c62c9 0%, #2b7be4 48%, #2ecc71 100%);
                    box-shadow: 0 18px 42px rgba(24, 96, 187, 0.22);
                }

                .confirm-rdv-grid {
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 16px;
                    margin-top: 18px;
                }

                .confirm-rdv-box {
                    background: rgba(255,255,255,0.16);
                    border: 1px solid rgba(255,255,255,0.18);
                    border-radius: 20px;
                    padding: 16px 18px;
                }

                .confirm-rdv-box strong {
                    display: block;
                    font-size: 1.6rem;
                }

                .confirm-status-actions {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                }

                .confirm-status-actions .btn {
                    border-radius: 999px;
                    padding: 8px 14px;
                    font-size: 0.85rem;
                    font-weight: 600;
                }

                @media (max-width: 992px) {
                    .confirm-rdv-grid {
                        grid-template-columns: 1fr;
                    }
                }
                </style>

                <div class="confirm-rdv-hero">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h2 class="mb-2"><i class="fas fa-circle-check me-2"></i>Confirmation rapide des rendez-vous</h2>
                            <p class="mb-0" style="color: rgba(255,255,255,0.88);">
                                Cette page sert a tester vos statistiques en changeant rapidement le statut des rendez-vous.
                            </p>
                        </div>
                        <a href="?page=statistiques" class="btn btn-light text-primary fw-semibold">
                            <i class="fas fa-chart-pie me-2"></i>Ouvrir les statistiques
                        </a>
                    </div>
                    <div class="confirm-rdv-grid">
                        <div class="confirm-rdv-box">
                            <strong><?php echo $rdvTotal; ?></strong>
                            <span>Rendez-vous disponibles pour le test</span>
                        </div>
                        <div class="confirm-rdv-box">
                            <strong>1 clic</strong>
                            <span>Pour confirmer ou terminer un RDV</span>
                        </div>
                        <div class="confirm-rdv-box">
                            <strong>Temps reel</strong>
                            <span>Les statistiques se mettent a jour apres modification</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>ID</th><th>Patient</th><th>Médecin</th><th>Date</th><th>Heure</th><th>Statut actuel</th><th>Tester un statut</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rendez_vous)): ?>
                                    <?php foreach ($rendez_vous as $rdv): ?>
                                        <tr>
                                            <td><?php echo $rdv['id_rdv']; ?></td>
                                            <td><?php echo htmlspecialchars($rdv['patient_nom'] ?? '-'); ?></td>
                                            <td>Dr. <?php echo htmlspecialchars($rdv['medecin_nom'] ?? '-'); ?></td>
                                            <td><?php echo !empty($rdv['date_rdv']) ? date('d/m/Y', strtotime($rdv['date_rdv'])) : '-'; ?></td>
                                            <td><?php echo htmlspecialchars($rdv['heure_rdv'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge status-badge-<?php echo $rdv['statut']; ?>">
                                                    <?php echo htmlspecialchars($rdv['statut']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="confirm-status-actions">
                                                    <a class="btn btn-success" href="?page=confirmation-rdv&action=changeStatus&id=<?php echo $rdv['id_rdv']; ?>&statut=confirme">Confirmer</a>
                                                    <a class="btn btn-primary" href="?page=confirmation-rdv&action=changeStatus&id=<?php echo $rdv['id_rdv']; ?>&statut=termine">Terminer</a>
                                                    <a class="btn btn-warning text-dark" href="?page=confirmation-rdv&action=changeStatus&id=<?php echo $rdv['id_rdv']; ?>&statut=en_attente">En attente</a>
                                                    <a class="btn btn-danger" href="?page=confirmation-rdv&action=changeStatus&id=<?php echo $rdv['id_rdv']; ?>&statut=annule">Annuler</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center">Aucun rendez-vous trouvé</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
                break;
                
            case 'statistiques':
                global $rdvStats, $dossierStats;
                $rdvTotal = (int)($rdvStats['total'] ?? 0);
                $dossierTotal = (int)($dossierStats['total'] ?? 0);
                $statusRows = $rdvStats['by_status'] ?? [];
                $statusCounts = [
                    'en_attente' => 0,
                    'confirme' => 0,
                    'termine' => 0,
                    'annule' => 0
                ];
                foreach ($statusRows as $row) {
                    $statut = $row['statut'] ?? '';
                    if (array_key_exists($statut, $statusCounts)) {
                        $statusCounts[$statut] = (int)($row['count'] ?? 0);
                    }
                }
                $confirmRate = $rdvTotal > 0 ? round(($statusCounts['confirme'] / $rdvTotal) * 100) : 0;
                $completionRate = $rdvTotal > 0 ? round(($statusCounts['termine'] / $rdvTotal) * 100) : 0;
                $doctorRows = $rdvStats['by_doctor'] ?? [];
                $topDoctor = !empty($doctorRows) ? $doctorRows[0]['nom'] : 'Aucun medecin';
                $topDoctorCount = !empty($doctorRows) ? (int)$doctorRows[0]['count'] : 0;
                $monthRows = $rdvStats['by_month'] ?? [];
                $latestMonthCount = !empty($monthRows) ? (int)$monthRows[0]['count'] : 0;
                ?>
                <style>
                .stats-hero {
                    position: relative;
                    overflow: hidden;
                    padding: 32px;
                    border-radius: 28px;
                    margin-bottom: 24px;
                    color: #fff;
                    background:
                        radial-gradient(circle at top right, rgba(255,255,255,0.22), transparent 28%),
                        radial-gradient(circle at bottom left, rgba(255,255,255,0.18), transparent 32%),
                        linear-gradient(135deg, #0f5cc0 0%, #1b7fe5 48%, #23b26d 100%);
                    box-shadow: 0 22px 50px rgba(25, 96, 185, 0.28);
                }

                .stats-hero::after {
                    content: '';
                    position: absolute;
                    top: -70px;
                    right: -70px;
                    width: 220px;
                    height: 220px;
                    border-radius: 50%;
                    background: rgba(255,255,255,0.08);
                }

                .stats-hero-chip {
                    display: inline-flex;
                    align-items: center;
                    gap: 10px;
                    padding: 8px 14px;
                    border-radius: 999px;
                    background: rgba(255,255,255,0.14);
                    backdrop-filter: blur(8px);
                    font-size: 0.9rem;
                    font-weight: 600;
                }

                .stats-kpi {
                    background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(245,249,255,0.96));
                    border: 1px solid rgba(43, 123, 228, 0.08);
                    border-radius: 24px;
                    padding: 22px;
                    height: 100%;
                    box-shadow: 0 14px 35px rgba(29, 57, 90, 0.08);
                }

                .stats-kpi-head {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 18px;
                    gap: 12px;
                }

                .stats-kpi-icon {
                    width: 52px;
                    height: 52px;
                    border-radius: 18px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    font-size: 1.2rem;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
                }

                .stats-kpi-label {
                    color: #6b7b93;
                    font-size: 0.92rem;
                    margin-bottom: 6px;
                }

                .stats-kpi-value {
                    font-size: 2rem;
                    line-height: 1.05;
                    font-weight: 800;
                    color: #15314b;
                    margin-bottom: 10px;
                }

                .stats-kpi-meta {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    color: #557089;
                    font-size: 0.92rem;
                }

                .stats-panel {
                    background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
                    border: 1px solid rgba(43, 123, 228, 0.08);
                    border-radius: 26px;
                    padding: 22px;
                    height: 100%;
                    box-shadow: 0 16px 35px rgba(29, 57, 90, 0.08);
                }

                .stats-panel-title {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 12px;
                    margin-bottom: 16px;
                }

                .stats-panel-title h5 {
                    margin: 0;
                    color: #15314b;
                    font-weight: 700;
                }

                .stats-panel-sub {
                    color: #7b8ea3;
                    font-size: 0.9rem;
                }

                .stats-mini-grid {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 12px;
                    margin-top: 18px;
                }

                .stats-mini-card {
                    padding: 14px 16px;
                    border-radius: 18px;
                    background: rgba(255,255,255,0.16);
                    border: 1px solid rgba(255,255,255,0.2);
                    backdrop-filter: blur(8px);
                }

                .stats-mini-card strong {
                    display: block;
                    color: #fff;
                    font-size: 1.1rem;
                }

                .stats-mini-card span {
                    color: rgba(255,255,255,0.8);
                    font-size: 0.88rem;
                }

                @media (max-width: 768px) {
                    .stats-hero {
                        padding: 24px;
                    }

                    .stats-mini-grid {
                        grid-template-columns: 1fr;
                    }
                }
                </style>

                <div class="stats-hero">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <span class="stats-hero-chip">
                                <i class="fas fa-chart-pie"></i>
                                Vue analytique avancee
                            </span>
                            <h2 class="mt-3 mb-2 fw-bold">Statistiques detaillees</h2>
                            <p class="mb-0" style="max-width: 720px; color: rgba(255,255,255,0.88);">
                                Suivez l'activite medicale, le volume des rendez-vous et la performance du service
                                avec une lecture plus claire et plus moderne.
                            </p>
                        </div>
                        <div class="col-lg-4">
                            <div class="stats-mini-grid">
                                <div class="stats-mini-card">
                                    <strong><?php echo $confirmRate; ?>%</strong>
                                    <span>Taux de confirmation</span>
                                </div>
                                <div class="stats-mini-card">
                                    <strong><?php echo $completionRate; ?>%</strong>
                                    <span>Consultations terminees</span>
                                </div>
                                <div class="stats-mini-card">
                                    <strong><?php echo $latestMonthCount; ?></strong>
                                    <span>Dernier mois suivi</span>
                                </div>
                                <div class="stats-mini-card">
                                    <strong><?php echo htmlspecialchars($topDoctor); ?></strong>
                                    <span>Leader avec <?php echo $topDoctorCount; ?> RDV</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-kpi">
                            <div class="stats-kpi-head">
                                <div>
                                    <div class="stats-kpi-label">Rendez-vous</div>
                                    <div class="stats-kpi-value"><?php echo $rdvTotal; ?></div>
                                </div>
                                <div class="stats-kpi-icon" style="background: linear-gradient(135deg, #2b7be4, #5ca5ff);">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                            <div class="stats-kpi-meta">
                                <i class="fas fa-circle" style="color:#2b7be4; font-size:0.55rem;"></i>
                                Vue globale de l'activite RDV
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-kpi">
                            <div class="stats-kpi-head">
                                <div>
                                    <div class="stats-kpi-label">Confirmes</div>
                                    <div class="stats-kpi-value"><?php echo $statusCounts['confirme']; ?></div>
                                </div>
                                <div class="stats-kpi-icon" style="background: linear-gradient(135deg, #1ba97b, #39d98a);">
                                    <i class="fas fa-check-double"></i>
                                </div>
                            </div>
                            <div class="stats-kpi-meta">
                                <i class="fas fa-arrow-trend-up" style="color:#1ba97b;"></i>
                                <?php echo $confirmRate; ?>% du total valide
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-kpi">
                            <div class="stats-kpi-head">
                                <div>
                                    <div class="stats-kpi-label">Dossiers crees</div>
                                    <div class="stats-kpi-value"><?php echo $dossierTotal; ?></div>
                                </div>
                                <div class="stats-kpi-icon" style="background: linear-gradient(135deg, #ff9f43, #ffbf69);">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                            </div>
                            <div class="stats-kpi-meta">
                                <i class="fas fa-notes-medical" style="color:#ff9f43;"></i>
                                Historique clinique consolide
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-kpi">
                            <div class="stats-kpi-head">
                                <div>
                                    <div class="stats-kpi-label">Medecin le plus actif</div>
                                    <div class="stats-kpi-value" style="font-size:1.2rem; line-height:1.35;">
                                        <?php echo htmlspecialchars($topDoctor); ?>
                                    </div>
                                </div>
                                <div class="stats-kpi-icon" style="background: linear-gradient(135deg, #7b61ff, #4ec6ff);">
                                    <i class="fas fa-user-doctor"></i>
                                </div>
                            </div>
                            <div class="stats-kpi-meta">
                                <i class="fas fa-chart-column" style="color:#7b61ff;"></i>
                                <?php echo $topDoctorCount; ?> rendez-vous enregistres
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="stats-panel">
                            <div class="stats-panel-title">
                                <div>
                                    <h5>Repartition par statut</h5>
                                    <div class="stats-panel-sub">Vue instantanee des rendez-vous confirmes, en attente, termines et annules.</div>
                                </div>
                                <span class="badge bg-light text-primary">RDV</span>
                            </div>
                            <canvas id="statusChart" height="260"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="stats-panel">
                            <div class="stats-panel-title">
                                <div>
                                    <h5>Top 5 medecins</h5>
                                    <div class="stats-panel-sub">Classement base sur le volume des rendez-vous enregistres.</div>
                                </div>
                                <span class="badge bg-light text-success">Performance</span>
                            </div>
                            <canvas id="doctorChart" height="260"></canvas>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="stats-panel">
                            <div class="stats-panel-title">
                                <div>
                                    <h5>Evolution mensuelle des rendez-vous</h5>
                                    <div class="stats-panel-sub">Tendance recente du volume de consultations mois apres mois.</div>
                                </div>
                                <span class="badge bg-light text-dark">Tendance</span>
                            </div>
                            <canvas id="monthlyChart" height="110"></canvas>
                        </div>
                    </div>
                </div>

                <div class="d-none">
                <h2 class="mb-4"><i class="fas fa-chart-line me-2" style="color: var(--medical-blue);"></i>Statistiques détaillées</h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h5>Rendez-vous par statut</h5>
                            <canvas id="statusChart" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h5>Top 5 médecins</h5>
                            <canvas id="doctorChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="stat-card">
                            <h5>Rendez-vous par mois</h5>
                            <canvas id="monthlyChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <script>
                // Graphique par statut
                <?php if (isset($rdvStats['by_status']) && $rdvStats['by_status']): ?>
                new Chart(document.getElementById('statusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($rdvStats['by_status'], 'statut')); ?>,
                        datasets: [{
                            label: 'Nombre de rendez-vous',
                            data: <?php echo json_encode(array_column($rdvStats['by_status'], 'count')); ?>,
                            backgroundColor: ['#ffc107', '#2ecc71', '#2b7be4', '#e74c3c'],
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '68%' }
                });
                <?php endif; ?>
                
                // Graphique par médecin
                <?php if (isset($rdvStats['by_doctor']) && $rdvStats['by_doctor']): ?>
                new Chart(document.getElementById('doctorChart'), {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($rdvStats['by_doctor'], 'nom')); ?>,
                        datasets: [{
                            label: 'Nombre de consultations',
                            data: <?php echo json_encode(array_column($rdvStats['by_doctor'], 'count')); ?>,
                            backgroundColor: ['#2b7be4', '#4e95ec', '#71aeef', '#89c4f4', '#a9d7f8'],
                            borderRadius: 12,
                            borderSkipped: false
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
                });
                <?php endif; ?>
                
                // Graphique mensuel
                <?php if (isset($rdvStats['by_month']) && $rdvStats['by_month']): ?>
                new Chart(document.getElementById('monthlyChart'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_column($rdvStats['by_month'], 'mois')); ?>,
                        datasets: [{
                            label: 'Rendez-vous',
                            data: <?php echo json_encode(array_column($rdvStats['by_month'], 'count')); ?>,
                            borderColor: '#1ba97b',
                            backgroundColor: 'rgba(27,169,123,0.14)',
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#1ba97b',
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            borderWidth: 3,
                            tension: 0.42,
                            fill: true
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
                <?php endif; ?>
                </script>
                <?php
                break;
        }
    }
    ?>
</div>

<style>
.status-badge-en_attente { background: #ffc107; color: #000; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; }
.status-badge-confirme { background: #2ecc71; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; }
.status-badge-termine { background: #2b7be4; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; }
.status-badge-annule { background: #e74c3c; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; }
</style>

<?php include __DIR__ . '/partials/footer.php'; ?>
