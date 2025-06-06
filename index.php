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
<link rel="stylesheet" href="/www/assets/css/main.css" />
<link rel="stylesheet" href="/www/assets/css/mobile.css" />
<link rel="stylesheet" href="/www/assets/css/login.css" />

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
