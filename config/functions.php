<?php
/**
 * Fichier des fonctions utilitaires - GlobalHealth Connect
 */

// ========== FONCTIONS DE NETTOYAGE ==========

/**
 * Nettoie une entrée utilisateur
 */
function sanitizeInput($data) {
    if ($data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Nettoie un tableau d'entrées
 */
function sanitizeArray($array) {
    $cleanArray = [];
    foreach ($array as $key => $value) {
        $cleanArray[sanitizeInput($key)] = sanitizeInput($value);
    }
    return $cleanArray;
}

/**
 * Valide une adresse email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valide un numéro de téléphone français
 */
function validatePhone($phone) {
    return preg_match('/^(?:(?:\+|00)33|0)[1-9]\d{8}$/', $phone);
}

/**
 * Génère un token sécurisé
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// ========== FONCTIONS DE DATE ==========

/**
 * Formate une date pour l'affichage
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formate une heure pour l'affichage
 */
function formatTime($time, $format = 'H:i') {
    if (!$time) return '';
    $timestamp = strtotime($time);
    return date($format, $timestamp);
}

/**
 * Vérifie si une date est dans le passé
 */
function isPastDate($date) {
    return strtotime($date) < strtotime(date('Y-m-d'));
}

/**
 * Vérifie si une date est dans le futur
 */
function isFutureDate($date) {
    return strtotime($date) > strtotime(date('Y-m-d'));
}

/**
 * Calcule l'âge à partir d'une date de naissance
 */
function calculateAge($birthDate) {
    $today = new DateTime();
    $birth = new DateTime($birthDate);
    $age = $today->diff($birth);
    return $age->y;
}

// ========== FONCTIONS EMAIL ==========

/**
 * Envoie un email simple
 */
function sendEmail($to, $subject, $message, $from = null) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    
    if ($from) {
        $headers .= 'From: ' . $from . "\r\n";
    } else {
        $headers .= 'From: GlobalHealth Connect <noreply@globalhealth.com>' . "\r\n";
    }
    
    $headers .= 'Reply-To: support@globalhealth.com' . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Envoie un lien de téléconsultation par email
 */
function sendVideoConferenceLink($email, $patientName, $doctorName, $link, $date) {
    $subject = "🔗 Lien de téléconsultation - GlobalHealth Connect";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2b7be4, #2ecc71); color: white; padding: 30px 20px; text-align: center; border-radius: 15px 15px 0 0; }
            .content { padding: 30px; background: #f5f7fa; border-radius: 0 0 15px 15px; }
            .link-box { background: #e8f4ff; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0; border: 1px solid #2b7be4; }
            .btn { background: #2b7be4; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: bold; }
            .btn:hover { background: #1a5bbf; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            .info { background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ffc107; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🏥 GlobalHealth Connect</h2>
                <p>Téléconsultation sécurisée</p>
            </div>
            <div class='content'>
                <h3>Bonjour Dr. $patientName,</h3>
                <p>Votre téléconsultation avec <strong>Dr. $doctorName</strong> est prévue pour le :</p>
                <p style='font-size: 18px; color: #2b7be4;'><strong>$date</strong></p>
                
                <div class='link-box'>
                    <p style='margin-bottom: 15px;'>Cliquez sur le bouton ci-dessous pour rejoindre la consultation :</p>
                    <a href='$link' class='btn' target='_blank'>🎥 Rejoindre la consultation</a>
                    <p style='margin-top: 15px; font-size: 12px;'>ou copiez ce lien : <br><code style='background:#fff;padding:5px;'>$link</code></p>
                </div>
                
                <div class='info'>
                    <strong>📋 Conseils avant la consultation :</strong>
                    <ul style='margin: 10px 0 0 20px;'>
                        <li>Assurez-vous d'avoir une bonne connexion internet</li>
                        <li>Vérifiez que votre caméra et micro fonctionnent</li>
                        <li>Connectez-vous 5 minutes avant l'heure prévue</li>
                        <li>Préparez vos questions et vos symptômes</li>
                    </ul>
                </div>
                
                <p style='margin-top: 20px;'>Si vous rencontrez des difficultés techniques, contactez-nous au <strong>01 23 45 67 89</strong>.</p>
            </div>
            <div class='footer'>
                <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
                <p>&copy; 2024 GlobalHealth Connect - Tous droits réservés</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

/**
 * Envoie un rappel de rendez-vous
 */
function sendAppointmentReminder($email, $patientName, $doctorName, $date, $time, $type) {
    $subject = "⏰ Rappel de rendez-vous - GlobalHealth Connect";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2b7be4, #2ecc71); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f5f7fa; }
            .btn { background: #2b7be4; color: white; padding: 10px 25px; text-decoration: none; border-radius: 5px; display: inline-block; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>GlobalHealth Connect</h2>
            </div>
            <div class='content'>
                <h3>Bonjour $patientName,</h3>
                <p>Ceci est un rappel pour votre rendez-vous :</p>
                <p><strong>Médecin :</strong> Dr. $doctorName</p>
                <p><strong>Date :</strong> $date</p>
                <p><strong>Heure :</strong> $time</p>
                <p><strong>Type :</strong> " . ($type == 'video' ? 'Téléconsultation' : 'Présentiel') . "</p>
                <p>Merci de votre confiance !</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

// ========== FONCTIONS FICHIERS ==========

/**
 * Télécharge un fichier
 */
function uploadFile($file, $allowedTypes = null, $maxSize = 5242880) {
    if (!$allowedTypes) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Erreur lors du téléchargement'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Fichier trop volumineux (max ' . ($maxSize / 1048576) . ' MB)'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Type de fichier non autorisé'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . date('Ymd_His') . '.' . $extension;
    $uploadPath = UPLOAD_DIR . $filename;
    
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename, 'message' => 'Fichier uploadé avec succès'];
    }
    
    return ['success' => false, 'message' => 'Erreur lors de la sauvegarde du fichier'];
}

/**
 * Supprime un fichier
 */
function deleteFile($filename) {
    $filePath = UPLOAD_DIR . $filename;
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// ========== FONCTIONS EXPORT ==========

/**
 * Exporte des données en CSV
 */
function exportToCSV($data, $filename, $headers = null) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Ajouter BOM pour UTF-8
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    if ($headers) {
        fputcsv($output, $headers);
    } elseif (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

/**
 * Exporte des données en PDF (nécessite TCPDF)
 */
function exportToPDF($data, $title, $filename) {
    // Vérifier si TCPDF est installé
    if (!class_exists('TCPDF')) {
        // Solution alternative : générer HTML et utiliser le navigateur pour imprimer
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . $title . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #2b7be4; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                th { background: #2b7be4; color: white; }
                tr:nth-child(even) { background: #f5f7fa; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <h1>' . $title . '</h1>
            <p>Généré le : ' . date('d/m/Y H:i:s') . '</p>
            <table>
                <thead>
                    <tr>';
        if (!empty($data)) {
            foreach (array_keys($data[0]) as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
        }
        echo '        </tr>
                </thead>
                <tbody>';
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '    </tbody>
            </table>
            <div class="footer">
                <p>GlobalHealth Connect - Document généré automatiquement</p>
            </div>
            <script>window.print();</script>
        </body>
        </html>';
        exit();
    }
    
    // Si TCPDF est installé
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('GlobalHealth Connect');
    $pdf->SetAuthor('GlobalHealth');
    $pdf->SetTitle($title);
    $pdf->SetHeaderData('', 0, $title, 'GlobalHealth Connect');
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->AddPage();
    
    $html = '<h1>' . $title . '</h1>';
    $html .= '<table border="1" cellpadding="5">';
    $html .= '<thead><tr>';
    if (!empty($data)) {
        foreach (array_keys($data[0]) as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
    }
    $html .= '</tr></thead><tbody>';
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($filename . '.pdf', 'D');
    exit();
}

// ========== FONCTIONS NOTIFICATION ==========

/**
 * Envoie une notification de rappel (pour démo - alerte navigateur)
 */
function getReminderScript($reminders) {
    if (empty($reminders)) return '';
    
    $script = "<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reminders = " . json_encode($reminders) . ";
        const now = new Date();
        
        reminders.forEach(function(reminder) {
            const rdvDate = new Date(reminder.date_rdv + ' ' + reminder.heure_rdv);
            const diffMinutes = Math.floor((rdvDate - now) / 60000);
            
            if (diffMinutes <= 60 && diffMinutes > 0) {
                setTimeout(function() {
                    showReminderAlert(reminder);
                }, 1000);
            }
        });
    });
    
    function showReminderAlert(reminder) {
        if (Notification.permission === 'granted') {
            new Notification('⏰ Rappel de rendez-vous', {
                body: 'Vous avez un rendez-vous avec Dr. ' + reminder.medecin_nom + ' dans moins d\\'une heure',
                icon: '/favicon.ico'
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    showReminderAlert(reminder);
                }
            });
        }
        
        // Afficher aussi une alerte classique
        alert('⏰ RAPPEL : Vous avez un rendez-vous avec Dr. ' + reminder.medecin_nom + '\\nDate : ' + reminder.date_rdv + ' à ' + reminder.heure_rdv);
    }
    </script>";
    
    return $script;
}

// ========== FONCTIONS STATISTIQUES ==========

/**
 * Calcule le pourcentage
 */
function calculatePercentage($value, $total) {
    if ($total == 0) return 0;
    return round(($value / $total) * 100, 2);
}

/**
 * Formate un nombre pour l'affichage des statistiques
 */
function formatNumber($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    }
    if ($number >= 1000) {
        return round($number / 1000, 1) . 'k';
    }
    return $number;
}

// ========== FONCTIONS DE SESSION ==========

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirige si non connecté
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

/**
 * Vérifie si l'utilisateur est admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// ========== FONCTIONS GÉNÉRATION LIENS ==========

/**
 * Génère un lien de téléconsultation unique
 */
function generateVideoLink() {
    $roomId = uniqid('room_') . '_' . bin2hex(random_bytes(8));
    return "https://meet.jit.si/GlobalHealth_" . $roomId;
}

/**
 * Génère un token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie le token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ========== FONCTIONS D'AFFICHAGE ==========

/**
 * Tronque un texte
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Nettoie et affiche du texte HTML
 */
function cleanOutput($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Affiche un message de succès flash
 */
function showSuccess($message) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>' . $message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

/**
 * Affiche un message d'erreur flash
 */
function showError($message) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>' . $message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

/**
 * Affiche un message d'info flash
 */
function showInfo($message) {
    echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>' . $message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}
?>