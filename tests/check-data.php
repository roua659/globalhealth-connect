<?php
require_once __DIR__ . '/config/database.php';
$pdo = config::getConnexion();

// Check publications
echo "📰 Publications:\n";
$stmt = $pdo->query("SELECT id_publication, id_medecin, contenu, date_publication FROM publication LIMIT 5");
$pubs = $stmt->fetchAll();
if (count($pubs) > 0) {
    foreach($pubs as $pub) {
        echo "  - ID: " . $pub['id_publication'] . ", Doctor ID: " . $pub['id_medecin'] . "\n";
    }
} else {
    echo "  ❌ No publications found\n";
}

// Check comments
echo "\n💬 Comments:\n";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM commentaire");
$result = $stmt->fetch();
echo "  Total: " . $result['count'] . "\n";

// Check users roles
echo "\n👥 Users:\n";
$stmt = $pdo->query("SELECT id, nom, prenom, id_role FROM utilisateur LIMIT 10");
$users = $stmt->fetchAll();
foreach($users as $user) {
    $role = $user['id_role'] == 1 ? 'Patient' : ($user['id_role'] == 2 ? 'Doctor' : 'Unknown');
    echo "  - ID: " . $user['id'] . ", " . $user['nom'] . " " . $user['prenom'] . " (Role: " . $role . ")\n";
}
?>
