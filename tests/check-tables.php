<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = config::getConnexion();
    
    echo "📊 TABLE STRUCTURES:\n\n";
    
    // Publication table
    echo "📰 PUBLICATION table:\n";
    $stmt = $pdo->query("DESCRIBE publication");
    $cols = $stmt->fetchAll();
    foreach($cols as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Publications in DB
    echo "\n📰 PUBLICATIONS in database:\n";
    $stmt = $pdo->query("SELECT id_publication, id_medecin, contenu FROM publication LIMIT 5");
    $pubs = $stmt->fetchAll();
    foreach($pubs as $pub) {
        echo "  - ID: " . $pub['id_publication'] . ", Doctor: " . $pub['id_medecin'] . "\n";
    }
    
    // Commentaire table
    echo "\n💬 COMMENTAIRE table:\n";
    $stmt = $pdo->query("DESCRIBE commentaire");
    $cols = $stmt->fetchAll();
    foreach($cols as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Check FK constraints
    echo "\n🔗 FOREIGN KEYS:\n";
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_NAME IN ('publication', 'commentaire') AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $fks = $stmt->fetchAll();
    foreach($fks as $fk) {
        echo "  - " . $fk['TABLE_NAME'] . "." . $fk['COLUMN_NAME'] . " → " . $fk['REFERENCED_TABLE_NAME'] . "." . $fk['REFERENCED_COLUMN_NAME'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
