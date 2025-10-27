<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../db/db.php';
require_once '../db/log.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ➕ Adaugă produs în coș
if (isset($_GET['add'])) {
    $produs_id = (int)$_GET['add'];

    // Verifică stocul produsului
    $stocCheck = $pdo->prepare("SELECT stoc FROM produse WHERE id = :id");
    $stocCheck->execute(['id' => $produs_id]);
    $produs = $stocCheck->fetch();

    if (!$produs || $produs['stoc'] <= 0) {
        $_SESSION['error'] = "Produsul nu mai este disponibil în stoc.";
        header("Location: produse.php");
        exit;
    }

    $check = $pdo->prepare("SELECT * FROM cos WHERE utilizator_id = :uid AND produs_id = :pid");
    $check->execute(['uid' => $user_id, 'pid' => $produs_id]);

    if ($check->rowCount() > 0) {
        $pdo->prepare("UPDATE cos SET cantitate = cantitate + 1 WHERE utilizator_id = :uid AND produs_id = :pid")
            ->execute(['uid' => $user_id, 'pid' => $produs_id]);
    } else {
        $pdo->prepare("INSERT INTO cos (utilizator_id, produs_id, cantitate) VALUES (:uid, :pid, 1)")
            ->execute(['uid' => $user_id, 'pid' => $produs_id]);
    }

    log_activitate($pdo, "Userul a adăugat în coș produsul cu ID: $produs_id");
    header("Location: cos.php");
    exit;
}

// ➖ Scade cantitate
if (isset($_GET['scade'])) {
    $pid = (int)$_GET['scade'];
    $pdo->prepare("UPDATE cos SET cantitate = cantitate - 1 WHERE utilizator_id = :uid AND produs_id = :pid AND cantitate > 1")
        ->execute(['uid' => $user_id, 'pid' => $pid]);
    log_activitate($pdo, "Userul a scăzut cantitatea pentru produsul cu ID: $pid");
    header("Location: cos.php");
    exit;
}

// ➕ Crește cantitate
if (isset($_GET['creste'])) {
    $pid = (int)$_GET['creste'];
    $pdo->prepare("UPDATE cos SET cantitate = cantitate + 1 WHERE utilizator_id = :uid AND produs_id = :pid")
        ->execute(['uid' => $user_id, 'pid' => $pid]);
    log_activitate($pdo, "Userul a crescut cantitatea pentru produsul cu ID: $pid");
    header("Location: cos.php");
    exit;
}

// ❌ Șterge produs din coș
if (isset($_GET['sterge'])) {
    $pid = (int)$_GET['sterge'];
    $pdo->prepare("DELETE FROM cos WHERE utilizator_id = :uid AND produs_id = :pid")
        ->execute(['uid' => $user_id, 'pid' => $pid]);
    log_activitate($pdo, "Userul a șters din coș produsul cu ID: $pid");
    header("Location: cos.php");
    exit;
}

// ✅ Plasează comanda
if (isset($_POST['plaseaza_comanda'])) {
    $cos = $pdo->prepare("SELECT c.*, p.nume, p.pret, p.stoc, p.categorie_id FROM cos c JOIN produse p ON c.produs_id = p.id WHERE c.utilizator_id = :uid");
    $cos->execute(['uid' => $user_id]);
    $items = $cos->fetchAll();

    $erori_stoc = [];

    foreach ($items as $item) {
        if ($item['cantitate'] > $item['stoc']) {
            $erori_stoc[] = "Stoc insuficient pentru produsul \"{$item['nume']}\".";
        }
    }

    if (!empty($erori_stoc)) {
        $error = implode("<br>", $erori_stoc);
    } else {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['pret'] * $item['cantitate'];
        }

        $pdo->prepare("INSERT INTO comenzi (utilizator_id, total) VALUES (:uid, :total)")
            ->execute(['uid' => $user_id, 'total' => $total]);

        $comanda_id = $pdo->lastInsertId();

        foreach ($items as $item) {
            // Obține numele categoriei
            $catStmt = $pdo->prepare("SELECT c.nume FROM categorii c WHERE c.id = :catid");
            $catStmt->execute(['catid' => $item['categorie_id']]);
            $categorie = $catStmt->fetchColumn() ?? 'Necunoscut';

            // Inserează produsul în comenzi_produse cu categorie
            $pdo->prepare("INSERT INTO comenzi_produse (comanda_id, produs_id, nume_produs, cantitate, pret_unitar, categorie)
                           VALUES (:cid, :pid, :nume, :cant, :pret, :cat)")
                ->execute([
                    'cid' => $comanda_id,
                    'pid' => $item['produs_id'],
                    'nume' => $item['nume'],
                    'cant' => $item['cantitate'],
                    'pret' => $item['pret'],
                    'cat' => $categorie
                ]);

            // Scade stocul
            $pdo->prepare("UPDATE produse SET stoc = stoc - :cant WHERE id = :pid")
                ->execute(['cant' => $item['cantitate'], 'pid' => $item['produs_id']]);

            // Șterge produsul dacă stocul e 0
            if ($item['stoc'] - $item['cantitate'] <= 0) {
                $pdo->prepare("DELETE FROM produse WHERE id = :pid")->execute(['pid' => $item['produs_id']]);
                log_activitate($pdo, "Produsul cu ID {$item['produs_id']} a fost șters automat (stoc 0).");
            }
        }

        $pdo->prepare("DELETE FROM cos WHERE utilizator_id = :uid")->execute(['uid' => $user_id]);
        $success = "Comanda a fost plasată cu succes!";
        log_activitate($pdo, "Userul a plasat comanda ID: $comanda_id în valoare de $total lei");
    }
}

// 🛒 Obține coșul curent
$cos = $pdo->prepare("SELECT c.*, p.nume, p.pret FROM cos c JOIN produse p ON c.produs_id = p.id WHERE c.utilizator_id = :uid");
$cos->execute(['uid' => $user_id]);
$produse_cos = $cos->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Coș de cumpărături</title>
    <style>
        body {
            background: #c6d9f1;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .container {
            background-color: white;
            padding: 40px;
            margin: 40px 0;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            max-width: 900px;
            width: 100%;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .nav-links {
            margin-bottom: 20px;
            text-align: center;
        }

        .nav-links a {
            margin: 0 10px;
            text-decoration: none;
            color: #2d89ef;
            font-weight: bold;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            display: inline-block;
        }

        .btn-green { background-color: green; }
        .btn-red { background-color: darkred; }
        .btn-blue { background-color: royalblue; }

        .btn:hover {
            opacity: 0.9;
        }

        .message {
            margin: 10px 0 20px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        button[type="submit"] {
            background-color: green;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
        }

        button[type="submit"]:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🛒 Coșul tău de cumpărături</h2>

        <div class="nav-links">
            <a href="dashboard.php">← Înapoi la Dashboard</a> |
            <a href="produse.php">📦 Vezi produse</a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= $success ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <?php if (count($produse_cos) === 0): ?>
            <p style="text-align: center;">Coșul este gol.</p>
        <?php else: ?>
            <form method="POST">
                <table>
                    <tr>
                        <th>Produs</th>
                        <th>Preț</th>
                        <th>Cantitate</th>
                        <th>Total</th>
                        <th>Acțiuni</th>
                    </tr>
                    <?php $total = 0; ?>
                    <?php foreach ($produse_cos as $item): ?>
                        <?php $subtotal = $item['pret'] * $item['cantitate']; ?>
                        <?php $total += $subtotal; ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nume']) ?></td>
                            <td><?= number_format($item['pret'], 2) ?> lei</td>
                            <td>
                                <a class="btn btn-blue" href="?scade=<?= $item['produs_id'] ?>">−</a>
                                <?= $item['cantitate'] ?>
                                <a class="btn btn-blue" href="?creste=<?= $item['produs_id'] ?>">+</a>
                            </td>
                            <td><?= number_format($subtotal, 2) ?> lei</td>
                            <td>
                                <a class="btn btn-red" href="?sterge=<?= $item['produs_id'] ?>">❌ Șterge</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                        <td colspan="2"><strong><?= number_format($total, 2) ?> lei</strong></td>
                    </tr>
                </table>

                <div style="text-align: center;">
                    <button type="submit" name="plaseaza_comanda">✅ Plasează Comanda</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
