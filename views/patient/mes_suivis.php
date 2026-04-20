<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth — Mon Suivi Santé</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0d9488;
            --primary-soft: #2dd4bf;
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
        .nav-item:hover, .nav-item.active { background:var(--sidebar-item-hover); color:#fff; }
        .nav-item.active { background:var(--primary); font-weight:700; }

        .logout { margin-top:auto; display:flex; align-items:center; gap:12px; text-decoration:none; color:#fca5a5; font-size:0.9rem; font-weight:600; padding:12px 14px; border-radius:12px; border:1px solid rgba(252, 165, 165, 0.2); }

        /* MAIN */
        .main-content { flex:1; margin-left:var(--sidebar-w); padding:40px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; }
        header h1 { font-size:1.6rem; font-weight:800; }

        .s-container { display:flex; flex-direction:column; gap:32px; }
        .s-card { background:var(--card-bg); border-radius:28px; border:1px solid var(--border); overflow:hidden; }
        .s-card-header { background:linear-gradient(to right, #f8fafc, #fff); padding:24px 32px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
        .s-card-content { padding:32px; }

        .grid-suivi { display:grid; grid-template-columns: 1fr 1fr; gap:32px; }
        .block-info { background:var(--bg-body); padding:24px; border-radius:20px; border:1px solid var(--border); height:100%; }
        .label-info { font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; margin-bottom:12px; display:flex; align-items:center; gap:8px; }
        .text-info { font-size:0.95rem; line-height:1.6; }

        .form-update { background:#f0fdfa; border:1.5px solid #99f6e4; padding:24px; border-radius:24px; margin-top:24px; }
        .form-update h4 { font-size:1rem; font-weight:700; color:#0f766e; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
        .f-row { display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px; }
        .f-group { margin-bottom:16px; }
        .f-label { display:block; font-size:0.75rem; font-weight:700; color:var(--text-muted); margin-bottom:8px; }
        .f-input { width:100%; border:1.5px solid #ccfbf1; border-radius:12px; padding:12px; font-family:inherit; outline:none; }
        .f-input:focus { border-color:var(--primary); }
        .btn-update { width:100%; background:var(--primary); color:#fff; border:none; padding:14px; border-radius:12px; font-weight:700; cursor:pointer; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-heart-pulse"></i></div>
        <div><strong>GlobalHealth</strong><small style="color:rgba(255,255,255,0.4); font-size:0.7rem; display:block;">Patient Portal</small></div>
    </div>

    <div class="nav-label">Mon Suivi</div>
    <a href="?controller=patient" class="nav-item"><i class="fas fa-home"></i>Dashboard</a>
    <a href="?controller=patient&action=consultations" class="nav-item"><i class="fas fa-file-medical-alt"></i>Mes Consultations</a>
    <a href="?controller=patient&action=suivis" class="nav-item active"><i class="fas fa-chart-line"></i>Mon Suivi Santé</a>

    <a href="?controller=auth&action=logout" class="logout"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
</aside>

<main class="main-content">
    <header>
        <h1>Mes Dossiers de Suivi</h1>
    </header>

    <div class="s-container">
        <?php foreach ($suivis as $s): ?>
        <div class="s-card">
            <div class="s-card-header">
                <div>
                    <span style="font-size:0.7rem; font-weight:800; color:var(--primary); text-transform:uppercase;">Dossier de Suivi</span>
                    <h3 style="font-size:1.1rem; font-weight:800;">Dr. <?php echo htmlspecialchars($s['medecin_nom']); ?></h3>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.8rem; color:var(--text-muted);">Initié le</div>
                    <div style="font-weight:700;"><?php echo $s['date_suivi']; ?></div>
                </div>
            </div>
            
            <div class="s-card-content">
                <div class="grid-suivi">
                    <div class="block-info">
                        <div class="label-info"><i class="fas fa-comment-medical"></i> Consignes du Médecin</div>
                        <div class="text-info"><?php echo nl2br(htmlspecialchars($s['etat_general'])); ?></div>
                        
                        <div style="margin-top:20px; display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                            <div>
                                <div class="label-info">Régime</div>
                                <div style="font-weight:600; font-size:0.9rem;"><?php echo $s['regime_alimentaire'] ?: 'Libre'; ?></div>
                            </div>
                            <div>
                                <div class="label-info">Activité</div>
                                <div style="font-weight:600; font-size:0.9rem;"><?php echo $s['activite_physique'] ?: 'Libre'; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="block-info">
                        <div class="label-info"><i class="fas fa-flask"></i> Examens & Analyses à faire</div>
                        <div class="text-info" style="font-weight:600; color:#0f766e;"><?php echo nl2br(htmlspecialchars($s['analyses_a_realiser'] ?: 'Aucune analyse prescrite.')); ?></div>
                        
                        <?php if($s['prochain_rdv']): ?>
                            <div style="margin-top:20px; padding:12px; background:#fff; border-radius:12px; border:1px solid #99f6e4;">
                                <div class="label-info" style="margin-bottom:4px;">Prochain RDV</div>
                                <div style="font-weight:800; color:#0d9488;"><?php echo $s['prochain_rdv']; ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-update">
                    <h4><i class="fas fa-pen-to-square"></i> Mettre à jour mes informations</h4>
                    <form method="POST" action="?controller=patient&action=update_suivi">
                        <input type="hidden" name="id_suivie" value="<?php echo $s['id_suivie']; ?>">
                        
                        <div class="f-row">
                            <div class="f-group">
                                <label class="f-label">Mon Poids (kg)</label>
                                <input type="number" step="0.1" name="poids" class="f-input" value="<?php echo $s['poids']; ?>" placeholder="Ex: 75.5">
                            </div>
                            <div class="f-group">
                                <label class="f-label">Ma Tension</label>
                                <input type="text" name="tension" class="f-input" value="<?php echo htmlspecialchars($s['tension']); ?>" placeholder="Ex: 12/8">
                            </div>
                        </div>
                        
                        <div class="f-group">
                            <label class="f-label">Résultats d'Analyses / Remarques</label>
                            <textarea name="resultat_analyses" class="f-input" rows="3" placeholder="Saisissez ici vos résultats d'analyses ou comment vous vous sentez..."><?php echo htmlspecialchars($s['resultat_analyses'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn-update"><i class="fas fa-paper-plane"></i> Envoyer au Docteur</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
