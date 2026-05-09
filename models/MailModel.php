<?php
declare(strict_types=1);

class MailModel
{
    public static function sendPatientNotification(array $notification): array
    {
        $to = trim((string)($notification['patient_email'] ?? ''));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email patient invalide ou manquant'];
        }

        $patientName = (string)($notification['patient_name'] ?? 'Patient');
        $message = trim((string)($notification['message'] ?? ''));
        if ($message === '') {
            return ['success' => false, 'error' => 'Message vide'];
        }

        $body = "Bonjour {$patientName},\n\n"
            . $message . "\n\n"
            . "Cordialement,\nGlobalHealth Connect";

        return self::sendConfiguredMail($to, 'Notification GlobalHealth Connect', $body);
    }

    public static function sendReviewNotification(array $review): array
    {
        $to = trim((string)($review['doctor_email'] ?? ''));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email medecin invalide ou manquant'];
        }

        $patientName = self::cleanHeader((string)($review['patient_name'] ?? 'Patient'));
        $doctorName = (string)($review['doctor_name'] ?? 'Docteur');
        $rating = max(1, min(5, (int)($review['rating'] ?? 0)));
        $comment = trim((string)($review['comment'] ?? ''));

        $body = "Bonjour {$doctorName},\n\n"
            . "Un patient vient de publier un nouvel avis sur GlobalHealth Connect.\n\n"
            . "Patient: {$patientName}\n"
            . "Note: {$rating}/5\n"
            . "Commentaire:\n{$comment}\n\n"
            . "Cet avis est en attente de moderation dans le backoffice.\n\n"
            . "Cordialement,\nGlobalHealth Connect";

        return self::sendConfiguredMail($to, 'Nouvel avis patient - GlobalHealth Connect', $body);
    }

    public static function sendWelcomeEmail(string $toEmail, string $prenom): array
    {
        $toEmail = trim($toEmail);
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email utilisateur invalide'];
        }

        $prenom = trim($prenom) !== '' ? trim($prenom) : 'Patient';
        $body = "Bonjour {$prenom},\n\n"
            . "Bienvenue sur GlobalHealth Connect.\n\n"
            . "Votre compte a ete cree avec succes. Vous pouvez maintenant vous connecter, prendre rendez-vous, consulter vos dossiers et utiliser les services de la plateforme.\n\n"
            . "Date d'inscription: " . date('d/m/Y H:i') . "\n"
            . "Email du compte: {$toEmail}\n\n"
            . "Si vous n'etes pas a l'origine de cette inscription, contactez rapidement le support.\n\n"
            . "Cordialement,\nGlobalHealth Connect";

        return self::sendConfiguredMail($toEmail, 'Bienvenue sur GlobalHealth Connect', $body);
    }

    public static function sendPasswordResetNotification(string $toEmail, string $prenom): array
    {
        $toEmail = trim($toEmail);
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email utilisateur invalide'];
        }

        $prenom = trim($prenom) !== '' ? trim($prenom) : 'Utilisateur';
        $body = "Bonjour {$prenom},\n\n"
            . "Votre mot de passe GlobalHealth Connect vient d'etre reinitialise.\n\n"
            . "Date et heure: " . date('d/m/Y H:i') . "\n"
            . "Email du compte: {$toEmail}\n\n"
            . "Si vous n'etes pas a l'origine de cette action, reconnectez-vous rapidement et changez votre mot de passe, puis contactez le support.\n\n"
            . "Cordialement,\nGlobalHealth Connect";

        return self::sendConfiguredMail($toEmail, 'Mot de passe reinitialise - GlobalHealth Connect', $body);
    }

    private static function sendConfiguredMail(string $to, string $subject, string $body): array
    {
        $config = self::getMailConfig();
        if (empty($config['username']) || empty($config['password'])) {
            self::logMail($to, $subject, $body, false, 'Configuration SMTP Gmail manquante');
            return [
                'success' => false,
                'error' => 'Configuration SMTP Gmail manquante. Ajoutez config/mail.local.php.',
            ];
        }

        $from = (string)($config['from_email'] ?? $config['username']);
        $fromName = (string)($config['from_name'] ?? 'GlobalHealth Connect');
        $headers = [
            'From: ' . self::encodeHeader($fromName) . ' <' . self::cleanHeader($from) . '>',
            'Reply-To: ' . self::cleanHeader($from),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];

        try {
            self::sendSmtp($config, $from, $to, $subject, implode("\r\n", $headers), $body);
            self::logMail($to, $subject, $body, true);
            return ['success' => true, 'error' => null];
        } catch (Throwable $e) {
            self::logMail($to, $subject, $body, false, $e->getMessage());
            return ['success' => false, 'error' => 'Envoi SMTP Gmail echoue: ' . $e->getMessage()];
        }
    }

    private static function getMailConfig(): array
    {
        $config = [
            'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
            'port' => (int)(getenv('SMTP_PORT') ?: 587),
            'username' => getenv('SMTP_USERNAME') ?: '',
            'password' => getenv('SMTP_PASSWORD') ?: '',
            'from_email' => getenv('MAIL_FROM') ?: '',
            'from_name' => getenv('MAIL_FROM_NAME') ?: 'GlobalHealth Connect',
            'timeout' => 20,
        ];

        $path = __DIR__ . '/../config/mail.local.php';
        if (is_file($path)) {
            $local = require $path;
            if (is_array($local)) {
                $config = array_merge($config, $local);
            }
        }

        if (empty($config['from_email'])) {
            $config['from_email'] = $config['username'];
        }

        return $config;
    }

    private static function sendSmtp(array $config, string $from, string $to, string $subject, string $headers, string $body): void
    {
        $socket = @stream_socket_client(
            'tcp://' . (string)($config['host'] ?? 'smtp.gmail.com') . ':' . (int)($config['port'] ?? 587),
            $errno,
            $errstr,
            (int)($config['timeout'] ?? 20),
            STREAM_CLIENT_CONNECT
        );

        if (!is_resource($socket)) {
            throw new RuntimeException("Connexion SMTP impossible ({$errno}: {$errstr})");
        }

        stream_set_timeout($socket, (int)($config['timeout'] ?? 20));

        try {
            self::expect($socket, [220]);
            self::command($socket, 'EHLO localhost', [250]);
            self::command($socket, 'STARTTLS', [220]);

            if (@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                throw new RuntimeException('Activation TLS impossible');
            }

            self::command($socket, 'EHLO localhost', [250]);
            self::command($socket, 'AUTH LOGIN', [334]);
            self::command($socket, base64_encode((string)($config['username'] ?? '')), [334]);
            self::command($socket, base64_encode((string)($config['password'] ?? '')), [235]);
            self::command($socket, 'MAIL FROM:<' . $from . '>', [250]);
            self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            self::command($socket, 'DATA', [354]);

            $message = 'Subject: ' . self::encodeHeader($subject) . "\r\n"
                . 'To: <' . $to . ">\r\n"
                . $headers . "\r\n\r\n"
                . self::dotStuff($body) . "\r\n.";

            self::command($socket, $message, [250]);
            self::command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    private static function command($socket, string $command, array $expectedCodes): string
    {
        fwrite($socket, $command . "\r\n");
        return self::expect($socket, $expectedCodes);
    }

    private static function expect($socket, array $expectedCodes): string
    {
        $response = '';
        do {
            $line = fgets($socket, 515);
            if ($line === false) {
                throw new RuntimeException('Reponse SMTP vide');
            }
            $response .= $line;
            $code = (int)substr($line, 0, 3);
            $continued = isset($line[3]) && $line[3] === '-';
        } while ($continued);

        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException(trim($response));
        }

        return $response;
    }

    private static function cleanHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n"], '', $value));
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode(self::cleanHeader($value)) . '?=';
    }

    private static function dotStuff(string $body): string
    {
        $body = str_replace(["\r\n", "\r"], "\n", $body);
        $body = preg_replace('/^\./m', '..', $body) ?? $body;
        return str_replace("\n", "\r\n", $body);
    }

    private static function logMail(string $to, string $subject, string $body, bool $sent, string $error = ''): void
    {
        $dir = __DIR__ . '/../storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $entry = '[' . date('Y-m-d H:i:s') . '] '
            . ($sent ? 'SENT' : 'FAILED')
            . ' to=' . $to
            . ' subject=' . $subject
            . ($error !== '' ? ' error=' . $error : '')
            . "\n" . $body . "\n---\n";

        @file_put_contents($dir . '/mail.log', $entry, FILE_APPEND);
    }
}
