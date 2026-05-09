<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth — Mon Profil (Médecin)</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
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

        .main-content { flex:1; margin-left:var(--sidebar-w); padding:40px; }
        header { margin-bottom:32px; }
        header h1 { font-size:1.6rem; font-weight:800; }

        .profile-grid { display:grid; grid-template-columns: 1fr 1fr; gap:32px; }
        .card { background:var(--card-bg); border-radius:24px; padding:32px; border:1px solid var(--border); }
        .info-row { margin-bottom:20px; }
        .info-label { font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px; }
        .info-val { font-size:1.1rem; font-weight:600; }

        .signature-box { 
            width:100%; height:180px; background:#f1f5f9; border:2px dashed var(--border); 
            border-radius:16px; display:flex; align-items:center; justify-content:center;
            margin-bottom:20px; overflow:hidden;
        }
        .signature-img { max-width:100%; max-height:100%; object-fit:contain; }

        .btn-upload {
            background:var(--primary); color:#fff; border:none; padding:12px 24px;
            border-radius:12px; font-weight:700; cursor:pointer; font-family:inherit;
            display:inline-flex; align-items:center; gap:8px;
        }

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
    <a href="?controller=medecin&action=suivie" class="nav-item"><i class="fas fa-heart-pulse"></i>Patient Follow-ups</a>
    
    <div class="nav-label">Settings</div>
    <a href="?controller=medecin&action=profile" class="nav-item active"><i class="fas fa-user-md"></i>Mon Profil</a>

    <a href="?controller=auth&action=logout" class="logout"><i class="fas fa-door-open"></i>Terminer Session</a>
</aside>

<main class="main-content">
    <header>
        <h1>Profil Médecin &amp; Paramètres</h1>
    </header>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'triangle-exclamation'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="profile-grid">
        <div class="card">
            <h3 style="margin-bottom:24px; font-weight:700;">Informations Personnelles</h3>
            <div class="info-row">
                <div class="info-label">Nom Complet</div>
                <div class="info-val"><?php echo htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Spécialité</div>
                <div class="info-val"><?php echo htmlspecialchars($medecin['specialite']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-val"><?php echo htmlspecialchars($medecin['email']); ?></div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom:24px; font-weight:700;">Signature Électronique</h3>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px;">
                Téléchargez une image de votre signature (PNG avec fond transparent recommandé). Elle sera apposée sur tous vos rapports PDF.
            </p>

            <div class="signature-box">
                <?php if ($medecin['signature']): ?>
                    <img src="uploads/signatures/<?php echo $medecin['signature']; ?>" class="signature-img" alt="Ma Signature">
                <?php else: ?>
                    <div style="text-align:center; color:var(--text-muted);">
                        <i class="fas fa-pen-nib" style="font-size:2rem; display:block; margin-bottom:10px;"></i>
                        <span style="font-size:0.8rem;">Aucune signature configurée</span>
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="signature" id="signatureFile" style="display:none;" accept="image/*" onchange="this.form.submit()">
                <button type="button" class="btn-upload" onclick="document.getElementById('signatureFile').click()">
                    <i class="fas fa-upload"></i> Mettre à jour ma signature
                </button>
            </form>
        </div>
    </div>
</main>

</body>
</html>
