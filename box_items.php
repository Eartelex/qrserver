<?php
require_once 'backend/config.php';
require_once 'backend/ochrana.php';
include 'header.php';

$token = $_GET['token'] ?? '';
if (!$token) {
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

$stmt = $conn->prepare("SELECT * FROM items WHERE box_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$items = $stmt->get_result();
?>

    <h1 style="text-align: center; margin-bottom: 10px;">Položky v: <?= htmlspecialchars($box['name']) ?></h1>
    <div class="top-bar">
        <a href="add_item.php?token=<?= urlencode($token) ?>" class="btn create-button">Pridať položku</a>
        <a href="index.php" class="btn cancel-button">Späť na zoznam krabíc</a>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Názov</th>
                <th style="width: 10%;">Množstvo</th>
                <th style="width: 15%;">Rozmer</th>
                <th style="width: 10%;">Stav</th>
                <th style="width: 30%;">Popis</th>
                <th style="width: 20%;">Akcie</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td><?= htmlspecialchars($item['rozmer']) ?></td>
                    <td><?= htmlspecialchars($item['stav']) ?></td>
                    <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
                    <td class="actions">
                        <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn add-button">Upraviť</a>
                        <a href="backend/delete_item.php?id=<?= $item['id'] ?>&token=<?= urlencode($token) ?>" class="btn delete-button" onclick="return confirm('Naozaj chceš vymazať túto položku?')">Vymazať</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

<?php include 'footer.php'; ?>