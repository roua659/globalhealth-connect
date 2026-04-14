<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';

$pageTitle = "GlobalHealth Connect - Plateforme Médicale";
?>

<?php include __DIR__ . '/partials/header.php'; ?>

<style>
    .hero-section {
        min-height: 100vh;
        background: linear-gradient(135deg, #ffffff 0%, #e8f4ff 100%);
        display: flex;
        align-items: center;
        padding-top: 80px;
        position: relative;
        overflow: hidden;
    }
    
    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 20px;
        background: linear-gradient(135deg, var(--medical-blue), var(--medical-green));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .floating-card {
        background: white;
        border-radius: 30px;
        padding: 30px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        animation: float 5s ease-in-out infinite;
    }
    
    .section-title {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 40px;
        text-align: center;
        position: relative;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, var(--medical-blue), var(--medical-green));
        border-radius: 2px;
    }
    
    .service-card {
        background: white;
        border-radius: 24px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        height: 100%;
    }
    
    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }
    
    .service-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--medical-light-blue), white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2rem;
        color: var(--medical-blue);
    }
    
    .review-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .rating-stars i {
        color: #ffc107;
        font-size: 0.9rem;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }
</style>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <div class="logo-icon"><i class="fas fa-heartbeat"></i></div>
            <div><span class="logo-text">GlobalHealth Connect</span></div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="#home">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#consultation">Consultation</a></li>
                <li class="nav-item"><a class="nav-link" href="#medecins">Médecins</a></li>
                <li class="nav-item"><a class="nav-link" href="#avis">Avis</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
            </ul>
            <div class="user-menu ms-3" id="userMenu" style="display: none;">
                <div class="dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" style="width:42px;height:42px;background:linear-gradient(135deg,var(--medical-blue),var(--medical-green));border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="showProfile()"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showAppointments()"><i class="fas fa-calendar me-2"></i>Mes RDV</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showMedicalFolder()"><i class="fas fa-folder-open me-2"></i>Mon dossier médical</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="auth-buttons ms-3" id="authButtons">
                <button class="btn btn-outline-medical me-2" onclick="showLoginModal()">Se connecter</button>
                <button class="btn btn-medical" onclick="showRegisterModal()">S'inscrire</button>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section id="home" class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 animate-fade-up">
                <div class="hero-badge" style="background:rgba(46,204,113,0.15);padding:8px 20px;border-radius:40px;display:inline-block;margin-bottom:20px;color:var(--medical-green);font-weight:600;">
                    <i class="fas fa-shield-alt me-2"></i>Soins 100% sécurisés
                </div>
                <h1 class="hero-title">Prenez soin de votre santé autrement</h1>
                <p class="lead mb-4" style="color: #5a6e7c;">Consultez des médecins qualifiés en ligne ou en présentiel. Prenez rendez-vous facilement et suivez votre parcours de soins.</p>
                <div class="d-flex gap-3">
                    <a href="#consultation" class="btn btn-medical btn-lg">Prendre rendez-vous <i class="fas fa-arrow-right ms-2"></i></a>
                    <a href="#teleconsultation" class="btn btn-outline-medical btn-lg">Téléconsultation <i class="fas fa-video ms-2"></i></a>
                </div>
            </div>
            <div class="col-lg-6 text-center animate-float">
                <div class="floating-card">
                    <i class="fas fa-stethoscope fa-4x" style="color: var(--medical-blue);"></i>
                    <h4 class="mt-3">Téléconsultation 24/7</h4>
                    <p class="text-muted">Consultation en visio avec nos experts</p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <span class="badge bg-success">✓ Remboursé</span>
                        <span class="badge bg-info">✓ Sécurisé</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="py-5" style="background: var(--medical-gray);">
    <div class="container">
        <h2 class="section-title">Nos services</h2>
        <p class="text-center mb-5">Des solutions complètes pour votre santé</p>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-calendar-check"></i></div>
                    <h4>Prise de RDV</h4>
                    <p>Prenez rendez-vous en ligne avec nos médecins spécialistes en quelques clics</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-video"></i></div>
                    <h4>Téléconsultation</h4>
                    <p>Consultez votre médecin à distance en visioconférence sécurisée</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-folder-open"></i></div>
                    <h4>Dossier médical</h4>
                    <p>Accédez à votre historique médical et à vos ordonnances en ligne</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Consultation Section -->
<section id="consultation" class="py-5">
    <div class="container">
        <h2 class="section-title">Prendre rendez-vous</h2>
        <p class="text-center mb-5">Remplissez le formulaire ci-dessous pour prendre un rendez-vous</p>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="stat-card">
                    <form id="appointmentFormFront" method="POST" action="index.php?page=rendezvous&action=create" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-medical">Nom complet *</label>
                                <input type="text" class="form-control form-control-medical" name="patient_nom" id="frontPatientName" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-medical">Email *</label>
                                <input type="email" class="form-control form-control-medical" name="patient_email" id="frontPatientEmail" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-medical">Téléphone *</label>
                                <input type="tel" class="form-control form-control-medical" name="patient_phone" id="frontPatientPhone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-medical">Médecin *</label>
                                <select class="form-control form-control-medical" name="id_medecin" id="frontMedecin" required>
                                    <option value="">Sélectionnez un médecin</option>
                                    <?php
                                    // Récupérer les médecins depuis la BD
                                    require_once __DIR__ . '/../models/MedecinModel.php';
                                    $medecinModel = new MedecinModel();
                                    $medecins = $medecinModel->readAll();
                                    foreach ($medecins as $medecin):
                                    ?>
                                        <option value="<?php echo $medecin['id_medecin']; ?>">
                                            Dr. <?php echo htmlspecialchars($medecin['nom']); ?> - <?php echo htmlspecialchars($medecin['specialite']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-medical">Date *</label>
                                <input type="date" class="form-control form-control-medical" name="date_rdv" id="frontDate" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-medical">Heure *</label>
                                <input type="time" class="form-control form-control-medical" name="heure_rdv" id="frontHeure" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-medical">Type de consultation *</label>
                                <select class="form-control form-control-medical" name="type_consultation" id="frontType" required>
                                    <option value="presentiel">Présentiel</option>
                                    <option value="video">Visioconférence</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-medical">Symptômes / Motif</label>
                                <textarea class="form-control form-control-medical" name="symptomes" id="frontSymptomes" rows="3" placeholder="Décrivez vos symptômes..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-medical w-100 py-3">
                                    <i class="fas fa-check-circle me-2"></i>Prendre rendez-vous
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Médecins Section -->
<section id="medecins" class="py-5" style="background: var(--medical-gray);">
    <div class="container">
        <h2 class="section-title">Nos médecins experts</h2>
        <p class="text-center mb-5">Une équipe de professionnels à votre écoute</p>
        
        <div class="row" id="doctorsListFront">
            <?php
            require_once __DIR__ . '/../models/MedecinModel.php';
            $medecinModel = new MedecinModel();
            $medecins = $medecinModel->readAll();
            foreach ($medecins as $medecin):
            ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="service-card text-center">
                    <div class="service-icon mx-auto">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h5>Dr. <?php echo htmlspecialchars($medecin['nom']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($medecin['specialite']); ?></p>
                    <p><small><?php echo htmlspecialchars($medecin['email']); ?></small></p>
                    <button class="btn btn-outline-medical btn-sm" onclick="prendreRendezVous(<?php echo $medecin['id_medecin']; ?>)">
                        Prendre RDV
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Avis Section -->
<section id="avis" class="py-5">
    <div class="container">
        <h2 class="section-title">Avis de nos patients</h2>
        <p class="text-center mb-5">Ce qu'ils pensent de nos services</p>
        
        <div class="row" id="reviewsListFront">
            <div class="col-md-4">
                <div class="review-card">
                    <div class="rating-stars mb-2">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p>"Très bonne plateforme, prise de rendez-vous facile et médecins à l'écoute."</p>
                    <h6>- Marie D.</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card">
                    <div class="rating-stars mb-2">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <p>"La téléconsultation m'a sauvé du temps. Je recommande vivement !"</p>
                    <h6>- Thomas L.</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="review-card">
                    <div class="rating-stars mb-2">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p>"Suivi médical de qualité, dossier en ligne très pratique."</p>
                    <h6>- Sophie M.</h6>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button class="btn btn-outline-medical" onclick="showReviewModal()">
                <i class="fas fa-star me-2"></i>Donner mon avis
            </button>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5" style="background: var(--medical-gray);">
    <div class="container">
        <h2 class="section-title">Contactez-nous</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="stat-card">
                    <form id="contactForm" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-medical" placeholder="Votre nom" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" class="form-control form-control-medical" placeholder="Votre email" required>
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control form-control-medical" placeholder="Sujet" required>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control form-control-medical" rows="5" placeholder="Votre message" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-medical w-100">Envoyer le message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="py-4 text-center" style="background: var(--medical-dark); color: white;">
    <div class="container">
        <p>&copy; 2024 GlobalHealth Connect. Tous droits réservés.</p>
        <p>
            <a href="#" class="text-white text-decoration-none me-3">Mentions légales</a>
            <a href="#" class="text-white text-decoration-none me-3">CGU</a>
            <a href="#" class="text-white text-decoration-none">Confidentialité</a>
        </p>
    </div>
</footer>

<!-- Modals -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-sign-in-alt me-2"></i>Connexion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="loginFormFront" novalidate>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control form-control-medical" id="loginEmail" required>
                    </div>
                    <div class="mb-3">
                        <label>Mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="loginPassword" required>
                    </div>
                    <button type="submit" class="btn btn-medical w-100">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Inscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="registerFormFront" novalidate>
                    <div class="mb-3">
                        <label>Nom complet</label>
                        <input type="text" class="form-control form-control-medical" id="registerName" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control form-control-medical" id="registerEmail" required>
                    </div>
                    <div class="mb-3">
                        <label>Téléphone</label>
                        <input type="tel" class="form-control form-control-medical" id="registerPhone" required>
                    </div>
                    <div class="mb-3">
                        <label>Mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="registerPassword" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" class="form-control form-control-medical" id="registerConfirmPassword" required>
                    </div>
                    <button type="submit" class="btn btn-medical w-100">S'inscrire</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Chatbot -->
<div class="chatbot-container">
    <div class="chatbot-toggle" onclick="toggleChatbot()">
        <i class="fas fa-comment-medical fa-2x"></i>
    </div>
    <div class="chatbot-window" id="chatbotWindow">
        <div class="chatbot-header"><i class="fas fa-robot me-2"></i>Assistant GlobalHealth</div>
        <div class="chatbot-messages" id="chatMessages">
            <div class="message bot">👋 Bonjour ! Je suis votre assistant santé. Posez-moi vos questions.</div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chatInput" placeholder="Écrivez votre message...">
            <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<div class="notification-toast" id="notificationToast"></div>

<script>
// Validation formulaire rendez-vous front
document.getElementById('appointmentFormFront')?.addEventListener('submit', function(e) {
    const name = document.getElementById('frontPatientName').value;
    const email = document.getElementById('frontPatientEmail').value;
    const phone = document.getElementById('frontPatientPhone').value;
    const date = document.getElementById('frontDate').value;
    const heure = document.getElementById('frontHeure').value;
    
    if (!name || name.length < 2) {
        e.preventDefault();
        showError('Nom invalide');
        return false;
    }
    
    if (!email || !email.includes('@')) {
        e.preventDefault();
        showError('Email invalide');
        return false;
    }
    
    if (!phone || phone.length < 10) {
        e.preventDefault();
        showError('Téléphone invalide');
        return false;
    }
    
    if (!date) {
        e.preventDefault();
        showError('Date obligatoire');
        return false;
    }
    
    if (!heure) {
        e.preventDefault();
        showError('Heure obligatoire');
        return false;
    }
    
    const selectedDate = new Date(date + ' ' + heure);
    if (selectedDate < new Date()) {
        e.preventDefault();
        showError('La date ne peut pas être dans le passé');
        return false;
    }
});

function showLoginModal() {
    new bootstrap.Modal(document.getElementById('loginModal')).show();
}

function showRegisterModal() {
    new bootstrap.Modal(document.getElementById('registerModal')).show();
}

function prendreRendezVous(medecinId) {
    document.getElementById('frontMedecin').value = medecinId;
    document.getElementById('consultation').scrollIntoView({ behavior: 'smooth' });
}

function showReviewModal() {
    alert('Fonctionnalité à venir - Donnez votre avis sur notre plateforme');
}

function showProfile() {
    alert('Mon profil - Consultez et modifiez vos informations personnelles');
}

function showAppointments() {
    alert('Mes rendez-vous - Consultez l\'historique de vos consultations');
}

function showMedicalFolder() {
    alert('Mon dossier médical - Accédez à vos documents médicaux');
}

function logout() {
    if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
        window.location.href = 'index.php?action=logout';
    }
}

// Validation inscription
document.getElementById('registerFormFront')?.addEventListener('submit', function(e) {
    const password = document.getElementById('registerPassword').value;
    const confirm = document.getElementById('registerConfirmPassword').value;
    
    if (password !== confirm) {
        e.preventDefault();
        showError('Les mots de passe ne correspondent pas');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        showError('Le mot de passe doit contenir au moins 8 caractères');
        return false;
    }
});
</script>

<script>
function setFrontFieldError(field, message) {
    if (!field) return;
    field.classList.add('is-invalid');
    let feedback = field.parentNode.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
}

function clearFrontFieldError(field) {
    if (!field) return;
    field.classList.remove('is-invalid');
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) feedback.remove();
}

document.getElementById('appointmentFormFront')?.addEventListener('submit', function(e) {
    const nameField = document.getElementById('frontPatientName');
    const emailField = document.getElementById('frontPatientEmail');
    const phoneField = document.getElementById('frontPatientPhone');
    const medecinField = document.getElementById('frontMedecin');
    const dateField = document.getElementById('frontDate');
    const heureField = document.getElementById('frontHeure');
    let isValid = true;

    [nameField, emailField, phoneField, medecinField, dateField, heureField].forEach(clearFrontFieldError);

    if (!nameField.value.trim() || nameField.value.trim().length < 2) {
        setFrontFieldError(nameField, 'Le nom doit contenir au moins 2 caracteres');
        isValid = false;
    }
    if (!emailField.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
        setFrontFieldError(emailField, 'Veuillez saisir un email valide');
        isValid = false;
    }
    if (!phoneField.value.trim() || phoneField.value.trim().length < 8) {
        setFrontFieldError(phoneField, 'Veuillez saisir un numero de telephone valide');
        isValid = false;
    }
    if (!medecinField.value) {
        setFrontFieldError(medecinField, 'Veuillez choisir un medecin');
        isValid = false;
    }
    if (!dateField.value) {
        setFrontFieldError(dateField, 'La date est obligatoire');
        isValid = false;
    }
    if (!heureField.value) {
        setFrontFieldError(heureField, 'L heure est obligatoire');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
});

document.getElementById('registerFormFront')?.addEventListener('submit', function(e) {
    const nameField = document.getElementById('registerName');
    const emailField = document.getElementById('registerEmail');
    const phoneField = document.getElementById('registerPhone');
    const passwordField = document.getElementById('registerPassword');
    const confirmField = document.getElementById('registerConfirmPassword');
    let isValid = true;

    [nameField, emailField, phoneField, passwordField, confirmField].forEach(clearFrontFieldError);

    if (!nameField.value.trim() || nameField.value.trim().length < 2) {
        setFrontFieldError(nameField, 'Le nom doit contenir au moins 2 caracteres');
        isValid = false;
    }
    if (!emailField.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
        setFrontFieldError(emailField, 'Veuillez saisir un email valide');
        isValid = false;
    }
    if (!phoneField.value.trim() || phoneField.value.trim().length < 8) {
        setFrontFieldError(phoneField, 'Veuillez saisir un numero de telephone valide');
        isValid = false;
    }
    if (passwordField.value.length < 8) {
        setFrontFieldError(passwordField, 'Le mot de passe doit contenir au moins 8 caracteres');
        isValid = false;
    }
    if (passwordField.value !== confirmField.value) {
        setFrontFieldError(confirmField, 'Les mots de passe ne correspondent pas');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
});

document.getElementById('loginFormFront')?.addEventListener('submit', function(e) {
    const emailField = document.getElementById('loginEmail');
    const passwordField = document.getElementById('loginPassword');
    let isValid = true;

    [emailField, passwordField].forEach(clearFrontFieldError);

    if (!emailField.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
        setFrontFieldError(emailField, 'Veuillez saisir un email valide');
        isValid = false;
    }
    if (!passwordField.value.trim()) {
        setFrontFieldError(passwordField, 'Le mot de passe est obligatoire');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
});

document.getElementById('contactForm')?.addEventListener('submit', function(e) {
    const fields = this.querySelectorAll('input, textarea');
    let isValid = true;

    fields.forEach(field => {
        clearFrontFieldError(field);
        if (!field.value.trim()) {
            setFrontFieldError(field, 'Ce champ est obligatoire');
            isValid = false;
        }
    });

    if (!isValid) {
        e.preventDefault();
    }
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
