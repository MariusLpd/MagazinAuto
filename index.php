<?php
session_start();
//require_once 'db/db.php'; // conexiune BD

$eroare = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $parola = $_POST['parola'];

    if (empty($username) || empty($parola)) {
        $eroare = "Completează toate câmpurile.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilizatori WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && $parola === $user['parola']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['rol'] = $user['rol'];

            header("Location: dashboard.php");
            exit;
        } else {
            $eroare = "Username sau parolă incorecte.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Autentificare - Magazin Auto</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: Arial, sans-serif;
            background-image: url('assets/img/background.png'); /* Schimbă calea dacă imaginea e altundeva */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #0056b3;
        }

        p {
            text-align: center;
            margin-top: 15px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Autentificare</h2>

        <?php if (!empty($eroare)): ?>
            <div class="error"><?= htmlspecialchars($eroare) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="parola" placeholder="Parolă" required>
            <button type="submit">Autentificare</button>
        </form>

        <p>Nu ai cont? <a href="register.php">Înregistrează-te</a></p>
    </div>
</body>
</html>
