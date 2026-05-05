<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth — Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-soft: #818cf8;
            --bg-body: #f8fafc;
            --sidebar: #0f172a;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --sidebar-w: 260px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Outfit', sans-serif; background:var(--bg-body); color:var(--text-main); display:flex; min-height:100vh; }

        /* SIDEBAR */
        .sidebar { position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w); background:var(--sidebar); padding:32px 20px; display:flex; flex-direction:column; z-index:50; }
        .brand { display:flex; align-items:center; gap:12px; margin-bottom:40px; padding-bottom:24px; border-bottom:1px solid rgba(255,255,255,0.1); }
        .brand-icon { width:40px; height:40px; background:var(--primary); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; color:#fff; }
        .brand strong { font-size:1.1rem; color:#fff; font-weight:800; }
        .nav-label { font-size:0.65rem; font-weight:700; color:rgba(255,255,255,0.3); text-transform:uppercase; letter-spacing:0.1em; padding:0 12px; margin:20px 0 8px; }
        .nav-item { display:flex; align-items:center; gap:12px; text-decoration:none; color:rgba(255,255,255,0.6); font-size:0.92rem; font-weight:500; padding:12px 14px; border-radius:12px; transition:all 0.2s; margin-bottom:4px; }
        .nav-item i { width:20px; text-align:center; }
        .nav-item:hover { background:rgba(255,255,255,0.05); color:#fff; }
        .nav-item.active { background:var(--primary); color:#fff; font-weight:700; box-shadow:0 4px 12px rgba(79,70,229,0.3); }
        .logout { margin-top:auto; display:flex; align-items:center; gap:12px; text-decoration:none; color:#fca5a5; font-size:0.9rem; font-weight:600; padding:12px 14px; border-radius:12px; border:1px solid rgba(252,165,165,0.2); transition:all 0.2s; }
        .logout:hover { background:rgba(252,165,165,0.1); }

        /* MAIN */
        .main-content { flex:1; margin-left:var(--sidebar-w); padding:40px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; }
        header h1 { font-size:1.6rem; font-weight:800; }
        .user-chip { display:flex; align-items:center; gap:12px; background:var(--card-bg); padding:8px 18px 8px 8px; border-radius:50px; border:1px solid var(--border); }
        .avatar { width:34px; height:34px; background:var(--primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; }

        /* STATS */
        .stats-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:24px; margin-bottom:40px; }
        .stat-card { background:var(--card-bg); padding:24px; border-radius:24px; border:1px solid var(--border); display:flex; align-items:center; gap:20px; transition:transform 0.2s; }
        .stat-card:hover { transform:translateY(-3px); }
        .stat-icon { width:52px; height:52px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; background:var(--bg-body); color:var(--primary); }
        .stat-val { font-size:1.8rem; font-weight:800; }
        .stat-lbl { font-size:0.85rem; color:var(--text-muted); font-weight:600; margin-top:2px; }

        /* PANELS */
        .panel { background:var(--card-bg); border-radius:24px; border:1px solid var(--border); padding:32px; margin-bottom:32px; }
        .panel-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .panel-title { font-size:1.1rem; font-weight:700; display:flex; align-items:center; gap:10px; }
        .panel-title i { color:var(--primary); }
        .count-badge { background:#eef2ff; color:var(--primary); font-size:0.75rem; font-weight:700; padding:4px 12px; border-radius:20px; }

        /* TABLES */
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        th { text-align:left; font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; padding:12px 16px; border-bottom:2px solid var(--border); white-space:nowrap; }
        td { padding:14px 16px; font-size:0.88rem; border-bottom:1px solid var(--bg-body); vertical-align:middle; }
        tr:hover td { background:#f8fafc; }
        .badge { display:inline-block; padding:4px 10px; border-radius:8px; font-size:0.72rem; font-weight:700; }
        .badge-blue  { background:#eff6ff; color:#2563eb; }
        .badge-green { background:#f0fdf4; color:#16a34a; }
        .badge-teal  { background:#f0fdfa; color:#0d9488; }
        .badge-purple{ background:#faf5ff; color:#7c3aed; }
        .badge-gray  { background:#f1f5f9; color:#475569; }
        .name-cell { font-weight:700; }
        .sub-cell  { font-size:0.8rem; color:var(--text-muted); margin-top:2px; }
        .diag-text { max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .empty-row td { text-align:center; color:var(--text-muted); padding:32px; font-style:italic; }

        /* NEW ADMIN SEARCH & PDF STYLES */
        .search-box { position:relative; margin-bottom:16px; width:300px; }
        .search-box i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:0.8rem; }
        .search-box input { width:100%; padding:8px 12px 8px 34px; border-radius:10px; border:1px solid var(--border); font-family:inherit; font-size:0.85rem; outline:none; }
        .search-box input:focus { border-color:var(--primary); }

        .btn-pdf-sm { background:#4f46e5; color:#fff; border:none; padding:4px 8px; border-radius:6px; font-size:0.7rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; }
        .btn-pdf-sm:hover { background:#4338ca; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-shield-halved"></i></div>
        <div><strong>GlobalHealth</strong><small style="color:rgba(255,255,255,0.4); font-size:0.7rem; display:block;">Admin Console</small></div>
    </div>

    <div class="nav-label">Navigation</div>
    <a href="?controller=admin" class="nav-item active"><i class="fas fa-gauge-high"></i>Dashboard</a>
    <a href="#consultations" class="nav-item"><i class="fas fa-notes-medical"></i>Consultations</a>
    <a href="#suivis" class="nav-item"><i class="fas fa-heart-pulse"></i>Suivis</a>
    <a href="#patients" class="nav-item"><i class="fas fa-users"></i>Patients</a>

    <a href="?controller=auth&action=logout" class="logout"><i class="fas fa-power-off"></i>Déconnexion</a>
</aside>

<main class="main-content">
    <header>
        <h1>Tableau de bord Admin</h1>
        <div class="user-chip">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></div>
            <span style="font-weight:600; font-size:0.9rem;"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrateur'); ?></span>
        </div>
    </header>

    <!-- STATISTIQUES -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-notes-medical"></i></div>
            <div><div class="stat-val"><?php echo $statsConsultation['total'] ?? 0; ?></div><div class="stat-lbl">Consultations</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-heart-pulse"></i></div>
            <div><div class="stat-val"><?php echo $statsSuivis['total'] ?? 0; ?></div><div class="stat-lbl">Suivis</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div><div class="stat-val"><?php echo $statsPatients ?? 0; ?></div><div class="stat-lbl">Patients</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-md"></i></div>
            <div><div class="stat-val"><?php echo $statsMedecins ?? 0; ?></div><div class="stat-lbl">Médecins</div></div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════ -->
    <!-- LISTE COMPLÈTE DES CONSULTATIONS               -->
    <!-- ══════════════════════════════════════════════ -->
    <div class="panel" id="consultations">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-notes-medical"></i>Liste des Consultations</div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" onkeyup="filterTable('tableConsultations', this.value)" placeholder="Rechercher consultation...">
            </div>
            <span class="count-badge"><?php echo count($listeConsultations); ?> enregistrement(s)</span>
        </div>
        <div class="table-wrap">
            <table id="tableConsultations">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Médecin</th>
                        <th>Spécialité</th>
                        <th>Date RDV</th>
                        <th>Diagnostic</th>
                        <th>Traitement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listeConsultations)): ?>
                    <tr class="empty-row"><td colspan="8">Aucune consultation enregistrée.</td></tr>
                    <?php else: ?>
                    <?php foreach ($listeConsultations as $c): ?>
                    <tr data-search="<?php echo htmlspecialchars(strtolower($c['patient_nom'].' '.$c['patient_prenom'].' '.$c['medecin_nom'].' '.$c['diagnostic'])); ?>">
                        <td><span class="badge badge-gray">#<?php echo $c['id_consultation']; ?></span></td>
                        <td>
                            <div class="name-cell"><?php echo htmlspecialchars($c['patient_nom'] . ' ' . $c['patient_prenom']); ?></div>
                        </td>
                        <td>
                            <div class="name-cell">Dr. <?php echo htmlspecialchars($c['medecin_nom'] . ' ' . $c['medecin_prenom']); ?></div>
                        </td>
                        <td><span class="badge badge-blue"><?php echo htmlspecialchars($c['specialite'] ?? 'Général'); ?></span></td>
                        <td><?php echo htmlspecialchars($c['date_rdv'] ?? '—'); ?></td>
                        <td><div class="diag-text" title="<?php echo htmlspecialchars($c['diagnostic']); ?>"><?php echo htmlspecialchars($c['diagnostic']); ?></div></td>
                        <td><div class="diag-text" title="<?php echo htmlspecialchars($c['traitement']); ?>"><?php echo htmlspecialchars($c['traitement']); ?></div></td>
                        <td>
                            <button class="btn-pdf-sm" onclick="exportConsultationPDF(<?php echo htmlspecialchars(json_encode($c)); ?>)">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════ -->
    <!-- LISTE COMPLÈTE DES SUIVIS                      -->
    <!-- ══════════════════════════════════════════════ -->
    <div class="panel" id="suivis">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-heart-pulse"></i>Liste des Suivis Médicaux</div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" onkeyup="filterTable('tableSuivis', this.value)" placeholder="Rechercher suivi...">
            </div>
            <span class="count-badge"><?php echo count($listeSuivis); ?> enregistrement(s)</span>
        </div>
        <div class="table-wrap">
            <table id="tableSuivis">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Médecin</th>
                        <th>Date Suivi</th>
                        <th>Poids</th>
                        <th>Tension</th>
                        <th>État Général</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listeSuivis)): ?>
                    <tr class="empty-row"><td colspan="8">Aucun suivi enregistré.</td></tr>
                    <?php else: ?>
                    <?php foreach ($listeSuivis as $s): ?>
                    <tr data-search="<?php echo htmlspecialchars(strtolower($s['patient_nom'].' '.$s['patient_prenom'].' '.$s['medecin_nom'].' '.$s['etat_general'])); ?>">
                        <td><span class="badge badge-gray">#<?php echo $s['id_suivie']; ?></span></td>
                        <td>
                            <div class="name-cell"><?php echo htmlspecialchars($s['patient_nom'] . ' ' . $s['patient_prenom']); ?></div>
                        </td>
                        <td>
                            <div class="name-cell">Dr. <?php echo htmlspecialchars($s['medecin_nom'] . ' ' . $s['medecin_prenom']); ?></div>
                            <div class="sub-cell"><?php echo htmlspecialchars($s['specialite'] ?? ''); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($s['date_suivi']); ?></td>
                        <td>
                            <?php if ($s['poids']): ?>
                                <span class="badge badge-teal"><?php echo $s['poids']; ?> kg</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($s['tension'] ?? '—'); ?></td>
                        <td><div class="diag-text" title="<?php echo htmlspecialchars($s['etat_general']); ?>"><?php echo htmlspecialchars($s['etat_general']); ?></div></td>
                        <td>
                            <button class="btn-pdf-sm" onclick="exportSuiviePDF(<?php echo htmlspecialchars(json_encode($s)); ?>)" style="background:#10b981;">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════ -->
    <!-- LISTE DES PATIENTS                             -->
    <!-- ══════════════════════════════════════════════ -->
    <div class="panel" id="patients">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-users"></i>Liste des Patients</div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" onkeyup="filterTable('tablePatients', this.value)" placeholder="Rechercher patient...">
            </div>
            <span class="count-badge"><?php echo count($listePatients); ?> patient(s)</span>
        </div>
        <div class="table-wrap">
            <table id="tablePatients">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Email</th>
                        <th>Consultations</th>
                        <th>Suivis</th>
                        <th>Activité</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listePatients)): ?>
                    <tr class="empty-row"><td colspan="5">Aucun patient enregistré.</td></tr>
                    <?php else: ?>
                    <?php foreach ($listePatients as $p): ?>
                    <tr data-search="<?php echo htmlspecialchars(strtolower($p['nom'].' '.$p['prenom'].' '.$p['email'])); ?>">
                        <td class="name-cell"><?php echo htmlspecialchars($p['nom'] . ' ' . $p['prenom']); ?></td>
                        <td style="color:var(--text-muted);"><?php echo htmlspecialchars($p['email']); ?></td>
                        <td>
                            <span class="badge badge-blue">
                                <i class="fas fa-notes-medical" style="margin-right:4px;"></i>
                                <?php echo $p['nb_consultations']; ?> consultation<?php echo $p['nb_consultations'] > 1 ? 's' : ''; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-teal">
                                <i class="fas fa-heart-pulse" style="margin-right:4px;"></i>
                                <?php echo $p['nb_suivis']; ?> suivi<?php echo $p['nb_suivis'] > 1 ? 's' : ''; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($p['nb_consultations'] > 0 || $p['nb_suivis'] > 0): ?>
                                <span class="badge badge-green"><i class="fas fa-circle" style="font-size:0.5rem; margin-right:5px;"></i>Actif</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Nouveau</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
function filterTable(tableId, term) {
    term = term.toLowerCase();
    const rows = document.querySelectorAll(`#${tableId} tbody tr:not(.empty-row)`);
    rows.forEach(row => {
        const text = row.getAttribute('data-search') || "";
        row.style.display = text.includes(term) ? "" : "none";
    });
}

function exportConsultationPDF(data) {
    const element = document.createElement('div');
    element.style.padding = '40px';
    element.style.fontFamily = "'Outfit', sans-serif";
    
    const diagnostic = data.diagnostic ? data.diagnostic.replace(/\n/g, '<br>') : 'Non renseigné';
    const traitement = data.traitement ? data.traitement.replace(/\n/g, '<br>') : 'Non renseigné';
    const notes = data.notes ? data.notes.replace(/\n/g, '<br>') : 'Aucune note particulière';

    element.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #4f46e5; padding-bottom:20px; margin-bottom:30px;">
            <div><h1 style="color:#4f46e5; margin:0; font-size:24px;">GlobalHealth Connect</h1><p style="margin:5px 0 0; color:#64748b; font-size:12px;">Admin Record Export</p></div>
            <div style="text-align:right;"><p style="margin:0; font-weight:bold;">Compte-rendu de Consultation</p><p style="margin:5px 0 0; color:#64748b; font-size:12px;">ID: #CONS-${data.id_consultation}</p></div>
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px; margin-bottom:40px;">
            <div><p style="font-size:10px; text-transform:uppercase; color:#64748b; margin-bottom:5px; font-weight:bold;">Patient</p><p style="font-size:16px; font-weight:bold; margin:0;">${data.patient_nom} ${data.patient_prenom}</p></div>
            <div style="text-align:right;"><p style="font-size:10px; text-transform:uppercase; color:#64748b; margin-bottom:5px; font-weight:bold;">Médecin</p><p style="font-size:16px; font-weight:bold; margin:0;">Dr. ${data.medecin_nom} ${data.medecin_prenom}</p></div>
        </div>
        <div style="margin-bottom:20px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #e2e8f0;">
            <p style="font-size:10px; text-transform:uppercase; color:#4f46e5; margin-bottom:10px; font-weight:bold;">Diagnostic</p>
            <p style="font-size:14px; line-height:1.6; color:#1e293b; margin:0;">${diagnostic}</p>
        </div>
        <div style="margin-bottom:20px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #e2e8f0;">
            <p style="font-size:10px; text-transform:uppercase; color:#4f46e5; margin-bottom:10px; font-weight:bold;">Traitement Prescrit</p>
            <p style="font-size:14px; line-height:1.6; color:#1e293b; margin:0;">${traitement}</p>
        </div>
        <div style="margin-bottom:30px; background:#fffcf0; padding:15px; border-radius:12px; border:1px solid #fde68a;">
            <p style="font-size:10px; text-transform:uppercase; color:#b45309; margin-bottom:5px; font-weight:bold;">Notes Additionnelles</p>
            <p style="font-size:13px; color:#78350f; margin:0;">${notes}</p>
        </div>
        <div style="margin-top:60px; border-top:1px solid #e2e8f0; pt:20px; text-align:center; color:#94a3b8; font-size:10px;">
            <p>Document administratif exporté depuis GlobalHealth Connect.</p>
            <p>Généré le ${new Date().toLocaleDateString('fr-FR')}</p>
        </div>
    `;
    const opt = { margin:10, filename:`Consultation_${data.patient_nom}.pdf`, image:{type:'jpeg',quality:0.98}, html2canvas:{scale:2}, jsPDF:{unit:'mm',format:'a4',orientation:'portrait'} };
    html2pdf().set(opt).from(element).save();
}

function exportSuiviePDF(data) {
    const element = document.createElement('div');
    element.style.padding = '40px';
    element.style.fontFamily = "'Outfit', sans-serif";

    const etat = data.etat_general ? data.etat_general.replace(/\n/g, '<br>') : 'Non renseigné';
    const analyses = data.analyses_a_realiser ? data.analyses_a_realiser.replace(/\n/g, '<br>') : 'Aucune';
    const resultats = data.resultat_analyses ? data.resultat_analyses.replace(/\n/g, '<br>') : null;
    const regime = data.regime_alimentaire ? data.regime_alimentaire.replace(/\n/g, '<br>') : null;
    const activite = data.activite_physique ? data.activite_physique.replace(/\n/g, '<br>') : null;

    element.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #10b981; padding-bottom:20px; margin-bottom:30px;">
            <div><h1 style="color:#10b981; margin:0; font-size:24px;">GlobalHealth Connect</h1><p style="margin:5px 0 0; color:#64748b; font-size:12px;">Admin Record Export</p></div>
            <div style="text-align:right;"><p style="margin:0; font-weight:bold;">Rapport de Suivi Médical</p><p style="margin:5px 0 0; color:#64748b; font-size:12px;">ID: #SUI-${data.id_suivie}</p></div>
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px; margin-bottom:40px;">
            <div><p style="font-size:10px; text-transform:uppercase; color:#64748b; margin-bottom:5px; font-weight:bold;">Patient</p><p style="font-size:16px; font-weight:bold; margin:0;">${data.patient_nom} ${data.patient_prenom}</p></div>
            <div style="text-align:right;"><p style="font-size:10px; text-transform:uppercase; color:#64748b; margin-bottom:5px; font-weight:bold;">Médecin</p><p style="font-size:16px; font-weight:bold; margin:0;">Dr. ${data.medecin_nom} ${data.medecin_prenom}</p></div>
        </div>
        <div style="display:flex; gap:20px; margin-bottom:30px;">
            <div style="flex:1; background:#f0fdf4; padding:15px; border-radius:10px; border:1px solid #dcfce7;">
                <p style="font-size:10px; text-transform:uppercase; color:#16a34a; margin:0 0 5px; font-weight:bold;">Indicateurs Physiques</p>
                <p style="font-size:16px; font-weight:bold; margin:0;">Poids: ${data.poids || '—'} kg | Tension: ${data.tension || '—'}</p>
            </div>
            <div style="flex:1; background:#f0fdf4; padding:15px; border-radius:10px; border:1px solid #dcfce7;">
                <p style="font-size:10px; text-transform:uppercase; color:#16a34a; margin:0 0 5px; font-weight:bold;">Prochain RDV</p>
                <p style="font-size:16px; font-weight:bold; margin:0;">${data.prochain_rdv || 'À définir'}</p>
            </div>
        </div>
        <div style="margin-bottom:20px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #e2e8f0;">
            <p style="font-size:10px; text-transform:uppercase; color:#10b981; margin-bottom:10px; font-weight:bold;">État Général / Observations</p>
            <p style="font-size:14px; line-height:1.6; color:#1e293b; margin:0;">${etat}</p>
        </div>
        <div style="margin-bottom:20px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #e2e8f0;">
            <p style="font-size:10px; text-transform:uppercase; color:#10b981; margin-bottom:10px; font-weight:bold;">Analyses Médicales</p>
            <p style="font-size:14px; line-height:1.6; color:#1e293b; margin:0;">Prescrites: ${analyses}</p>
            ${resultats ? `<p style="margin-top:10px; font-size:13px; color:#4f46e5; font-weight:600;">Résultats: ${resultats}</p>` : ''}
        </div>
        ${(regime || activite) ? `
        <div style="margin-bottom:20px; display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div style="background:#fffcf0; padding:15px; border-radius:12px; border:1px solid #fde68a;">
                <p style="font-size:10px; text-transform:uppercase; color:#b45309; margin-bottom:5px; font-weight:bold;">Régime</p>
                <p style="font-size:13px; color:#78350f; margin:0;">${regime || 'Non spécifié'}</p>
            </div>
            <div style="background:#fffcf0; padding:15px; border-radius:12px; border:1px solid #fde68a;">
                <p style="font-size:10px; text-transform:uppercase; color:#b45309; margin-bottom:5px; font-weight:bold;">Activité</p>
                <p style="font-size:13px; color:#78350f; margin:0;">${activite || 'Non spécifié'}</p>
            </div>
        </div>
        ` : ''}
        <div style="margin-top:40px; border-top:1px solid #e2e8f0; pt:20px; text-align:center; color:#94a3b8; font-size:10px;">
            <p>Document administratif exporté depuis GlobalHealth Connect.</p>
            <p>Généré le ${new Date().toLocaleDateString('fr-FR')}</p>
        </div>
    `;
    const opt = { margin:10, filename:`Suivi_${data.patient_nom}.pdf`, image:{type:'jpeg',quality:0.98}, html2canvas:{scale:2}, jsPDF:{unit:'mm',format:'a4',orientation:'portrait'} };
    html2pdf().set(opt).from(element).save();
}
</script>

</body>
</html>
