<?php
require_once __DIR__ . '/../config/config.php';

class ChatbotController {
    private function jsonResponse($payload, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function getJsonInput() {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function buildSystemPrompt() {
        return <<<PROMPT
Tu es l'assistant IA de GlobalHealth Connect.
Tu reponds toujours en francais, avec un ton clair, rassurant et professionnel.
Ton role:
- aider l'utilisateur a comprendre les fonctionnalites de la plateforme
- guider vers la prise de rendez-vous, la teleconsultation, le dossier medical et les ordonnances
- donner des informations generales de navigation dans le site

Regles importantes:
- ne pretends jamais etre un medecin
- ne fournis pas de diagnostic medical certain
- en cas de symptomes graves, conseille de contacter un professionnel de sante ou les urgences
- si une information precise n'est pas connue dans la conversation, dis-le franchement
- reste concis et utile
PROMPT;
    }

    private function extractGeminiText(array $response) {
        $texts = [];
        $candidates = $response['candidates'] ?? [];

        if (!is_array($candidates)) {
            return '';
        }

        foreach ($candidates as $candidate) {
            $parts = $candidate['content']['parts'] ?? [];
            if (!is_array($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                $text = trim((string)($part['text'] ?? ''));
                if ($text !== '') {
                    $texts[] = $text;
                }
            }
        }

        return trim(implode("\n", $texts));
    }

    public function ask() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Methode non autorisee'], 405);
            return;
        }

        if (GEMINI_API_KEY === '') {
            $this->jsonResponse([
                'success' => false,
                'message' => "La cle Gemini est absente. Definissez GEMINI_API_KEY ou config/gemini.local.php."
            ], 500);
            return;
        }

        $input = $this->getJsonInput();
        $message = trim((string)($input['message'] ?? ''));
        $history = $input['history'] ?? [];

        if ($message === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Message vide'], 422);
            return;
        }

        $contents = [];

        if (is_array($history)) {
            foreach (array_slice($history, -8) as $item) {
                $role = $item['role'] ?? '';
                $content = trim((string)($item['content'] ?? ''));

                if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                    continue;
                }

                $contents[] = [
                    'role' => $role === 'assistant' ? 'model' : 'user',
                    'parts' => [
                        ['text' => $content]
                    ]
                ];
            }
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $message]
            ]
        ];

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $this->buildSystemPrompt()]
                ]
            ],
            'contents' => $contents
        ];

        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            rawurlencode(GEMINI_MODEL)
        );

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'x-goog-api-key: ' . GEMINI_API_KEY,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 45
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Impossible de contacter le service IA.',
                'details' => $curlError
            ], 502);
            return;
        }

        $decoded = json_decode($response, true);

        if ($statusCode >= 400) {
            $apiMessage = $decoded['error']['message'] ?? 'Erreur Gemini';
            $this->jsonResponse([
                'success' => false,
                'message' => $apiMessage
            ], $statusCode);
            return;
        }

        $answer = $this->extractGeminiText(is_array($decoded) ? $decoded : []);

        if ($answer === '') {
            $this->jsonResponse([
                'success' => false,
                'message' => 'La reponse IA est vide.'
            ], 502);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'reply' => $answer,
            'model' => GEMINI_MODEL
        ]);
    }
}
