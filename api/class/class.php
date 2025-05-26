<?php

class Client {
    private $firstName;
    private $lastName;
    private $phone;
    private $dateCreation;
    private $db;

    public function __construct($db, $firstName = null, $lastName = null, $phone = null, $dateCreation = null) {
        $this->db = $db;

        if ($firstName !== null) {
            $this->setFirstName($firstName);
            $this->setLastName($lastName);
            $this->setPhone($phone);
            $this->setDateCreation($dateCreation ?? date("Y-m-d H:i:s"));
        }
    }

    // Getters
    public function getFirstName() { return $this->firstName; }
    public function getLastName() { return $this->lastName; }
    public function getPhone() { return $this->phone; }
    public function getDateCreation() { return $this->dateCreation; }

    // Setters
    public function setFirstName($firstName) {
        $this->firstName = ucfirst(strtolower($firstName));
    }

    public function setLastName($lastName) {
        $this->lastName = strtoupper($lastName);
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function setDateCreation($date) {
        $this->dateCreation = $date;
    }

    // Liste tous les clients
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM clients ORDER BY date_creation DESC");
        return $stmt->fetchAll();
    }

    public function getClient($id) {
    $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(); // fetch() pour un seul résultat
    }


public function save() {
    $stmt = $this->db->prepare("INSERT INTO clients (nom, prenom, telephone, date_creation) VALUES (?, ?, ?, NOW())");
    $stmt->execute([
        $this->lastName,
        $this->firstName,
        $this->phone
    ]);
}
}