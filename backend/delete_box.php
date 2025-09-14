<?php
require_once 'config.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    header('Location: ../index.php');
    exit;
}

$stmtItems = $conn->prepare("DELETE FROM items WHERE box_token = ?");
$stmtItems->bind_param("s", $token);
$stmtItems->execute();
$stmtItems->close();

$stmtBox = $conn->prepare("DELETE FROM boxes WHERE token = ?");
$stmtBox->bind_param("s", $token);
$stmtBox->execute();
$stmtBox->close();

$conn->close();

header('Location: ../index.php');
exit;