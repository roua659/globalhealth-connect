<?php
declare(strict_types=1);

final class ForumModerationService
{
    public static function ensurePublicationSchema(PDO $pdo): void
    {
        self::addColumnIfMissing($pdo, 'publication', 'statut', "ALTER TABLE publication ADD COLUMN statut ENUM('approved','blocked') NOT NULL DEFAULT 'approved'");
        self::addColumnIfMissing($pdo, 'publication', 'moderation_status', "ALTER TABLE publication ADD COLUMN moderation_status VARCHAR(20) NOT NULL DEFAULT 'safe'");
        self::addColumnIfMissing($pdo, 'publication', 'toxicity_score', "ALTER TABLE publication ADD COLUMN toxicity_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissing($pdo, 'publication', 'sensitive_score', "ALTER TABLE publication ADD COLUMN sensitive_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissing($pdo, 'publication', 'medical_risk_score', "ALTER TABLE publication ADD COLUMN medical_risk_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissing($pdo, 'publication', 'moderation_reason', "ALTER TABLE publication ADD COLUMN moderation_reason TEXT NULL");
        self::addColumnIfMissing($pdo, 'publication', 'moderation_source', "ALTER TABLE publication ADD COLUMN moderation_source VARCHAR(20) NOT NULL DEFAULT 'fallback'");
        self::addColumnIfMissing($pdo, 'publication', 'flagged_at', "ALTER TABLE publication ADD COLUMN flagged_at DATETIME NULL");
        self::addColumnIfMissing($pdo, 'publication', 'reviewed_at', "ALTER TABLE publication ADD COLUMN reviewed_at DATETIME NULL");
    }

    public static function ensureCommentSchema(PDO $pdo): void
    {
        self::addColumnIfMissing($pdo, 'commentaire', 'moderation_status', "ALTER TABLE commentaire ADD COLUMN moderation_status VARCHAR(20) NOT NULL DEFAULT 'safe'");
        self::addColumnIfMissing($pdo, 'commentaire', 'toxicity_score', "ALTER TABLE commentaire ADD COLUMN toxicity_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissing($pdo, 'commentaire', 'sensitive_score', "ALTER TABLE commentaire ADD COLUMN sensitive_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissing($pdo, 'commentaire', 'medical_risk_score', "ALTER TABLE commentaire ADD COLUMN medical_risk_score DECIMAL(5,4) NOT NULL DEFAULT 0");
        self::addColumnIfMissing($pdo, 'commentaire', 'moderation_reason', "ALTER TABLE commentaire ADD COLUMN moderation_reason TEXT NULL");
        self::addColumnIfMissing($pdo, 'commentaire', 'moderation_source', "ALTER TABLE commentaire ADD COLUMN moderation_source VARCHAR(20) NOT NULL DEFAULT 'fallback'");
        self::addColumnIfMissing($pdo, 'commentaire', 'flagged_at', "ALTER TABLE commentaire ADD COLUMN flagged_at DATETIME NULL");
        self::addColumnIfMissing($pdo, 'commentaire', 'reviewed_at', "ALTER TABLE commentaire ADD COLUMN reviewed_at DATETIME NULL");
    }

    public static function ensureForumSchema(PDO $pdo): void
    {
        self::ensurePublicationSchema($pdo);
        self::ensureCommentSchema($pdo);
    }

    public static function analyze(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return self::emptyResult();
        }

        $config = self::getGeminiConfig();
        if (!empty($config['api_key']) && function_exists('curl_init')) {
            try {
                return self::analyzeWithGemini($text, $config);
            } catch (Throwable) {
                return self::fallbackAnalyze($text);
            }
        }

        return self::fallbackAnalyze($text);
    }

    public static function applyAnalysisToPublication(Publication $publication, array $analysis): void
    {
        $status = (string)($analysis['status'] ?? 'safe');
        $publication->set('moderation_status', $status);
        $publication->set('toxicity_score', self::clamp01((float)($analysis['toxicity'] ?? 0)));
        $publication->set('sensitive_score', self::clamp01((float)($analysis['sensitive'] ?? 0)));
        $publication->set('medical_risk_score', self::clamp01((float)($analysis['medicalRisk'] ?? 0)));
        $publication->set('moderation_reason', implode(' | ', $analysis['reasons'] ?? []));
        $publication->set('moderation_source', (string)($analysis['source'] ?? 'fallback'));
        $publication->set('flagged_at', $status === 'safe' ? null : date('Y-m-d H:i:s'));
        $publication->set('reviewed_at', null);
        $publication->set('statut', $status === 'safe' ? 'approved' : 'blocked');
    }

    public static function applyAnalysisToComment(Commentaire $commentaire, array $analysis): void
    {
        $status = (string)($analysis['status'] ?? 'safe');
        $commentaire->set('moderation_status', $status);
        $commentaire->set('toxicity_score', self::clamp01((float)($analysis['toxicity'] ?? 0)));
        $commentaire->set('sensitive_score', self::clamp01((float)($analysis['sensitive'] ?? 0)));
        $commentaire->set('medical_risk_score', self::clamp01((float)($analysis['medicalRisk'] ?? 0)));
        $commentaire->set('moderation_reason', implode(' | ', $analysis['reasons'] ?? []));
        $commentaire->set('moderation_source', (string)($analysis['source'] ?? 'fallback'));
        $commentaire->set('flagged_at', $status === 'safe' ? null : date('Y-m-d H:i:s'));
        $commentaire->set('reviewed_at', null);
        $commentaire->setStatut($status === 'safe' ? 'publie' : 'supprime');
    }

    public static function setModerationStatus(PDO $pdo, int $publicationId, string $status): bool
    {
        if (!in_array($status, ['safe', 'review', 'blocked'], true)) {
            $status = 'review';
        }

        $publicationStatut = $status === 'safe' ? 'approved' : 'blocked';
        $flaggedAtSql = $status === 'safe' ? 'NULL' : 'COALESCE(flagged_at, NOW())';

        $stmt = $pdo->prepare("
            UPDATE publication
            SET moderation_status = :moderation_status,
                statut = :statut,
                flagged_at = {$flaggedAtSql},
                reviewed_at = NOW()
            WHERE id_publication = :id
        ");

        return $stmt->execute([
            'moderation_status' => $status,
            'statut' => $publicationStatut,
            'id' => $publicationId,
        ]);
    }

    public static function setCommentModerationStatus(PDO $pdo, int $commentId, string $status): bool
    {
        if (!in_array($status, ['safe', 'review', 'blocked'], true)) {
            $status = 'review';
        }

        $commentStatut = $status === 'safe' ? 'publie' : 'supprime';
        $flaggedAtSql = $status === 'safe' ? 'NULL' : 'COALESCE(flagged_at, NOW())';

        $stmt = $pdo->prepare("
            UPDATE commentaire
            SET moderation_status = :moderation_status,
                statut = :statut,
                flagged_at = {$flaggedAtSql},
                reviewed_at = NOW()
            WHERE id_commentaire = :id
        ");

        return $stmt->execute([
            'moderation_status' => $status,
            'statut' => $commentStatut,
            'id' => $commentId,
        ]);
    }

    private static function analyzeWithGemini(string $text, array $config): array
    {
        $model = (string)($config['model'] ?? 'gemini-2.5-flash');
        $prompt = "Tu es un moderateur IA d'un forum sante.\n"
            . "Analyse le texte et retourne uniquement un JSON strict comme cet exemple:\n"
            . "{\"toxicity\":0.0,\"sensitive\":0.0,\"medical_risk\":0.0,\"reasons\":[\"raison courte\"]}\n"
            . "toxicity = insultes, haine, harcelement.\n"
            . "sensitive = suicide, violence, danger immediat, contenu choquant.\n"
            . "medical_risk = conseil medical dangereux, arret de traitement, fausse information medicale.\n"
            . "N'ajoute aucun texte hors JSON.\n\nTexte:\n" . $text;

        $payload = [
            'contents' => [[
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature' => 0.0,
                'maxOutputTokens' => 1024,
            ],
        ];

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('curl init failed');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . (string)$config['api_key'],
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 25,
        ]);

        $raw = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException('Gemini moderation failed');
        }

        $response = json_decode((string)$raw, true);
        $content = self::extractJsonObject((string)($response['candidates'][0]['content']['parts'][0]['text'] ?? ''));
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Gemini JSON');
        }

        $toxicity = self::clamp01((float)($decoded['toxicity'] ?? 0));
        $sensitive = self::clamp01((float)($decoded['sensitive'] ?? 0));
        $medicalRisk = self::clamp01((float)($decoded['medical_risk'] ?? 0));

        return [
            'status' => self::resolveStatus($toxicity, $sensitive, $medicalRisk),
            'toxicity' => $toxicity,
            'sensitive' => $sensitive,
            'medicalRisk' => $medicalRisk,
            'reasons' => self::normalizeReasons($decoded['reasons'] ?? []),
            'source' => 'gemini',
        ];
    }

    private static function fallbackAnalyze(string $text): array
    {
        $content = self::normalizeForMatching($text);
        $aggressivePatterns = ['idiot', 'stupide', 'imbecile', 'imbecile', 'nul', 'haine', 'ta gueule', 'ferme la'];
        $sensitivePatterns = ['suicide', 'tuer', 'tue', 'poison', 'overdose', 'violence', 'arme'];
        $medicalRiskPatterns = [
            'arrete ton traitement',
            'arreter ton traitement',
            'arrete la metformine',
            'arretez la metformine',
            'arrete le metformine',
            'arretez votre traitement',
            'stoppe ton traitement',
            'ne prends plus tes medicaments',
            'ne prends plus ton medicament',
            'ne prends plus ton traitement',
            'ne prends plus ton insuline',
            'ne prenez plus votre insuline',
            'arrete ton insuline',
            'arretez votre insuline',
            'ne prenez plus vos medicaments',
            'double la dose',
            'doubler la dose',
            'dose double',
            'les vaccins tuent',
            'insuline inutile',
            'antibiotique pour virus',
            'bois seulement des tisanes',
            'prends seulement des tisanes',
            'prends uniquement du miel',
            'bois uniquement du miel',
            'uniquement du miel',
            'remplace ton traitement par',
            'remplace tes medicaments par',
            'remplacez votre traitement par',
            'soigner ton diabete',
        ];

        $toxicityHits = self::countHits($content, $aggressivePatterns);
        $sensitiveHits = self::countHits($content, $sensitivePatterns);
        $medicalHits = self::countHits($content, $medicalRiskPatterns);

        $toxicity = self::clamp01($toxicityHits / 3.0);
        $sensitive = self::clamp01($sensitiveHits / 2.0);
        $medicalRisk = self::clamp01($medicalHits / 2.0);

        $reasons = [];
        if ($toxicityHits > 0) $reasons[] = 'Langage agressif detecte';
        if ($sensitiveHits > 0) $reasons[] = 'Contenu potentiellement dangereux detecte';
        if ($medicalHits > 0) $reasons[] = 'Conseil medical risque detecte';

        return [
            'status' => self::resolveStatus($toxicity, $sensitive, $medicalRisk),
            'toxicity' => $toxicity,
            'sensitive' => $sensitive,
            'medicalRisk' => $medicalRisk,
            'reasons' => $reasons,
            'source' => 'fallback',
        ];
    }

    private static function resolveStatus(float $toxicity, float $sensitive, float $medicalRisk): string
    {
        if ($toxicity >= 0.85 || $sensitive >= 0.90 || $medicalRisk >= 0.90) {
            return 'blocked';
        }
        if ($toxicity >= 0.55 || $sensitive >= 0.60 || $medicalRisk >= 0.60) {
            return 'review';
        }
        return 'safe';
    }

    private static function getGeminiConfig(): array
    {
        $config = [
            'api_key' => getenv('GEMINI_API_KEY') ?: '',
            'model' => getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash',
        ];

        $path = __DIR__ . '/../config/gemini.local.php';
        if (is_file($path)) {
            $local = require $path;
            if (is_array($local)) {
                $config = array_merge($config, $local);
            }
        }

        return $config;
    }

    private static function addColumnIfMissing(PDO $pdo, string $table, string $column, string $sql): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM {$table} LIKE " . $pdo->quote($column));
        if ($stmt->rowCount() === 0) {
            $pdo->exec($sql);
        }
    }

    private static function normalizeReasons($raw): array
    {
        if (!is_array($raw)) return [];
        $reasons = [];
        foreach ($raw as $reason) {
            if (!is_string($reason)) continue;
            $reason = trim($reason);
            if ($reason !== '') $reasons[] = $reason;
        }
        return array_slice(array_values(array_unique($reasons)), 0, 4);
    }

    private static function extractJsonObject(string $content): string
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/', '', $content) ?? $content;
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start !== false && $end !== false && $end >= $start) {
            return substr($content, $start, $end - $start + 1);
        }
        return $content;
    }

    private static function countHits(string $content, array $patterns): int
    {
        $hits = 0;
        foreach ($patterns as $pattern) {
            if (str_contains($content, self::normalizeForMatching($pattern))) {
                ++$hits;
            }
        }
        return $hits;
    }

    private static function normalizeForMatching(string $text): string
    {
        $text = strtolower($text);
        $text = strtr($text, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'œ' => 'oe', 'æ' => 'ae',
            '’' => "'", '‘' => "'", '`' => "'", '´' => "'",
        ]);
        return preg_replace('/\s+/', ' ', $text) ?? $text;
    }

    private static function clamp01(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }

    private static function emptyResult(): array
    {
        return [
            'status' => 'safe',
            'toxicity' => 0.0,
            'sensitive' => 0.0,
            'medicalRisk' => 0.0,
            'reasons' => [],
            'source' => 'fallback',
        ];
    }
}
