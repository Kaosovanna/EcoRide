-- seed.sql - données d'exemple
USE ecoride;

INSERT INTO users (pseudo, email, password_hash, credits, role, created_at) VALUES
('Jose', 'jose@ecoride.fr', '$2y$10$abcdefghijklmnopqrstuv', 100, 'admin', NOW()),
('Alice', 'alice@example.com', '$2y$10$abcdefghijklmnopqrstuv', 20, 'utilisateur', NOW()),
('Bob', 'bob@example.com', '$2y$10$abcdefghijklmnopqrstuv', 20, 'utilisateur', NOW());

-- Attention : remplacez les hashs par de vrais hashs générés par PHP si nécessaire.

INSERT INTO vehicles (user_id, marque, modele, couleur, energie, immatriculation, first_registration) VALUES
(1, 'Renault', 'Zoé', 'Vert', 'electrique', 'AA-123-AA', '2022-05-10'),
(2, 'Peugeot', '208', 'Bleu', 'essence', 'BB-456-BB', '2020-03-12');

INSERT INTO driver_prefs (user_id, pref) VALUES
(1, 'Non-fumeur'),(1, 'Animaux OK');

INSERT INTO trips (driver_id, vehicle_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_total, places_restantes) VALUES
(1, 1, 'Paris', 'Lyon', DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 8 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 12 HOUR, 30.00, 3, 3),
(1, 1, 'Lille', 'Bruxelles', DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 10 HOUR, DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 12 HOUR, 18.00, 4, 4),
(2, 2, 'Nantes', 'Bordeaux', DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 14 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 17 HOUR, 22.00, 2, 2);

INSERT INTO reviews (trip_id, author_id, driver_id, note, commentaire, statut) VALUES
(NULL, 2, 1, 5, 'Conducteur très agréable, trajet parfait !', 'valide'),
(NULL, 3, 1, 4, 'Ponctuel et véhicule propre.', 'valide');
