<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
header('Content-Type: application/json');

try {
    $description = trim($_POST['description'] ?? '');
    $vehicule_id = intval($_POST['vehicule_id'] ?? 0);
    $client_id = intval($_POST['client_id'] ?? 0);

    if (!$description || !$vehicule_id || !$client_id) {
        throw new Exception("Champ requis manquant ou invalide.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO travaux (vehicule_id, client_id, description, statut, date_travail)
        VALUES (?, ?, ?, 'en_attente', NOW())
    ");
    $stmt->execute([$vehicule_id, $client_id, $description]);

    echo json_encode([
        'success' => true,
        'message' => '✅ Travail ajouté avec succès.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '💥 Erreur serveur',
        'error' => $e->getMessage()
    ]);
}
