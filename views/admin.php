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
        .brand strong { font-size:1.1rem; color:#fff; font-weight:800; letter-spacing:-0.02em; }
        
        .nav-label { font-size:0.65rem; font-weight:700; color:rgba(255,255,255,0.3); text-transform:uppercase; letter-spacing:0.1em; padding:0 12px; margin:20px 0 8px; }
        .nav-item {
            display:flex; align-items:center; gap:12px; text-decoration:none;
            color:rgba(255,255,255,0.6); font-size:0.92rem; font-weight:500;
            padding:12px 14px; border-radius:12px; transition:all 0.2s; margin-bottom:4px;
        }
        .nav-item i { width:20px; text-align:center; }
        .nav-item:hover, .nav-item.active { background:var(--sidebar-item-hover); color:#fff; }
        .nav-item.active { background:var(--primary); font-weight:700; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }

        .logout { margin-top:auto; display:flex; align-items:center; gap:12px; text-decoration:none; color:#fca5a5; font-size:0.9rem; font-weight:600; padding:12px 14px; border-radius:12px; border:1px solid rgba(252, 165, 165, 0.2); transition:all 0.2s; }
        .logout:hover { background:rgba(252, 165, 165, 0.1); }

        /* MAIN CONTENT */
        .main-content { flex:1; margin-left:var(--sidebar-w); padding:40px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; }
        header h1 { font-size:1.6rem; font-weight:800; }
        
        .user-chip { display:flex; align-items:center; gap:12px; background:var(--card-bg); padding:8px 18px 8px 8px; border-radius:50px; border:1px solid var(--border); }
        .avatar { width:34px; height:34px; background:var(--primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; }

        /* GRID STATS */
        .stats-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:24px; margin-bottom:40px; }
        .stat-card { background:var(--card-bg); padding:24px; border-radius:24px; border:1px solid var(--border); display:flex; align-items:center; gap:20px; }
        .stat-icon { width:52px; height:52px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; background:var(--bg-body); color:var(--primary); }
        .stat-val { font-size:1.8rem; font-weight:800; }
        .stat-lbl { font-size:0.85rem; color:var(--text-muted); font-weight:600; margin-top:2px; }

        /* PANELS */
        .panel { background:var(--card-bg); border-radius:24px; border:1px solid var(--border); padding:32px; margin-bottom:32px; }
        .panel-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .panel-title { font-size:1.1rem; font-weight:700; display:flex; align-items:center; gap:10px; }
        .panel-title i { color:var(--primary); }

        /* TABLES */
        table { width:100%; border-collapse:collapse; }
        th { text-align:left; font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; padding:12px 16px; border-bottom:1px solid var(--border); }
        td { padding:16px; font-size:0.9rem; border-bottom:1px solid var(--bg-body); }
        .badge { padding:6px 12px; border-radius:8px; font-size:0.75rem; font-weight:700; background:var(--bg-body); color:var(--primary); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-shield-halved"></i></div>
        <div><strong>GlobalHealth</strong><small style="color:rgba(255,255,255,0.4); font-size:0.7rem; display:block;">Admin Console</small></div>
    </div>

    <div class="nav-label">Main Console</div>
    <a href="?controller=admin" class="nav-item active"><i class="fas fa-gauge-high"></i>Dashboard</a>
    <a href="#medecins" class="nav-item"><i class="fas fa-user-md"></i>Staff Directory</a>
    <a href="#admins" class="nav-item"><i class="fas fa-user-shield"></i>Admin Team</a>

    <a href="?controller=auth&action=logout" class="logout"><i class="fas fa-power-off"></i>Sign Out</a>
</aside>

<main class="main-content">
    <header>
        <h1>Executive Dashboard</h1>
        <div class="user-chip">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></div>
            <span style="font-weight:600; font-size:0.9rem;"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrator'); ?></span>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-notes-medical"></i></div>
            <div><div class="stat-val"><?php echo $statsConsultation['total'] ?? 0; ?></div><div class="stat-lbl">Consultations</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <div><div class="stat-val"><?php echo $statsSuivis['total'] ?? 0; ?></div><div class="stat-lbl">Active Follow-ups</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div><div class="stat-val"><?php echo $statsPatients ?? 0; ?></div><div class="stat-lbl">Patients</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-md"></i></div>
            <div><div class="stat-val"><?php echo $statsMedecins ?? 0; ?></div><div class="stat-lbl">Staff</div></div>
        </div>
    </div>

    <div class="panel" id="medecins">
        <div class="panel-title"><i class="fas fa-stethoscope"></i>Medical Staff Directory</div>
        <div style="margin-top:24px; overflow-x:auto;">
            <table>
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Specialty</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($listeMedecins as $m): ?>
                    <tr>
                        <td style="font-weight:600;">Dr. <?php echo htmlspecialchars($m['nom'] . ' ' . $m['prenom']); ?></td>
                        <td style="color:var(--text-muted);"><?php echo htmlspecialchars($m['email']); ?></td>
                        <td><span class="badge"><?php echo htmlspecialchars($m['specialite'] ?? 'General'); ?></span></td>
                        <td><span style="color:#10b981; font-size:0.8rem; font-weight:700;"><i class="fas fa-circle" style="font-size:0.5rem; margin-right:6px;"></i>Active</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel" id="admins">
        <div class="panel-title"><i class="fas fa-lock"></i>Administrative Access</div>
        <div style="margin-top:24px; overflow-x:auto;">
            <table>
                <thead><tr><th>Administrator</th><th>Email</th><th>Access Level</th></tr></thead>
                <tbody>
                    <?php foreach ($listeAdmins as $a): ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($a['nom'] . ' ' . $a['prenom']); ?></td>
                        <td style="color:var(--text-muted);"><?php echo htmlspecialchars($a['email']); ?></td>
                        <td><span class="badge" style="background:#eef2ff;">Super User</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
