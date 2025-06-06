<?php
session_start();

define('MAX_ATTEMPTS', 5);
define('BLOCK_TIME', 600); // 10 minutes

$identifiantCorrect = "jean.marie";
$motDePasseHache = '$2y$12$EBs7h8x1yJO.Jqlck7PLTOaHI.Ry1Q0GNZjRdiFbdeQchDwVl9Lzm'; // "Charlotte333"
$ip = $_SERVER['REMOTE_ADDR'];
$attemptsFile = __DIR__ . "/logs/attempts_" . md5($ip) . ".json";

// === FUNCTIONS ===
function getAttempts($file) {
    if (!file_exists($file)) return ['count' => 0, 'last' => 0];
    return json_decode(file_get_contents($file), true);
}

function saveAttempts($file, $count) {
    file_put_contents($file, json_encode([
        'count' => $count,
        'last' => time()
    ]));
}

// === HANDLE POST LOGIN ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");

    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    // === Brute Force Check ===
    $attemptData = getAttempts($attemptsFile);
    if ($attemptData['count'] >= MAX_ATTEMPTS && (time() - $attemptData['last']) < BLOCK_TIME) {
        echo json_encode(['success' => false, 'message' => 'Trop de tentatives. Réessayez plus tard.']);
        exit;
    }

    if ($username === $identifiantCorrect && password_verify($password, $motDePasseHache)) {
        $_SESSION['logged_in'] = true;
        if (file_exists($attemptsFile)) unlink($attemptsFile);
        echo json_encode(['success' => true, 'redirect' => '/www/accueil.html']);
    } else {
        $attemptData['count'] = ($attemptData['count'] ?? 0) + 1;
        $attemptData['last'] = time();
        saveAttempts($attemptsFile, $attemptData['count']);
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
    }
    exit;
}
?>

<!-- === HTML FRONTEND PART (visible on GET only) === -->
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Connexion</title>
<link rel="stylesheet" href="style.css" />
  <style>html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  min-height: 100dvh;
  background: linear-gradient(to right, #4f8cff, #1ec8e7);
  font-family: 'Inter', sans-serif;
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  overflow-x: hidden;
  overflow-y: auto;
}

body.android-webview {
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* 🧱 Wrapper Formulaire */
.login-wrapper {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
  background: #fff;
  padding: 2rem 1.8rem;
  border-radius: 18px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  width: 100%;
  max-width: 420px;
  animation: fadeIn 0.6s ease-out;
}

/* 🧾 Titre */
.login-wrapper h2 {
  text-align: center;
  color: #1f2e42;
  font-size: 1.6em;
  font-weight: 700;
}

/* 🧩 Champs */
.login-wrapper form {
  display: grid;
  gap: 18px;
}

.login-wrapper input {
  width: 100%;
  padding: 13px 15px;
  border-radius: 10px;
  border: 1.5px solid #d0d7e2;
  font-size: 1em;
  background: #f9fafb;
  transition: border 0.2s ease, box-shadow 0.2s ease;
  box-sizing: border-box;
}

.login-wrapper input:focus {
  border-color: #4f8cff;
  box-shadow: 0 0 0 3px rgba(79, 140, 255, 0.2);
  outline: none;
}

/* 🎯 Bouton */
.login-wrapper button {
  padding: 14px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(90deg, #4f8cff 0%, #1ec8e7 100%);
  color: white;
  font-weight: bold;
  font-size: 1.1em;
  cursor: pointer;
  box-shadow: 0 3px 10px rgba(79, 140, 255, 0.3);
  transition: background 0.2s ease, transform 0.15s ease;
}

.login-wrapper button:hover {
  filter: brightness(1.05);
}

.login-wrapper button:active {
  transform: scale(0.97);
}

/* 🎉 Animation d'entrée */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* 🧃 Toast Notification */
.toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  background: #222;
  color: #fff;
  padding: 12px 18px;
  border-radius: 8px;
  display: none;
  font-size: 0.95em;
  box-shadow: 0 3px 10px rgba(0,0,0,0.3);
  z-index: 1000;
}

/* 📱 Responsive ajusté */
@media (max-width: 480px) {
  .login-wrapper {
    padding: 1.5rem 1.2rem;
    border-radius: 12px;
  }

  .login-wrapper h2 {
    font-size: 1.4em;
  }

  .login-wrapper input, .login-wrapper button {
    font-size: 1em;
  }
}

  </style>
</head>
<body>
  <div class="login-wrapper">
    <h2>Connexion Mécano 🔧</h2>
    <form onsubmit="login(event)">
      <input type="text" id="username" placeholder="Identifiant" required />
      <input type="password" id="password" placeholder="Mot de passe" required />
      <button type="submit">Connexion</button>
    </form>
  </div>
  <div id="toast" class="toast"></div>

  <script>
    function login(e) {
      e.preventDefault();
      const username = document.getElementById("username").value;
      const password = document.getElementById("password").value;

      fetch("index.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
      })
      .then(res => {
  console.log("STATUS", res.status);
  return res.text(); // ← pour inspecter la vraie réponse brute
})
.then(text => {
  console.log("TEXT RESPONSE", text); // ← Que contient vraiment la réponse ?
  let data;
  try {
    data = JSON.parse(text);
  } catch (e) {
    showToast("🚨 Réponse non JSON : " + text);
    return;
  }

  if (data.success) {
    window.location.href = data.redirect;
  } else {
    showToast(data.message || "Erreur de connexion");
  }
})
.catch(err => {
  showToast("Erreur de communication avec le serveur");
  console.error(err);
});
    }

    function showToast(message) {
      const toast = document.getElementById("toast");
      toast.textContent = message;
      toast.style.display = "block";
      setTimeout(() => { toast.style.display = "none"; }, 3000);
    }
  </script>
</body>
</html>
