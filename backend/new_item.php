<?php
require_once 'config.php';

$token = $name = $description = $rozmer = $stav = '';
$quantity = 0;

$contentType = $_SERVER["CONTENT_TYPE"] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (strpos($contentType, 'application/json') !== false) {
        $rawData = file_get_contents("php://input");
        $data = json_decode($rawData, true);

        $token = trim($data['token'] ?? '');
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $quantity = (int)($data['quantity'] ?? 0);
        $rozmer = trim($data['rozmer'] ?? '');
        $stav = trim($data['stav'] ?? '');
    } else {
        $token = trim($_POST['token'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        $rozmer = trim($_POST['rozmer'] ?? '');
        $stav = trim($_POST['stav'] ?? '');
    }

    if (empty($token) || empty($name) || $quantity < 1 || empty($stav)) {
        $error = "Chýbajú povinné údaje alebo sú neplatné.";
    } else {

        $stmt = $conn->prepare("SELECT token FROM boxes WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "Krabica s daným tokenom neexistuje.";
        } else {
        
            $stmt = $conn->prepare("INSERT INTO items (box_token, name, description, quantity, rozmer, stav) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiss", $token, $name, $description, $quantity, $rozmer, $stav);
            if ($stmt->execute()) {
            
                if (strpos($contentType, 'application/json') !== false) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Položka pridaná.']);
                } else {
                 
                    header("Location: ../box_items.php?token=" . urlencode($token));
                }
                exit;
            } else {
                $error = "Nepodarilo sa pridať položku: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

if (!empty($error)) {
    if (strpos($contentType, 'application/json') !== false) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $error]);
    } else {
        echo "<p style='color:red;'>$error</p>";
        echo "<p><a href='javascript:history.back()'>Späť</a></p>";
    }
}
?>