<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth Connect — Gestion Consultations & Suivis</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --blue:  #2b7be4;
            --green: #27ae60;
            --dark:  #0d1b2a;
            --navy:  #112036;
            --card:  rgba(255,255,255,0.05);
            --bord:  rgba(255,255,255,0.10);
            --text:  #e8f0fe;
            --muted: rgba(232,240,254,0.55);
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text);
            line-height: 1.6;
        }
        .navbar {
            padding: 18px 60px;
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(13,27,42,0.95);
            border-bottom: 1px solid var(--bord);
            position: sticky; top: 0; z-index: 1000;
        }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; font-weight: 800; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: rgba(39, 174, 96, 0.2); border: 1px solid var(--green); color: #55efc4; }
        .alert-error { background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #ff7675; }

        .grid-forms { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 50px; }
        .card { background: var(--card); border: 1px solid var(--bord); border-radius: 16px; padding: 25px; }
        .card h2 { margin-bottom: 20px; font-size: 1.4rem; color: var(--blue); border-bottom: 1px solid var(--bord); padding-bottom: 10px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 0.9rem; color: var(--muted); }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--bord);
            background: rgba(255,255,255,0.05); color: white; font-family: inherit;
        }
        .btn {
            padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer; font-weight: 700;
            transition: 0.3s; width: 100%;
        }
        .btn-primary { background: var(--blue); color: white; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }

        .data-section { margin-top: 50px; }
        .data-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
        .data-item { 
            background: var(--card); border: 1px solid var(--bord); border-radius: 12px; padding: 20px;
            display: flex; flex-direction: column; gap: 10px;
        }
        .tag { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .tag-cons { background: rgba(43, 123, 228, 0.2); color: var(--blue); }
        .tag-suiv { background: rgba(39, 174, 96, 0.2); color: var(--green); }
        
        .link-info { font-style: italic; color: var(--muted); font-size: 0.85rem; border-left: 2px solid var(--blue); padding-left: 10px; margin-top: 5px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="?controller=front" class="nav-brand">
        <i class="fas fa-heartbeat"></i> GlobalHealth Connect
    </a>
    <div>
        <?php if(isset($_SESSION['user_id'])): ?>
            <span style="margin-right: 20px;">Bonjour, <strong><?php echo $_SESSION['user_nom']; ?></strong></span>
            <a href="?controller=auth&action=logout" style="color: #ff7675; text-decoration: none;">Déconnexion</a>
        <?php else: ?>
            <a href="?controller=auth&action=login" class="btn" style="background: var(--blue); padding: 8px 20px; text-decoration: none; color: white;">Connexion Médecin</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'medecin'): ?>
        <div class="grid-forms">
            <!-- Formulaire Créer Consultation -->
            <div class="card">
                <h2><i class="fas fa-file-medical"></i> Créer une Consultation</h2>
                <form method="POST" id="frontConsForm" novalidate>
                    <input type="hidden" name="action" value="create_consultation">
                    <div class="form-group">
                        <label>Choisir un Rendez-vous</label>
                        <select name="id_rdv" id="fc_id_rdv">
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($rendezvous as $rdv): ?>
                                <option value="<?php echo $rdv['id_rdv']; ?>">
                                    <?php echo $rdv['date_rdv'] . " - " . $rdv['patient_nom'] . " " . $rdv['patient_prenom']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-msg" id="err_fc_id_rdv">Veuillez choisir un rendez-vous.</div>
                    </div>
                    <div class="form-group">
                        <label>Diagnostic</label>
                        <textarea name="diagnostic" id="fc_diagnostic" rows="3" placeholder="Entrez le diagnostic..."></textarea>
                        <div class="error-msg" id="err_fc_diagnostic">Diagnostic requis.</div>
                    </div>
                    <div class="form-group">
                        <label>Traitement</label>
                        <textarea name="traitement" id="fc_traitement" rows="3" placeholder="Prescriptions..."></textarea>
                        <div class="error-msg" id="err_fc_traitement">Traitement requis.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer la Consultation</button>
                </form>
            </div>

            <!-- Formulaire Créer Suivi (LIÉ À CONSULTATION) -->
            <div class="card">
                <h2><i class="fas fa-chart-line"></i> Créer un Suivi (Jointure)</h2>
                <form method="POST" id="frontSuivForm" novalidate>
                    <input type="hidden" name="action" value="create_suivie">
                    
                    <div class="form-group">
                        <label>Sélectionner par ID de Consultation (Jointure)</label>
                        <select name="id_consultation" id="fs_id_consultation">
                            <option value="">-- Choisir l'ID de Consultation --</option>
                            <?php foreach ($consultationsForSuivie as $c): ?>
                                <option value="<?php echo $c['id_consultation']; ?>">
                                    Consultation N° <?php echo $c['id_consultation']; ?> (du <?php echo $c['date_rdv']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-msg" id="err_fs_id_consultation">Veuillez choisir une consultation.</div>
                    </div>
                    <div class="form-group">
                        <label>Date du suivi</label>
                        <input type="date" name="date_suivi" id="fs_date_suivi" value="<?php echo date('Y-m-d'); ?>">
                        <div class="error-msg" id="err_fs_date_suivi">La date est obligatoire.</div>
                    </div>
                    <div class="form-group">
                        <label>État Général</label>
                        <input type="text" name="etat_general" id="fs_etat_general" placeholder="Ex: Stable, amélioration...">
                        <div class="error-msg" id="err_fs_etat_general">Veuillez détailler l'état (min 3 car.).</div>
                    </div>
                    <button type="submit" class="btn" style="background: var(--green); color: white;">Enregistrer le Suivi Lié</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="card" style="text-align: center; margin-bottom: 40px;">
            <h1 style="font-size: 2.5rem; margin-bottom: 20px;">Bienvenue sur GlobalHealth Connect</h1>
            <p style="color: var(--muted); font-size: 1.2rem; margin-bottom: 30px;">
                Espace dédié aux professionnels de santé pour la gestion et le suivi des consultations.
            </p>
            <a href="?controller=auth&action=login" class="btn btn-primary" style="max-width: 300px; display: inline-block; text-decoration: none;">Accéder à mon espace</a>
        </div>
    <?php endif; ?>

    <div class="data-section">
        <h2 style="margin-bottom: 25px;"><i class="fas fa-list"></i> Historique et Jointures</h2>
        
        <div class="data-grid">
            <?php foreach ($suivis as $s): ?>
                <div class="data-item">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="tag tag-suiv">Suivi Médical</span>
                        <small><?php echo $s['date_suivi']; ?></small>
                    </div>
                    <p><strong>Patient :</strong> <?php echo $s['patient_nom'] . " " . $s['patient_prenom']; ?></p>
                    <p><strong>État :</strong> <?php echo $s['etat_general']; ?></p>
                    
                    <?php if ($s['id_consultation']): ?>
                        <div class="link-info">
                            <i class="fas fa-link"></i> Lié à la consultation : 
                            <strong><?php echo $s['consultation_diagnostic']; ?></strong> (<?php echo $s['consultation_date']; ?>)
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<footer style="margin-top: 100px; padding: 40px; text-align: center; border-top: 1px solid var(--bord); color: var(--muted); font-size: 0.9rem;">
    GlobalHealth Connect © 2026 - Plateforme de gestion médicale
</footer>

<script>
// Fonction utilitaire pour afficher les erreurs en rouge au Front
function showFrontErr(inputId, errorId, message) {
    const input = document.getElementById(inputId);
    const errorDiv = document.getElementById(errorId);
    if (input && errorDiv) {
        input.classList.add('is-invalid');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

// Réinitialiser les erreurs
function resetFrontErrors(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');
}

// Validation Formulaire Consultation Front
if (document.getElementById('frontConsForm')) {
    document.getElementById('frontConsForm').addEventListener('submit', function(e) {
        resetFrontErrors('frontConsForm');
        let hasError = false;

        if (!document.getElementById('fc_id_rdv').value) {
            showFrontErr('fc_id_rdv', 'err_fc_id_rdv', "Veuillez choisir un rendez-vous.");
            hasError = true;
        }
        if (document.getElementById('fc_diagnostic').value.trim().length < 3) {
            showFrontErr('fc_diagnostic', 'err_fc_diagnostic', "Diagnostic trop court.");
            hasError = true;
        }
        if (document.getElementById('fc_traitement').value.trim().length < 3) {
            showFrontErr('fc_traitement', 'err_fc_traitement', "Traitement trop court.");
            hasError = true;
        }
        
        if (hasError) e.preventDefault();
    });
}

// Validation Formulaire Suivi Front
if (document.getElementById('frontSuivForm')) {
    document.getElementById('frontSuivForm').addEventListener('submit', function(e) {
        resetFrontErrors('frontSuivForm');
        let hasError = false;

        if (!document.getElementById('fs_id_consultation').value) {
            showFrontErr('fs_id_consultation', 'err_fs_id_consultation', "Veuillez choisir une consultation.");
            hasError = true;
        }
        if (!document.getElementById('fs_date_suivi').value) {
            showFrontErr('fs_date_suivi', 'err_fs_date_suivi', "La date est obligatoire.");
            hasError = true;
        }
        if (document.getElementById('fs_etat_general').value.trim().length < 3) {
            showFrontErr('fs_etat_general', 'err_fs_etat_general', "État général trop court.");
            hasError = true;
        }
        
        if (hasError) e.preventDefault();
    });
}
</script>

</body>
</html>

