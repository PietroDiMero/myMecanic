<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
header('Content-Type: application/json');

try {
    // 🔍 Sécurisation des inputs
    $description   = trim($_POST['description'] ?? '');
    $vehicule_id   = intval($_POST['vehicule_id'] ?? 0);
    $client_id     = intval($_POST['client_id'] ?? 0);
    $forfait_id    = isset($_POST['forfait_id']) && $_POST['forfait_id'] !== '' ? intval($_POST['forfait_id']) : null;
    $date_travail  = !empty($_POST['date_travail']) ? $_POST['date_travail'] : date('Y-m-d');

    if (!$description || !$vehicule_id || !$client_id) {
        throw new Exception("Champs requis manquants ou invalides.");
    }

    // 💵 Récupération du prix du forfait si applicable
    $prix_forfait = 0;
    if ($forfait_id) {
        $stmt = $pdo->prepare("SELECT prix FROM forfaits WHERE id = ?");
        $stmt->execute([$forfait_id]);
        $prix_forfait = $stmt->fetchColumn();
        if ($prix_forfait === false) {
            throw new Exception("Forfait introuvable.");
        }
    }

    // 📥 Insertion du travail
    $stmt = $pdo->prepare("
        INSERT INTO travaux (vehicule_id, client_id, description, statut, date_travail, total, forfait_id)
        VALUES (?, ?, ?, 'en_attente', ?, ?, ?)
    ");
    $stmt->execute([
        $vehicule_id,
        $client_id,
        $description,
        $date_travail,
        $prix_forfait,
        $forfait_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => '✅ Travail ajouté avec succès.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '💥 Erreur serveur',
        'error'   => $e->getMessage()
    ]);
}
