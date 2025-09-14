<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

$token = $data['token'] ?? '';
$name = trim($data['name'] ?? '');
$description = trim($data['description'] ?? '');

if (!$token || !$name) {
    echo json_encode(['success' => false, 'error' => 'Chýbajúci token alebo názov krabice.']);
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM boxes WHERE name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    echo json_encode(['success' => false, 'error' => 'Krabica s týmto názvom už existuje.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO boxes (token, name, description, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $token, $name, $description);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Chyba pri ukladaní do databázy.']);
}
$stmt->close();
$conn->close();
?>