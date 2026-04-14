<?php
header('Content-Type: application/json');

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Publication.php';
require_once __DIR__ . '/models/Commentaire.php';

// Test 1: Check database connection
try {
    $pdo = config::getConnexion();
    $result['database'] = 'Connected ✅';
} catch (Exception $e) {
    $result['database'] = 'Error: ' . $e->getMessage();
}

// Test 2: Check publication table exists
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->query("SHOW TABLES LIKE 'publication'");
    $tableExists = $stmt->rowCount() > 0;
    $result['table_publication'] = $tableExists ? 'Exists ✅' : 'Not found ❌';
} catch (Exception $e) {
    $result['table_publication'] = 'Error: ' . $e->getMessage();
}

// Test 3: Check commentaire table exists
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->query("SHOW TABLES LIKE 'commentaire'");
    $tableExists = $stmt->rowCount() > 0;
    $result['table_commentaire'] = $tableExists ? 'Exists ✅' : 'Not found ❌';
} catch (Exception $e) {
    $result['table_commentaire'] = 'Error: ' . $e->getMessage();
}

// Test 4: Try to create a publication
try {
    $pub = new Publication();
    $pub->setContenu('This is a test publication content for debugging ' . time())
        ->setDatePublication(date('Y-m-d H:i:s'));
    
    $result['publication_before_save'] = $pub->toArray();
    
    $saveResult = $pub->save();
    $result['publication_save_result'] = $saveResult;
    
    if ($saveResult['success']) {
        $result['publication_saved'] = 'Success ✅';
        $result['publication_id'] = $saveResult['id'];
    } else {
        $result['publication_saved'] = 'Failed ❌';
        $result['publication_error'] = $saveResult['error'];
    }
} catch (Exception $e) {
    $result['publication_error'] = $e->getMessage();
    $result['publication_saved'] = 'Exception ❌';
}

// Test 5: Verify publication was saved
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM publication");
    $row = $stmt->fetch();
    $result['publication_count'] = $row['count'];
} catch (Exception $e) {
    $result['publication_count_error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
