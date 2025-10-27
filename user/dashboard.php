<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Utilizator</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('../assets/img/background.png') no-repeat center center fixed;
            background-size: cover;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
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
            margin: 15px auto;
            padding: 15px 20px;
            background-color: #2d89ef;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            width: 80%;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #1b5fbd;
        }

        .danger {
            background-color: darkred;
        }

        .danger:hover {
            background-color: #a80000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bine ai venit, <?= htmlspecialchars($_SESSION['username']) ?> (User)</h2>

        <a href="produse.php" class="button">🔍 Vezi toate produsele</a>
        <a href="cos.php" class="button">🛒 Vezi coșul tău</a>
        <a href="comenzi.php" class="button">🧾 Istoric comenzi</a>
        <a href="../logout.php" class="button danger">🚪 Logout</a>
    </div>
</body>
</html>
