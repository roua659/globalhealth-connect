<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'Authenticité — GlobalHealth Connect</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --error: #ef4444;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background: var(--card);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            text-align: center;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 24px;
        }
        .icon-success { background: #dcfce7; color: var(--success); }
        .icon-error { background: #fef2f2; color: var(--error); }
        
        h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 8px; }
        .status-msg { font-size: 1rem; color: var(--text-muted); margin-bottom: 32px; }
        
        .info-card {
            background: var(--bg);
            border-radius: 16px;
            padding: 20px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
        .info-val { font-size: 0.95rem; font-weight: 600; }
        
        .footer { margin-top: 32px; font-size: 0.8rem; color: var(--text-muted); }
        .btn-home {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 24px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            transition: opacity 0.2s;
        }
        .btn-home:hover { opacity: 0.9; }
    </style>
</head>
<body>

<div class="container">
    <?php if ($data): ?>
        <div class="icon-box icon-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Document Authentique</h1>
        <p class="status-msg">Ce document a été certifié par GlobalHealth Connect.</p>
        
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Référence</span>
                <span class="info-val">#<?php echo isset($data['id_consultation']) ? 'CONS-'.$data['id_consultation'] : 'SUI-'.$data['id_suivie']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Patient</span>
                <span class="info-val"><?php 
                    // Pour la confidentialité, on n'affiche que les initiales et le nom
                    echo htmlspecialchars($data['patient_nom'] . ' ' . substr($data['patient_prenom'], 0, 1) . '.'); 
                ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Médecin</span>
                <span class="info-val">Dr. <?php echo htmlspecialchars($data['medecin_nom'] . ' ' . $data['medecin_prenom']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date du Document</span>
                <span class="info-val"><?php echo isset($data['date_rdv']) ? $data['date_rdv'] : $data['date_suivi']; ?></span>
            </div>
        </div>
    <?php else: ?>
        <div class="icon-box icon-error">
            <i class="fas fa-times-circle"></i>
        </div>
        <h1>Échec de Vérification</h1>
        <p class="status-msg"><?php echo htmlspecialchars($error); ?></p>
        <p style="font-size: 0.9rem; color: var(--text-muted);">Si vous pensez qu'il s'agit d'une erreur, veuillez contacter le support technique de GlobalHealth.</p>
    <?php endif; ?>

    <div class="footer">
        <p>GlobalHealth Connect &copy; 2026</p>
        <p>Sécurité et Intégrité des Données Médicales</p>
    </div>
    
    <a href="index.php" class="btn-home">Retour à l'accueil</a>
</div>

</body>
</html>
