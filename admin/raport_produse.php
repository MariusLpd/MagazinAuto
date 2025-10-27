<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$de_la = $_GET['de_la'] ?? '';
$pana_la = $_GET['pana_la'] ?? '';
$raport = [];
$produse = [];
$vanzari = [];

if ($de_la && $pana_la) {
    $sql = "
        SELECT p.nume AS produs, SUM(cp.cantitate) AS total_cantitate,
               SUM(cp.cantitate * cp.pret_unitar) AS total_vanzari
        FROM comenzi c
        JOIN comenzi_produse cp ON c.id = cp.comanda_id
        JOIN produse p ON cp.produs_id = p.id
        WHERE DATE(c.data_comanda) BETWEEN :de_la AND :pana_la
        GROUP BY p.id
        ORDER BY total_vanzari DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['de_la' => $de_la, 'pana_la' => $pana_la]);
    $raport = $stmt->fetchAll();

    foreach ($raport as $r) {
        $produse[] = $r['produs'];
        $vanzari[] = $r['total_vanzari'];
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Raport Vânzări pe Produse</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #eef3fa;
        }
        h2 {
            color: #2d89ef;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background-color: white;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        input[type="date"] {
            padding: 5px;
            margin-right: 10px;
        }
        button {
            padding: 6px 12px;
            background-color: #2d89ef;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #1b5fbd;
        }
        canvas {
            max-width: 100%;
            margin-top: 30px;
            background-color: white;
            padding: 10px;
            border-radius: 10px;
        }
        .btn-group {
            margin-top: 15px;
        }
        .btn-group form {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h2>📦 Raport vânzări pe produse</h2>
    <p><a href="dashboard.php">← Înapoi la Dashboard</a></p>

    <form method="GET">
        <label>De la:</label>
        <input type="date" name="de_la" value="<?= htmlspecialchars($de_la) ?>" required>
        <label>Până la:</label>
        <input type="date" name="pana_la" value="<?= htmlspecialchars($pana_la) ?>" required>
        <button type="submit">Generează raport</button>
    </form>

    <?php if ($de_la && $pana_la): ?>
        <h3>Rezultate pentru perioada: <?= htmlspecialchars($de_la) ?> – <?= htmlspecialchars($pana_la) ?></h3>

        <?php if (count($raport) === 0): ?>
            <p>Nu există comenzi în perioada selectată.</p>
        <?php else: ?>
            <canvas id="chartProduse" height="100"></canvas>

            <script>
                const ctx = document.getElementById('chartProduse').getContext('2d');
                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($produse) ?>,
                        datasets: [{
                            label: 'Vânzări (lei)',
                            data: <?= json_encode($vanzari) ?>,
                            backgroundColor: 'rgba(45, 137, 239, 0.8)',
                            borderColor: 'rgba(45, 137, 239, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + ' lei';
                                    }
                                }
                            }
                        }
                    }
                });
            </script>

            <table>
                <tr>
                    <th>Produs</th>
                    <th>Cantitate totală</th>
                    <th>Total vânzări (lei)</th>
                </tr>
                <?php foreach ($raport as $linie): ?>
                    <tr>
                        <td><?= htmlspecialchars($linie['produs']) ?></td>
                        <td><?= $linie['total_cantitate'] ?></td>
                        <td><?= number_format($linie['total_vanzari'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="btn-group">
                <form method="GET" action="export_raport_produse_xls.php">
                    <input type="hidden" name="de_la" value="<?= htmlspecialchars($de_la) ?>">
                    <input type="hidden" name="pana_la" value="<?= htmlspecialchars($pana_la) ?>">
                    <button type="submit">📅 Exportă XLS</button>
                </form>

                <form method="GET" action="export_raport_produse_pdf.php">
                    <input type="hidden" name="de_la" value="<?= htmlspecialchars($de_la) ?>">
                    <input type="hidden" name="pana_la" value="<?= htmlspecialchars($pana_la) ?>">
                    <button type="submit">📄 Exportă PDF</button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
