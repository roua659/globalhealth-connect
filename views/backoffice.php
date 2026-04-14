<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/UtilisateurModel.php';

// Récupérer les listes pour les formulaires
$utilisateurModel = new UtilisateurModel();
$patients = $utilisateurModel->getPatients();
$medecins = $utilisateurModel->getMedecins();

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
        <a href="?page=dossiers" class="sidebar-menu-item <?php echo $currentPage == 'dossiers' ? 'active' : ''; ?>">
            <i class="fas fa-folder-open"></i>
            <span>Dossiers médicaux</span>
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
        case 'dossiers':
            includeContent('dossiers');
            break;
        case 'statistiques':
            includeContent('statistiques');
            break;
        case 'confirmation-rdv':
            includeContent('confirmation-rdv');
            break;
        default:
            includeContent('dashboard');
            break;
    }
    
    function includeContent($page) {
        global $patients, $medecins, $rendez_vous, $stats, $dossiers, $rdvStats, $dossierStats;

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
                    window.open(`index.php?page=dossiers&action=view&id=${id}`, '_blank');
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
