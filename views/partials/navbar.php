<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>">
            <div class="logo-icon"><i class="fas fa-heartbeat"></i></div>
            <div><span class="logo-text">GlobalHealth Connect</span></div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>?page=dashboard">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>?page=rendezvous">Rendez-vous</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>?page=dossiers">Dossiers médicaux</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>?page=statistiques">Statistiques</a></li>
            </ul>
            <div class="user-menu ms-3">
                <div class="dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown" style="width:42px;height:42px;background:linear-gradient(135deg,var(--medical-blue),var(--medical-green));border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="notification-toast" id="notificationToast"></div>