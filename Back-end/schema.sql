
-- schema.sql — tables minimales pour la démo
CREATE TABLE IF NOT EXISTS utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pseudo VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  motdepasse VARCHAR(255) NOT NULL,
  credits INT NOT NULL DEFAULT 20,
  note DECIMAL(3,1) DEFAULT 5.0,
  photo VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS vehicules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  marque VARCHAR(100) NOT NULL,
  modele VARCHAR(100) NOT NULL,
  energie VARCHAR(50) NOT NULL, -- 'électrique' pour éco
  couleur VARCHAR(50),
  immatriculation VARCHAR(50),
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE IF NOT EXISTS trajets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chauffeur_id INT NOT NULL,
  vehicule_id INT NOT NULL,
  ville_depart VARCHAR(100) NOT NULL,
  ville_arrivee VARCHAR(100) NOT NULL,
  date_depart DATETIME NOT NULL,
  date_arrivee DATETIME NOT NULL,
  places_restantes INT NOT NULL,
  prix INT NOT NULL,
  FOREIGN KEY (chauffeur_id) REFERENCES utilisateurs(id),
  FOREIGN KEY (vehicule_id) REFERENCES vehicules(id)
);

CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trajet_id INT NOT NULL,
  passager_id INT NOT NULL,
  date_resa DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trajet_id) REFERENCES trajets(id),
  FOREIGN KEY (passager_id) REFERENCES utilisateurs(id)
);

CREATE TABLE IF NOT EXISTS avis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chauffeur_id INT NOT NULL,
  auteur VARCHAR(100) NOT NULL,
  note DECIMAL(2,1) NOT NULL,
  commentaire TEXT,
  publie TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (chauffeur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE IF NOT EXISTS preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  intitule VARCHAR(200) NOT NULL,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE IF NOT EXISTS temoignages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  auteur VARCHAR(100) NOT NULL,
  ville VARCHAR(100) NOT NULL,
  message VARCHAR(300) NOT NULL
);
