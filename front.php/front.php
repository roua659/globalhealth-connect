<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'globalhealth_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les médecins pour l'affichage
$doctors = $pdo->query("
    SELECT m.id_medecin, u.nom, u.prenom, m.specialite, u.email, u.telephone
    FROM medecin m
    JOIN utilisateur u ON m.id_user = u.id_user
    LIMIT 6
")->fetchAll();

// Récupérer les derniers posts du forum
$latestPosts = $pdo->query("
    SELECT fm.*, u.nom as medecin_nom, u.prenom as medecin_prenom
    FROM forum_messages fm
    JOIN medecin m ON fm.id_medecin = m.id_medecin
    JOIN utilisateur u ON m.id_user = u.id_user
    WHERE fm.statut = 'publie'
    ORDER BY fm.date_creation DESC
    LIMIT 3
")->fetchAll();

// Gestion de l'inscription
$register_error = '';
$register_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $age = $_POST['age'] ?? null;
    $sexe = $_POST['sexe'] ?? '';
    
    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $register_error = "Veuillez remplir tous les champs obligatoires";
    } else {
        // Vérifier si l'email existe
        $check = $pdo->prepare("SELECT id_user FROM utilisateur WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->fetch()) {
            $register_error = "Cet email est déjà utilisé";
        } else {
            // Insérer l'utilisateur (rôle patient = 3)
            $stmt = $pdo->prepare("
                INSERT INTO utilisateur (nom, prenom, age, sexe, email, mot_de_passe, telephone, id_role) 
                VALUES (?, ?, ?, ?, ?, MD5(?), ?, 3)
            ");
            
            if ($stmt->execute([$nom, $prenom, $age, $sexe, $email, $password, $telephone])) {
                $userId = $pdo->lastInsertId();
                
                // Créer l'entrée patient
                $pdo->prepare("INSERT INTO patient (id_user) VALUES (?)")->execute([$userId]);
                
                $register_success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $register_error = "Erreur lors de l'inscription";
            }
        }
    }
}

// Gestion de la connexion
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("
        SELECT u.*, r.type_role as role 
        FROM utilisateur u 
        JOIN role r ON u.id_role = r.id_role 
        WHERE u.email = ? AND u.mot_de_passe = MD5(?)
    ");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user_name'] = $user['nom'] . ' ' . $user['prenom'];
        $_SESSION['user_role'] = $user['role'];
        
        if ($user['role'] === 'patient') {
            header('Location: ?action=patient_dashboard');
            exit();
        } elseif ($user['role'] === 'admin') {
            header('Location: ../consultation/');
            exit();
        }
    } else {
        $login_error = "Email ou mot de passe incorrect";
    }
}

// Déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ?');
    exit();
}

// Vérifier si l'utilisateur est connecté pour les pages patient
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'patient';

// ==================== PAGES PATIENT (après connexion) ====================

// Dashboard patient
if (isset($_GET['action']) && $_GET['action'] === 'patient_dashboard' && $isLoggedIn) {
    $userId = $_SESSION['user_id'];
    
    // Récupérer l'ID patient
    $stmt = $pdo->prepare("SELECT id_patient FROM patient WHERE id_user = ?");
    $stmt->execute([$userId]);
    $patient = $stmt->fetch();
    $patientId = $patient['id_patient'];
    
    // Statistiques
    $rdvCount = $pdo->prepare("SELECT COUNT(*) FROM rendezvous WHERE id_patient = ?");
    $rdvCount->execute([$patientId]);
    $rdvCount = $rdvCount->fetchColumn();
    
    $reviewCount = $pdo->prepare("SELECT COUNT(*) FROM avis WHERE id_patient = ?");
    $reviewCount->execute([$patientId]);
    $reviewCount = $reviewCount->fetchColumn();
    
    // Prochains rendez-vous
    $nextRdvs = $pdo->prepare("
        SELECT r.*, u.nom as medecin_nom, u.prenom as medecin_prenom, m.specialite
        FROM rendezvous r
        JOIN medecin m ON r.id_medecin = m.id_medecin
        JOIN utilisateur u ON m.id_user = u.id_user
        WHERE r.id_patient = ? AND r.date_rdv >= CURDATE()
        ORDER BY r.date_rdv ASC
        LIMIT 5
    ");
    $nextRdvs->execute([$patientId]);
    $nextRdvs = $nextRdvs->fetchAll();
    
    // Dernier suivi médical
    $lastSuivi = $pdo->prepare("
        SELECT s.*, u.nom as medecin_nom, u.prenom as medecin_prenom
        FROM suivie s
        JOIN medecin m ON s.id_medecin = m.id_medecin
        JOIN utilisateur u ON m.id_user = u.id_user
        WHERE s.id_patient = ?
        ORDER BY s.date_suivi DESC
        LIMIT 1
    ");
    $lastSuivi->execute([$patientId]);
    $lastSuivi = $lastSuivi->fetch();
    
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GlobalHealth - Mon Espace Patient</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            :root {
                --medical-blue: #2b7be4;
                --medical-green: #2ecc71;
                --medical-gray: #f5f7fa;
                --medical-text: #2c3e50;
            }
            body { font-family: 'Inter', sans-serif; background: var(--medical-gray); }
            .navbar-custom { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            .logo-icon {
                width: 45px; height: 45px;
                background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border-radius: 14px; display: flex; align-items: center; justify-content: center;
                color: white; font-size: 1.4rem;
            }
            .stat-card {
                background: white; border-radius: 24px; padding: 25px;
                transition: all 0.3s; box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            }
            .stat-card:hover { transform: translateY(-5px); }
            .stat-number { font-size: 2rem; font-weight: 800; color: var(--medical-blue); }
            .btn-medical {
                background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border: none; padding: 10px 24px; border-radius: 40px; color: white; font-weight: 600;
            }
            .welcome-banner {
                background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border-radius: 24px; padding: 30px; color: white; margin-bottom: 30px;
            }
            .nav-links a {
                text-decoration: none; color: var(--medical-text); padding: 8px 20px;
                border-radius: 40px; transition: all 0.3s;
            }
            .nav-links a:hover, .nav-links a.active {
                background: var(--medical-gray); color: var(--medical-blue);
            }
        </style>
    </head>
    <body>
        <nav class="navbar-custom">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="logo-icon me-3"><i class="fas fa-heartbeat"></i></div>
                    <div><strong style="font-size:1.3rem;">GlobalHealth</strong><br><small>Espace Patient</small></div>
                </div>
                <div class="nav-links">
                    <a href="?action=patient_dashboard" class="active"><i class="fas fa-home me-1"></i>Accueil</a>
                    <a href="?action=doctors"><i class="fas fa-user-md me-1"></i>Médecins</a>
                    <a href="?action=appointments"><i class="fas fa-calendar me-1"></i>Mes RDV</a>
                    <a href="?action=forum"><i class="fas fa-comments me-1"></i>Forum</a>
                    <a href="?action=reviews"><i class="fas fa-star me-1"></i>Mes avis</a>
                    <a href="?action=profile"><i class="fas fa-user me-1"></i>Profil</a>
                    <a href="?action=logout" style="color: #e74c3c;"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
                </div>
            </div>
        </nav>

        <div class="container mt-4">
            <div class="welcome-banner">
                <h2><i class="fas fa-waveform me-2"></i>Bonjour, <?php echo htmlspecialchars($_SESSION['user_name']); ?> !</h2>
                <p class="mb-0">Bienvenue sur votre espace santé GlobalHealth.</p>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <i class="fas fa-calendar-check fa-3x" style="color: var(--medical-blue);"></i>
                        <div class="stat-number"><?php echo $rdvCount; ?></div>
                        <div class="text-muted">Rendez-vous</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <i class="fas fa-star fa-3x" style="color: #f39c12;"></i>
                        <div class="stat-number"><?php echo $reviewCount; ?></div>
                        <div class="text-muted">Avis donnés</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center">
                        <i class="fas fa-chart-line fa-3x" style="color: var(--medical-green);"></i>
                        <div class="stat-number"><?php echo $lastSuivi ? '✓' : '0'; ?></div>
                        <div class="text-muted">Dernier suivi</div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-7">
                    <div class="stat-card">
                        <h5><i class="fas fa-calendar-alt me-2"></i>Prochains rendez-vous</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>Date</th><th>Médecin</th><th>Spécialité</th><th>Statut</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php foreach($nextRdvs as $rdv): ?>
                                    <tr>
                                        <td><?php echo $rdv['date_rdv']; ?> <?php echo $rdv['heure_rdv']; ?></td>
                                        <td>Dr. <?php echo $rdv['medecin_nom']; ?> <?php echo $rdv['medecin_prenom']; ?></td>
                                        <td><?php echo $rdv['specialite']; ?></td>
                                        <td><span class="badge bg-success"><?php echo $rdv['statut']; ?></span></td>
                                        <td><a href="?action=appointment_details&id=<?php echo $rdv['id_rdv']; ?>" class="btn btn-sm btn-outline-primary">Détails</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($nextRdvs)): ?>
                                    <tr><td colspan="5" class="text-center">Aucun rendez-vous programmé</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="?action=doctors" class="btn btn-medical mt-2">Prendre un rendez-vous</a>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="stat-card">
                        <h5><i class="fas fa-notes-medical me-2"></i>Dernier suivi médical</h5>
                        <?php if($lastSuivi): ?>
                            <p><strong>Date :</strong> <?php echo $lastSuivi['date_suivi']; ?></p>
                            <p><strong>Médecin :</strong> Dr. <?php echo $lastSuivi['medecin_nom']; ?> <?php echo $lastSuivi['medecin_prenom']; ?></p>
                            <p><strong>Poids :</strong> <?php echo $lastSuivi['poids'] ?: '-'; ?> kg</p>
                            <p><strong>Tension :</strong> <?php echo $lastSuivi['tension'] ?: '-'; ?></p>
                            <p><strong>État général :</strong> <?php echo substr($lastSuivi['etat_general'], 0, 100); ?>...</p>
                        <?php else: ?>
                            <p class="text-muted">Aucun suivi médical enregistré pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

// Page liste des médecins
if (isset($_GET['action']) && $_GET['action'] === 'doctors' && $isLoggedIn) {
    $specialite = $_GET['specialite'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT m.id_medecin, u.nom, u.prenom, m.specialite, u.email, u.telephone
            FROM medecin m
            JOIN utilisateur u ON m.id_user = u.id_user
            WHERE 1=1";
    $params = [];
    
    if ($specialite) {
        $sql .= " AND m.specialite LIKE ?";
        $params[] = "%$specialite%";
    }
    if ($search) {
        $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR m.specialite LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $doctors = $stmt->fetchAll();
    
    // Récupérer les spécialités pour le filtre
    $specialites = $pdo->query("SELECT DISTINCT specialite FROM medecin WHERE specialite IS NOT NULL")->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GlobalHealth - Nos Médecins</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            :root {
                --medical-blue: #2b7be4;
                --medical-green: #2ecc71;
                --medical-gray: #f5f7fa;
            }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--medical-gray); }
            .navbar-custom { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            .logo-icon {
                width: 45px; height: 45px;
                background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border-radius: 14px; display: flex; align-items: center; justify-content: center;
                color: white; font-size: 1.4rem;
            }
            .doctor-card {
                background: white; border-radius: 20px; padding: 20px;
                transition: all 0.3s; height: 100%;
                box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            }
            .doctor-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
            .doctor-avatar {
                width: 100px; height: 100px; background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border-radius: 50%; display: flex; align-items: center; justify-content: center;
                margin: 0 auto 15px; color: white; font-size: 2.5rem;
            }
            .btn-medical {
                background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border: none; padding: 8px 20px; border-radius: 40px; color: white; font-weight: 500;
            }
            .nav-links a {
                text-decoration: none; color: #333; padding: 8px 20px; border-radius: 40px;
            }
            .nav-links a:hover, .nav-links a.active { background: var(--medical-gray); color: var(--medical-blue); }
        </style>
    </head>
    <body>
        <nav class="navbar-custom">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="logo-icon me-3"><i class="fas fa-heartbeat"></i></div>
                    <div><strong style="font-size:1.3rem;">GlobalHealth</strong><br><small>Espace Patient</small></div>
                </div>
                <div class="nav-links">
                    <a href="?action=patient_dashboard"><i class="fas fa-home me-1"></i>Accueil</a>
                    <a href="?action=doctors" class="active"><i class="fas fa-user-md me-1"></i>Médecins</a>
                    <a href="?action=appointments"><i class="fas fa-calendar me-1"></i>Mes RDV</a>
                    <a href="?action=forum"><i class="fas fa-comments me-1"></i>Forum</a>
                    <a href="?action=logout" style="color: #e74c3c;"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
                </div>
            </div>
        </nav>

        <div class="container mt-4">
            <h2 class="mb-4"><i class="fas fa-user-md me-2"></i>Nos Médecins</h2>
            
            <!-- Filtres -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2">
                        <input type="hidden" name="action" value="doctors">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher un médecin..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-medical">Rechercher</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2">
                        <input type="hidden" name="action" value="doctors">
                        <select name="specialite" class="form-select" onchange="this.form.submit()">
                            <option value="">Toutes les spécialités</option>
                            <?php foreach($specialites as $spec): ?>
                            <option value="<?php echo htmlspecialchars($spec['specialite']); ?>" <?php echo $specialite == $spec['specialite'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec['specialite']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
            
            <!-- Liste des médecins -->
            <div class="row g-4">
                <?php foreach($doctors as $doctor): ?>
                <div class="col-md-4">
                    <div class="doctor-card text-center">
                        <div class="doctor-avatar">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h5>Dr. <?php echo htmlspecialchars($doctor['nom'] . ' ' . $doctor['prenom']); ?></h5>
                        <p class="text-primary fw-bold"><?php echo htmlspecialchars($doctor['specialite']); ?></p>
                        <p class="text-muted small">
                            <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($doctor['email']); ?><br>
                            <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($doctor['telephone'] ?: 'Non renseigné'); ?>
                        </p>
                        <a href="?action=book_appointment&id=<?php echo $doctor['id_medecin']; ?>" class="btn btn-medical">
                            <i class="fas fa-calendar-plus me-2"></i>Prendre RDV
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($doctors)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">Aucun médecin trouvé</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

// Page de prise de rendez-vous
if (isset($_GET['action']) && $_GET['action'] === 'book_appointment' && $isLoggedIn) {
    $doctorId = $_GET['id'] ?? 0;
    
    // Récupérer les infos du médecin
    $stmt = $pdo->prepare("
        SELECT m.id_medecin, u.nom, u.prenom, m.specialite
        FROM medecin m
        JOIN utilisateur u ON m.id_user = u.id_user
        WHERE m.id_medecin = ?
    ");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch();
    
    if (!$doctor) {
        header('Location: ?action=doctors');
        exit();
    }
    
    // Traitement du formulaire
    $appointment_error = '';
    $appointment_success = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date_rdv = $_POST['date_rdv'] ?? '';
        $heure_rdv = $_POST['heure_rdv'] ?? '';
        $motif = $_POST['motif'] ?? '';
        $type_consultation = $_POST['type_consultation'] ?? 'presentiel';
        
        // Récupérer l'ID patient
        $stmt = $pdo->prepare("SELECT id_patient FROM patient WHERE id_user = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $patient = $stmt->fetch();
        
        if ($patient && $date_rdv && $heure_rdv) {
            $insert = $pdo->prepare("
                INSERT INTO rendezvous (date_rdv, heure_rdv, motif, statut, type_consultation, date_creation, id_patient, id_medecin)
                VALUES (?, ?, ?, 'confirmé', ?, NOW(), ?, ?)
            ");
            
            if ($insert->execute([$date_rdv, $heure_rdv, $motif, $type_consultation, $patient['id_patient'], $doctorId])) {
                $appointment_success = "Rendez-vous pris avec succès !";
            } else {
                $appointment_error = "Erreur lors de la prise de rendez-vous";
            }
        } else {
            $appointment_error = "Veuillez remplir tous les champs obligatoires";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GlobalHealth - Prise de RDV</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            :root {
                --medical-blue: #2b7be4;
                --medical-green: #2ecc71;
            }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
            .navbar-custom { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            .logo-icon {
                width: 45px; height: 45px;
                background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border-radius: 14px; display: flex; align-items: center; justify-content: center;
                color: white; font-size: 1.4rem;
            }
            .booking-card {
                background: white; border-radius: 24px; padding: 30px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            }
            .btn-medical {
                background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border: none; padding: 12px; border-radius: 40px; color: white; font-weight: 600; width: 100%;
            }
        </style>
    </head>
    <body>
        <nav class="navbar-custom">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="logo-icon me-3"><i class="fas fa-heartbeat"></i></div>
                    <div><strong style="font-size:1.3rem;">GlobalHealth</strong><br><small>Prise de RDV</small></div>
                </div>
                <a href="?action=doctors" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
            </div>
        </nav>

        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="booking-card">
                        <div class="text-center mb-4">
                            <div class="doctor-avatar bg-light d-inline-block p-3 rounded-circle mb-3">
                                <i class="fas fa-user-md fa-3x" style="color: var(--medical-blue);"></i>
                            </div>
                            <h3>Dr. <?php echo htmlspecialchars($doctor['nom'] . ' ' . $doctor['prenom']); ?></h3>
                            <p class="text-primary"><?php echo htmlspecialchars($doctor['specialite']); ?></p>
                        </div>
                        
                        <?php if($appointment_success): ?>
                        <div class="alert alert-success"><?php echo $appointment_success; ?></div>
                        <?php endif; ?>
                        
                        <?php if($appointment_error): ?>
                        <div class="alert alert-danger"><?php echo $appointment_error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date du rendez-vous *</label>
                                    <input type="date" name="date_rdv" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Heure *</label>
                                    <input type="time" name="heure_rdv" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type de consultation *</label>
                                <select name="type_consultation" class="form-select">
                                    <option value="presentiel">Présentiel</option>
                                    <option value="visio">Visio-consultation</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Motif de la consultation</label>
                                <textarea name="motif" class="form-control" rows="3" placeholder="Décrivez brièvement le motif de votre consultation..."></textarea>
                            </div>
                            <button type="submit" class="btn-medical">Confirmer le rendez-vous</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

// Page des rendez-vous du patient
if (isset($_GET['action']) && $_GET['action'] === 'appointments' && $isLoggedIn) {
    $stmt = $pdo->prepare("SELECT id_patient FROM patient WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $patient = $stmt->fetch();
    $patientId = $patient['id_patient'];
    
    $appointments = $pdo->prepare("
        SELECT r.*, u.nom as medecin_nom, u.prenom as medecin_prenom, m.specialite
        FROM rendezvous r
        JOIN medecin m ON r.id_medecin = m.id_medecin
        JOIN utilisateur u ON m.id_user = u.id_user
        WHERE r.id_patient = ?
        ORDER BY r.date_rdv DESC
    ");
    $appointments->execute([$patientId]);
    $appointments = $appointments->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GlobalHealth - Mes Rendez-vous</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            :root {
                --medical-blue: #2b7be4;
                --medical-green: #2ecc71;
                --medical-gray: #f5f7fa;
            }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--medical-gray); }
            .navbar-custom { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            .logo-icon {
                width: 45px; height: 45px;
                background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border-radius: 14px; display: flex; align-items: center; justify-content: center;
                color: white; font-size: 1.4rem;
            }
            .nav-links a {
                text-decoration: none; color: #333; padding: 8px 20px; border-radius: 40px;
            }
            .nav-links a:hover, .nav-links a.active { background: var(--medical-gray); color: var(--medical-blue); }
        </style>
    </head>
    <body>
        <nav class="navbar-custom">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="logo-icon me-3"><i class="fas fa-heartbeat"></i></div>
                    <div><strong style="font-size:1.3rem;">GlobalHealth</strong><br><small>Mes RDV</small></div>
                </div>
                <div class="nav-links">
                    <a href="?action=patient_dashboard"><i class="fas fa-home me-1"></i>Accueil</a>
                    <a href="?action=doctors"><i class="fas fa-user-md me-1"></i>Médecins</a>
                    <a href="?action=appointments" class="active"><i class="fas fa-calendar me-1"></i>Mes RDV</a>
                    <a href="?action=forum"><i class="fas fa-comments me-1"></i>Forum</a>
                    <a href="?action=logout" style="color: #e74c3c;"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
                </div>
            </div>
        </nav>

        <div class="container mt-4">
            <h2 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Mes Rendez-vous</h2>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Heure</th><th>Médecin</th><th>Spécialité</th><th>Type</th><th>Statut</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($appointments as $rdv): ?>
                        <tr>
                            <td><?php echo $rdv['date_rdv']; ?></td>
                            <td><?php echo $rdv['heure_rdv']; ?></td>
                            <td>Dr. <?php echo $rdv['medecin_nom'] . ' ' . $rdv['medecin_prenom']; ?></td>
                            <td><?php echo $rdv['specialite']; ?></td>
                            <td><?php echo $rdv['type_consultation']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $rdv['statut'] == 'confirmé' ? 'success' : 'warning'; ?>">
                                    <?php echo $rdv['statut']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="?action=appointment_details&id=<?php echo $rdv['id_rdv']; ?>" class="btn btn-sm btn-outline-primary">Détails</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($appointments)): ?>
                        <tr><td colspan="7" class="text-center">Aucun rendez-vous trouvé</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

// Page forum
if (isset($_GET['action']) && $_GET['action'] === 'forum' && $isLoggedIn) {
    $posts = $pdo->prepare("
        SELECT fm.*, u.nom as medecin_nom, u.prenom as medecin_prenom
        FROM forum_messages fm
        JOIN medecin m ON fm.id_medecin = m.id_medecin
        JOIN utilisateur u ON m.id_user = u.id_user
        WHERE fm.statut = 'publie'
        ORDER BY fm.date_creation DESC
    ");
    $posts->execute();
    $posts = $posts->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>GlobalHealth - Forum Médical</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            :root {
                --medical-blue: #2b7be4;
                --medical-green: #2ecc71;
                --medical-gray: #f5f7fa;
            }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--medical-gray); }
            .navbar-custom { background: white; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            .logo-icon {
                width: 45px; height: 45px;
                background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
                border-radius: 14px; display: flex; align-items: center; justify-content: center;
                color: white; font-size: 1.4rem;
            }
            .post-card {
                background: white; border-radius: 20px; padding: 20px; margin-bottom: 20px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            }
            .nav-links a {
                text-decoration: none; color: #333; padding: 8px 20px; border-radius: 40px;
            }
            .nav-links a:hover, .nav-links a.active { background: var(--medical-gray); color: var(--medical-blue); }
        </style>
    </head>
    <body>
        <nav class="navbar-custom">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="logo-icon me-3"><i class="fas fa-heartbeat"></i></div>
                    <div><strong style="font-size:1.3rem;">GlobalHealth</strong><br><small>Forum Médical</small></div>
                </div>
                <div class="nav-links">
                    <a href="?action=patient_dashboard"><i class="fas fa-home me-1"></i>Accueil</a>
                    <a href="?action=doctors"><i class="fas fa-user-md me-1"></i>Médecins</a>
                    <a href="?action=appointments"><i class="fas fa-calendar me-1"></i>Mes RDV</a>
                    <a href="?action=forum" class="active"><i class="fas fa-comments me-1"></i>Forum</a>
                    <a href="?action=logout" style="color: #e74c3c;"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
                </div>
            </div>
        </nav>

        <div class="container mt-4">
            <h2 class="mb-4"><i class="fas fa-comments me-2"></i>Forum Médical</h2>
            <p class="text-muted mb-4">Espace d'échange et d'information avec nos médecins</p>
            
            <?php foreach($posts as $post): ?>
            <div class="post-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5><i class="fas fa-user-md me-2" style="color: var(--medical-blue);"></i>Dr. <?php echo htmlspecialchars($post['medecin_nom'] . ' ' . $post['medecin_prenom']); ?></h5>
                        <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i><?php echo $post['date_creation']; ?></small>
                    </div>
                </div>
                <div class="mt-3">
                    <p><?php echo nl2br(htmlspecialchars($post['contenu'])); ?></p>
                    <?php if($post['url_image']): ?>
                        <img src="<?php echo htmlspecialchars($post['url_image']); ?>" class="img-fluid rounded mt-2" style="max-height: 300px;">
                    <?php endif; ?>
                    <?php if($post['url_video']): ?>
                        <div class="mt-2">
                            <a href="<?php echo htmlspecialchars($post['url_video']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-video me-1"></i>Voir la vidéo
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($posts)): ?>
            <div class="alert alert-info">Aucune publication pour le moment.</div>
            <?php endif; ?>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

// ==================== PAGE D'ACCUEIL PUBLIQUE (si non connecté) ====================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalHealth - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --medical-white: #ffffff;
            --medical-blue: #2b7be4;
            --medical-light-blue: #e8f4ff;
            --medical-green: #2ecc71;
            --medical-dark: #1e293b;
            --medical-gray: #f5f7fa;
            --medical-text: #2c3e50;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--medical-white);
            color: var(--medical-text);
            overflow-x: hidden;
        }
        .navbar-custom {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-right: 12px;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--medical-dark) !important;
            display: flex;
            align-items: center;
        }
        .nav-link {
            font-weight: 500;
            color: var(--medical-text) !important;
            margin: 0 10px;
            transition: color 0.3s;
        }
        .nav-link:hover { color: var(--medical-blue) !important; }
        .btn-medical {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-medical:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(43,123,228,0.3);
            color: white;
        }
        .btn-outline-medical {
            border: 2px solid var(--medical-blue);
            padding: 8px 22px;
            border-radius: 40px;
            color: var(--medical-blue);
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            background: transparent;
        }
        .btn-outline-medical:hover {
            background: var(--medical-light-blue);
            color: var(--medical-blue);
        }
        .hero-section {
            padding: 100px 0;
            background: linear-gradient(to right, #f8fafc, #eef2f6);
        }
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--medical-dark);
            line-height: 1.2;
            margin-bottom: 20px;
        }
        .hero-title span {
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-subtitle {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 40px;
        }
        .hero-image {
            width: 100%;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .services-section { padding: 80px 0; }
        .section-title { text-align: center; font-weight: 700; margin-bottom: 50px; color: var(--medical-dark); }
        .service-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            transition: all 0.3s;
            height: 100%;
            text-align: center;
            border: 1px solid #f1f5f9;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border-color: var(--medical-light-blue);
        }
        .service-icon {
            width: 70px;
            height: 70px;
            background: var(--medical-light-blue);
            color: var(--medical-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }
        .service-title { font-weight: 700; margin-bottom: 15px; }
        .service-desc { color: #64748b; font-size: 0.95rem; }
        .doctor-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            height: 100%;
        }
        .doctor-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .doctor-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2.5rem;
        }
        footer {
            background: var(--medical-dark);
            color: white;
            padding: 50px 0 20px;
        }
        .footer-logo { display:flex; align-items:center; font-size:1.5rem; font-weight:700; margin-bottom:20px; }
        .footer-logo .logo-icon { background: white; color: var(--medical-blue); }
        .social-links a { color: white; opacity: 0.7; font-size: 1.2rem; margin-right: 15px; transition: opacity 0.3s; }
        .social-links a:hover { opacity: 1; }
        .footer-title { font-weight: 600; margin-bottom: 20px; color: #cbd5e1; }
        .footer-links { list-style: none; padding: 0; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: #94a3b8; text-decoration: none; transition: color 0.3s; }
        .footer-links a:hover { color: white; }
        .modal-content { border-radius: 24px; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="?">
                <div class="logo-icon"><i class="fas fa-heartbeat"></i></div>
                GlobalHealth
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="#accueil">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Nos Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#medecins">Nos Médecins</a></li>
                </ul>
                <div class="d-flex">
                    <button class="btn btn-outline-medical me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-sign-in-alt me-2"></i>Connexion
                    </button>
                    <button class="btn btn-medical" data-bs-toggle="modal" data-bs-target="#registerModal">
                        <i class="fas fa-user-plus me-2"></i>Inscription
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="accueil" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="hero-title">Votre santé,<br>notre <span>priorité absolue</span>.</h1>
                    <p class="hero-subtitle">GlobalHealth vous offre un accompagnement médical de pointe avec des consultations personnalisées et un suivi rigoureux pour votre bien-être.</p>
                    <div class="d-flex gap-3">
                        <button class="btn btn-medical" data-bs-toggle="modal" data-bs-target="#registerModal">S'inscrire</button>
                        <button class="btn btn-outline-medical" data-bs-toggle="modal" data-bs-target="#loginModal">Se connecter</button>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=800&q=80" alt="Médecin" class="hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <h2 class="section-title">Nos Services Médicaux</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-stethoscope"></i></div>
                        <h4 class="service-title">Consultations Générales</h4>
                        <p class="service-desc">Des bilans de santé complets et des diagnostics précis effectués par nos médecins expérimentés.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-user-md"></i></div>
                        <h4 class="service-title">Soins Spécialisés</h4>
                        <p class="service-desc">Accédez à un réseau de spécialistes de la santé pour des traitements spécifiques et adaptés.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-notes-medical"></i></div>
                        <h4 class="service-title">Suivi Continu</h4>
                        <p class="service-desc">Un système de suivi rigoureux post-consultation pour s'assurer du bon déroulement de vos traitements.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Médecins Section -->
    <section id="medecins" class="services-section" style="padding-top: 0;">
        <div class="container">
            <h2 class="section-title">Nos Médecins</h2>
            <div class="row g-4">
                <?php foreach($doctors as $doctor): ?>
                <div class="col-md-4">
                    <div class="doctor-card">
                        <div class="doctor-avatar">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h5>Dr. <?php echo htmlspecialchars($doctor['nom'] . ' ' . $doctor['prenom']); ?></h5>
                        <p class="text-primary"><?php echo htmlspecialchars($doctor['specialite']); ?></p>
                        <p class="text-muted small">
                            <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($doctor['email']); ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="footer-logo">
                        <div class="logo-icon me-2"><i class="fas fa-heartbeat"></i></div>
                        GlobalHealth
                    </div>
                    <p style="color: #94a3b8; font-size: 0.95rem;">Excellence médicale et accompagnement personnalisé pour toute la famille.</p>
                    <div class="social-links mt-3">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 offset-lg-2 col-md-4 mb-4">
                    <h5 class="footer-title">Liens Utiles</h5>
                    <ul class="footer-links">
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#services">Nos Services</a></li>
                        <li><a href="#medecins">Nos Médecins</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-8">
                    <h5 class="footer-title">Contact</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Avenue de la Santé, Tunis</li>
                        <li><i class="fas fa-phone me-2"></i> +216 71 123 456</li>
                        <li><i class="fas fa-envelope me-2"></i> contact@globalhealth.com</li>
                    </ul>
                </div>
            </div>
            <div class="border-top mt-4 pt-4 text-center" style="border-color: #334155 !important;">
                <p class="mb-0" style="color: #64748b; font-size: 0.9rem;">&copy; 2026 GlobalHealth. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Modal Connexion -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="fas fa-sign-in-alt me-2"></i>Connexion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($login_error): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-medical w-100">Se connecter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Inscription -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Inscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($register_error): ?>
                    <div class="alert alert-danger"><?php echo $register_error; ?></div>
                    <?php endif; ?>
                    <?php if($register_success): ?>
                    <div class="alert alert-success"><?php echo $register_success; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="register">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom *</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prénom *</label>
                                <input type="text" name="prenom" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="telephone" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Âge</label>
                                <input type="number" name="age" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sexe</label>
                                <select name="sexe" class="form-select">
                                    <option value="">Sélectionner</option>
                                    <option value="M">Homme</option>
                                    <option value="F">Femme</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-medical w-100">S'inscrire</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Afficher les modals si erreurs -->
    <?php if($login_error): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('loginModal')).show();
        });
    </script>
    <?php endif; ?>
    
    <?php if($register_error || $register_success): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('registerModal')).show();
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>