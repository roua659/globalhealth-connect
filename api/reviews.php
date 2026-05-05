<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Avis.php';
require_once __DIR__ . '/../services/MailService.php';

header('Content-Type: application/json; charset=utf-8');

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = config::getConnexion();
        $reviews = array_map(static function (array $review): array {
            return [
                'id' => (int)$review['id_avis'],
                'patient_name' => $review['patient_name'],
                'doctor_id' => (int)$review['id_medecin'],
                'doctor_name' => trim((string)$review['doctor_name']),
                'rating' => (int)$review['rating'],
                'comment' => $review['commentaire'],
                'date' => $review['created_at'],
                'status' => $review['statut'],
            ];
        }, Avis::all($pdo));

        jsonResponse(['success' => true, 'data' => $reviews]);
    } catch (Throwable $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($input)) {
    jsonResponse(['success' => false, 'error' => 'JSON invalide'], 400);
}

$patientName = trim((string)($input['patient_name'] ?? ''));
$doctorId = (int)($input['doctor_id'] ?? 0);
$rating = (int)($input['rating'] ?? 0);
$comment = trim((string)($input['comment'] ?? ''));

if ($patientName === '' || $doctorId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
    jsonResponse(['success' => false, 'error' => 'Donnees avis invalides'], 422);
}

try {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("
        SELECT id_user, nom, prenom, email, specialite
        FROM utilisateur
        WHERE id_user = :id_user AND id_role = 2
        LIMIT 1
    ");
    $stmt->execute(['id_user' => $doctorId]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        jsonResponse(['success' => false, 'error' => 'Medecin introuvable'], 404);
    }

    $review = [
        'patient_name' => $patientName,
        'id_patient' => (int)($_SESSION['user_id'] ?? ($input['patient_id'] ?? 0)) ?: null,
        'doctor_id' => $doctorId,
        'doctor_name' => trim((string)$doctor['prenom'] . ' ' . (string)$doctor['nom']),
        'doctor_email' => (string)$doctor['email'],
        'rating' => $rating,
        'comment' => $comment,
    ];

    $reviewId = Avis::create($pdo, [
        'patient_name' => $patientName,
        'id_patient' => $review['id_patient'],
        'id_medecin' => $doctorId,
        'rating' => $rating,
        'commentaire' => $comment,
        'statut' => 'pending',
    ]);
    $review['id'] = $reviewId;
    $review['status'] = 'pending';

    $mail = MailService::sendReviewNotification($review);

    jsonResponse([
        'success' => true,
        'message' => $mail['success']
            ? 'Avis publie et email envoye au medecin'
            : 'Avis publie, mais email non envoye',
        'mail' => $mail,
        'data' => $review,
    ]);
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
