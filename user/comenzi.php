<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Obține comenzile cu produsele aferente
$comenzi = $pdo->prepare("SELECT * FROM comenzi WHERE utilizator_id = :uid ORDER BY data_comanda DESC");
$comenzi->execute(['uid' => $user_id]);
$lista_comenzi = $comenzi->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Istoric Comenzi</title>
    <style>
        body {
            background-color: #c6d9f1;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .container {
            background: white;
            margin: 40px 0;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            max-width: 900px;
            width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .nav-links {
            text-align: center;
            margin-bottom: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: #2d89ef;
            font-weight: bold;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        .comanda {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .comanda h3 {
            margin: 0 0 15px 0;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #aaa;
            text-align: center;
        }

        form {
            display: inline;
        }

        button {
            margin-top: 12px;
            padding: 8px 16px;
            background-color: #2d89ef;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #1b5fbd;
        }

        .no-comenzi {
            text-align: center;
            color: #555;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧾 Istoricul comenzilor tale</h2>

        <div class="nav-links">
            <a href="dashboard.php">← Înapoi la Dashboard</a>
        </div>

        <?php if (count($lista_comenzi) === 0): ?>
            <p class="no-comenzi">Nu ai nicio comandă plasată încă.</p>
        <?php else: ?>
            <?php foreach ($lista_comenzi as $comanda): ?>
                <div class="comanda">
                    <h3>Comandă #<?= $comanda['id'] ?> – <?= $comanda['data_comanda'] ?> – Total: <?= number_format($comanda['total'], 2) ?> lei</h3>

                    <?php
                    $detalii = $pdo->prepare("SELECT nume_produs, cantitate, pret_unitar FROM comenzi_produse WHERE comanda_id = :cid");
                    $detalii->execute(['cid' => $comanda['id']]);
                    $produse = $detalii->fetchAll();
                    ?>

                    <table>
                        <tr>
                            <th>Produs</th>
                            <th>Cantitate</th>
                            <th>Preț unitar</th>
                            <th>Total produs</th>
                        </tr>
                        <?php foreach ($produse as $prod): ?>
                            <tr>
                                <td><?= htmlspecialchars($prod['nume_produs']) ?></td>
                                <td><?= $prod['cantitate'] ?></td>
                                <td><?= number_format($prod['pret_unitar'], 2) ?> lei</td>
                                <td><?= number_format($prod['pret_unitar'] * $prod['cantitate'], 2) ?> lei</td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <form action="factura_pdf.php" method="GET">
                        <input type="hidden" name="id_comanda" value="<?= $comanda['id'] ?>">
                        <button type="submit">🧾 Exportă factură PDF</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
