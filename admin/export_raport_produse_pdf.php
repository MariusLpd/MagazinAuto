<?php
require_once '../db/db.php';
require '../lib/fpdf/fpdf.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    exit('Acces interzis');
}

$de_la = $_GET['de_la'] ?? '';
$pana_la = $_GET['pana_la'] ?? '';

if (!$de_la || !$pana_la) exit('Perioadă lipsa');

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

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Raport Vanzari pe Produse', 0, 1, 'C');
$pdf->SetFont('Arial','',12);
$pdf->Ln(3);
$pdf->Cell(0,10,'Perioada: '.$de_la.' - '.$pana_la, 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,10,'Produs',1);
$pdf->Cell(40,10,'Cantitate',1);
$pdf->Cell(50,10,'Total (lei)',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
foreach ($raport as $r) {
    $pdf->Cell(90,10,$r['produs'],1);
    $pdf->Cell(40,10,$r['total_cantitate'],1,0,'C');
    $pdf->Cell(50,10,number_format($r['total_vanzari'], 2).' lei',1,0,'R');
    $pdf->Ln();
}

$pdf->Output('I', 'Raport_Produse_'.$de_la.'_to_'.$pana_la.'.pdf');
exit;
