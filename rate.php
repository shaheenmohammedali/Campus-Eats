<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$menu_id = intval($input['menu_id'] ?? 0);
$rating = intval($input['rating'] ?? 0);

if ($menu_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success'=>false,'message'=>'Invalid data']);
    exit;
}

// check if existing rating by this user for this menu
$stmt = $conn->prepare("SELECT rating_id FROM ratings WHERE user_id=? AND menu_id=? LIMIT 1");
$stmt->bind_param('ii', $user_id, $menu_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    // update existing rating
    $stmt2 = $conn->prepare("UPDATE ratings SET rating=?, created_at=NOW() WHERE rating_id=?");
    $stmt2->bind_param('ii', $rating, $row['rating_id']);
    $stmt2->execute();
} else {
    // insert new rating
    $stmt2 = $conn->prepare("INSERT INTO ratings (user_id, menu_id, rating, created_at) VALUES (?,?,?,NOW())");
    $stmt2->bind_param('iii', $user_id, $menu_id, $rating);
    $stmt2->execute();
}

// compute new stats
$stmt3 = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS count_rating FROM ratings WHERE menu_id=?");
$stmt3->bind_param('i', $menu_id);
$stmt3->execute();
$stats = $stmt3->get_result()->fetch_assoc();

echo json_encode([
    'success' => true,
    'avg' => round($stats['avg_rating'] ?? 0, 2),
    'count' => $stats['count_rating']
]);
exit;
?>
