<?php
require_once 'db.php';
require_once 'class/class.php';

header('Content-Type: application/json');

if (isset($_GET['client_id_from_vehicule'])) {
    $stmt = $pdo->prepare("SELECT client_id FROM vehicules WHERE id = ?");
    $stmt->execute([$_GET['client_id_from_vehicule']]);
    $client_id = $stmt->fetchColumn();
    echo json_encode(['client_id' => $client_id]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification des champs requis
    if (
        isset($_POST['marque'], $_POST['modele'], $_POST['immatriculation'], $_POST['annee'], $_POST['client_id'])
    ) {
        $clientId = (int) $_POST['client_id'];

        // Vérifie si le client existe
        $clientCheck = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE id = ?");
        $clientCheck->execute([$clientId]);

        if ($clientCheck->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'message' => 'Client inexistant.']);
            exit;
        }

        // Création du véhicule avec gestion d’erreur
        try {
            $vehicule = new Vehicule(
                $pdo,
                $_POST['marque'],
                $_POST['modele'],
                $_POST['immatriculation'],
                $_POST['annee'],
                $clientId
            );

            $vehicule->save();

            echo json_encode(['success' => true, 'message' => 'Véhicule ajouté avec succès']);
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout du véhicule: ");
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur:']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['client_id']) && is_numeric($_GET['client_id'])) {
        $id = intval($_GET['client_id']);
        $stmt = $pdo->prepare("SELECT * FROM vehicules WHERE client_id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll());
    } else {
        echo json_encode([]);
    }
    exit;
}
