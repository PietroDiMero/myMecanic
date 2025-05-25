<?php
require_once 'db.php';
require_once 'class/class.php';

$client = new Client($pdo); // attention : ici c'est bien $mysql
$liste = $client->getAll();



header('Content-Type: application/json');
echo json_encode($liste);
