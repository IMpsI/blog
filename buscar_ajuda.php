<?php
require 'db.php';
$q = $_GET['q'] ?? '';
$posts = [];
if (strlen($q) >= 2) {
    $stmt = $db->prepare("SELECT id, titulo FROM posts WHERE titulo LIKE :q LIMIT 3");
    $stmt->bindValue(':q', "%$q%", SQLITE3_TEXT);
    $res = $stmt->execute();
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $posts[] = $row;
    }
}
echo json_encode($posts);
