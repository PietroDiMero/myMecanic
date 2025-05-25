<?php
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
            <nav> <a href="clients.html">Ajouter un Client</a></nav>
            
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
