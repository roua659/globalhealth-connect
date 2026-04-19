<?php
// Configuration de l'application
define('BASE_URL', 'http://localhost/Rdv2/');
define('SITE_NAME', 'GlobalHealth Connect');

// Configuration email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre_email@gmail.com');
define('SMTP_PASS', 'votre_mot_de_passe');

// Timezone
date_default_timezone_set('Europe/Paris');

// Configuration des fichiers uploadés
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Configuration des exports
define('EXPORT_DIR', __DIR__ . '/../exports/');
?>