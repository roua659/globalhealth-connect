<?php
echo "<h2>Vérification des données</h2>";

require_once 'config/database.php';
require_once 'models/UtilisateurModel.php';

$db = new Database();
$conn = $db->getConnection();
$utilisateurModel = new UtilisateurModel();

// Vérifier les médecins
echo "<h3>Médecins :</h3>";
$medecins = $utilisateurModel->getMedecins();
if (empty($medecins)) {
    echo "<p style='color:red'>❌ Aucun médecin trouvé !</p>";
    
    // Vérifier la table medecin
    $query = "SELECT * FROM medecin";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    echo "<p>Table medecin: " . $stmt->rowCount() . " ligne(s)</p>";
    
    $query = "SELECT * FROM utilisateur WHERE id_role = 2";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    echo "<p>Utilisateurs avec rôle médecin: " . $stmt->rowCount() . "</p>";
} else {
    foreach($medecins as $m) {
        echo "<p>✅ Dr. " . $m['prenom'] . " " . $m['nom'] . " - " . $m['specialite'] . "</p>";
    }
}

// Vérifier les patients
echo "<h3>Patients :</h3>";
$patients = $utilisateurModel->getPatients();
if (empty($patients)) {
    echo "<p style='color:red'>❌ Aucun patient trouvé !</p>";
} else {
    foreach($patients as $p) {
        echo "<p>✅ " . $p['prenom'] . " " . $p['nom'] . " - " . $p['email'] . "</p>";
    }
}
?>