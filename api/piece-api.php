<?php
require_once 'db.php';



// 🔄 Ajout d’une pièce
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['travail_id']) ||
        empty(trim($_POST['nom_piece'])) ||
        !isset($_POST['prix'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Champs requis manquants.']);
        exit;
    }

    $travail_id = intval($_POST['travail_id']);
    $nom_piece = trim($_POST['nom_piece']);
    $prix = floatval($_POST['prix']);

    try {
        // ➕ Ajouter la pièce
        $stmt = $pdo->prepare("INSERT INTO pieces (travail_id, nom_piece, prix) VALUES (?, ?, ?)");
        $stmt->execute([$travail_id, $nom_piece, $prix]);

        // ♻️ Recalcul du coût total des pièces
        $stmt = $pdo->prepare("SELECT SUM(prix) FROM pieces WHERE travail_id = ?");
        $stmt->execute([$travail_id]);
        $total_pieces = $stmt->fetchColumn() ?: 0;

        // 🔄 Mise à jour du champ cout_pieces et total dans travaux
        $update = $pdo->prepare("UPDATE travaux SET cout_pieces = ?, total = cout_main_oeuvre + ? WHERE id = ?");
        $update->execute([$total_pieces, $total_pieces, $travail_id]);

        echo json_encode(['success' => true, 'message' => 'Pièce ajoutée et total mis à jour.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur BDD.', 'error' => $e->getMessage()]);
    }

    exit;
}

// 📄 Affichage des pièces pour un travail
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['travail_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM pieces WHERE travail_id = ? ORDER BY id DESC");
    $stmt->execute([$_GET['travail_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['piece_id'])) {
    $piece_id = filter_input(INPUT_GET, 'piece_id', FILTER_VALIDATE_INT);
    if (!$piece_id) {
        echo json_encode(['success' => false, 'message' => 'ID pièce invalide.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT travail_id FROM pieces WHERE id = ?");
    $stmt->execute([$piece_id]);
    $travail_id = $stmt->fetchColumn();

    if (!$travail_id) {
        echo json_encode(['success' => false, 'message' => 'Pièce introuvable.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM pieces WHERE id = ?");
        $stmt->execute([$piece_id]);

        $stmt = $pdo->prepare("SELECT SUM(prix) FROM pieces WHERE travail_id = ?");
        $stmt->execute([$travail_id]);
        $total_pieces = floatval($stmt->fetchColumn() ?? 0);

        $update = $pdo->prepare("UPDATE travaux SET cout_pieces = ?, total = cout_main_oeuvre + ? WHERE id = ?");
        $update->execute([$total_pieces, $total_pieces, $travail_id]);

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Pièce supprimée et total mis à jour.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erreur BDD.', 'error' => $e->getMessage()]);
    }
    exit;
}



// ⛔ Requête non supportée
echo json_encode(['success' => false, 'message' => 'Méthode non supportée.']);
