<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

// Preluăm categoriile pentru filtrare
$categorii = $pdo->query("SELECT id, nume FROM categorii ORDER BY nume")->fetchAll();

// Construim query dinamic
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "p.nume LIKE :search";
    $params['search'] = '%' . $_GET['search'] . '%';
}

if (!empty($_GET['categorie'])) {
    $where[] = "p.categorie_id = :categorie";
    $params['categorie'] = $_GET['categorie'];
}

if (!empty($_GET['pret_min'])) {
    $where[] = "p.pret >= :pret_min";
    $params['pret_min'] = $_GET['pret_min'];
}

if (!empty($_GET['pret_max'])) {
    $where[] = "p.pret <= :pret_max";
    $params['pret_max'] = $_GET['pret_max'];
}

$sortare = "p.nume ASC";
if (!empty($_GET['sort'])) {
    if ($_GET['sort'] == 'pret_asc') $sortare = "p.pret ASC";
    if ($_GET['sort'] == 'pret_desc') $sortare = "p.pret DESC";
}

// Final query
$sql = "SELECT p.*, c.nume AS categorie FROM produse p 
        LEFT JOIN categorii c ON p.categorie_id = c.id";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY $sortare";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produse = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Toate produsele</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
            background-color: #eef3fa;
        }
        h2 {
            color: #2d89ef;
        }
        form {
            background: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        form input, form select {
            padding: 8px;
            margin: 6px 8px 6px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        form button {
            padding: 8px 14px;
            background-color: #2d89ef;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #1b5fbd;
        }
        .reset-btn {
            background-color: #888;
        }
        .reset-btn:hover {
            background-color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #2d89ef;
            color: white;
        }
        .btn {
            padding: 6px 12px;
            background-color: green;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>

    <h2>📦 Toate produsele disponibile</h2>
    <p><a href="dashboard.php">← Înapoi la Dashboard</a></p>

    <form method="GET">
        <input type="text" name="search" placeholder="Caută produs..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

        <select name="categorie">
            <option value="">-- Toate categoriile --</option>
            <?php foreach ($categorii as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= (($_GET['categorie'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nume']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="pret_min" placeholder="Preț minim" value="<?= htmlspecialchars($_GET['pret_min'] ?? '') ?>">
        <input type="number" name="pret_max" placeholder="Preț maxim" value="<?= htmlspecialchars($_GET['pret_max'] ?? '') ?>">

        <select name="sort">
            <option value="">-- Sortare --</option>
            <option value="pret_asc" <?= ($_GET['sort'] ?? '') == 'pret_asc' ? 'selected' : '' ?>>Preț crescător</option>
            <option value="pret_desc" <?= ($_GET['sort'] ?? '') == 'pret_desc' ? 'selected' : '' ?>>Preț descrescător</option>
        </select>

        <button type="submit">🔍 Filtrează</button>
        <a href="produse.php" class="reset-btn" style="padding: 8px 14px; text-decoration: none; color: white; margin-left: 8px;">♻️ Resetează</a>
    </form>

    <table>
        <tr>
            <th>Nume</th>
            <th>Preț</th>
            <th>Stoc</th>
            <th>Producător</th>
            <th>Categorie</th>
            <th>Acțiuni</th>
        </tr>
        <?php foreach ($produse as $prod): ?>
        <tr>
            <td><?= htmlspecialchars($prod['nume']) ?></td>
            <td><?= number_format($prod['pret'], 2) ?> lei</td>
            <td><?= $prod['stoc'] ?></td>
            <td><?= htmlspecialchars($prod['producator']) ?></td>
            <td><?= htmlspecialchars($prod['categorie'] ?? '—') ?></td>
            <td>
                <a href="cos.php?add=<?= $prod['id'] ?>" class="btn">➕ Adaugă în coș</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
