-- db.sql - schéma MySQL simplifié pour EcoRide
CREATE DATABASE IF NOT EXISTS ecoride CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecoride;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pseudo VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  credits INT NOT NULL DEFAULT 20,
  role ENUM('utilisateur','employe','admin') NOT NULL DEFAULT 'utilisateur',
  created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  marque VARCHAR(100) NOT NULL,
  modele VARCHAR(100) NOT NULL,
  couleur VARCHAR(50) DEFAULT NULL,
  energie ENUM('electrique','hybride','essence','diesel') NOT NULL,
  immatriculation VARCHAR(20),
  first_registration DATE,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS driver_prefs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  pref VARCHAR(255) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS trips (
  id INT AUTO_INCREMENT PRIMARY KEY,
  driver_id INT NOT NULL,
  vehicle_id INT NOT NULL,
  ville_depart VARCHAR(100) NOT NULL,
  ville_arrivee VARCHAR(100) NOT NULL,
  date_depart DATETIME NOT NULL,
  date_arrivee DATETIME NOT NULL,
  prix DECIMAL(8,2) NOT NULL,
  places_total INT NOT NULL,
  places_restantes INT NOT NULL,
  FOREIGN KEY (driver_id) REFERENCES users(id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trip_id INT NOT NULL,
  user_id INT NOT NULL,
  places INT NOT NULL DEFAULT 1,
  status ENUM('confirme','annule') NOT NULL DEFAULT 'confirme',
  created_at DATETIME NOT NULL,
  FOREIGN KEY (trip_id) REFERENCES trips(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trip_id INT,
  author_id INT NOT NULL,
  driver_id INT NOT NULL,
  note INT NOT NULL,
  commentaire TEXT,
  statut ENUM('en_attente','valide','refuse') NOT NULL DEFAULT 'valide',
  FOREIGN KEY (trip_id) REFERENCES trips(id),
  FOREIGN KEY (author_id) REFERENCES users(id),
  FOREIGN KEY (driver_id) REFERENCES users(id)
);
