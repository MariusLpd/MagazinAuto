<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../db/db.php';
require_once '../db/log.php';

require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fisier'])) {
    $ext = pathinfo($_FILES['fisier']['name'], PATHINFO_EXTENSION);

    if ($ext !== 'xls' && $ext !== 'xlsx') {
        $mesaj = 'Fișier invalid. Se acceptă doar XLS sau XLSX.';
    } else {
        $filePath = $_FILES['fisier']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $total = 0;
            foreach ($rows as $index => $row) {
                if ($index === 0) continue;
                [$nume, $descriere, $pret, $stoc, $producator, $categorie_id] = $row;

                $stmt = $pdo->prepare("INSERT INTO produse (nume, descriere, pret, stoc, producator, categorie_id)
                                       VALUES (:nume, :descriere, :pret, :stoc, :producator, :categorie_id)");
                $stmt->execute([
                    'nume' => $nume,
                    'descriere' => $descriere,
                    'pret' => (float)$pret,
                    'stoc' => (int)$stoc,
                    'producator' => $producator,
                    'categorie_id' => (int)$categorie_id
                ]);
                $total++;
            }

            log_activitate($pdo, "Adminul a importat $total produse din fișier XLS.");
            $_SESSION['import_succes'] = "Import reușit: $total produse adăugate.";
            header("Location: produse.php");
            exit;

        } catch (Exception $e) {
            $mesaj = 'Eroare la procesarea fișierului: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Import Produse din XLS</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #e8f0fe;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
        }

        .container {
            background-color: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        h2 {
            text-align: center;
            color: #2d3e50;
            margin-bottom: 20px;
        }

        a {
            text-decoration: none;
            color: #2d89ef;
        }

        a:hover {
            text-decoration: underline;
        }

        form {
            margin-top: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="file"] {
            padding: 8px;
            margin-bottom: 15px;
            width: 100%;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background-color: #218838;
        }

        .mesaj {
            margin-top: 20px;
            color: red;
            font-weight: bold;
            text-align: center;
        }

        .top-link {
            display: block;
            margin-bottom: 10px;
            color: #2d89ef;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📥 Import Produse din Fișier XLS</h2>
        <a class="top-link" href="dashboard.php">← Înapoi la Dashboard</a>

        <form method="POST" enctype="multipart/form-data">
            <label>Selectează fișier XLS/XLSX:</label>
            <input type="file" name="fisier" required>
            <button type="submit">Importă</button>
        </form>

        <?php if ($mesaj): ?>
            <div class="mesaj"><?= htmlspecialchars($mesaj) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
