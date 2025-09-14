<?php
require_once 'backend/config.php';
require_once 'backend/ochrana.php';
$token = $_GET['token'] ?? '';
if (!$token) {
    echo "<p class='error'>Chýbajúci token krabice.</p>";
    exit;
}

$error = '';
$name = $_GET['name'] ?? '';
$quantity = $_GET['quantity'] ?? 1;
$rozmer = $_GET['rozmer'] ?? '';
$stav = $_GET['stav'] ?? 'Dostupné';
$description = $_GET['description'] ?? '';

if (isset($_GET['check']) && !empty($name)) {
    $stmt = $conn->prepare("SELECT id FROM items WHERE box_token = ? AND name = ?");
    $stmt->bind_param("ss", $token, $name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $error = "Položka s názvom '$name' už v tejto krabici existuje.";
    }
    $stmt->close();
}

include 'header.php';
?>

<h1 class="main-title">Pridať položku do krabice</h1>

<?php if ($error): ?>
    <p style="color: red; text-align: center; font-weight: bold; margin-bottom: 20px;">Error:<?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form class="createForm" method="POST" action="backend/new_item.php" onsubmit="return validateForm()">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <div>
        <label for="name" class="form-label">Názov položky:</label>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($name) ?>">
    </div>

    <div>
        <label for="quantity" class="form-label">Množstvo:</label>
        <input type="number" id="quantity" name="quantity" required min="1" value="<?= htmlspecialchars($quantity) ?>">
    </div>

    <div>
        <label for="rozmer" class="form-label">Rozmer:</label>
        <input type="text" id="rozmer" name="rozmer" value="<?= htmlspecialchars($rozmer) ?>">
    </div>

    <div>
        <label for="stav" class="form-label">Stav:</label>
        <select name="stav" id="stav" required>
            <option value="Dostupne" <?= $stav === 'Dostupne' ? 'selected' : '' ?>>Dostupné</option>
            <option value="Pouziva sa" <?= $stav === 'Pouziva sa' ? 'selected' : '' ?>>Používa sa</option>
            <option value="Pozicane" <?= $stav === 'Pozicane' ? 'selected' : '' ?>>Požičané</option>
        </select>
    </div>

    <div>
        <label for="description" class="form-label">Popis:</label>
        <textarea id="description" name="description" rows="5"><?= htmlspecialchars($description) ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn add-button">Vytvoriť</button>
        <a href="box_items.php?token=<?= urlencode($token) ?>" class="btn cancel-button">Zrušiť</a>
    </div>
</form>
</div>

<script>
function validateForm() {
    const name = document.getElementById('name').value.trim();
    if (!name) {
        alert('Názov položky je povinný.');
        return false;
    }
    return true;
}
</script>

<?php include 'footer.php'; ?>