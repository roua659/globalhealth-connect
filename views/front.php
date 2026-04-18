<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth Connect — Soins de qualité pour tous</title>
    <meta name="description" content="GlobalHealth Connect — Plateforme de gestion médicale moderne. Consultations, suivis de patients et rendez-vous en ligne.">
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

        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text);
            overflow-x: hidden;
        }

        /* ════════ NAVBAR ════════ */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0;
            z-index: 100;
            padding: 18px 60px;
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(13,27,42,0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--bord);
        }
        .nav-brand {
            display: flex; align-items: center; gap: 12px;
            text-decoration: none;
        }
        .nav-logo {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--blue), var(--green));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: #fff;
        }
        .nav-brand-name {
            font-size: 1.15rem; font-weight: 800; color: var(--text);
        }
        .nav-links {
            display: flex; align-items: center; gap: 8px;
        }
        .nav-links a {
            text-decoration: none; color: var(--muted);
            font-size: 0.9rem; font-weight: 500;
            padding: 8px 16px; border-radius: 30px;
            transition: all 0.25s;
        }
        .nav-links a:hover { color: var(--text); background: var(--card); }
        .btn-connect {
            background: linear-gradient(135deg, var(--blue), var(--green));
            color: #fff !important;
            padding: 10px 24px !important;
            border-radius: 30px !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 20px rgba(43,123,228,0.35);
            transition: transform 0.2s, box-shadow 0.2s !important;
        }
        .btn-connect:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(43,123,228,0.55) !important;
            background: var(--card) !important;
        }

        /* ════════ HERO ════════ */
        .hero {
            min-height: 100vh;
            display: flex; align-items: center;
            padding: 120px 60px 80px;
            position: relative;
            overflow: hidden;
        }
        .hero-bg {
            position: absolute; inset: 0; pointer-events: none;
        }
        .blob {
            position: absolute; border-radius: 50%;
            filter: blur(90px); opacity: 0.22;
            animation: drift 12s ease-in-out infinite alternate;
        }
        .b1 { width:600px;height:600px;background:var(--blue);  top:-150px;left:-150px; }
        .b2 { width:450px;height:450px;background:var(--green); bottom:-100px;right:-100px;animation-delay:4s; }
        .b3 { width:350px;height:350px;background:#7c3aed; top:30%;left:40%;animation-delay:8s; }

        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(40px,-40px) scale(1.1); }
        }

        /* Grille héro */
        .hero-grid {
            position: relative; z-index: 1;
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 60px; align-items: center;
            max-width: 1280px; margin: 0 auto; width: 100%;
        }
        .hero-tag {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(43,123,228,0.15);
            border: 1px solid rgba(43,123,228,0.35);
            border-radius: 30px; padding: 6px 16px;
            font-size: 0.82rem; font-weight: 600; color: #74b9ff;
            margin-bottom: 20px;
        }
        .hero h1 {
            font-size: clamp(2.2rem, 4vw, 3.5rem);
            font-weight: 900; line-height: 1.15;
            margin-bottom: 20px;
        }
        .hero h1 span {
            background: linear-gradient(135deg, var(--blue), var(--green));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero p {
            font-size: 1.08rem; color: var(--muted);
            line-height: 1.75; max-width: 500px;
            margin-bottom: 36px;
        }
        .hero-actions {
            display: flex; gap: 14px; flex-wrap: wrap;
        }
        .btn-primary-hero {
            background: linear-gradient(135deg, var(--blue), var(--green));
            color: #fff; text-decoration: none;
            padding: 15px 32px; border-radius: 40px;
            font-weight: 700; font-size: 1rem;
            box-shadow: 0 6px 25px rgba(43,123,228,0.4);
            transition: transform 0.2s, box-shadow 0.2s;
            display: inline-flex; align-items: center; gap: 10px;
        }
        .btn-primary-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(43,123,228,0.55);
        }
        .btn-ghost-hero {
            background: var(--card);
            border: 1px solid var(--bord);
            color: var(--text); text-decoration: none;
            padding: 15px 32px; border-radius: 40px;
            font-weight: 600; font-size: 1rem;
            transition: background 0.2s, border-color 0.2s;
            display: inline-flex; align-items: center; gap: 10px;
        }
        .btn-ghost-hero:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.25); }

        /* Carte décorative hero */
        .hero-visual {
            display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
        }
        .mini-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--bord);
            border-radius: 20px; padding: 22px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s;
        }
        .mini-card:hover { transform: translateY(-5px); }
        .mini-card.wide { grid-column: span 2; }
        .mini-card-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem; margin-bottom: 12px;
        }
        .ic-blue  { background: rgba(43,123,228,0.2); color: #74b9ff; }
        .ic-green { background: rgba(39,174,96,0.2);  color: #55efc4; }
        .ic-purp  { background: rgba(124,58,237,0.2); color: #a78bfa; }
        .ic-gold  { background: rgba(243,156,18,0.2); color: #fdcb6e; }
        .mini-card h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 4px; }
        .mini-card p  { font-size: 0.8rem; color: var(--muted); }
        .big-num { font-size: 1.8rem; font-weight: 800; color: var(--text); }

        /* ════════ STATS BAND ════════ */
        .stats-band {
            background: rgba(255,255,255,0.03);
            border-top: 1px solid var(--bord);
            border-bottom: 1px solid var(--bord);
            padding: 40px 60px;
        }
        .stats-inner {
            max-width: 1000px; margin: 0 auto;
            display: flex; justify-content: space-around; flex-wrap: wrap; gap: 30px;
        }
        .stat-item { text-align: center; }
        .stat-num {
            font-size: 2.4rem; font-weight: 900;
            background: linear-gradient(135deg, var(--blue), var(--green));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .stat-lbl { font-size: 0.85rem; color: var(--muted); margin-top: 4px; }

        /* ════════ SERVICES ════════ */
        .section {
            padding: 90px 60px;
            max-width: 1280px; margin: 0 auto;
        }
        .section-header {
            text-align: center; margin-bottom: 60px;
        }
        .section-tag {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(39,174,96,0.12);
            border: 1px solid rgba(39,174,96,0.3);
            border-radius: 30px; padding: 6px 16px;
            font-size: 0.8rem; font-weight: 600; color: #55efc4;
            margin-bottom: 16px;
        }
        .section-header h2 {
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            font-weight: 800; line-height: 1.2; margin-bottom: 14px;
        }
        .section-header h2 span {
            background: linear-gradient(135deg, var(--blue), var(--green));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .section-header p {
            font-size: 1rem; color: var(--muted); max-width: 560px; margin: 0 auto;
        }

        .services-grid {
            display: grid; grid-template-columns: repeat(3,1fr); gap: 24px;
        }
        .service-card {
            background: var(--card);
            border: 1px solid var(--bord);
            border-radius: 24px; padding: 32px;
            transition: transform 0.3s, border-color 0.3s, box-shadow 0.3s;
            position: relative; overflow: hidden;
        }
        .service-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--blue), var(--green));
            transform: scaleX(0); transform-origin: left;
            transition: transform 0.3s;
        }
        .service-card:hover {
            transform: translateY(-6px);
            border-color: rgba(43,123,228,0.4);
            box-shadow: 0 20px 50px rgba(0,0,0,0.35);
        }
        .service-card:hover::before { transform: scaleX(1); }
        .service-icon {
            width: 60px; height: 60px; border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; margin-bottom: 20px;
        }
        .service-card h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 10px; }
        .service-card p  { font-size: 0.88rem; color: var(--muted); line-height: 1.7; }

        /* ════════ COMMENT ÇA MARCHE ════════ */
        .steps-wrap {
            padding: 90px 60px;
            background: rgba(255,255,255,0.02);
            border-top: 1px solid var(--bord);
        }
        .steps-inner { max-width: 1280px; margin: 0 auto; }
        .steps-grid {
            display: grid; grid-template-columns: repeat(4,1fr); gap: 24px;
            margin-top: 60px;
        }
        .step-card {
            text-align: center; padding: 28px 20px;
            background: var(--card); border: 1px solid var(--bord);
            border-radius: 24px; transition: transform 0.3s;
        }
        .step-card:hover { transform: translateY(-5px); }
        .step-num {
            width: 50px; height: 50px;
            background: linear-gradient(135deg, var(--blue), var(--green));
            border-radius: 50%; display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 1.2rem; font-weight: 800; color: #fff;
            margin-bottom: 16px;
        }
        .step-card h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 8px; }
        .step-card p  { font-size: 0.82rem; color: var(--muted); line-height: 1.65; }

        /* ════════ CTA FINAL ════════ */
        .cta-section {
            padding: 90px 60px;
            text-align: center;
        }
        .cta-box {
            background: linear-gradient(135deg, rgba(43,123,228,0.18), rgba(39,174,96,0.14));
            border: 1px solid rgba(43,123,228,0.3);
            border-radius: 32px;
            padding: 70px 40px;
            max-width: 760px; margin: 0 auto;
            position: relative; overflow: hidden;
        }
        .cta-box::after {
            content: '';
            position: absolute; top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(43,123,228,0.08) 0%, transparent 60%);
            pointer-events: none;
        }
        .cta-box h2 { font-size: 2rem; font-weight: 800; margin-bottom: 16px; }
        .cta-box p  { font-size: 1rem; color: var(--muted); margin-bottom: 32px; }

        /* ════════ FOOTER ════════ */
        .footer {
            padding: 30px 60px;
            border-top: 1px solid var(--bord);
            display: flex; align-items: center; justify-content: space-between;
            font-size: 0.83rem; color: var(--muted);
        }
        .footer-brand {
            display: flex; align-items: center; gap: 10px;
        }
        .footer-logo {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--blue), var(--green));
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; color: #fff;
        }
    </style>
</head>
<body>

<!-- ════════ NAVBAR ════════ -->
<nav class="navbar">
    <a href="?controller=front" class="nav-brand">
        <div class="nav-logo"><i class="fas fa-heartbeat"></i></div>
        <span class="nav-brand-name">GlobalHealth Connect</span>
    </a>
    <div class="nav-links">
        <a href="#services">Services</a>
        <a href="#comment">Comment ça marche</a>
        <a href="?controller=auth&action=login" class="btn-connect">
            <i class="fas fa-sign-in-alt" style="margin-right:6px;"></i>Connexion
        </a>
    </div>
</nav>

<!-- ════════ HERO ════════ -->
<section class="hero">
    <div class="hero-bg">
        <div class="blob b1"></div>
        <div class="blob b2"></div>
        <div class="blob b3"></div>
    </div>
    <div class="hero-grid">
        <div class="hero-text">
            <div class="hero-tag">
                <i class="fas fa-star" style="color:#fdcb6e;font-size:.7rem;"></i>
                Plateforme médicale de confiance
            </div>
            <h1>
                Des soins <span>intelligents</span><br>
                pour un suivi <span>optimal</span>
            </h1>
            <p>
                GlobalHealth Connect centralise vos consultations médicales, vos suivis de santé 
                et la gestion des rendez-vous dans une seule plateforme sécurisée et intuitive.
            </p>
            <div class="hero-actions">
                <a href="?controller=auth&action=login" class="btn-primary-hero">
                    <i class="fas fa-sign-in-alt"></i>Accéder à mon espace
                </a>
                <a href="#services" class="btn-ghost-hero">
                    <i class="fas fa-info-circle"></i>Découvrir les services
                </a>
            </div>
        </div>

        <div class="hero-visual">
            <div class="mini-card">
                <div class="mini-card-icon ic-blue"><i class="fas fa-stethoscope"></i></div>
                <p style="font-size:.75rem;color:var(--muted);margin-bottom:4px;">Consultations</p>
                <div class="big-num">100%</div>
                <p>Dossiers sécurisés</p>
            </div>
            <div class="mini-card">
                <div class="mini-card-icon ic-green"><i class="fas fa-chart-line"></i></div>
                <p style="font-size:.75rem;color:var(--muted);margin-bottom:4px;">Suivis</p>
                <div class="big-num">Temps réel</div>
                <p>Surveillance continue</p>
            </div>
            <div class="mini-card wide">
                <div class="mini-card-icon ic-purp"><i class="fas fa-shield-alt"></i></div>
                <h4>Données protégées</h4>
                <p>Toutes vos données médicales sont chiffrées et strictement confidentielles. Seuls les professionnels autorisés y ont accès.</p>
            </div>
        </div>
    </div>
</section>

<!-- ════════ STATISTIQUES ════════ -->
<div class="stats-band">
    <div class="stats-inner">
        <div class="stat-item">
            <div class="stat-num">500+</div>
            <div class="stat-lbl">Patients suivis</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">50+</div>
            <div class="stat-lbl">Médecins spécialistes</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">2 000+</div>
            <div class="stat-lbl">Consultations enregistrées</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">99%</div>
            <div class="stat-lbl">Satisfaction patients</div>
        </div>
    </div>
</div>

<!-- ════════ SERVICES ════════ -->
<div id="services">
<div class="section">
    <div class="section-header">
        <div class="section-tag"><i class="fas fa-sparkles"></i>Nos fonctionnalités</div>
        <h2>Tout ce dont vous avez besoin<br><span>en un seul endroit</span></h2>
        <p>Une plateforme médicale complète pensée pour simplifier le quotidien des professionnels de santé et de leurs patients.</p>
    </div>

    <div class="services-grid">
        <div class="service-card">
            <div class="service-icon ic-blue"><i class="fas fa-stethoscope"></i></div>
            <h3>Gestion des Consultations</h3>
            <p>Enregistrez et gérez les dossiers médicaux, diagnostics et prescriptions. Accès immédiat à l'historique complet de chaque patient.</p>
        </div>
        <div class="service-card">
            <div class="service-icon ic-green"><i class="fas fa-chart-line"></i></div>
            <h3>Suivi Médical Continu</h3>
            <p>Surveillez l'évolution de l'état de santé : poids, tension artérielle, état général et analyses. Des graphiques clairs et lisibles.</p>
        </div>
        <div class="service-card">
            <div class="service-icon ic-purp"><i class="fas fa-calendar-check"></i></div>
            <h3>Rendez-vous en Ligne</h3>
            <p>Planifiez et gérez les rendez-vous en quelques clics. Notifications automatiques et rappels pour médecins et patients.</p>
        </div>
        <div class="service-card">
            <div class="service-icon ic-gold"><i class="fas fa-user-md"></i></div>
            <h3>Espace Médecin</h3>
            <p>Interface dédiée aux professionnels de santé pour gérer leurs patients, rédiger des comptes rendus et planifier les suivis.</p>
        </div>
        <div class="service-card">
            <div class="service-icon" style="background:rgba(0,206,201,0.15);color:#00cec9;">
                <i class="fas fa-user-circle"></i>
            </div>
            <h3>Espace Patient</h3>
            <p>Les patients accèdent à leur dossier médical, leurs consultations et leurs suivis de façon sécurisée depuis n'importe quel appareil.</p>
        </div>
        <div class="service-card">
            <div class="service-icon" style="background:rgba(231,76,60,0.15);color:#e74c3c;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Sécurité & Confidentialité</h3>
            <p>Accès sécurisé par rôle (admin, médecin, patient). Chiffrement des données, journalisation des accès.</p>
        </div>
    </div>
</div>
</div>

<!-- ════════ COMMENT ÇA MARCHE ════════ -->
<div id="comment" class="steps-wrap">
    <div class="steps-inner">
        <div class="section-header">
            <div class="section-tag"><i class="fas fa-route"></i>Parcours utilisateur</div>
            <h2>Comment <span>ça fonctionne</span> ?</h2>
            <p>Un processus simple et fluide du premier rendez-vous jusqu'au suivi à long terme.</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-num">1</div>
                <h4>Connexion sécurisée</h4>
                <p>Identifiez-vous avec votre email et mot de passe. Le système vous redirige automatiquement vers votre espace.</p>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <h4>Prise de rendez-vous</h4>
                <p>Choisissez votre médecin, sélectionnez une date et un créneau disponible. Confirmation immédiate.</p>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <h4>Consultation enregistrée</h4>
                <p>Le médecin rédige le diagnostic et le traitement après la consultation. Disponible dans votre dossier instantanément.</p>
            </div>
            <div class="step-card">
                <div class="step-num">4</div>
                <h4>Suivi personnalisé</h4>
                <p>Accédez à l'évolution de votre santé, vos indicateurs et les recommandations médicales à tout moment.</p>
            </div>
        </div>
    </div>
</div>

<!-- ════════ CTA FINAL ════════ -->
<div class="cta-section">
    <div class="cta-box">
        <h2>Prêt à rejoindre<br><span style="background:linear-gradient(135deg,var(--blue),var(--green));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">GlobalHealth Connect</span> ?</h2>
        <p>Accédez à votre espace médical personnalisé dès maintenant.<br>
        Médecins, patients et administrateurs — tout est centralisé ici.</p>
        <a href="?controller=auth&action=login" class="btn-primary-hero" style="display:inline-flex;">
            <i class="fas fa-sign-in-alt"></i>Accéder à mon espace
        </a>
    </div>
</div>

<!-- ════════ FOOTER ════════ -->
<footer class="footer">
    <div class="footer-brand">
        <div class="footer-logo"><i class="fas fa-heartbeat"></i></div>
        <span>GlobalHealth Connect &copy; <?php echo date('Y'); ?></span>
    </div>
    <span>Plateforme médicale sécurisée — Gestion des consultations & suivis</span>
</footer>

</body>
</html>
