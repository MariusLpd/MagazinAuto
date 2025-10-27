<?php
session_start();
require_once '../db/db.php';
require_once '../db/log.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Preluăm categoriile pentru dropdown
$categorii = $pdo->query("SELECT id, nume FROM categorii ORDER BY nume")->fetchAll();

// Adăugare produs
if (isset($_POST['add'])) {
    $nume = trim($_POST['nume']);
    $descriere = trim($_POST['descriere']);
    $pret = floatval($_POST['pret']);
    $stoc = intval($_POST['stoc']);
    $producator = trim($_POST['producator']);
    $categorie_id = intval($_POST['categorie_id']);

    if (!empty($nume) && $pret >= 0 && $stoc >= 0) {
        $stmt = $pdo->prepare("INSERT INTO produse (nume, descriere, pret, stoc, producator, categorie_id) 
                               VALUES (:nume, :descriere, :pret, :stoc, :producator, :categorie_id)");
        $stmt->execute([
            'nume' => $nume,
            'descriere' => $descriere,
            'pret' => $pret,
            'stoc' => $stoc,
            'producator' => $producator,
            'categorie_id' => $categorie_id ?: null
        ]);
        log_activitate($pdo, "Adminul a adăugat produsul: $nume (preț: $pret, stoc: $stoc)");
    }
}

// Ștergere produs
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM produse WHERE id = :id")->execute(['id' => $id]);
    log_activitate($pdo, "Adminul a șters produsul cu ID: $id");
}

// Editare produs
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $nume = trim($_POST['nume']);
    $descriere = trim($_POST['descriere']);
    $pret = floatval($_POST['pret']);
    $stoc = intval($_POST['stoc']);
    $producator = trim($_POST['producator']);
    $categorie_id = intval($_POST['categorie_id']);

    if (!empty($nume)) {
        $stmt = $pdo->prepare("UPDATE produse 
            SET nume = :nume, descriere = :descriere, pret = :pret, stoc = :stoc, 
                producator = :producator, categorie_id = :categorie_id 
            WHERE id = :id");
        $stmt->execute([
            'nume' => $nume,
            'descriere' => $descriere,
            'pret' => $pret,
            'stoc' => $stoc,
            'producator' => $producator,
            'categorie_id' => $categorie_id ?: null,
            'id' => $id
        ]);
        log_activitate($pdo, "Adminul a modificat produsul cu ID: $id ($nume)");
    }
}

// Obținem toate produsele
$produse = $pdo->query("
    SELECT p.*, c.nume AS categorie
    FROM produse p
    LEFT JOIN categorii c ON p.categorie_id = c.id
    ORDER BY p.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestionare Produse</title>
    <style>
        body {
            font-family: Arial;
            background-color: #e8f0fe;
            margin: 0;
            padding: 30px;
        }

        h2 {
            color: #2d3e50;
        }

        a {
            color: #2d89ef;
            text-decoration: none;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 5px;
            background-color: #2d89ef;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #1b5fbd;
        }

        .button-link {
            display: inline-block;
            margin: 10px 0 20px 0;
            padding: 8px 14px;
            background-color: #2d89ef;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .button-link:hover {
            background-color: #1b5fbd;
        }

        .button-orange {
            background-color: orange !important;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }

        form {
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }

        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin: 6px 0 12px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .edit-form {
            margin-top: 10px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 10px;
        }

        .actions a {
            margin: 0 4px;
        }
    </style>
</head>
<body>
    <h2>Gestionare Produse</h2>
    <p><a href="dashboard.php">← Înapoi la Dashboard</a></p>

    <a href="import_produse.php" class="button-link button-orange">📥 Importă produse</a>

    <form method="POST">
        <h3>Adaugă Produs Nou</h3>
        <input type="text" name="nume" placeholder="Nume produs" required>
        <textarea name="descriere" placeholder="Descriere"></textarea>
        <input type="number" step="0.01" name="pret" placeholder="Preț" required>
        <input type="number" name="stoc" placeholder="Stoc" required>
        <input type="text" name="producator" placeholder="Producător">

        <select name="categorie_id">
            <option value="">-- Selectează categorie --</option>
            <?php foreach ($categorii as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nume']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="add" class="btn">Adaugă</button>
    </form>

    <h3>Lista Produse</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Nume</th>
            <th>Descriere</th>
            <th>Preț</th>
            <th>Stoc</th>
            <th>Producător</th>
            <th>Categorie</th>
            <th>Acțiuni</th>
        </tr>
        <?php foreach ($produse as $prod): ?>
        <tr>
            <td><?= $prod['id'] ?></td>
            <td><?= htmlspecialchars($prod['nume']) ?></td>
            <td><?= htmlspecialchars($prod['descriere']) ?></td>
            <td><?= number_format($prod['pret'], 2) ?> lei</td>
            <td><?= $prod['stoc'] ?></td>
            <td><?= htmlspecialchars($prod['producator']) ?></td>
            <td><?= htmlspecialchars($prod['categorie'] ?? '—') ?></td>
            <td class="actions">
                <a href="?delete=<?= $prod['id'] ?>" onclick="return confirm('Ștergi acest produs?')">🗑️</a>
                <a href="?edit=<?= $prod['id'] ?>">✏️</a>
            </td>
        </tr>
        <?php if (isset($_GET['edit']) && $_GET['edit'] == $prod['id']): ?>
        <tr>
            <td colspan="8">
                <div class="edit-form">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                        <input type="text" name="nume" value="<?= htmlspecialchars($prod['nume']) ?>" required>
                        <textarea name="descriere"><?= htmlspecialchars($prod['descriere']) ?></textarea>
                        <input type="number" step="0.01" name="pret" value="<?= $prod['pret'] ?>" required>
                        <input type="number" name="stoc" value="<?= $prod['stoc'] ?>" required>
                        <input type="text" name="producator" value="<?= htmlspecialchars($prod['producator']) ?>">

                        <select name="categorie_id">
                            <option value="">-- Fără categorie --</option>
                            <?php foreach ($categorii as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($prod['categorie_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nume']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" name="edit" class="btn">Salvează</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </table>
</body>
</html>
