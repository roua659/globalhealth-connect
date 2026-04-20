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
        .s-item { background:var(--card-bg); border-radius:24px; padding:28px; border:1px solid var(--border); border-left:6px solid #10b981; margin-bottom:24px; }
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

        .btn-delete { background:transparent; border:none; color:#ef4444; cursor:pointer; font-size:0.8rem; font-weight:700; margin-top:20px; display:flex; align-items:center; gap:8px; }

        .alert { padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600; display:flex; align-items:center; gap:10px; }
        .alert-success { background:#f0fdf4; color:#16a34a; border: 1px solid #dcfce7; }
        .alert-error { background:#fef2f2; color:#ef4444; border: 1px solid #fee2e2; }
    </style>
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
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="f-group">
                    <label class="f-label">Patient</label>
                    <select name="id_patient" class="f-control" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($patients as $p): ?>
                            <option value="<?php echo $p['id_patient']; ?>"><?php echo htmlspecialchars($p['nom'].' '.$p['prenom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="f-group">
                    <label class="f-label">Date du Suivi</label>
                    <input type="date" name="date_suivi" class="f-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="f-group">
                    <label class="f-label">Consultation liée (Optionnel)</label>
                    <select name="id_consultation" class="f-control">
                        <option value="">— Aucune —</option>
                        <?php foreach ($consultations as $c): ?>
                            <option value="<?php echo $c['id_consultation']; ?>">
                                <?php echo htmlspecialchars($c['patient_nom'] . ' ' . $c['patient_prenom']); ?> 
                                (ID: #<?php echo $c['id_consultation']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="f-group">
                    <label class="f-label">État Général & Objectifs</label>
                    <textarea name="etat_general" class="f-control" rows="3" required></textarea>
                </div>

                <div class="f-group">
                    <label class="f-label">Analyses Médicales à Réaliser</label>
                    <textarea name="analyses_a_realiser" class="f-control" rows="2"></textarea>
                </div>

                <div class="f-group">
                    <label class="f-label">Échéance Prochain RDV</label>
                    <input type="date" name="prochain_rdv" class="f-control">
                </div>

                <button type="submit" class="btn-primary">Valider la Prescription</button>
            </form>
        </div>

        <div class="list-card">
            <h3 style="margin-bottom:24px; font-weight:700;">Dossiers de Suivi Actifs</h3>
            <?php foreach ($suivis as $s): ?>
            <div class="s-item">
                <div class="s-header">
                    <div>
                        <div class="s-patient"><?php echo htmlspecialchars($s['patient_nom'] . ' ' . $s['patient_prenom']); ?></div>
                        <div class="s-date"><i class="fas fa-calendar-alt"></i> Prescrit le <?php echo $s['date_suivi']; ?></div>
                    </div>
                </div>

                <div class="row-metrics">
                    <div class="metric-box"><i class="fas fa-weight-scale"></i><div><div class="m-lbl">Poids</div><div class="m-val"><?php echo $s['poids'] ? $s['poids'].' kg' : '-'; ?></div></div></div>
                    <div class="metric-box"><i class="fas fa-droplet"></i><div><div class="m-lbl">Tension</div><div class="m-val"><?php echo htmlspecialchars($s['tension'] ?? '-'); ?></div></div></div>
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

                <form method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_suivie" value="<?php echo $s['id_suivie']; ?>">
                    <button type="submit" class="btn-delete"><i class="fas fa-trash-can"></i> Supprimer ce dossier</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

</body>
</html>
