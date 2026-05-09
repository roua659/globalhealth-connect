<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth — Suivis Patients (Médecin)</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-soft: #60a5fa;
            --bg-body: #f8fafc;
            --sidebar: #0f172a;
            --sidebar-item-hover: rgba(255,255,255,0.05);
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --sidebar-w: 260px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Outfit', sans-serif; background:var(--bg-body); color:var(--text-main); display:flex; min-height:100vh; }

        /* SIDEBAR UNIFIÉE */
        .sidebar {
            position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w);
            background:var(--sidebar); padding:32px 20px;
            display:flex; flex-direction:column; z-index:50;
        }
        .brand { display:flex; align-items:center; gap:12px; margin-bottom:40px; padding-bottom:24px; border-bottom:1px solid rgba(255,255,255,0.1); }
        .brand-icon { width:40px; height:40px; background:var(--primary); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; color:#fff; }
        .brand strong { font-size:1.1rem; color:#fff; font-weight:800; }
        
        .nav-label { font-size:0.65rem; font-weight:700; color:rgba(255,255,255,0.3); text-transform:uppercase; letter-spacing:0.1em; padding:0 12px; margin:20px 0 8px; }
        .nav-item {
            display:flex; align-items:center; gap:12px; text-decoration:none;
            color:rgba(255,255,255,0.6); font-size:0.92rem; font-weight:500;
            padding:12px 14px; border-radius:12px; transition:all 0.2s; margin-bottom:4px;
        }
        .nav-item i { width:20px; text-align:center; }
        .nav-item:hover, .nav-item.active { background:var(--sidebar-item-hover); color:#fff; }
        .nav-item.active { background:var(--primary); font-weight:700; }

        .logout { margin-top:auto; display:flex; align-items:center; gap:12px; text-decoration:none; color:#fca5a5; font-size:0.9rem; font-weight:600; padding:12px 14px; border-radius:12px; border:1px solid rgba(252, 165, 165, 0.2); }

        /* MAIN CONTENT */
        .main-content { flex:1; margin-left:var(--sidebar-w); padding:40px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; }
        header h1 { font-size:1.6rem; font-weight:800; }
        
        /* PAGE SPLIT */
        .page-split { display:grid; grid-template-columns: 400px 1fr; gap:32px; align-items:start; }

        /* FORM */
        .form-card { background:var(--card-bg); border-radius:24px; padding:32px; border:1px solid var(--border); position:sticky; top:40px; }
        .f-group { margin-bottom:20px; }
        .f-label { display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px; }
        .f-control { width:100%; border:1.5px solid var(--border); border-radius:12px; padding:12px 16px; font-family:inherit; font-size:0.9rem; outline:none; }
        .f-control:focus { border-color:var(--primary-soft); }
        .btn-primary { width:100%; background:var(--primary); color:#fff; border:none; padding:14px; border-radius:12px; font-weight:700; cursor:pointer; font-family:inherit; }

        /* LIST */
        .s-item { background:var(--card-bg); border-radius:24px; padding:28px; border:1px solid var(--border); border-left:6px solid #10b981; margin-bottom:24px; transition:all 0.3s ease; }
        .s-item:hover { transform:translateY(-5px); box-shadow:0 20px 40px rgba(0,0,0,0.05); }
        
        /* Styles d'Alerte */
        .s-item.alert-active { border-left-color: #ef4444; background: #fffafb; }
        .alert-badge { 
            background: #ef4444; color: #fff; font-size: 0.65rem; font-weight: 800; 
            padding: 4px 10px; border-radius: 20px; margin-left: 10px; 
            display: inline-flex; align-items: center; gap: 5px; vertical-align: middle;
            animation: pulse-red 2s infinite;
        }
        @keyframes pulse-red {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .metric-alert { background: #fef2f2 !important; border-color: #fee2e2 !important; }
        .metric-alert i { color: #ef4444 !important; }
        .metric-alert .m-val { color: #b91c1c !important; }
        .s-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; }
        .s-patient { font-weight:800; font-size:1.1rem; }
        .s-date { font-size:0.85rem; color:var(--text-muted); margin-top:4px; display:flex; align-items:center; gap:8px; }
        
        .row-metrics { display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:24px; }
        .metric-box { background:var(--bg-body); padding:12px 18px; border-radius:16px; display:flex; align-items:center; gap:12px; }
        .metric-box i { color:#10b981; font-size:1.1rem; }
        .m-lbl { font-size:0.65rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; }
        .m-val { font-size:1rem; font-weight:800; }

        .s-body { display:grid; grid-template-columns: 1fr 1fr; gap:20px; }
        .s-block { background:#f8fafc; padding:16px; border-radius:16px; border:1px solid var(--border); }
        .s-block.highlight { background:#fffcf0; border-color:#fde68a; grid-column: span 2; }
        .s-block-label { font-size:0.65rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px; }
        .s-block-text { font-size:0.9rem; line-height:1.5; }

        .btn-delete { background:transparent; border:none; color:#ef4444; cursor:pointer; font-size:0.8rem; font-weight:700; display:flex; align-items:center; gap:8px; }
        .btn-pdf { background:#10b981; color:#fff; border:none; padding:8px 16px; border-radius:10px; font-size:0.75rem; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px; transition: all 0.2s; }
        .btn-pdf:hover { background:#059669; transform:scale(1.05); }

        .actions-row { display:flex; gap:16px; align-items:center; margin-top:20px; padding-top:20px; border-top:1px dashed var(--border); }

        .search-container { position:relative; margin-bottom:24px; }
        .search-container i { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--text-muted); }
        .search-input { width:100%; padding:12px 16px 12px 44px; border-radius:14px; border:1.5px solid var(--border); font-family:inherit; outline:none; transition:all 0.2s; }
        .search-input:focus { border-color:var(--primary); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }

        .alert { padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600; display:flex; align-items:center; gap:10px; }
        .alert-success { background:#f0fdf4; color:#16a34a; border: 1px solid #dcfce7; }
        .alert-error { background:#fef2f2; color:#ef4444; border: 1px solid #fee2e2; }

        .error-msg { color: #ef4444; font-size: 0.75rem; font-weight: 600; margin-top: 4px; display: none; }
        .f-control.is-invalid { border-color: #ef4444 !important; background-color: #fef2f2; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-stethoscope"></i></div>
        <div><strong>GlobalHealth</strong><small style="color:rgba(255,255,255,0.4); font-size:0.7rem; display:block;">Medical Workspace</small></div>
    </div>

    <div class="nav-label">Medical Tools</div>
    <a href="?controller=medecin" class="nav-item"><i class="fas fa-house-chimney-medical"></i>Dashboard</a>
    <a href="?controller=medecin&action=consultation" class="nav-item"><i class="fas fa-notes-medical"></i>Consultations</a>
    <a href="?controller=medecin&action=suivie" class="nav-item active"><i class="fas fa-heart-pulse"></i>Patient Follow-ups</a>

    <div class="nav-label">Settings</div>
    <a href="?controller=medecin&action=profile" class="nav-item"><i class="fas fa-user-md"></i>Mon Profil</a>

    <a href="?controller=auth&action=logout" class="logout"><i class="fas fa-door-open"></i>Terminer Session</a>
</aside>

<main class="main-content">
    <header>
        <h1>Suivis Médicaux</h1>
    </header>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'triangle-exclamation'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="page-split">
        <div class="form-card">
            <h3 style="margin-bottom:24px; font-weight:700;">Nouvelle Prescription</h3>
            <!-- novalidate désactive les bulles par défaut du navigateur -->
            <form method="POST" id="suivieForm" novalidate>
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id_suivie" id="formId" value="">

                <div id="editModeIndicator" style="display:none; background:#ecfdf5; padding:12px; border-radius:12px; border:1px solid #d1fae5; margin-bottom:20px; font-size:0.85rem; color:#065f46;">
                    <i class="fas fa-pen-to-square"></i> Mode modification activé
                    <button type="button" onclick="resetForm()" style="float:right; background:none; border:none; color:#065f46; font-weight:700; cursor:pointer;">Annuler</button>
                </div>
                
                <div class="f-group">
                    <label class="f-label">ID Consultation (Jointure) <span style="color:#ef4444;">*</span></label>
                    <select name="id_consultation" class="f-control" id="id_consultation">
                        <option value="">— Choisir l'ID de Consultation —</option>
                        <?php foreach ($consultations as $c): ?>
                            <option value="<?php echo $c['id_consultation']; ?>">
                                Consultation N° <?php echo $c['id_consultation']; ?> (Patient: <?php echo htmlspecialchars($c['patient_nom']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-msg" id="err_id_consultation">Veuillez sélectionner une consultation.</div>
                </div>

                <div class="f-group">
                    <label class="f-label">Date du Suivi <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="date_suivi" id="date_suivi" class="f-control" value="<?php echo date('Y-m-d'); ?>">
                    <div class="error-msg" id="err_date_suivi">La date est obligatoire.</div>
                </div>

                <div class="f-group">
                    <label class="f-label">Poids (kg)</label>
                    <input type="number" name="poids" id="poids" class="f-control" step="0.1" placeholder="Ex: 75.5">
                    <div class="error-msg" id="err_poids">Le poids doit être entre 1 et 500 kg.</div>
                </div>

                <div class="f-group">
                    <label class="f-label">Tension Artérielle</label>
                    <input type="text" name="tension" id="tension" class="f-control" placeholder="Ex: 120/80">
                    <div class="error-msg" id="err_tension">Format invalide (ex: 120/80).</div>
                </div>

                <div class="f-group">
                    <label class="f-label">État Général &amp; Objectifs <span style="color:#ef4444;">*</span></label>
                    <textarea name="etat_general" id="etat_general" class="f-control" rows="3" placeholder="Observations cliniques..."></textarea>
                    <div class="error-msg" id="err_etat_general">Veuillez saisir au moins 5 caractères.</div>
                </div>

                <div class="f-group">
                    <label class="f-label">Analyses Médicales à Réaliser</label>
                    <textarea name="analyses_a_realiser" class="f-control" rows="2"></textarea>
                </div>

                <div class="f-group">
                    <label class="f-label">Échéance Prochain RDV</label>
                    <input type="date" name="prochain_rdv" id="prochain_rdv" class="f-control">
                </div>

                <button type="submit" id="submitBtn" class="btn-primary">Valider la Prescription</button>
            </form>
        </div>

        <div class="list-card">
            <h3 style="margin-bottom:24px; font-weight:700;">Dossiers de Suivi Actifs</h3>
            
            <div class="search-container" style="display:flex; gap:12px; align-items:center;">
                <div style="position:relative; flex:1;">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Rechercher par patient, ID, état, date...">
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <select id="sortSelect" class="f-control" style="width:160px; padding:8px 12px; margin:0;">
                        <option value="date">Date Suivi</option>
                        <option value="patient">Patient</option>
                        <option value="poids">Poids</option>
                        <option value="id">ID #</option>
                    </select>
                    <button id="sortOrderBtn" class="btn-pdf" style="padding:10px; background:#fff; color:var(--text-main); border:1.5px solid var(--border);">
                        <i class="fas fa-sort-amount-down"></i>
                    </button>
                </div>
            </div>

            <div id="suivieList">
                <?php foreach ($suivis as $s): 
                    $isAlert = false;
                    $alertReason = "";
                    if(!empty($s['tension'])) {
                        $parts = explode('/', $s['tension']);
                        if(count($parts) == 2) {
                            $syst = (int)$parts[0];
                            $diast = (int)$parts[1];
                            if($syst >= 140 || $diast >= 90) {
                                $isAlert = true;
                                $alertReason = "Alerte : Tension élevée ($syst/$diast)";
                            }
                        }
                    }
                ?>
                <div class="s-item <?php echo $isAlert ? 'alert-active' : ''; ?>" 
                     data-search="<?php echo htmlspecialchars(strtolower($s['patient_nom'].' '.$s['patient_prenom'].' '.$s['id_suivie'].' '.$s['etat_general'].' '.$s['date_suivi'])); ?>"
                     data-date="<?php echo $s['date_suivi']; ?>"
                     data-patient="<?php echo htmlspecialchars(strtolower($s['patient_nom'].' '.$s['patient_prenom'])); ?>"
                     data-poids="<?php echo $s['poids'] ?: 0; ?>"
                     data-id="<?php echo $s['id_suivie']; ?>">
                    <div class="s-header">
                        <div>
                            <div class="s-patient">
                                <?php echo htmlspecialchars($s['patient_nom'] . ' ' . $s['patient_prenom']); ?>
                                <?php if($isAlert): ?>
                                    <span class="alert-badge"><i class="fas fa-triangle-exclamation"></i> ALERTE SANTÉ</span>
                                <?php endif; ?>
                            </div>
                            <div class="s-date"><i class="fas fa-calendar-alt"></i> Prescrit le <?php echo $s['date_suivi']; ?></div>
                        </div>
                        <span class="badge" style="background:#fff; border:1px solid var(--border); padding:6px 12px; border-radius:10px; font-size:0.7rem; font-weight:750;">ID: #<?php echo $s['id_suivie']; ?></span>
                    </div>

                    <div class="row-metrics">
                        <div class="metric-box"><i class="fas fa-weight-scale"></i><div><div class="m-lbl">Poids</div><div class="m-val"><?php echo $s['poids'] ? $s['poids'].' kg' : '-'; ?></div></div></div>
                        <div class="metric-box <?php echo $isAlert ? 'metric-alert' : ''; ?>"><i class="fas fa-droplet"></i><div><div class="m-lbl">Tension</div><div class="m-val"><?php echo htmlspecialchars($s['tension'] ?? '-'); ?></div></div></div>
                    </div>

                    <div class="s-body">
                        <div class="s-block">
                            <div class="s-block-label">Consigne Médicale / État</div>
                            <div class="s-block-text"><?php echo nl2br(htmlspecialchars($s['etat_general'])); ?></div>
                        </div>
                        <div class="s-block">
                            <div class="s-block-label">Analyses Prescrites</div>
                            <div class="s-block-text"><?php echo nl2br(htmlspecialchars($s['analyses_a_realiser'] ?? 'Aucune')); ?></div>
                        </div>
                        
                        <?php if(!empty($s['resultat_analyses'])): ?>
                        <div class="s-block highlight">
                            <div class="s-block-label" style="color:#b45309;"><i class="fas fa-flask"></i> Résultats Postulés par le Patient</div>
                            <div class="s-block-text" style="color:#78350f; font-weight:600;"><?php echo nl2br(htmlspecialchars($s['resultat_analyses'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="actions-row">
                        <button class="btn-pdf" onclick="exportSuiviePDF(<?php echo htmlspecialchars(json_encode($s)); ?>)">
                            <i class="fas fa-file-pdf"></i> Exporter PDF
                        </button>

                        <button class="btn-pdf" style="background:#3b82f6;" onclick='prepareEdit(<?php echo json_encode($s); ?>)'>
                            <i class="fas fa-edit"></i> Modifier
                        </button>

                        <form method="POST" onsubmit="return confirm('Confirmer la suppression ?');" style="margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id_suivie" value="<?php echo $s['id_suivie']; ?>">
                            <button type="submit" class="btn-delete"><i class="fas fa-trash-can"></i> Supprimer ce dossier</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<script>
// --- Tri et Filtre Combinés ---
const searchInput = document.getElementById('searchInput');
const sortSelect = document.getElementById('sortSelect');
const sortOrderBtn = document.getElementById('sortOrderBtn');
let sortDirection = -1; // -1 pour Descendant par défaut (plus récent)

searchInput.addEventListener('input', filterAndSort);
sortSelect.addEventListener('change', filterAndSort);
sortOrderBtn.addEventListener('click', () => {
    sortDirection *= -1;
    sortOrderBtn.querySelector('i').className = sortDirection === 1 ? 'fas fa-sort-amount-up' : 'fas fa-sort-amount-down';
    filterAndSort();
});

function filterAndSort() {
    const term = searchInput.value.toLowerCase();
    const sortBy = sortSelect.value;
    const container = document.getElementById('suivieList');
    const items = Array.from(container.querySelectorAll('.s-item'));

    // 1. Filtrer
    items.forEach(item => {
        const content = item.getAttribute('data-search');
        item.style.display = content.includes(term) ? 'block' : 'none';
    });

    // 2. Trier
    items.sort((a, b) => {
        let valA = a.getAttribute(`data-${sortBy}`);
        let valB = b.getAttribute(`data-${sortBy}`);

        if (sortBy === 'id' || sortBy === 'poids') { 
            valA = parseFloat(valA); 
            valB = parseFloat(valB); 
        }

        if (valA < valB) return -1 * sortDirection;
        if (valA > valB) return 1 * sortDirection;
        return 0;
    });

    // 3. Réorganiser le DOM
    items.forEach(item => container.appendChild(item));
}

// --- Export PDF ---
function exportSuiviePDF(data) {
    const element = document.createElement('div');
    element.style.padding = '40px';
    element.style.fontFamily = "'Outfit', sans-serif";
    
    const verifyUrl = `${window.location.origin}${window.location.pathname}?controller=verify&action=suivie&id=${data.id_suivie}`;
    const qrImageUrl = `https://quickchart.io/qr?text=${encodeURIComponent(verifyUrl)}&size=100`;

    // On charge les images (QR + Signature)
    const qrImg = new Image();
    const sigImg = new Image();
    let loaded = 0;
    const total = <?php echo $signature ? '2' : '1'; ?>;

    function checkLoaded() {
        loaded++;
        if (loaded === total) {
            element.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #10b981; padding-bottom:20px; margin-bottom:30px;">
                    <div>
                        <h1 style="color:#10b981; margin:0; font-size:24px;">GlobalHealth Connect</h1>
                        <p style="margin:5px 0 0; color:#64748b; font-size:12px;">Plateforme de Suivi Médical</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="margin:0; font-weight:bold;">Rapport de Suivi Médical</p>
                        <p style="margin:5px 0 0; color:#64748b; font-size:12px;">ID: #SUI-${data.id_suivie}</p>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px; margin-bottom:40px;">
                    <div>
                        <p style="font-size:10px; text-transform:uppercase; color:#64748b; margin-bottom:5px; font-weight:bold;">Patient</p>
                        <p style="font-size:16px; font-weight:bold; margin:0;">${data.patient_nom} ${data.patient_prenom}</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:10px; text-transform:uppercase; color:#64748b; margin-bottom:5px; font-weight:bold;">Date du rapport</p>
                        <p style="font-size:16px; font-weight:bold; margin:0;">${data.date_suivi}</p>
                    </div>
                </div>

                <div style="display:flex; gap:20px; margin-bottom:30px;">
                    <div style="flex:1; background:#f0fdf4; padding:15px; border-radius:10px; border:1px solid #dcfce7;">
                        <p style="font-size:10px; text-transform:uppercase; color:#16a34a; margin:0 0 5px; font-weight:bold;">Poids</p>
                        <p style="font-size:18px; font-weight:bold; margin:0;">${data.poids ? data.poids + ' kg' : '-'}</p>
                    </div>
                    <div style="flex:1; background:#f0fdf4; padding:15px; border-radius:10px; border:1px solid #dcfce7;">
                        <p style="font-size:10px; text-transform:uppercase; color:#16a34a; margin:0 0 5px; font-weight:bold;">Tension</p>
                        <p style="font-size:18px; font-weight:bold; margin:0;">${data.tension || '-'}</p>
                    </div>
                </div>

                <div style="margin-bottom:30px;">
                    <p style="font-size:10px; text-transform:uppercase; color:#10b981; margin-bottom:10px; font-weight:bold; border-bottom:1px solid #e2e8f0; padding-bottom:5px;">État Général & Observations</p>
                    <p style="font-size:14px; line-height:1.6; color:#1e293b; margin:0;">${data.etat_general.replace(/\n/g, '<br>')}</p>
                </div>

                <div style="margin-bottom:30px;">
                    <p style="font-size:10px; text-transform:uppercase; color:#10b981; margin-bottom:10px; font-weight:bold; border-bottom:1px solid #e2e8f0; padding-bottom:5px;">Analyses Prescrites</p>
                    <p style="font-size:14px; line-height:1.6; color:#1e293b; margin:0;">${(data.analyses_a_realiser || 'Aucune').replace(/\n/g, '<br>')}</p>
                </div>

                ${data.resultat_analyses ? `
                <div style="margin-bottom:30px; background:#fffcf0; padding:20px; border-radius:12px; border:1px solid #fde68a;">
                    <p style="font-size:10px; text-transform:uppercase; color:#b45309; margin-bottom:10px; font-weight:bold;">Résultats d'Analyses (Patient)</p>
                    <p style="font-size:14px; line-height:1.6; color:#78350f; margin:0;">${data.resultat_analyses.replace(/\n/g, '<br>')}</p>
                </div>
                ` : ''}

                <div style="margin-top:40px; border-top:1px solid #e2e8f0; padding-top:20px; display:flex; justify-content:space-between; align-items:flex-end;">
                    <div style="color:#94a3b8; font-size:10px; flex:1;">
                        <p>Ce document est un rapport médical généré par GlobalHealth Connect.</p>
                        <p>Prochain rendez-vous prévu : ${data.prochain_rdv || 'À définir'}</p>
                        <p style="margin-top:10px;">Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                        <p style="margin-top:5px; color:#10b981; font-weight:bold;">Scan QR pour vérifier l'authenticité</p>
                    </div>
                    <div style="display:flex; gap:20px; align-items:flex-end;">
                        <?php if ($signature): ?>
                        <div style="text-align:center;">
                            <p style="font-size:9px; color:#64748b; margin-bottom:5px; text-transform:uppercase; font-weight:bold;">Signature du Médecin</p>
                            <img src="uploads/signatures/<?php echo $signature; ?>" style="height:60px; object-fit:contain;">
                        </div>
                        <?php endif; ?>
                        <div style="text-align:right;">
                            <img src="${qrImageUrl}" style="width:70px; height:70px; border:1px solid #e2e8f0; padding:5px; border-radius:8px;">
                        </div>
                    </div>
                </div>
            `;

            const opt = {
                margin:       10,
                filename:     `Suivi_${data.patient_nom}_${data.date_suivi}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save();
        }
    }

    qrImg.crossOrigin = "Anonymous";
    qrImg.onload = checkLoaded;
    qrImg.onerror = checkLoaded;
    qrImg.src = qrImageUrl;

    <?php if ($signature): ?>
    sigImg.onload = checkLoaded;
    sigImg.onerror = checkLoaded;
    sigImg.src = "uploads/signatures/<?php echo $signature; ?>";
    <?php endif; ?>
}

// --- Validation Formulaire ---
function validateSuivieForm(event) {
    const fields = {
        id_consultation: { input: document.getElementById('id_consultation'), err: document.getElementById('err_id_consultation') },
        date_suivi:      { input: document.getElementById('date_suivi'),      err: document.getElementById('err_date_suivi') },
        poids:           { input: document.getElementById('poids'),           err: document.getElementById('err_poids') },
        tension:         { input: document.getElementById('tension'),         err: document.getElementById('err_tension') },
        etat_general:    { input: document.getElementById('etat_general'),    err: document.getElementById('err_etat_general') }
    };

    let hasError = false;

    for (let key in fields) {
        fields[key].input.classList.remove('is-invalid');
        fields[key].err.style.display = 'none';
    }

    if (fields.id_consultation.input.value === "") {
        showError(fields.id_consultation, "Veuillez sélectionner une consultation.");
        hasError = true;
    }
    if (fields.date_suivi.input.value === "") {
        showError(fields.date_suivi, "La date est obligatoire.");
        hasError = true;
    }
    if (fields.poids.input.value !== "") {
        const p = parseFloat(fields.poids.input.value);
        if (isNaN(p) || p <= 0 || p > 500) {
            showError(fields.poids, "Le poids doit être entre 1 et 500 kg.");
            hasError = true;
        }
    }
    if (fields.tension.input.value !== "") {
        if (!/^\d{2,3}\/\d{2,3}$/.test(fields.tension.input.value)) {
            showError(fields.tension, "Format invalide (ex: 120/80).");
            hasError = true;
        }
    }
    if (fields.etat_general.input.value.trim().length < 5) {
        showError(fields.etat_general, "Veuillez saisir au moins 5 caractères.");
        hasError = true;
    }

    if (hasError) {
        event.preventDefault();
        return false;
    }
    return true;
}

function showError(fieldObj, message) {
    fieldObj.input.classList.add('is-invalid');
    fieldObj.err.textContent = message;
    fieldObj.err.style.display = 'block';
}

document.getElementById('suivieForm').onsubmit = validateSuivieForm;

// --- Fonctions de Modification ---
function prepareEdit(data) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formId').value = data.id_suivie;
    
    document.getElementById('id_consultation').value = data.id_consultation;
    document.getElementById('date_suivi').value = data.date_suivi;
    document.getElementById('poids').value = data.poids || '';
    document.getElementById('tension').value = data.tension || '';
    document.getElementById('etat_general').value = data.etat_general;
    
    // Pour les champs qui n'ont pas d'ID explicite mais dont on a besoin
    const form = document.getElementById('suivieForm');
    form.querySelector('[name="analyses_a_realiser"]').value = data.analyses_a_realiser || '';
    form.querySelector('[name="prochain_rdv"]').value = data.prochain_rdv || '';
    
    document.getElementById('editModeIndicator').style.display = 'block';
    document.getElementById('submitBtn').textContent = 'Mettre à jour la Prescription';
    document.getElementById('submitBtn').style.background = '#3b82f6';
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('suivieForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('formId').value = '';
    document.getElementById('editModeIndicator').style.display = 'none';
    document.getElementById('submitBtn').textContent = 'Valider la Prescription';
    document.getElementById('submitBtn').style.background = 'var(--primary)';
}
</script>

</body>
</html>
