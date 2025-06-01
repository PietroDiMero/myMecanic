<?php
require_once 'db.php';
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['planning'])) {
    try {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

$stmt = $pdo->prepare("
    SELECT t.*, c.nom, c.prenom,c.telephone, v.marque, v.modele
    FROM travaux t
    JOIN clients c ON c.id = t.client_id
    JOIN vehicules v ON v.id = t.vehicule_id
    WHERE DATE(t.date_travail) IN (?, ?)
    ORDER BY t.date_travail ASC
");

$stmt->execute([$today, $tomorrow]); // ✅ cette fois, 2 dates ⇒ 2 paramètres


        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur BDD', 'error' => $e->getMessage()]);
        exit;
    }
}


// === Convertir un rendez-vous en travail ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'from_rdv') {
    $rdv_id = $_POST['id'] ?? null;

    if (!$rdv_id) {
        echo json_encode(['success' => false, 'message' => 'ID du rendez-vous manquant.']);
        exit;
    }

    // Vérifie s'il existe déjà un travail pour ce RDV
    $stmt = $pdo->prepare("SELECT id FROM travaux WHERE rendez_vous_id = ?");
    $stmt->execute([$rdv_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Un travail existe déjà pour ce RDV.']);
        exit;
    }

    // Récupérer les infos du rendez-vous
    $stmt = $pdo->prepare("SELECT * FROM rendez_vous WHERE id = ?");
    $stmt->execute([$rdv_id]);
    $rdv = $stmt->fetch();

    if (!$rdv) {
        echo json_encode(['success' => false, 'message' => 'Rendez-vous introuvable.']);
        exit;
    }

    // Insérer le travail à partir du RDV
    $stmt = $pdo->prepare("
        INSERT INTO travaux (client_id, vehicule_id, description, date_travail, statut, cout_pieces, duree, cout_main_oeuvre, total, rendez_vous_id)
        VALUES (?, ?, ?, ?, 'prévu', 0, 0, 0, 0, ?)
    ");
    $stmt->execute([
        $rdv['client_id'],
        $rdv['vehicule_id'],
        $rdv['commentaire'],
        $rdv['date_rdv'],
        $rdv_id
    ]);

    echo json_encode(['success' => true, 'message' => 'Travail créé depuis le rendez-vous.']);
    exit;
}

// === Actions sur un travail (démarrer, pause, finaliser, etc.)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $travailId = $_POST['id'] ?? $_GET['id'] ?? null;

    if (!$travailId) {
        echo json_encode(['success' => false, 'message' => 'ID travail manquant']);
        exit;
    }

    try {
        switch ($action) {
            case 'demarrer':
                $stmt = $pdo->prepare("UPDATE travaux SET date_debut = NOW(), statut = 'en cours' WHERE id = ?");
                $stmt->execute([$travailId]);
                break;

            case 'pause':
                $stmt = $pdo->prepare("INSERT INTO pauses (travail_id, debut_pause) VALUES (?, NOW())");
                $stmt->execute([$travailId]);
                break;

            case 'reprendre':
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT id FROM pauses WHERE travail_id = ? AND fin_pause IS NULL ORDER BY id DESC LIMIT 1");
                $stmt->execute([$travailId]);
                $pause = $stmt->fetch();
                if ($pause) {
                    $stmt = $pdo->prepare("UPDATE pauses SET fin_pause = NOW() WHERE id = ?");
                    $stmt->execute([$pause['id']]);
                }
                $pdo->commit();
                break;

            case 'finaliser':
                $pdo->beginTransaction();

                // Clôturer le travail
                $pdo->prepare("UPDATE travaux SET date_fin = NOW(), statut = 'terminé' WHERE id = ?")->execute([$travailId]);

                // Calcul durée nette
                $stmt = $pdo->prepare("
                    SELECT TIMESTAMPDIFF(SECOND, date_debut, date_fin) -
                        IFNULL(SUM(TIMESTAMPDIFF(SECOND, debut_pause, fin_pause)), 0) AS duree
                    FROM travaux
                    LEFT JOIN pauses ON travaux.id = pauses.travail_id
                    WHERE travaux.id = ?
                    GROUP BY travaux.id
                ");
                $stmt->execute([$travailId]);
                $seconds = $stmt->fetchColumn() ?: 0;
                $heures = round($seconds / 3600, 2);

                // Calcul coût main d'œuvre
                $taux = $pdo->query("SELECT taux_horaire FROM parametres WHERE id = 1")->fetchColumn() ?: 50;
                $main_oeuvre = $heures * $taux;

                // Total pièces
                $stmt = $pdo->prepare("SELECT SUM(prix) FROM pieces WHERE travail_id = ?");
                $stmt->execute([$travailId]);
                $pieces = $stmt->fetchColumn() ?: 0;

                $total = $main_oeuvre + $pieces;

                // Mettre à jour le travail
                $stmt = $pdo->prepare("UPDATE travaux SET duree = ?, cout_main_oeuvre = ?, cout_pieces = ?, total = ? WHERE id = ?");
                $stmt->execute([$heures, $main_oeuvre, $pieces, $total, $travailId]);

                // ⚠️ Mise à jour RDV si lié
                $stmt = $pdo->prepare("SELECT rendez_vous_id FROM travaux WHERE id = ?");
                $stmt->execute([$travailId]);
                $rdv_id = $stmt->fetchColumn();
                if ($rdv_id) {
                    $pdo->prepare("UPDATE rendez_vous SET statut = 'honoré' WHERE id = ?")->execute([$rdv_id]);
                }

                $pdo->commit();
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Action inconnue']);
                exit;
        }

        echo json_encode(['success' => true, 'message' => "Action $action effectuée."]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erreur serveur BDD', 'error' => $e->getMessage()]);
    }
    exit;
}

// === Création classique d’un travail
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['vehicule_id']) ||
        empty($_POST['client_id']) ||
        empty(trim($_POST['description']))
    ) {
        echo json_encode(['success' => false, 'message' => 'Champs requis manquants ou vides.']);
        exit;
    }

    $vehicule_id = $_POST['vehicule_id'];
    $client_id = $_POST['client_id'];
    $description = trim($_POST['description']);
    $date_travail = !empty($_POST['date_travail']) ? $_POST['date_travail'] : null;

    try {
        // Insère le rendez-vous
        $stmt = $pdo->prepare("
            INSERT INTO rendez_vous (client_id, vehicule_id, date_rdv, commentaire, statut)
            VALUES (?, ?, ?, ?, 'prévu')
        ");
        $stmt->execute([$client_id, $vehicule_id, $date_travail, $description]);
        $rdv_id = $pdo->lastInsertId();

        // Insère le travail lié
        $stmt = $pdo->prepare("
            INSERT INTO travaux (client_id, vehicule_id, description, date_travail, cout_pieces, duree, cout_main_oeuvre, total, rendez_vous_id)
            VALUES (?, ?, ?, ?, 0, 0, 0, 0, ?)
        ");
        $stmt->execute([$client_id, $vehicule_id, $description, $date_travail, $rdv_id]);

        echo json_encode(['success' => true, 'message' => 'Travail + rendez-vous ajoutés avec succès.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur BDD.', 'error' => $e->getMessage()]);
    }
    exit;
}

// === Liste des travaux pour un véhicule
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['vehicule_id'])) {
    $stmt = $pdo->prepare("
        SELECT t.*,
            EXISTS (
                SELECT 1 FROM pauses p
                WHERE p.travail_id = t.id AND p.fin_pause IS NULL
            ) AS en_pause
        FROM travaux t
        WHERE t.vehicule_id = ?
        ORDER BY t.date_debut DESC
    ");
    $stmt->execute([$_GET['vehicule_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// === Suppression d’un travail
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['travaux_id'])) {
    $stmt = $pdo->prepare("DELETE FROM travaux WHERE id = :id");
    $stmt->execute(['id' => $_GET['travaux_id']]);
    echo json_encode(['success' => true, 'message' => 'Travail supprimé']);
    exit;
}

// === Méthode non supportée
echo json_encode(['success' => false, 'message' => 'Méthode non supportée.']);
