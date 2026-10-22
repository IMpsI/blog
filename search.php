<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$query = trim($_GET['q'] ?? '');
echo json_encode($query === '' ? [] : dbAll('SELECT id, titulo FROM posts WHERE titulo LIKE ? ORDER BY data_publicacao DESC LIMIT 3', ["%{$query}%"]), JSON_UNESCAPED_UNICODE);
