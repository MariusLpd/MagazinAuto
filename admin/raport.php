<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$raport = [];
$categorii = [];
$vanzari = [];
$de_la = $_GET['de_la'] ?? '';
$pana_la = $_GET['pana_la'] ?? '';

if ($de_la && $pana_la) {
    $sql = "
        SELECT cat.nume AS categorie, SUM(cp.cantitate * cp.pret_unitar) AS total_vanzari
        FROM comenzi c
        JOIN comenzi_produse cp ON c.id = cp.comanda_id
        JOIN produse p ON cp.produs_id = p.id
        JOIN categorii cat ON p.categorie_id = cat.id
        WHERE DATE(c.data_comanda) BETWEEN :de_la AND :pana_la
        GROUP BY cat.id
        ORDER BY total_vanzari DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['de_la' => $de_la, 'pana_la' => $pana_la]);
    $raport = $stmt->fetchAll();

    foreach ($raport as $r) {
        $categorii[] = $r['categorie'];
        $vanzari[] = $r['total_vanzari'];
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Raport Vânzări pe Categorii</title>
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
            padding: 6px;
            margin: 10px 10px 10px 0;
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
    <h2>📊 Raport vânzări pe categorii</h2>
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
            <canvas id="chartVanzari" height="100"></canvas>

            <script>
                const ctx = document.getElementById('chartVanzari').getContext('2d');
                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($categorii) ?>,
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
                    <th>Categorie</th>
                    <th>Total vânzări (lei)</th>
                </tr>
                <?php foreach ($raport as $linie): ?>
                    <tr>
                        <td><?= htmlspecialchars($linie['categorie']) ?></td>
                        <td><?= number_format($linie['total_vanzari'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="btn-group">
                <form method="GET" action="export_raport_xls.php">
                    <input type="hidden" name="de_la" value="<?= htmlspecialchars($de_la) ?>">
                    <input type="hidden" name="pana_la" value="<?= htmlspecialchars($pana_la) ?>">
                    <button type="submit">📅 Exportă XLS</button>
                </form>

                <form method="GET" action="export_raport_pdf.php">
                    <input type="hidden" name="de_la" value="<?= htmlspecialchars($de_la) ?>">
                    <input type="hidden" name="pana_la" value="<?= htmlspecialchars($pana_la) ?>">
                    <button type="submit">📄 Exportă PDF</button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
