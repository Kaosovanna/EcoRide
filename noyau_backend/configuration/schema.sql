-- MySQL Initialization Schema
CREATE DATABASE IF NOT EXISTS ecoride CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecoride;

-- Table Utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    role ENUM('passager', 'chauffeur', 'employe', 'admin', 'suspendu') DEFAULT 'passager',
    credits INT DEFAULT 20,
    photo LONGTEXT DEFAULT NULL,
    biographie TEXT DEFAULT NULL,
    pref_fumeur BOOLEAN DEFAULT FALSE,
    pref_animaux BOOLEAN DEFAULT FALSE,
    pref_musique BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table Vehicules
CREATE TABLE IF NOT EXISTS vehicules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    immatriculation VARCHAR(20) NOT NULL UNIQUE,
    modele VARCHAR(100) NOT NULL,
    couleur VARCHAR(30) NOT NULL,
    est_electrique BOOLEAN DEFAULT FALSE,  -- True = Voyage Écologique
    photos LONGTEXT DEFAULT NULL,          -- JSON list of base64 photos or URLs
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table Trajets (Covoiturages)
CREATE TABLE IF NOT EXISTS trajets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conducteur_id INT NOT NULL,
    vehicule_id INT NOT NULL,
    ville_depart VARCHAR(100) NOT NULL,
    ville_arrivee VARCHAR(100) NOT NULL,
    date_depart DATE NOT NULL,
    heure_depart TIME NOT NULL,
    prix INT NOT NULL, -- Prix en crédits
    duree_max INT NOT NULL, -- en minutes
    places_max INT NOT NULL,
    statut ENUM('planifie', 'demarre', 'termine', 'annule') DEFAULT 'planifie',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conducteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicule_id) REFERENCES vehicules(id) ON DELETE CASCADE
);

-- Table Reservations
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trajet_id INT NOT NULL,
    passager_id INT NOT NULL,
    statut ENUM('en_attente', 'valide', 'refuse', 'annule') DEFAULT 'en_attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trajet_id) REFERENCES trajets(id) ON DELETE CASCADE,
    FOREIGN KEY (passager_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table Incidents
CREATE TABLE IF NOT EXISTS incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trajet_id INT NOT NULL,
    utilisateur_id INT NOT NULL,  -- Personne qui a signalé l'incident
    description TEXT NOT NULL,
    statut ENUM('ouvert', 'resolu') DEFAULT 'ouvert',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trajet_id) REFERENCES trajets(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table Avis
CREATE TABLE IF NOT EXISTS avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trajet_id INT NOT NULL,
    auteur_id INT NOT NULL,
    cible_id INT NOT NULL,
    note TINYINT NOT NULL,
    commentaire TEXT DEFAULT NULL,
    statut ENUM('en_attente', 'approuve', 'rejete') DEFAULT 'en_attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trajet_id) REFERENCES trajets(id) ON DELETE CASCADE,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (cible_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

