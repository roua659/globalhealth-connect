<?php
require_once __DIR__ . '/config/database.php';
$pdo = config::getConnexion();
$stmt = $pdo->query('DESCRIBE publication');
$columns = $stmt->fetchAll();
echo json_encode($columns, JSON_PRETTY_PRINT);
?>
