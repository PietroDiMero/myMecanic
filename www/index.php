<?php
session_start();
//je définie le mdp de connexion
$motDePasse = "Charlotte333";
$Identifiant = "jean.marie";

if (isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($password === $motDePasse && $Identifiant === strtolower($username)) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Accueil</title>
        </head>
        <body>
            <nav> 
                <button onclick="chargerPage('clients.html')">Ajouter des clients</button>
                <button onclick="chargerPage('listClients.html')">Voir mes clients</button>
                
        </nav>
        <div id="contenu"></div>
            <script src="script.js"></script>
        </body>
        </html>
        <?php
    } else {
        $message = "Erreur de mot de passe ou d'identifiant";
        exit;
    }
} else {
    // formulaire non soumis
    header('Location: ../api/connect.php');
    exit;
}
