<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../db/db.php';
require_once '../db/log.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Adăugare categorie
if (isset($_POST['add'])) {
    $nume = trim($_POST['nume']);
    $descriere = trim($_POST['descriere']);

    if (!empty($nume)) {
        $stmt = $pdo->prepare("INSERT INTO categorii (nume, descriere) VALUES (:nume, :descriere)");
        $stmt->execute(['nume' => $nume, 'descriere' => $descriere]);
        log_activitate($pdo, "Adminul a adăugat categoria: $nume");
    }
}

// Ștergere categorie
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM categorii WHERE id = :id")->execute(['id' => $id]);
    log_activitate($pdo, "Adminul a șters categoria cu ID: $id");
}

// Editare categorie
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $nume = trim($_POST['nume']);
    $descriere = trim($_POST['descriere']);
    if (!empty($nume)) {
        $stmt = $pdo->prepare("UPDATE categorii SET nume = :nume, descriere = :descriere WHERE id = :id");
        $stmt->execute(['nume' => $nume, 'descriere' => $descriere, 'id' => $id]);
        log_activitate($pdo, "Adminul a modificat categoria cu ID: $id ($nume)");
    }
}

$categorii = $pdo->query("SELECT * FROM categorii ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestionare Categorii</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1f2e55;
            color: #fff;
            margin: 0;
            padding: 40px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        a.back {
            color: #ccc;
            text-decoration: none;
        }
        a.back:hover {
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background: #fff;
            color: #333;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f3f3f3;
        }
        .form-box {
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            margin: 0 auto;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #2d89ef;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #1b5fbd;
        }
        .edit-form {
            background-color: #eef2f7;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
        a.action {
            color: #2d89ef;
            margin-right: 10px;
        }
        a.action:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>🗂 Gestionare Categorii</h2>
<p style="text-align:center;"><a class="back" href="dashboard.php">← Înapoi la Dashboard</a></p>

<div class="form-box">
    <form method="POST">
        <h3>➕ Adaugă Categorie Nouă</h3>
        <label>Nume categorie:</label>
        <input type="text" name="nume" required>
        <label>Descriere (opțional):</label>
        <textarea name="descriere"></textarea>
        <button type="submit" name="add">Adaugă</button>
    </form>
</div>

<h3 style="margin-top: 40px;">📋 Lista Categorii</h3>
<table>
    <tr>
        <th>ID</th>
        <th>Nume</th>
        <th>Descriere</th>
        <th>Acțiuni</th>
    </tr>
    <?php foreach ($categorii as $cat): ?>
        <tr>
            <td><?= $cat['id'] ?></td>
            <td><?= htmlspecialchars($cat['nume']) ?></td>
            <td><?= htmlspecialchars($cat['descriere']) ?></td>
            <td>
                <a class="action" href="?edit=<?= $cat['id'] ?>">✏️ Editează</a>
                <a class="action" href="?delete=<?= $cat['id'] ?>" onclick="return confirm('Ștergi această categorie?')">🗑️ Șterge</a>
            </td>
        </tr>

        <?php if (isset($_GET['edit']) && $_GET['edit'] == $cat['id']): ?>
            <tr>
                <td colspan="4">
                    <div class="edit-form">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <label>Nume:</label>
                            <input type="text" name="nume" value="<?= htmlspecialchars($cat['nume']) ?>" required>
                            <label>Descriere:</label>
                            <textarea name="descriere"><?= htmlspecialchars($cat['descriere']) ?></textarea>
                            <button type="submit" name="edit">💾 Salvează</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>

</body>
</html>
