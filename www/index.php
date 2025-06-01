<?php
session_start();

// --- Identifiants statiques (à stocker dans un fichier sécurisé si possible) ---
$motDePasseHache = password_hash("Charlotte333", PASSWORD_DEFAULT);
$identifiantCorrect = "jean.marie";

// Si déjà connecté
if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    showAccueil();
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = strtolower(trim($_POST['username']));
    $password = $_POST['password'];

    if ($username === $identifiantCorrect && password_verify($password, $motDePasseHache)) {
        $_SESSION['logged_in'] = true;
        showAccueil();
        exit;
    } else {
        echo "❌ Identifiant ou mot de passe incorrect.";
        exit;
    }
}

// Sinon → afficher le formulaire
showLogin();

// === FONCTIONS ===

function showLogin() {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
                <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion</title>
    </head>
    <body>
        <h2>Connexion Mécano 🔧</h2>
        <form method="POST">
            <label for="username">Identifiant :</label>
            <input type="text" name="username" required><br>

            <label for="password">Mot de passe :</label>
            <input type="password" name="password" required><br>

            <button type="submit">Se connecter</button>
        </form>
    </body>
    </html>
    <?php
}
function showAccueil() {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Accueil</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <!-- === Menu slide === -->
<div class="side-menu" id="sideMenu">
  <button onclick="chargerPage('clients.html'); toggleMenu(false)">➕ Ajouter des clients</button>
  <button onclick="chargerPage('listClients.html'); toggleMenu(false)">👥 Voir mes clients</button>
</div>


<!-- === Overlay sombre -->
<div class="menu-overlay" id="menuOverlay" onclick="toggleMenu(false)"></div>

        <!-- === Barre de navigation supérieure fixe === -->
        <header class="app-navbar">
            <div class="nav-left">
               <button class="burger-button" onclick="toggleMenu()">☰</button>

                <span class="app-logo">🔧 Mécano</span>
            </div>
            <div class="nav-right">
                <form method="POST" action="/api/logout.php">
                    <button type="submit" class="icon-button" title="Déconnexion">🔓</button>
                </form>
            </div>
        </header>



        <div id="contenu"></div>

        <script src="script.js"></script>
    </body>
    </html>
    <?php
}
