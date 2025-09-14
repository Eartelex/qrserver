<?php
include 'header.php';
require_once 'backend/ochrana.php';
$token = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
?>

<h1 class="main-title">Pridať krabicu</h1>

<form class="createForm">
    <div>
        <label for="name" class="form-label">Názov krabice:</label>
        <input type="text" id="name" required>
    </div>

    <div>
        <label for="description" class="form-label">Popis:</label>
        <textarea id="description" rows="5"></textarea>
    </div>

    <div>
        <label for="token" class="form-label">Token (automaticky):</label>
        <input type="text" id="token" value="<?= htmlspecialchars($token) ?>" readonly>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn add-button">Vytvoriť</button>
        <a href="index.php" class="btn cancel-button">Zrušiť</a>
    </div>
</form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('.createForm');
    if (!form) {
        console.error('Form with class createForm not found!');
        return;
    }
    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const token = document.querySelector("#token").value.trim();
        const name = document.querySelector("#name").value.trim();
        const description = document.querySelector("#description").value.trim();

        fetch("backend/new_box.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ token, name, description })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = "index.php";
            } else {
                alert("Chyba: " + data.error);
            }
        })
        .catch(err => {
            console.error("Chyba pri odosielaní:", err);
            alert("Nastala chyba pri odosielaní požiadavky.");
        });
    });
});
</script>

<?php include "footer.php"; ?>