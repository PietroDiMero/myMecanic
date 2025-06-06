<?php
require_once "db.php"; // Connexion à $pdo

header('Content-Type: application/json');

$periode = $_GET['periode'] ?? 'global';
$mois = $_GET['mois'] ?? null;

try {
    if ($periode === 'mois' && $mois) {
        // 🔍 Stats filtrées par mois
        $stmt = $pdo->prepare("
            SELECT 
              ROUND(SUM(TIMESTAMPDIFF(MINUTE, date_debut, date_fin)) / 60, 2) AS total_heures,
              ROUND(SUM(total), 2) AS total,
              ROUND(SUM(CASE WHEN statut = 'reglé' THEN total ELSE 0 END), 2) AS total_regle,
              ROUND(SUM(CASE WHEN statut = 'termine' THEN total ELSE 0 END), 2) AS total_prevu
            FROM travaux
            WHERE DATE_FORMAT(date_travail, '%Y-%m') = ?
        ");
        $stmt->execute([$mois]);
    } else {
        // 🌍 Stats globales
        $stmt = $pdo->query("
            SELECT 
              ROUND(SUM(TIMESTAMPDIFF(MINUTE, date_debut, date_fin)) / 60, 2) AS total_heures,
              ROUND(SUM(total), 2) AS total,
              ROUND(SUM(CASE WHEN statut = 'reglé' THEN total ELSE 0 END), 2) AS total_regle,
              ROUND(SUM(CASE WHEN statut = 'termine' THEN total ELSE 0 END), 2) AS total_prevu
            FROM travaux
        ");
    }

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // 📊 Statistiques par mois (dynamiques)
    $moisStmt = $pdo->query("
        SELECT 
            DATE_FORMAT(date_travail, '%Y-%m') AS mois,
            ROUND(SUM(total), 2) AS montant
        FROM travaux
        GROUP BY mois
        ORDER BY mois ASC
    ");

    $parMois = [];
    while ($row = $moisStmt->fetch(PDO::FETCH_ASSOC)) {
        $parMois[$row['mois']] = floatval($row['montant']);
    }

    echo json_encode([
        'success' => true,
        'total_heures' => floatval($result['total_heures']),
        'total' => floatval($result['total']),
        'total_regle' => floatval($result['total_regle']),
        'total_prevu' => floatval($result['total_prevu']),
        'par_mois' => $parMois
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur stats'
    ]);
}
