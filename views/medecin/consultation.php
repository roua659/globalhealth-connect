<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth — Consultations (Médecin)</title>
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

        /* TABLE / LIST */
        .list-card { background:var(--card-bg); border-radius:24px; border:1px solid var(--border); padding:32px; }
        .cons-item { padding:24px; background:var(--bg-body); border-radius:20px; margin-bottom:20px; border:1px solid var(--border); transition: transform 0.2s; }
        .cons-item:hover { transform: translateX(8px); }
        .cons-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; }
        .cons-patient { font-size:1.05rem; font-weight:800; }
        .cons-date { font-size:0.85rem; color:var(--text-muted); margin-top:4px; }
        .cons-body { display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px; padding-top:20px; border-top:1px solid var(--border); }
        .cons-label { font-size:0.65rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px; }
        .cons-text { font-size:0.9rem; line-height:1.5; }

        .btn-delete { background:transparent; border:none; color:#ef4444; cursor:pointer; font-size:0.8rem; font-weight:700; }
        .btn-pdf { background:#4f46e5; color:#fff; border:none; padding:8px 16px; border-radius:10px; font-size:0.75rem; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px; transition: all 0.2s; }
        .btn-pdf:hover { background:#4338ca; transform:scale(1.05); }

        .actions-row { display:flex; gap:16px; align-items:center; margin-top:16px; padding-top:16px; border-top:1px dashed var(--border); }

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
    <a href="?controller=medecin&action=consultation" class="nav-item active"><i class="fas fa-notes-medical"></i>Consultations</a>
    <a href="?controller=medecin&action=suivie" class="nav-item"><i class="fas fa-heart-pulse"></i>Patient Follow-ups</a>

    <a href="?controller=auth&action=logout" class="logout"><i class="fas fa-door-open"></i>Terminer Session</a>
</aside>

<main class="main-content">
    <header>
        <h1>Gestion des Consultations</h1>
    </header>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'triangle-exclamation'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="page-split">
        <div class="form-card">
            <h3 style="margin-bottom:24px; font-weight:700;">Nouvelle Consultation</h3>
            <form method="POST" id="consultationForm" novalidate>
                <input type="hidden" name="action" value="add">
                
                <div class="f-group">
                    <label class="f-label">Rendez-vous associé <span style="color:#ef4444;">*</span></label>
                    <select name="id_rdv" id="id_rdv" class="f-control">
                        <option value="">— Sélectionner un RDV —</option>
                        <?php foreach ($rendezvous as $r): ?>
                            <option value="<?php echo $r['id_rdv']; ?>">
                                <?php echo htmlspecialchars($r['patient_nom'].' '.$r['patient_prenom']); ?> 
                                (<?php echo $r['date_rdv']; ?> — <?php echo htmlspecialchars($r['motif']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-msg" id="err_id_rdv">Veuillez choisir un rendez-vous.</div>
                </div>

                <div class="f-group">
                    <label class="f-label">Diagnostic <span style="color:#ef4444;">*</span></label>
                    <textarea name="diagnostic" id="diagnostic" class="f-control" rows="3"></textarea>
                    <div class="error-msg" id="err_diagnostic">Diagnostic obligatoire (min 3 car.).</div>
                </div>

                <div class="f-group">
                    <label class="f-label">Traitement prescrit <span style="color:#ef4444;">*</span></label>
                    <textarea name="traitement" id="traitement" class="f-control" rows="3"></textarea>
                    <div class="error-msg" id="err_traitement">Traitement obligatoire (min 3 car.).</div>
                </div>

                <div class="f-group">
                    <label class="f-label">Notes privées</label>
                    <textarea name="notes" class="f-control" rows="2"></textarea>
                </div>

                <button type="submit" class="btn-primary">Enregistrer la Consultation</button>
            </form>
        </div>

        <div class="list-card">
            <h3 style="margin-bottom:24px; font-weight:700;">Journal des Consultations</h3>
            
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Rechercher par patient, ID, diagnostic, date...">
            </div>

            <div id="consultationList">
                <?php foreach ($consultations as $c): ?>
                <div class="cons-item" data-search="<?php echo htmlspecialchars(strtolower($c['patient_nom'].' '.$c['patient_prenom'].' '.$c['id_consultation'].' '.$c['diagnostic'].' '.$c['date_rdv'])); ?>">
                    <div class="cons-header">
                        <div>
                            <div class="cons-patient"><?php echo htmlspecialchars($c['patient_nom'] . ' ' . $c['patient_prenom']); ?></div>
                            <div class="cons-date">Rendez-vous du <?php echo $c['date_rdv']; ?> à <?php echo $c['heure_rdv']; ?></div>
                        </div>
                        <span class="badge" style="background:#fff; border:1px solid var(--border); padding:6px 12px; border-radius:10px; font-size:0.7rem; font-weight:750;">ID: #<?php echo $c['id_consultation']; ?></span>
                    </div>
                    
                    <div class="cons-body">
                        <div>
                            <div class="cons-label">Diagnostic</div>
                            <div class="cons-text"><?php echo nl2br(htmlspecialchars($c['diagnostic'])); ?></div>
                        </div>
                        <div>
                            <div class="cons-label">Traitement</div>
                            <div class="cons-text"><?php echo nl2br(htmlspecialchars($c['traitement'])); ?></div>
                        </div>
                    </div>

                    <div class="actions-row">
                        <button class="btn-pdf" onclick="exportConsultationPDF(<?php echo htmlspecialchars(json_encode($c)); ?>)">
                            <i class="fas fa-file-pdf"></i> Exporter PDF
                        </button>

                        <form method="POST" onsubmit="return confirm('Supprimer définitivement ?');" style="margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id_consultation" value="<?php echo $c['id_consultation']; ?>">
                            <button type="submit" class="btn-delete"><i class="fas fa-trash-can"></i> Supprimer</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<script>
// --- Recherche en temps réel ---
document.getElementById('searchInput').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.cons-item');
    
    items.forEach(item => {
        const content = item.getAttribute('data-search');
        if (content.includes(term)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// --- Export PDF ---
function exportConsultationPDF(data) {
    const element = document.createElement('div');
    element.style.padding = '40px';
    element.style.fontFamily = "'Outfit', sans-serif";
    
    element.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #2563eb; padding-bottom:20px; margin-bottom:30px;">
            <div>
                <h1 style="color:#2563eb; margin:0; font-size:24px;">GlobalHealth Connect</h1>
                <p style="margin:5px 0 0; color:#64748b; font-size:12px;">Plateforme de Suivi Médical</p>
            </div>
            <div style="text-align:right;">
                <p style="margin:0; font-weight:bold;">Compte-rendu de Consultation</p>
                <p style="margin:5px 0 0; color:#64748b; font-size:12px;">ID: #CONS-${data.id_consultation}</p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px; margin-bottom:40px;">
            <div>
                <p style="font-size:10px; text-transform:uppercase; color:#64748b; margin-bottom:5px; font-weight:bold;">Patient</p>
                <p style="font-size:16px; font-weight:bold; margin:0;">${data.patient_nom} ${data.patient_prenom}</p>
            </div>
            <div style="text-align:right;">
                <p style="font-size:10px; text-transform:uppercase; color:#64748b; margin-bottom:5px; font-weight:bold;">Date de consultation</p>
                <p style="font-size:16px; font-weight:bold; margin:0;">${data.date_rdv}</p>
            </div>
        </div>

        <div style="margin-bottom:30px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #e2e8f0;">
            <p style="font-size:10px; text-transform:uppercase; color:#2563eb; margin-bottom:10px; font-weight:bold;">Diagnostic</p>
            <p style="font-size:14px; line-height:1.6; color:#1e293b; margin:0;">${data.diagnostic.replace(/\n/g, '<br>')}</p>
        </div>

        <div style="margin-bottom:30px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #e2e8f0;">
            <p style="font-size:10px; text-transform:uppercase; color:#2563eb; margin-bottom:10px; font-weight:bold;">Traitement Prescrit</p>
            <p style="font-size:14px; line-height:1.6; color:#1e293b; margin:0;">${data.traitement.replace(/\n/g, '<br>')}</p>
        </div>

        ${data.notes ? `
        <div style="margin-bottom:30px;">
            <p style="font-size:10px; text-transform:uppercase; color:#64748b; margin-bottom:10px; font-weight:bold;">Notes additionnelles</p>
            <p style="font-size:13px; line-height:1.6; color:#475569; margin:0;">${data.notes.replace(/\n/g, '<br>')}</p>
        </div>
        ` : ''}

        <div style="margin-top:60px; border-top:1px solid #e2e8f0; pt:20px; text-align:center; color:#94a3b8; font-size:10px;">
            <p>Ce document est un compte-rendu médical officiel généré par GlobalHealth Connect.</p>
            <p>Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
        </div>
    `;

    const opt = {
        margin:       10,
        filename:     `Consultation_${data.patient_nom}_${data.date_rdv}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save();
}

// --- Validation Formulaire ---
document.getElementById('consultationForm').addEventListener('submit', function(e) {
    const fields = {
        id_rdv:     { input: document.getElementById('id_rdv'),     err: document.getElementById('err_id_rdv') },
        diagnostic: { input: document.getElementById('diagnostic'), err: document.getElementById('err_diagnostic') },
        traitement: { input: document.getElementById('traitement'), err: document.getElementById('err_traitement') }
    };

    let hasError = false;

    // Reset
    for(let k in fields) {
        fields[k].input.classList.remove('is-invalid');
        fields[k].err.style.display = 'none';
    }

    if (!fields.id_rdv.input.value) {
        showErr(fields.id_rdv, "Veuillez sélectionner un rendez-vous.");
        hasError = true;
    }
    if (fields.diagnostic.input.value.trim().length < 3) {
        showErr(fields.diagnostic, "Le diagnostic doit faire au moins 3 caractères.");
        hasError = true;
    }
    if (fields.traitement.input.value.trim().length < 3) {
        showErr(fields.traitement, "Le traitement doit faire au moins 3 caractères.");
        hasError = true;
    }

    if (hasError) { e.preventDefault(); }
});

function showErr(f, m) {
    f.input.classList.add('is-invalid');
    f.err.textContent = m;
    f.err.style.display = 'block';
}
</script>

</body>
</html>
