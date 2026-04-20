<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth Connect — Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --blue: #2b7be4;
            --green: #27ae60;
            --dark: #0d1b2a;
            --card: rgba(255,255,255,0.06);
            --border: rgba(255,255,255,0.12);
            --text: #e8f0fe;
            --muted: rgba(232,240,254,0.55);
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* ── Fond animé ── */
        .bg-blobs {
            position: fixed; inset: 0; pointer-events: none; z-index: 0;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.25;
            animation: float 10s ease-in-out infinite alternate;
        }
        .blob-1 { width: 500px; height: 500px; background: var(--blue);  top: -120px; left: -120px; animation-delay: 0s; }
        .blob-2 { width: 400px; height: 400px; background: var(--green); bottom: -100px; right: -100px; animation-delay: 3s; }
        .blob-3 { width: 300px; height: 300px; background: #6c5ce7; top: 40%; left: 50%; animation-delay: 6s; }

        @keyframes float {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(30px, -30px) scale(1.08); }
        }

        /* ── Carte login ── */
        .login-wrapper {
            position: relative; z-index: 1;
            width: 100%; max-width: 440px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 44px 40px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.4);
        }

        .logo-area {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--blue), var(--green));
            border-radius: 20px;
            display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 1.8rem; color: #fff;
            margin-bottom: 14px;
            box-shadow: 0 8px 25px rgba(43,123,228,0.4);
        }
        .logo-area h1 {
            font-size: 1.55rem; font-weight: 800;
            color: var(--text);
        }
        .logo-area p {
            font-size: 0.88rem; color: var(--muted);
            margin-top: 4px;
        }

        /* ── Alerte erreur ── */
        .alert-error {
            background: rgba(231,76,60,0.15);
            border: 1px solid rgba(231,76,60,0.4);
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #ff7675;
            font-size: 0.9rem;
            display: flex; align-items: center; gap: 10px;
        }

        /* ── Champs ── */
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 0.82rem; font-weight: 600;
            color: var(--muted);
            margin-bottom: 8px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%);
            color: var(--muted); font-size: 0.95rem;
            pointer-events: none;
        }
        .form-control {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 13px 16px 13px 44px;
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.25s, box-shadow 0.25s;
            outline: none;
        }
        .form-control::placeholder { color: var(--muted); }
        .form-control:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(43,123,228,0.2);
            background: rgba(255,255,255,0.09);
        }
        .form-control.is-invalid { border-color: #e74c3c !important; }
        .invalid-msg {
            font-size: 0.8rem; color: #ff7675;
            margin-top: 5px; display: none;
        }
        .invalid-msg.show { display: block; }

        /* ── Bouton connexion ── */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--blue), var(--green));
            border: none;
            border-radius: 14px;
            padding: 14px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 1rem; font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 6px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(43,123,228,0.45);
        }
        .btn-login:active { transform: translateY(0); }

        /* ── Lien retour ── */
        .back-link {
            text-align: center;
            margin-top: 22px;
        }
        .back-link a {
            color: var(--muted);
            font-size: 0.88rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link a:hover { color: var(--text); }

        /* ── Badges rôle indicatif ── */
        .role-hint {
            display: flex; gap: 8px; flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }
        .role-badge {
            font-size: 0.75rem; font-weight: 600;
            padding: 4px 12px; border-radius: 20px;
            border: 1px solid var(--border);
            color: var(--muted);
        }
        .role-badge i { margin-right: 5px; }
    </style>
</head>
<body>

<div class="bg-blobs">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<div class="login-wrapper">
    <div class="login-card">

        <div class="logo-area">
            <div class="logo-icon"><i class="fas fa-heartbeat"></i></div>
            <h1>GlobalHealth Connect</h1>
            <p>Connectez-vous à votre espace personnel</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="?controller=auth&action=login" novalidate>

            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="votre@email.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           autocomplete="email">
                </div>
                <div class="invalid-msg" id="emailError">Veuillez saisir une adresse e-mail valide.</div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="••••••••"
                           autocomplete="current-password">
                </div>
                <div class="invalid-msg" id="passwordError">Le mot de passe est obligatoire.</div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Se connecter
            </button>
        </form>

        <div class="role-hint">
            <span class="role-badge"><i class="fas fa-user-shield"></i>Admin</span>
            <span class="role-badge"><i class="fas fa-user-md"></i>Médecin</span>
            <span class="role-badge"><i class="fas fa-user"></i>Patient</span>
        </div>

        <div class="back-link">
            <a href="?controller=front"><i class="fas fa-arrow-left" style="margin-right:5px;"></i>Retour à l'accueil</a>
        </div>

    </div>
</div>

<script>
// ── Contrôle de saisie JavaScript ──
document.getElementById('loginForm').addEventListener('submit', function(e) {
    let valid = true;

    const email    = document.getElementById('email');
    const password = document.getElementById('password');
    const emailErr = document.getElementById('emailError');
    const passErr  = document.getElementById('passwordError');

    // Reset
    [email, password].forEach(f => f.classList.remove('is-invalid'));
    [emailErr, passErr].forEach(e => e.classList.remove('show'));

    // Validation email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email.value.trim() || !emailRegex.test(email.value.trim())) {
        email.classList.add('is-invalid');
        emailErr.classList.add('show');
        valid = false;
    }

    // Validation mot de passe
    if (!password.value.trim()) {
        password.classList.add('is-invalid');
        passErr.classList.add('show');
        valid = false;
    }

    if (!valid) e.preventDefault();
});

// Nettoyage en temps réel
['email','password'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', function() {
        this.classList.remove('is-invalid');
        const errEl = document.getElementById(id + 'Error');
        if (errEl) errEl.classList.remove('show');
    });
});
</script>
</body>
</html>
