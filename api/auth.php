<?php
session_start();
header("Content-Type: application/json");

$identifiantCorrect = "jean.marie";
$motDePasseHache = '$2y$12$EBs7h8x1yJO.Jqlck7PLTOaHI.Ry1Q0GNZjRdiFbdeQchDwVl9Lzm'; // "Charlotte333"

if (!empty($_SESSION['logged_in'])) {
    echo json_encode(['success' => true, 'redirect' => '/www/accueil.html']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " | USER: $username | PASS: $password\n", FILE_APPEND);



    if ($username === $identifiantCorrect && password_verify($password, $motDePasseHache)) {
        $_SESSION['logged_in'] = true;
        echo json_encode(['success' => true, 'redirect' => '/www/accueil.html']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
