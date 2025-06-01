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
               <span class="app-logo" onclick="chargerPage('home.html'); toggleMenu(false)" style="cursor:pointer;">🔧 Mécano</span>

            </div>
           
            <div class="nav-right">
                <form method="POST" action="/api/logout.php">
            🚪</button>
                </form>
            </div>
        </header>



        <div id="contenu"></div>
      <!-- ✅ Zone toast -->
<div id="toast" class="toast"></div>

<!-- ✅ Zone confirm dialog -->
<div id="confirmBox" class="modal hidden">
  <div class="modal-content">
    <p id="confirmMessage">Es-tu sûr de vouloir faire ça ?</p>
    <button id="btnYes" class="confirm-btn-yes">✅ Oui</button>
    <button id="btnNo" class="confirm-btn-no">❌ Non</button>
  </div>
</div>

<!-- ✅ Styles -->
<style>
  .toast {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: var(--accent-primary, #323232);
    color: white;
    padding: 12px 18px;
    border-radius: 8px;
    font-weight: 500;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease, transform 0.3s ease;
    z-index: 9999;
  }
  .toast.show {
    opacity: 1;
    pointer-events: auto;
    transform: translateX(-50%) translateY(0);
  }

  .modal.hidden { display: none; }

  .modal {
    position: fixed;
    top: 0; left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
  }

  .modal-content {
    background: white;
    border-radius: 10px;
    padding: 20px 30px;
    text-align: center;
    max-width: 400px;
    box-shadow: 0 0 12px rgba(0,0,0,0.25);
  }

  .modal-content p {
    margin-bottom: 20px;
    font-size: 1.1em;
    font-weight: 500;
  }

  .modal-content button {
    margin: 0 10px;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1em;
  }

  .confirm-btn-yes {
    background-color: #4caf50;
    color: white;
  }

  .confirm-btn-no {
    background-color: #f44336;
    color: white;
  }
</style>


        <script src="script.js"></script>
        <script>
  document.addEventListener("DOMContentLoaded", () => {
    chargerPage("home.html");
  });
</script>

    </body>
    </html>
    <?php
}
