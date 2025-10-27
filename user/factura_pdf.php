<?php
require_once '../db/db.php';
require '../lib/fpdf/fpdf.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    exit('Acces interzis');
}

if (!isset($_GET['id_comanda'])) {
    exit('Comandă invalidă');
}

$comanda_id = (int)$_GET['id_comanda'];
$user_id = $_SESSION['user_id'];

// Verificăm dacă comanda aparține userului
$stmt = $pdo->prepare("SELECT * FROM comenzi WHERE id = :id AND utilizator_id = :uid");
$stmt->execute(['id' => $comanda_id, 'uid' => $user_id]);
$comanda = $stmt->fetch();

if (!$comanda) {
    exit('Comandă inexistentă');
}

// Preluăm produsele din comandă
$stmt = $pdo->prepare("
    SELECT cp.*, p.nume 
    FROM comenzi_produse cp
    JOIN produse p ON cp.produs_id = p.id
    WHERE cp.comanda_id = :cid
");
$stmt->execute(['cid' => $comanda_id]);
$produse = $stmt->fetchAll();

// Inițializare PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Factura - Comanda #'.$comanda_id, 0, 1, 'C');
$pdf->SetFont('Arial','',12);
$pdf->Ln(5);

$pdf->Cell(0,10,'Data comenzii: ' . $comanda['data_comanda'], 0, 1);
$pdf->Cell(0,10,'Total: ' . number_format($comanda['total'], 2) . ' lei', 0, 1);
$pdf->Ln(5);

// Tabel produse
$pdf->SetFont('Arial','B',12);
$pdf->Cell(80,10,'Produs',1);
$pdf->Cell(30,10,'Cantitate',1);
$pdf->Cell(40,10,'Pret unitar',1);
$pdf->Cell(40,10,'Total',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
foreach ($produse as $p) {
    $total_produs = $p['cantitate'] * $p['pret_unitar'];
    $pdf->Cell(80,10,$p['nume'],1);
    $pdf->Cell(30,10,$p['cantitate'],1,0,'C');
    $pdf->Cell(40,10,number_format($p['pret_unitar'], 2).' lei',1,0,'R');
    $pdf->Cell(40,10,number_format($total_produs, 2).' lei',1,0,'R');
    $pdf->Ln();
}

$pdf->Output('I', 'Factura_Comanda_'.$comanda_id.'.pdf');
exit;
