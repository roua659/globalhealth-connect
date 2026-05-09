<?php
require_once __DIR__ . '/../controllers/ForumController.php';

$action = $_GET['action'] ?? 'list';
$map = [
    'index' => 'list',
    'store' => 'store',
    'update' => 'update-publication',
    'destroy' => 'delete-publication',
];

$controller = new ForumController();
$controller->handle($map[$action] ?? $action);
