<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth — Mes Consultations</title>
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
        .nav-item i { width:20px; text-align:center; }
        .nav-item:hover, .nav-item.active { background:var(--sidebar-item-hover); color:#fff; }
        .nav-item.active { background:var(--primary); font-weight:700; }

        .logout { margin-top:auto; display:flex; align-items:center; gap:12px; text-decoration:none; color:#fca5a5; font-size:0.9rem; font-weight:600; padding:12px 14px; border-radius:12px; border:1px solid rgba(252, 165, 165, 0.2); }

        /* MAIN */
        .main-content { flex:1; margin-left:var(--sidebar-w); padding:40px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; }
        header h1 { font-size:1.6rem; font-weight:800; }
        
        /* CONTENT */
        .cons-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap:24px; }
        .cons-card { background:var(--card-bg); padding:32px; border-radius:24px; border:1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .cons-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; }
        .cons-meta { display:flex; align-items:center; gap:12px; }
        .dr-icon { width:48px; height:48px; border-radius:14px; background:var(--bg-body); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
        .dr-name { font-weight:800; font-size:1.1rem; color:#0f172a; }
        .dr-date { font-size:0.85rem; color:var(--text-muted); font-weight:500; }

        .cons-body { display:grid; grid-template-columns: 1fr; gap:20px; }
        .cons-block { background:var(--bg-body); padding:20px; border-radius:16px; border:1px solid var(--border); }
        .cons-label { font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em; margin-bottom:10px; display:flex; align-items:center; gap:8px; }
        .cons-label i { color:var(--primary); }
        .cons-text { font-size:0.95rem; line-height:1.6; color:#1e293b; }
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
    <a href="?controller=patient&action=consultations" class="nav-item active"><i class="fas fa-file-medical-alt"></i>Mes Consultations</a>
    <a href="?controller=patient&action=suivis" class="nav-item"><i class="fas fa-chart-line"></i>Mon Suivi Santé</a>

    <a href="?controller=auth&action=logout" class="logout"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
</aside>

<main class="main-content">
    <header>
        <h1>Historique des Consultations</h1>
    </header>

    <div class="cons-grid">
        <?php if(empty($consultations)): ?>
            <div style="grid-column: 1/-1; text-align:center; padding:100px; background:#fff; border-radius:24px; border:1px dashed var(--border);">
                <i class="fas fa-folder-open" style="font-size:3rem; color:var(--border); margin-bottom:20px; display:block;"></i>
                <p style="color:var(--text-muted); font-weight:600;">Aucune consultation enregistrée pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach($consultations as $c): ?>
            <div class="cons-card">
                <div class="cons-header">
                    <div class="cons-meta">
                        <div class="dr-icon"><i class="fas fa-user-md"></i></div>
                        <div>
                            <div class="dr-name">Dr. <?php echo htmlspecialchars($c['medecin_nom'] . ' ' . $c['medecin_prenom']); ?></div>
                            <div class="dr-date">Le <?php echo $c['date_rdv']; ?> à <?php echo $c['heure_rdv']; ?></div>
                        </div>
                    </div>
                    <div style="font-size:0.7rem; font-weight:700; color:var(--text-muted); background:var(--bg-body); padding:6px 12px; border-radius:10px;">REF: #<?php echo $c['id_consultation']; ?></div>
                </div>

                <div class="cons-body">
                    <div class="cons-block">
                        <div class="cons-label"><i class="fas fa-stethoscope"></i> Diagnostic</div>
                        <div class="cons-text"><?php echo nl2br(htmlspecialchars($c['diagnostic'])); ?></div>
                    </div>
                    <div class="cons-block">
                        <div class="cons-label"><i class="fas fa-prescription"></i> Traitement Prescrit</div>
                        <div class="cons-text" style="font-weight:600; color:var(--primary);"><?php echo nl2br(htmlspecialchars($c['traitement'])); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
