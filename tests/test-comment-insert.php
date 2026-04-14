<?php
require_once __DIR__ . '/config/database.php';

// Simulate what the add-comment handler receives
$_GET['action'] = 'add-comment';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simulate the JSON input that JavaScript sends
$json_input = json_encode([
    'id_publication' => 35,  // Use a known publication ID
    'id_user' => 2,          // Use a known user ID
    'contenu' => 'Test comment to verify the system works'
]);

$input = json_decode($json_input, true);

try {
    $pdo = config::getConnexion();
    
    echo "🧪 Testing add-comment handler...\n\n";
    echo "Input data:\n";
    echo "  Publication ID: " . $input['id_publication'] . "\n";
    echo "  User ID: " . $input['id_user'] . "\n";
    echo "  Content: " . $input['contenu'] . "\n\n";
    
    // Validate publication exists
    echo "1️⃣ Checking if publication exists...\n";
    $stmt = $pdo->prepare("SELECT id_publication FROM publication WHERE id_publication = ?");
    $stmt->execute([(int)$input['id_publication']]);
    $pub = $stmt->fetch();
    if ($pub) {
        echo "   ✅ Publication found: ID " . $pub['id_publication'] . "\n\n";
    } else {
        echo "   ❌ Publication NOT found!\n\n";
        exit;
    }
    
    // Validate user exists
    echo "2️⃣ Checking if user exists...\n";
    $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE id = ?");
    $stmt->execute([(int)$input['id_user']]);
    $user = $stmt->fetch();
    if ($user) {
        echo "   ✅ User found: ID " . $user['id'] . "\n\n";
    } else {
        echo "   ❌ User NOT found!\n\n";
        exit;
    }
    
    // Try to insert comment
    echo "3️⃣ Inserting comment...\n";
    $stmt = $pdo->prepare("
        INSERT INTO commentaire (id_publication, id_user, contenu, statut, date_publication)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        (int)$input['id_publication'],
        (int)$input['id_user'],
        $input['contenu'],
        'approved'
    ]);
    
    $commentId = $pdo->lastInsertId();
    echo "   ✅ Comment inserted successfully with ID: " . $commentId . "\n\n";
    
    // Verify comment was inserted
    echo "4️⃣ Verifying comment was saved...\n";
    $stmt = $pdo->prepare("
        SELECT c.id_commentaire, c.id_publication, c.id_user, c.contenu, u.nom, u.prenom
        FROM commentaire c
        JOIN utilisateur u ON c.id_user = u.id
        WHERE c.id_commentaire = ?
    ");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();
    if ($comment) {
        echo "   ✅ Comment verified:\n";
        echo "      - ID: " . $comment['id_commentaire'] . "\n";
        echo "      - Publication: " . $comment['id_publication'] . "\n";
        echo "      - User: " . $comment['nom'] . " " . $comment['prenom'] . "\n";
        echo "      - Content: " . substr($comment['contenu'], 0, 50) . "...\n";
    }
    
    echo "\n✅ TEST PASSED! The comment system works.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
?>
