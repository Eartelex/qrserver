<?php
require_once 'backend/config.php';
require_once 'backend/ochrana.php';
include 'header.php';

$search = $_GET['search'] ?? '';

$sql = "
SELECT DISTINCT b.*
FROM boxes b
LEFT JOIN items i ON b.token = i.box_token
WHERE b.name LIKE ? OR b.description LIKE ? OR i.name LIKE ?
ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>

<h1 class="main-title">QR Storage</h1>

<div class="top-bar">
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Hľadať..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn search-button">Hľadaj</button>
    </form>
    <a href="add_box.php" class="btn create-button">Pridať krabicu</a>
</div>
<table>
    <thead>
    <tr>
        <th style="width: 20%;">Token</th>
        <th style="width: 25%;">Názov</th>
        <th style="width: 35%;">Popis</th>
        <th style="width: 15%;">Vytvorené</th>
        <th style="width: 40%;">Akcie</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($result->num_rows == 0): ?>
        <tr>
            <td colspan="5" class="no-data">Žiadne krabice na zobrazenie.</td>
        </tr>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            $matchedItems = [];
            if (!empty($search)) {
                $stmtItems = $conn->prepare("SELECT name FROM items WHERE box_token = ? AND name LIKE ?");
                $stmtItems->bind_param("ss", $row['token'], $searchTerm);
                $stmtItems->execute();
                $resItems = $stmtItems->get_result();
                while ($item = $resItems->fetch_assoc()) {
                    $matchedItems[] = $item['name'];
                }
                $stmtItems->close();
            }
            ?>
            <tr>
                <td><?= htmlspecialchars($row['token']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>
                    <?= htmlspecialchars($row['description']) ?>
                    <?php if (!empty($matchedItems)): ?>
                        <div style="margin-top: 6px; font-size: 13px; color: #aaa;">
                            Zhody: <?= htmlspecialchars(implode(", ", $matchedItems)) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td class="actions">
                    <a class="btn open-button" href="box_items.php?token=<?= urlencode($row['token']) ?>">Otvoriť</a>
                    <a class="btn add-button" href="edit_box.php?token=<?= urlencode($row['token']) ?>">Upraviť</a>
                    <a class="btn delete-button" href="backend/delete_box.php?token=<?= urlencode($row['token']) ?>" onclick="return confirm('Naozaj vymazať túto krabicu a všetky položky v nej?')">Vymazať</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php include "footer.php"; ?>