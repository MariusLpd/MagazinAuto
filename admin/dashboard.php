<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panou de administrare</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            background: url('../assets/img/background.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        h2 {
            margin-bottom: 30px;
            color: #333;
        }

        .button {
            display: block;
            margin: 12px auto;
            padding: 12px 20px;
            background-color: #2d89ef;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            width: 80%;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #1b5fbd;
        }

        .logout {
            background-color: darkred !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bine ai venit, <?= htmlspecialchars($_SESSION['username']) ?> (Admin)</h2>

        <a href="categorii.php" class="button">🗂 Gestionare Categorii</a>
        <a href="produse.php" class="button">🛠 Gestionare Produse</a>
        <a href="raport.php" class="button">📊 Raport Vânzări</a>
        <a href="raport_produse.php" class="button">📦 Raport Produse</a>
        <a href="import_produse.php" class="button">📥 Import Produse</a>
        <a href="../logout.php" class="button logout">🚪 Logout</a>
    </div>
</body>
</html>
