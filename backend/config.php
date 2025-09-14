<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "daniel_durana_items";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Chyba pripojenia: " . $conn->connect_error);
}
?>