<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = config::getConnexion();
    
    echo "🔧 Setting up test data...\n\n";
    
    // 1. Add roles if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO role (id_role, nom_role) VALUES (?, ?)");
    $stmt->execute([1, 'Patient']);
    $stmt->execute([2, 'Medecin']);
    echo "✅ Roles added\n";
    
    // 2. Add test patient/user
    $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $stmt->execute(['patient@test.com']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO utilisateur (nom, prenom, email, pwd, id_role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute(['Dupont', 'Jean', 'patient@test.com', password_hash('123456', PASSWORD_BCRYPT), 1]);
        echo "✅ Test patient created (ID: " . $pdo->lastInsertId() . ")\n";
    } else {
        echo "ℹ️ Test patient already exists\n";
    }
    
    // 3. Add test doctor/medecin
    $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ? AND id_role = 2");
    $stmt->execute(['doctor@test.com']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO utilisateur (nom, prenom, email, pwd, id_role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute(['Martin', 'Pierre', 'doctor@test.com', password_hash('123456', PASSWORD_BCRYPT), 2]);
        $doctorId = $pdo->lastInsertId();
        echo "✅ Test doctor created (ID: " . $doctorId . ")\n";
        
        // 4. Add test publication
        $stmt = $pdo->prepare("
            INSERT INTO publication (id_medecin, contenu, date_publication, statut) 
            VALUES (?, ?, NOW(), 'approved')
        ");
        $stmt->execute([$doctorId, 'Ceci est une publication test. Vous pouvez maintenant ajouter des commentaires!']);
        $pubId = $pdo->lastInsertId();
        echo "✅ Test publication created (ID: " . $pubId . ")\n";
    } else {
        echo "ℹ️ Test doctor already exists\n";
    }
    
    echo "\n📊 Database Summary:\n";
    
    // Show users count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateur");
    $count = $stmt->fetch();
    echo "   Users: " . $count['count'] . "\n";
    
    // Show publications count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM publication");
    $count = $stmt->fetch();
    echo "   Publications: " . $count['count'] . "\n";
    
    // Show comments count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM commentaire");
    $count = $stmt->fetch();
    echo "   Comments: " . $count['count'] . "\n";
    
    echo "\n✅ Setup complete!\n";
    echo "\n🌐 You can now:\n";
    echo "   1. Visit: http://localhost/globalhealth-connect1/views/frontoffice/layout/index.php\n";
    echo "   2. Scroll to Forum section\n";
    echo "   3. Click 'Commenter' on any publication\n";
    echo "   4. Add your comment and publish!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
