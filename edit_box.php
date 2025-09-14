<?php
require_once 'backend/config.php';
require_once 'backend/ochrana.php';
$token = $_GET['token'] ?? '';
if (empty($token)) {
    echo "<p class='error'>Chýbajúci token.</p>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM boxes WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$box = $result->fetch_assoc();

if (!$box) {
    echo "<p class='error'>Krabica neexistuje.</p>";
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name === '') {
        $error = "Názov nemôže byť prázdny.";
    } else {
        $check = $conn->prepare("SELECT COUNT(*) FROM boxes WHERE name = ? AND token != ?");
        $check->bind_param("ss", $name, $token);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            $error = "Krabica s týmto názvom už existuje.";
        } else {
            $update = $conn->prepare("UPDATE boxes SET name = ?, description = ? WHERE token = ?");
            $update->bind_param("sss", $name, $description, $token);
            $update->execute();
            $update->close();
            header("Location: ../index.php");
            exit;
        }
    }
}
include 'header.php';
?>

<h1 class="main-title">Upraviť krabicu</h1>

<form method="POST" class="createForm">
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div>
        <label for="name" class="form-label">Názov krabice:</label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($box['name']) ?>">
    </div>

    <div>
        <label for="description" class="form-label">Popis:</label>
        <textarea id="description" name="description" rows="5"><?= htmlspecialchars($box['description']) ?></textarea>
    </div>

    <div>
        <label for="token" class="form-label">Token:</label>
        <input type="text" id="token" readonly value="<?= htmlspecialchars($box['token']) ?>">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn add-button">Uložiť zmeny</button>
        <a href="../index.php" class="btn cancel-button">Zrušiť</a>
    </div>
</form>

<?php include 'footer.php'; ?>