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


class Travaux {
    private $db;
    private $clientId;
    private $vehiculeId;
    private $description;

    public function __construct($db, $clientId = null, $vehiculeId = null, $description = null) {
        $this->db = $db;
        $this->clientId = $clientId;
        $this->vehiculeId = $vehiculeId;
        $this->description = $description;
    }

    // 🔹 Démarrer un nouveau travail (date_debut maintenant)
    public function demarrerTravail() {
        $stmt = $this->db->prepare("
            INSERT INTO travaux (client_id, vehicule_id, description, date_debut, statut)
            VALUES (?, ?, ?, NOW(), 'en cours')
        ");
        return $stmt->execute([$this->clientId, $this->vehiculeId, $this->description]);
    }

    // 🔹 Ajouter un travail rétroactif (non démarré en temps réel)
    public function ajouterTravail() {
        $stmt = $this->db->prepare("
            INSERT INTO travaux (client_id, vehicule_id, description, statut)
            VALUES (?, ?, ?, 'ajouté')
        ");
        return $stmt->execute([$this->clientId, $this->vehiculeId, $this->description]);
    }

    // 🔴 Terminer un travail : calcule durée, coût, total
    public function terminerTravail($id, $coutPieces = 0) {
        // Récupération date_debut
        $stmt = $this->db->prepare("SELECT date_debut FROM travaux WHERE id = ? AND statut = 'en cours'");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row || !$row['date_debut']) {
            return false;
        }

        // Calcul de la durée
        $dateDebut = new DateTime($row['date_debut']);
        $dateFin = new DateTime();
        $interval = $dateDebut->diff($dateFin);
        $duree = $interval->h + ($interval->i / 60); // Ex: 1h30 → 1.5

        // Récupérer taux horaire depuis parametres
        $res = $this->db->query("SELECT taux_horaire FROM parametres WHERE id = 1");
        $taux = $res->fetchColumn();
        if (!$taux) $taux = 50;

        // Calcul
        $coutMainOeuvre = $duree * $taux;
        $total = $coutMainOeuvre + $coutPieces;

        // Update travaux
        $update = $this->db->prepare("
            UPDATE travaux
            SET date_fin = NOW(),
                duree = ?,
                cout_main_oeuvre = ?,
                cout_pieces = ?,
                total = ?,
                statut = 'terminé'
            WHERE id = ?
        ");
        return $update->execute([$duree, $coutMainOeuvre, $coutPieces, $total, $id]);
    }

    // 📄 Obtenir tous les travaux d’un véhicule
    public function getTravauxVehicule($vehiculeId) {
        $stmt = $this->db->prepare("
            SELECT * FROM travaux
            WHERE vehicule_id = ?
            ORDER BY date_debut DESC
        ");
        $stmt->execute([$vehiculeId]);
        return $stmt->fetchAll();
    }

    // 📄 Obtenir les détails d’un travail
    public function getTravail($id) {
        $stmt = $this->db->prepare("SELECT * FROM travaux WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // 🧾 Lister les pièces d’un travail
    public function getPieces($travailId) {
        $stmt = $this->db->prepare("SELECT * FROM pieces WHERE travail_id = ?");
        $stmt->execute([$travailId]);
        return $stmt->fetchAll();
    }

    // ➕ Ajouter une pièce à un travail
    public function ajouterPiece($travailId, $nom, $prix) {
        $stmt = $this->db->prepare("
            INSERT INTO pieces (travail_id, nom_piece, prix)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$travailId, $nom, $prix]);
    }
}
