<?php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120); 

require_once 'backend/config.php';
require_once 'backend/ochrana.php';
header('Content-Type: application/json');

$token = $_GET['token'] ?? '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(["error" => "Chýba token."]);
    exit;
}

$stmt = $conn->prepare("SELECT name, description FROM boxes WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Krabica s daným tokenom neexistuje."]);
    exit;
}

$box = $result->fetch_assoc();

$stmt = $conn->prepare("SELECT id, name, description, quantity, rozmer, stav FROM items WHERE box_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    "box" => $box,
    "items" => $items
]);
?>