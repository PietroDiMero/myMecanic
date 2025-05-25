<?php 


require_once 'db.php';  // Doit être en haut pour avoir $mysql
require_once 'class/class.php';




if (isset($_POST['firstName']) && isset($_POST['lastName'])) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $phone = $_POST['phone'] ?? '';

    $client = new Client($pdo, $firstName, $lastName, $phone);
    $client->save();

    // Redirection vers la page des clients
   if (!headers_sent()) {
    header('Location: ../www/clients.html?success=1');
    exit;
}
    exit;
} else {
    echo "Champs obligatoires manquants.";
}

