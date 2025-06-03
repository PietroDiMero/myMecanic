<?php
require_once 'db.php';
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    $stmt = $pdo->query("SELECT * FROM forfaits ORDER BY id DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    break;

  case 'POST':
    $nom = $_POST['nom'] ?? '';
    $prix = $_POST['prix'] ?? 0;

    if (!$nom || $prix <= 0) {
        echo json_encode(['success' => false, 'message' => 'Nom ou prix invalide']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO forfaits (nom, prix) VALUES (?, ?)");
    $stmt->execute([$nom, $prix]);
    echo json_encode(['success' => true, 'message' => 'Forfait ajouté']);
    break;

  case 'DELETE':
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = $_DELETE['id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM forfaits WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Forfait supprimé']);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
}
