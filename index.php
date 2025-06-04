<?php
session_start();
header("Content-Type: text/html; charset=UTF-8");

$identifiantCorrect = "jean.marie";
$motDePasseHache = '$2y$12$EBs7h8x1yJO.Jqlck7PLTOaHI.Ry1Q0GNZjRdiFbdeQchDwVl9Lzm'; // "Charlotte333"

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    file_put_contents("debug.log", date('Y-m-d H:i:s') . " | USER: $username | PASS: $password\n", FILE_APPEND);

    if ($username === $identifiantCorrect && password_verify($password, $motDePasseHache)) {
        $_SESSION['logged_in'] = true;
        echo json_encode(['success' => true, 'redirect' => '/www/accueil.html']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Connexion</title>
  <link rel="stylesheet" href="style.css" />
  <style>
 html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  min-height: 100%;
  background: linear-gradient(to right, #4f8cff, #1ec8e7);
  font-family: 'Inter', sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
  box-sizing: border-box;
}


    .login-wrapper {
      background: #fff;
      padding: 2rem 1.5rem;
      border-radius: 16px;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      animation: fadeIn 0.6s ease-out;
    }

    .login-wrapper h2 {
      text-align: center;
      color: #123;
      margin-bottom: 1.5rem;
    }

    .login-wrapper label {
      font-weight: 600;
      color: #444;
      display: block;
      margin-bottom: 6px;
      margin-top: 14px;
    }

    .login-wrapper input {
      width: 100%;
      padding: 12px;
      border: 1.5px solid #d0d7e2;
      border-radius: 10px;
      font-size: 1em;
      background: #f9fafd;
      box-sizing: border-box;
      transition: border 0.2s;
    }

    .login-wrapper input:focus {
      border-color: #4f8cff;
      outline: none;
    }

    .login-wrapper button {
      margin-top: 20px;
      width: 100%;
      padding: 14px;
      font-size: 1.1em;
      border: none;
      border-radius: 10px;
      background: linear-gradient(90deg, #4f8cff 0%, #1ec8e7 100%);
      color: #fff;
      font-weight: bold;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s;
      cursor: pointer;
    }

    .login-wrapper button:active {
      transform: scale(0.97);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .toast {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0, 0, 0, 0.8);
      color: #fff;
      padding: 10px 18px;
      border-radius: 8px;
      display: none;
      font-size: 0.95em;
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
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.redirect;
        } else {
          showToast(data.message || "Erreur de connexion");
        }
      })
      .catch(() => showToast("Erreur serveur"));
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
