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

class Vehicule {
    private $marque;
    private $modele;
    private $immatriculation; 
    private $annee; 
    private $dateAjout;
    private $clientId;
    private $db;

    public function __construct($db, $marque = null, $modele = null, $immatriculation = null, $annee = null, $clientId = null, $dateAjout = null) {
        $this->db = $db;

        if ($marque !== null) {
            $this->setMarque($marque);
            $this->setModele($modele);
            $this->setImmatriculation($immatriculation);
            $this->setAnnee($annee);
            $this->setClientId($clientId);
            $this->setDateAjout($dateAjout ?? date('Y-m-d H:i:s'));
        }
    }

    // --- Setters ---
    public function setMarque($marque) {
        $this->marque = ucfirst(strtolower($marque));
    }

    public function setModele($modele) {
        $this->modele = ucfirst(strtolower($modele));
    }

    public function setImmatriculation($immatriculation) {
        $this->immatriculation = strtoupper($immatriculation);
    }

    public function setAnnee($annee) {
        $this->annee = (int) $annee;
    }

    public function setDateAjout($date) {
        $this->dateAjout = $date;
    }

    public function setClientId($id) {
        $this->clientId = (int) $id;
    }

    // --- Getters ---
    public function getClientId() { return $this->clientId; }
    public function getMarque() { return $this->marque; }
    public function getModele() { return $this->modele; }
    public function getImmatriculation() { return $this->immatriculation; }
    public function getAnnee() { return $this->annee; }
    public function getDateAjout() { return $this->dateAjout; }

    // --- Enregistre le véhicule ---
public function save() {
    try {
        $stmt = $this->db->prepare(
            "INSERT INTO vehicules (marque, modele, immatriculation, annee, date_ajout, client_id)
             VALUES (?, ?, ?, ?, NOW(), ?)"
        );
        $stmt->execute([
            $this->marque,
            $this->modele,
            $this->immatriculation,
            $this->annee,
            $this->clientId
        ]);
    } catch (PDOException $e) {
        error_log("Erreur lors de l'ajout d'un véhicule: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        exit;
    }
}


    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM vehicules ORDER BY date_ajout DESC");
        return $stmt->fetchAll();
    }

    public function getVehicule($id) {
        $stmt = $this->db->prepare("SELECT * FROM vehicules WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM vehicules WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
