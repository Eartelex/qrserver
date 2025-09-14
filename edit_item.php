<?php
require_once 'backend/config.php';
require_once 'backend/ochrana.php';
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<p class='error'>Chýbajúce ID položky.</p>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo "<p class='error'>Položka neexistuje.</p>";
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']) ?? '';
    $quantity = (int)($_POST['quantity'] ?? 0);
    $rozmer = trim($_POST['rozmer'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stav = $_POST['stav'] ?? 'dostupné';

    if ($name === '' || $quantity <= 0) {
        $error = "Názov a množstvo sú povinné polia.";
    } else {
        $stmt = $conn->prepare("UPDATE items SET name = ?, quantity = ?, rozmer = ?, description = ?, stav = ? WHERE id = ?");
        $stmt->bind_param("sisssi", $name, $quantity, $rozmer, $description, $stav, $id);
        $stmt->execute();

        header("Location: box_items.php?token=" . urlencode($item['box_token']));
        exit;
    }
}

include 'header.php';
?>

<h1 class="main-title">Upraviť položku</h1>

<form method="POST" class="createForm">
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div>
        <label for="name" class="form-label">Názov položky:</label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($item['name']) ?>">
    </div>

    <div>
        <label for="quantity" class="form-label">Množstvo:</label>
        <input type="number" id="quantity" name="quantity" min="1" required value="<?= htmlspecialchars($item['quantity']) ?>">
    </div>

    <div>
        <label for="rozmer" class="form-label">Rozmer:</label>
        <input type="text" id="rozmer" name="rozmer" value="<?= htmlspecialchars($item['rozmer']) ?>">
    </div>

    <div>
        <label for="description" class="form-label">Popis:</label>
        <textarea id="description" name="description" rows="5"><?= htmlspecialchars($item['description']) ?></textarea>
    </div>

    <div>
        <label for="stav" class="form-label">Stav:</label>
        <select name="stav" id="stav" required>
            <option value="dostupne" <?= $item['stav'] === 'dostupne' ? 'selected' : '' ?>>Dostupné</option>
            <option value="pozicane" <?= $item['stav'] === 'pozicane' ? 'selected' : '' ?>>Požičané</option>
            <option value="vybrane" <?= $item['stav'] === 'vybrane' ? 'selected' : '' ?>>Vybrané</option>
        </select>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn add-button">Uložiť zmeny</button>
        <a href="box_items.php?token=<?= urlencode($item['box_token']) ?>" class="btn cancel-button">Zrušiť</a>
    </div>
</form>

<?php include 'footer.php'; ?>