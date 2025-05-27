<?php
session_start();

$motDePasse = "Charlotte333";
$Identifiant = "jean.marie";

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Déjà connecté → afficher directement la page
    showAccueil();
    exit;
}

if (isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($password === $motDePasse && $Identifiant === strtolower($username)) {
        $_SESSION['logged_in'] = true;
        showAccueil();
        exit;
    } else {
        echo "Erreur de mot de passe ou d'identifiant";
        exit;
    }
} else {
    header('Location: ../api/connect.php');
    exit;
}

// Fonction pour afficher la page d'accueil
function showAccueil() {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Accueil</title>
    </head>
    <body>
        <nav> 
            <button onclick="chargerPage('clients.html')">Ajouter des clients</button>
            <button onclick="chargerPage('listClients.html')">Voir mes clients</button>
            <form method="POST" action="logout.php" style="display:inline;">
                <button type="submit">Déconnexion</button>
            </form>
        </nav>
        <div id="contenu"></div>
        <script src="script.js"></script>
    </body>
    </html>
    <?php
}
