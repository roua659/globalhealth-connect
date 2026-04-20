<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth — Espace Médical</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb; /* Blue Royal */
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
        .nav-item.active { background:var(--primary); font-weight:700; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }

        .logout { margin-top:auto; display:flex; align-items:center; gap:12px; text-decoration:none; color:#fca5a5; font-size:0.9rem; font-weight:600; padding:12px 14px; border-radius:12px; border:1px solid rgba(252, 165, 165, 0.2); }

        /* MAIN CONTENT */
        .main-content { flex:1; margin-left:var(--sidebar-w); padding:40px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; }
        header h1 { font-size:1.6rem; font-weight:800; }
        .user-chip { display:flex; align-items:center; gap:12px; background:var(--card-bg); padding:8px 18px 8px 8px; border-radius:50px; border:1px solid var(--border); }
        .avatar { width:34px; height:34px; background:var(--primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; }

        /* GRID STATS */
        .stats-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:24px; margin-bottom:40px; }
        .stat-card { background:var(--card-bg); padding:24px; border-radius:24px; border:1px solid var(--border); display:flex; align-items:center; gap:20px; }
        .stat-icon { width:52px; height:52px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; background:var(--bg-body); color:var(--primary); }
        .stat-val { font-size:1.8rem; font-weight:800; }
        .stat-lbl { font-size:0.85rem; color:var(--text-muted); font-weight:600; }

        /* SECTION TILES */
        .grid-2 { display:grid; grid-template-columns: 1fr 1fr; gap:32px; }
        .section-card { background:var(--card-bg); padding:32px; border-radius:24px; border:1px solid var(--border); }
        .section-title { font-size:1.1rem; font-weight:700; margin-bottom:24px; display:flex; align-items:center; gap:10px; }
        .section-title i { color:var(--primary); }

        /* ACTIVITY LIST */
        .act-item { display:flex; gap:16px; padding:16px 0; border-bottom:1px solid var(--bg-body); }
        .act-date { min-width:80px; font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; }
        .act-info h4 { font-size:0.95rem; font-weight:700; margin-bottom:4px; }
        .act-info p { font-size:0.85rem; color:var(--text-muted); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-stethoscope"></i></div>
        <div><strong>GlobalHealth</strong><small style="color:rgba(255,255,255,0.4); font-size:0.7rem; display:block;">Medical Workspace</small></div>
    </div>

    <div class="nav-label">Medical Tools</div>
    <a href="?controller=medecin" class="nav-item active"><i class="fas fa-house-chimney-medical"></i>Dashboard</a>
    <a href="?controller=medecin&action=consultation" class="nav-item"><i class="fas fa-notes-medical"></i>Consultations</a>
    <a href="?controller=medecin&action=suivie" class="nav-item"><i class="fas fa-heart-pulse"></i>Patient Follow-ups</a>

    <a href="?controller=auth&action=logout" class="logout"><i class="fas fa-door-open"></i>Terminer Session</a>
</aside>

<main class="main-content">
    <header>
        <h1>Tableau de Bord Médical</h1>
        <div class="user-chip">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'M', 0, 1)); ?></div>
            <span style="font-weight:600; font-size:0.9rem;">Dr. <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Médecin'); ?></span>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div><div class="stat-val"><?php echo $nbConsultations; ?></div><div class="stat-lbl">Consultations faites</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div><div class="stat-val"><?php echo $nbPatients; ?></div><div class="stat-lbl">Patients suivis</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-wave-square"></i></div>
            <div><div class="stat-val"><?php echo $nbSuivis; ?></div><div class="stat-lbl">Bilans de suivi</div></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="section-card">
            <h3 class="section-title"><i class="fas fa-clock-rotate-left"></i>Dernières Consultations</h3>
            <?php foreach ($dernieresConsultations as $c): ?>
            <div class="act-item">
                <div class="act-date"><?php echo date('d M', strtotime($c['date_creation'])); ?></div>
                <div class="act-info">
                    <h4><?php echo htmlspecialchars($c['patient_nom'] . ' ' . $c['patient_prenom']); ?></h4>
                    <p><?php echo htmlspecialchars(substr($c['diagnostic'], 0, 40)); ?>...</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="section-card">
            <h3 class="section-title"><i class="fas fa-clipboard-pulse"></i>Suivis récents</h3>
            <?php foreach ($derniersSuivis as $s): ?>
            <div class="act-item">
                <div class="act-date"><?php echo date('d M', strtotime($s['date_suivi'])); ?></div>
                <div class="act-info">
                    <h4><?php echo htmlspecialchars($s['patient_nom'] . ' ' . $s['patient_prenom']); ?></h4>
                    <p>État: <?php echo htmlspecialchars(substr($s['etat_general'], 0, 40)); ?>...</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

</body>
</html>
