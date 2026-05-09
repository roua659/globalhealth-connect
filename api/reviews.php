<?php
require_once __DIR__ . '/../controllers/ForumController.php';

$controller = new ForumController();
$controller->handle($_SERVER['REQUEST_METHOD'] === 'GET' ? 'get-reviews' : 'add-review');
