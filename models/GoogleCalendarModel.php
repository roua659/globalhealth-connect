<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';

class GoogleCalendarModel {
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const CALENDAR_EVENTS_URL = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';
    private const TOKEN_SESSION_KEY = 'google_calendar_token';
    private const STATE_SESSION_KEY = 'google_calendar_state';
    private const PENDING_RDV_SESSION_KEY = 'google_calendar_pending_rdv';
    private const PENDING_REDIRECT_SESSION_KEY = 'google_calendar_pending_redirect';

    public function isConfigured() {
        return defined('GOOGLE_CALENDAR_CLIENT_ID')
            && defined('GOOGLE_CALENDAR_CLIENT_SECRET')
            && defined('GOOGLE_CALENDAR_REDIRECT_URI')
            && GOOGLE_CALENDAR_CLIENT_ID !== ''
            && GOOGLE_CALENDAR_CLIENT_SECRET !== ''
            && GOOGLE_CALENDAR_REDIRECT_URI !== '';
    }

    public function isConnected() {
        return $this->getAccessToken() !== null;
    }

    public function setPendingAppointment($rdvId, $redirectTo) {
        Session::set(self::PENDING_RDV_SESSION_KEY, (int)$rdvId);
        Session::set(self::PENDING_REDIRECT_SESSION_KEY, $redirectTo ?: 'views/frontoffice.php#consultation');
    }

    public function getPendingAppointmentId() {
        return Session::get(self::PENDING_RDV_SESSION_KEY);
    }

    public function getPendingRedirect() {
        return Session::get(self::PENDING_REDIRECT_SESSION_KEY, 'views/frontoffice.php#consultation');
    }

    public function clearPendingAppointment() {
        Session::remove(self::PENDING_RDV_SESSION_KEY);
        Session::remove(self::PENDING_REDIRECT_SESSION_KEY);
    }

    public function disconnect() {
        Session::remove(self::TOKEN_SESSION_KEY);
        Session::remove(self::STATE_SESSION_KEY);
        $this->clearPendingAppointment();
    }

    public function getAuthUrl() {
        if (!$this->isConfigured()) {
            return null;
        }

        $state = bin2hex(random_bytes(16));
        Session::set(self::STATE_SESSION_KEY, $state);

        $params = [
            'client_id' => GOOGLE_CALENDAR_CLIENT_ID,
            'redirect_uri' => GOOGLE_CALENDAR_REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.events',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    public function handleCallback($code, $state) {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Google Calendar n est pas configure.'];
        }

        if (!$state || !hash_equals((string)Session::get(self::STATE_SESSION_KEY, ''), (string)$state)) {
            return ['success' => false, 'message' => 'Reponse Google invalide.'];
        }

        $response = $this->request('POST', self::TOKEN_URL, [
            'code' => $code,
            'client_id' => GOOGLE_CALENDAR_CLIENT_ID,
            'client_secret' => GOOGLE_CALENDAR_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_CALENDAR_REDIRECT_URI,
            'grant_type' => 'authorization_code',
        ], false);

        if (!$response['success']) {
            return ['success' => false, 'message' => $response['message']];
        }

        $token = $response['data'];
        $token['created_at'] = time();
        Session::set(self::TOKEN_SESSION_KEY, $token);
        Session::remove(self::STATE_SESSION_KEY);

        return ['success' => true, 'message' => 'Compte Google Agenda connecte.'];
    }

    public function createAppointmentEvent($rdv) {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Google Calendar n est pas configure.'];
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Compte Google Agenda non connecte.', 'requires_auth' => true];
        }

        $start = $this->buildDateTime($rdv['date_rdv'] ?? '', $rdv['heure_rdv'] ?? '');
        if (!$start) {
            return ['success' => false, 'message' => 'Date du rendez-vous invalide.'];
        }

        $end = clone $start;
        $end->modify('+30 minutes');

        $doctorName = trim(($rdv['medecin_prenom'] ?? '') . ' ' . ($rdv['medecin_nom'] ?? ''));
        if ($doctorName === '') {
            $doctorName = $rdv['medecin_nom'] ?? 'Medecin';
        }

        $event = [
            'summary' => 'Rendez-vous medical - Dr. ' . $doctorName,
            'description' => $this->buildDescription($rdv),
            'start' => [
                'dateTime' => $start->format(DateTimeInterface::RFC3339),
                'timeZone' => GOOGLE_CALENDAR_TIMEZONE,
            ],
            'end' => [
                'dateTime' => $end->format(DateTimeInterface::RFC3339),
                'timeZone' => GOOGLE_CALENDAR_TIMEZONE,
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'popup', 'minutes' => 30],
                    ['method' => 'email', 'minutes' => 60],
                ],
            ],
        ];

        if (!empty($rdv['lien_visio'])) {
            $event['location'] = $rdv['lien_visio'];
        }

        $response = $this->request('POST', self::CALENDAR_EVENTS_URL, $event, true);
        if (!$response['success']) {
            return ['success' => false, 'message' => $response['message']];
        }

        return [
            'success' => true,
            'message' => 'Rendez-vous ajoute a Google Agenda.',
            'event' => $response['data'],
        ];
    }

    private function buildDateTime($date, $time) {
        if (!$date || !$time) {
            return null;
        }

        try {
            return new DateTime($date . ' ' . $time, new DateTimeZone(GOOGLE_CALENDAR_TIMEZONE));
        } catch (Exception $e) {
            return null;
        }
    }

    private function buildDescription($rdv) {
        $lines = [
            'GlobalHealth Connect',
            'Motif: ' . ($rdv['motif'] ?? 'Consultation medicale'),
            'Type: ' . (($rdv['type_consultation'] ?? '') === 'video' ? 'Visioconference' : 'Presentiel'),
            'Statut: ' . ($rdv['statut'] ?? 'en_attente'),
        ];

        if (!empty($rdv['lien_visio'])) {
            $lines[] = 'Lien visio: ' . $rdv['lien_visio'];
        }

        return implode("\n", $lines);
    }

    private function getAccessToken() {
        $token = Session::get(self::TOKEN_SESSION_KEY);
        if (!$token || empty($token['access_token'])) {
            return null;
        }

        $expiresIn = (int)($token['expires_in'] ?? 0);
        $createdAt = (int)($token['created_at'] ?? 0);
        if ($expiresIn > 0 && $createdAt > 0 && time() >= ($createdAt + $expiresIn - 60)) {
            $token = $this->refreshToken($token);
            if (!$token || empty($token['access_token'])) {
                return null;
            }
        }

        return $token['access_token'];
    }

    private function refreshToken($token) {
        if (empty($token['refresh_token'])) {
            Session::remove(self::TOKEN_SESSION_KEY);
            return null;
        }

        $response = $this->request('POST', self::TOKEN_URL, [
            'client_id' => GOOGLE_CALENDAR_CLIENT_ID,
            'client_secret' => GOOGLE_CALENDAR_CLIENT_SECRET,
            'refresh_token' => $token['refresh_token'],
            'grant_type' => 'refresh_token',
        ], false);

        if (!$response['success']) {
            Session::remove(self::TOKEN_SESSION_KEY);
            return null;
        }

        $newToken = array_merge($token, $response['data']);
        $newToken['created_at'] = time();
        Session::set(self::TOKEN_SESSION_KEY, $newToken);

        return $newToken;
    }

    private function request($method, $url, $payload = [], $useBearer = true) {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'Extension PHP cURL requise pour Google Calendar.'];
        }

        $ch = curl_init();
        $headers = ['Accept: application/json'];

        if ($useBearer) {
            $headers[] = 'Authorization: Bearer ' . $this->getAccessToken();
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        } else {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
        ]);

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            return ['success' => false, 'message' => 'Erreur reseau Google: ' . $error];
        }

        $data = json_decode($body, true);
        if ($status < 200 || $status >= 300) {
            $message = $data['error_description'] ?? $data['error']['message'] ?? 'Erreur Google Calendar.';
            return ['success' => false, 'message' => $message];
        }

        return ['success' => true, 'data' => is_array($data) ? $data : []];
    }
}
?>
