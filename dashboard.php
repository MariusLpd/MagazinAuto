<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$rol = $_SESSION['rol'] ?? 'user';

if ($rol === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
} else {
    header("Location: user/dashboard.php");
    exit;
}
