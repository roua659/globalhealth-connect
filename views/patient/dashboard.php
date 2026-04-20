<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth — Mon Espace Santé</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0d9488; /* Teal / Turquoise */
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
        .nav-item.active { background:var(--primary); font-weight:700; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); }

        .logout { margin-top:auto; display:flex; align-items:center; gap:12px; text-decoration:none; color:#fca5a5; font-size:0.9rem; font-weight:600; padding:12px 14px; border-radius:12px; border:1px solid rgba(252, 165, 165, 0.2); }

        /* MAIN CONTENT */
        .main-content { flex:1; margin-left:var(--sidebar-w); padding:40px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; }
        header h1 { font-size:1.6rem; font-weight:800; }
        .user-chip { display:flex; align-items:center; gap:12px; background:var(--card-bg); padding:8px 18px 8px 8px; border-radius:50px; border:1px solid var(--border); }
        .avatar { width:34px; height:34px; background:var(--primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; }

        /* HERO SECTION */
        .hero { background: linear-gradient(135deg, var(--primary), #0f766e); padding:40px; border-radius:32px; color:#fff; margin-bottom:40px; position:relative; overflow:hidden; }
        .hero h2 { font-size:2rem; font-weight:800; margin-bottom:8px; }
        .hero p { opacity:0.9; font-size:1rem; max-width:500px; }
        .hero-decor { position:absolute; right:-20px; bottom:-20px; font-size:12rem; opacity:0.1; transform:rotate(-15deg); }

        /* GRID STATS */
        .stats-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:24px; margin-bottom:40px; }
        .stat-card { background:var(--card-bg); padding:32px; border-radius:24px; border:1px solid var(--border); }
        .stat-card h3 { font-size:0.75rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
        .stat-card h3 i { color:var(--primary); }
        .stat-val { font-size:2rem; font-weight:800; color:#0f172a; margin-bottom:4px; }
        .stat-desc { font-size:0.85rem; color:var(--text-muted); }

        /* LISTS */
        .section-card { background:var(--card-bg); padding:32px; border-radius:24px; border:1px solid var(--border); margin-bottom:40px; }
        .section-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .section-title { font-size:1.1rem; font-weight:700; display:flex; align-items:center; gap:10px; }
        .section-title i { color:var(--primary); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-heart-pulse"></i></div>
        <div><strong>GlobalHealth</strong><small style="color:rgba(255,255,255,0.4); font-size:0.7rem; display:block;">Patient Portal</small></div>
    </div>

    <div class="nav-label">Mon Suivi</div>
    <a href="?controller=patient" class="nav-item active"><i class="fas fa-home"></i>Dashboard</a>
    <a href="?controller=patient&action=consultations" class="nav-item"><i class="fas fa-file-medical-alt"></i>Mes Consultations</a>
    <a href="?controller=patient&action=suivis" class="nav-item"><i class="fas fa-chart-line"></i>Mon Suivi Santé</a>

    <a href="?controller=auth&action=logout" class="logout"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
</aside>

<main class="main-content">
    <header>
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Patient'); ?></h1>
        <div class="user-chip">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'P', 0, 1)); ?></div>
            <span style="font-weight:600; font-size:0.9rem;">Espace Patient</span>
        </div>
    </header>

    <div class="hero">
        <i class="fas fa-shield-heart hero-decor"></i>
        <h2>Votre santé, notre priorité.</h2>
        <p>Retrouvez ici tout votre historique médical, vos prescriptions et le suivi de vos constantes en temps réel avec votre médecin.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><i class="fas fa-clipboard-list"></i>Consultations</h3>
            <div class="stat-val"><?php echo count($consultations); ?></div>
            <div class="stat-desc">Dernière visite le <?php echo $consultations[0]['date_rdv'] ?? 'N/A'; ?></div>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-clock"></i>Suivis Actifs</h3>
            <div class="stat-val"><?php echo count($suivis); ?></div>
            <div class="stat-desc">Mise à jour régulière conseillée</div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-notes-medical"></i>Prescriptions Récentes</h3>
            <a href="?controller=patient&action=suivis" style="font-size:0.8rem; font-weight:700; color:var(--primary); text-decoration:none;">Voir tout <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <?php if(empty($suivis)): ?>
            <p style="color:var(--text-muted); text-align:center; padding:20px;">Aucune prescription active pour le moment.</p>
        <?php else: ?>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <?php foreach(array_slice($suivis, 0, 2) as $s): ?>
                <div style="background:var(--bg-body); padding:20px; border-radius:20px; border:1px solid var(--border);">
                    <div style="font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Médecin : Dr. <?php echo htmlspecialchars($s['medecin_nom']); ?></div>
                    <div style="font-size:0.95rem; font-weight:700; margin-bottom:8px;">Consigne : <?php echo htmlspecialchars(substr($s['etat_general'], 0, 60)); ?>...</div>
                    <div style="font-size:0.85rem; color:var(--text-muted);"><i class="fas fa-flask"></i> Analyses : <?php echo htmlspecialchars($s['analyses_a_realiser'] ?: 'Aucune'); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
