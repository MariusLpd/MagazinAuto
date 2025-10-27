<?php
session_start();
require_once 'db/db.php';

$eroare = '';
$succes = '';
$rol_selectat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $parola = $_POST['parola'];
    $parola_conf = $_POST['parola_conf'];
    $rol = $_POST['rol'] ?? 'user';

    if (empty($username) || empty($email) || empty($parola) || empty($parola_conf)) {
        $eroare = "Completează toate câmpurile.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $eroare = "Email invalid.";
    } elseif ($parola !== $parola_conf) {
        $eroare = "Parolele nu coincid.";
    } elseif ($rol !== 'admin' && $rol !== 'user') {
        $eroare = "Rol invalid.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utilizatori WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        if ($stmt->rowCount() > 0) {
            $eroare = "Username sau email deja folosit.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO utilizatori (username, email, parola, rol) VALUES (:username, :email, :parola, :rol)");
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'parola' => $parola, // fără hash
                'rol' => $rol
            ]);
            $succes = "Cont creat cu succes ca '$rol'. Te poți autentifica.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Înregistrare - Magazin Auto</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: Arial, sans-serif;
            background-image: url('assets/img/background.png');
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
            max-width: 500px;
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        button {
            flex: 1;
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
        .success {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Înregistrare cont</h2>

        <?php if ($eroare): ?>
            <div class="error"><?= htmlspecialchars($eroare) ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="success"><?= htmlspecialchars($succes) ?></div>
            <p><a href="index.php">Mergi la login</a></p>
        <?php else: ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="parola" placeholder="Parolă" required>
            <input type="password" name="parola_conf" placeholder="Confirmare parolă" required>

            <input type="hidden" name="rol" id="rolInput" value="">

            <div class="btn-container">
                <button type="submit" onclick="document.getElementById('rolInput').value='user'">Cont User</button>
                <button type="submit" onclick="document.getElementById('rolInput').value='admin'">Cont Admin</button>
            </div>
        </form>

        <p>Ai deja cont? <a href="index.php">Autentifică-te</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
