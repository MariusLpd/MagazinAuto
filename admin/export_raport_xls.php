<?php
require_once '../db/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    exit('Acces interzis');
}

$de_la = $_GET['de_la'] ?? '';
$pana_la = $_GET['pana_la'] ?? '';

if (!$de_la || !$pana_la) {
    exit('Interval lipsă');
}

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

// Setăm header-ele pentru XLS
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=raport_vanzari_{$de_la}_{$pana_la}.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr><th>Categorie</th><th>Total vanzari (lei)</th></tr>";

foreach ($raport as $linie) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($linie['categorie']) . "</td>";
    echo "<td>" . number_format($linie['total_vanzari'], 2) . "</td>";
    echo "</tr>";
}

echo "</table>";
exit;
