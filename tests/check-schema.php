<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = config::getConnexion();
    
    // Check utilisateur table structure
    $stmt = $pdo->query("DESCRIBE utilisateur");
    $cols = $stmt->fetchAll();
    
    echo "Utilisateur table columns:\n";
    foreach($cols as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Check what users exist
    echo "\nExisting users:\n";
    $stmt = $pdo->query("SELECT id, nom, prenom, email FROM utilisateur LIMIT 10");
    $users = $stmt->fetchAll();
    if (count($users) > 0) {
        foreach($users as $user) {
            echo "  - ID: " . $user['id'] . ", Name: " . $user['nom'] . " " . $user['prenom'] . ", Email: " . $user['email'] . "\n";
        }
    } else {
        echo "  No users found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
