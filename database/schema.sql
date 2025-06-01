-- ----------------------------------------
-- Table des clients
-- ----------------------------------------
CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100),
  telephone VARCHAR(20),
  email VARCHAR(100),
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------------------
-- Table des véhicules
-- ----------------------------------------
CREATE TABLE vehicules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  marque VARCHAR(100),
  modele VARCHAR(100),
  immatriculation VARCHAR(20) UNIQUE,
  annee INT,
  date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- ----------------------------------------
-- Table des paramètres (ex: taux horaire)
-- ----------------------------------------
CREATE TABLE parametres (
  id INT PRIMARY KEY,
  taux_horaire DECIMAL(10,2) NOT NULL
);

-- Valeur par défaut (modifiable ensuite)
INSERT INTO parametres (id, taux_horaire) VALUES (1, 50.00);

-- ----------------------------------------
-- Table des travaux
-- ----------------------------------------
CREATE TABLE travaux (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  vehicule_id INT NOT NULL,
  description TEXT,
  date_debut DATETIME,
  date_fin DATETIME,
  duree DECIMAL(5,2), -- en heures
  cout_main_oeuvre DECIMAL(10,2),
  cout_pieces DECIMAL(10,2),
  total DECIMAL(10,2),
  statut VARCHAR(50) DEFAULT 'en cours',
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
  FOREIGN KEY (vehicule_id) REFERENCES vehicules(id) ON DELETE CASCADE
);

-- ----------------------------------------
-- Table des pièces associées à un travail
-- ----------------------------------------
CREATE TABLE pieces (
  id INT AUTO_INCREMENT PRIMARY KEY,
  travail_id INT NOT NULL,
  nom_piece VARCHAR(255),
  prix DECIMAL(10,2),
  FOREIGN KEY (travail_id) REFERENCES travaux(id) ON DELETE CASCADE
);

CREATE TABLE rendez_vous (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  vehicule_id INT NOT NULL,
  date_rdv DATETIME NOT NULL,
  commentaire TEXT,
  statut ENUM('prévu', 'honoré', 'annulé') DEFAULT 'prévu',
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
  FOREIGN KEY (vehicule_id) REFERENCES vehicules(id) ON DELETE CASCADE
);


CREATE TABLE pauses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  travail_id INT NOT NULL,
  debut_pause DATETIME,
  fin_pause DATETIME,
  FOREIGN KEY (travail_id) REFERENCES travaux(id) ON DELETE CASCADE
);

