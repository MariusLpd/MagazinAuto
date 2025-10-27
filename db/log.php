<?php
function log_activitate($pdo, $actiune, $sql = null) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) return;

    $stmt = $pdo->prepare("INSERT INTO activitati (utilizator_id, rol, actiune, sql_comanda) 
                           VALUES (:uid, :rol, :actiune, :sql)");
    $stmt->execute([
        'uid' => $_SESSION['user_id'],
        'rol' => $_SESSION['rol'],
        'actiune' => $actiune,
        'sql' => $sql
    ]);
}
