<?php
// Configuration de l'application
define('BASE_URL', 'http://localhost/rdv4/');
define('SITE_NAME', 'GlobalHealth Connect');

// Configuration locale optionnelle
$geminiLocalConfig = [];
$geminiLocalConfigPath = __DIR__ . '/gemini.local.php';
if (file_exists($geminiLocalConfigPath)) {
    $loadedGeminiConfig = require $geminiLocalConfigPath;
    if (is_array($loadedGeminiConfig)) {
        $geminiLocalConfig = $loadedGeminiConfig;
    }
}

// Configuration Gemini
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: ($geminiLocalConfig['api_key'] ?? ''));
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: ($geminiLocalConfig['model'] ?? 'gemini-2.0-flash'));

// Configuration Google Calendar locale optionnelle
$googleCalendarLocalConfig = [];
$googleCalendarLocalConfigPath = __DIR__ . '/google_calendar.local.php';
if (file_exists($googleCalendarLocalConfigPath)) {
    $loadedGoogleCalendarConfig = require $googleCalendarLocalConfigPath;
    if (is_array($loadedGoogleCalendarConfig)) {
        $googleCalendarLocalConfig = $loadedGoogleCalendarConfig;
    }
}

define('GOOGLE_CALENDAR_CLIENT_ID', getenv('GOOGLE_CALENDAR_CLIENT_ID') ?: ($googleCalendarLocalConfig['client_id'] ?? ''));
define('GOOGLE_CALENDAR_CLIENT_SECRET', getenv('GOOGLE_CALENDAR_CLIENT_SECRET') ?: ($googleCalendarLocalConfig['client_secret'] ?? ''));
define('GOOGLE_CALENDAR_REDIRECT_URI', getenv('GOOGLE_CALENDAR_REDIRECT_URI') ?: ($googleCalendarLocalConfig['redirect_uri'] ?? 'http://localhost/rdv4/index.php?page=google-calendar&action=callback'));
define('GOOGLE_CALENDAR_TIMEZONE', getenv('GOOGLE_CALENDAR_TIMEZONE') ?: ($googleCalendarLocalConfig['timezone'] ?? 'Africa/Tunis'));

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
