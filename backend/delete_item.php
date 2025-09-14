<?php
require_once 'config.php';

$id = $_GET['id'] ?? null;
$token = $_GET['token'] ?? '';

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Neplatné ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Chyba databázy']);
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: ../box_items.php' . ($token ? '?token=' . urlencode($token) : ''));
exit;
?>
