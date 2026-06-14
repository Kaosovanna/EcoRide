-- noyau_backend/configuration/seed_data.sql
USE ecoride;

-- 1. Nettoyage des données existantes (pour éviter les doublons lors des tests)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE reservations;
TRUNCATE TABLE trajets;
TRUNCATE TABLE vehicules;
TRUNCATE TABLE utilisateurs;
TRUNCATE TABLE avis;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Insertion des Utilisateurs Réalistes (Hash de 'password' pour tous)
-- Mot de passe: password
INSERT INTO utilisateurs (id, pseudo, email, mot_de_passe_hash, role, credits, photo, biographie, pref_fumeur, pref_animaux, pref_musique) VALUES
(1, 'Administrateur', 'admin@ecoride.fr', '$2y$10$Kjgj.pjzhcODkLdSkLCg6utXscW7Y9Icohq6ew/OB2yHfL4tkxkZW', 'admin', 150, 'antoine.png', 'Administrateur de la plateforme EcoRide.', 0, 1, 1),
(2, 'Marc D.', 'marc.d@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'chauffeur', 85, 'marc.png', 'Trajets réguliers entre Paris et Lyon.', 0, 0, 1),
(3, 'Julie D.', 'julie.d@outlook.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'chauffeur', 120, 'julie.png', 'Voyagez confortablement et en musique ! 🎶', 0, 1, 1),
(4, 'Thomas G.', 'thomas.g@yahoo.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'passager', 40, 'thomas.png', 'Adepte du covoiturage depuis 5 ans.', 1, 1, 0),
(5, 'Employé', 'employe@ecoride.fr', '$2y$10$Kjgj.pjzhcODkLdSkLCg6utXscW7Y9Icohq6ew/OB2yHfL4tkxkZW', 'employe', 60, 'sophie.png', 'Support EcoRide à votre service.', 0, 0, 1);

-- 3. Insertion des Véhicules Électriques
INSERT INTO vehicules (id, utilisateur_id, immatriculation, modele, couleur, est_electrique) VALUES
(1, 1, 'EV-123-AB', 'Tesla Model 3', 'Blanc', 1),
(2, 2, 'EV-456-CD', 'Renault Zoe', 'Bleu Azur', 1),
(3, 3, 'EV-789-EF', 'Peugeot e-208', 'Gris', 1);

-- 4. Insertion des Trajets Réalistes (Dates de 2026 pour être dans le futur)
INSERT INTO trajets (id, conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, heure_depart, prix, duree_max, places_max, statut) VALUES
(1, 1, 1, 'Paris', 'Lyon', '2026-04-10', '08:00:00', 25, 240, 3, 'planifie'),
(2, 2, 2, 'Marseille', 'Nice', '2026-04-11', '14:30:00', 15, 120, 2, 'planifie'),
(3, 3, 3, 'Bordeaux', 'Toulouse', '2026-04-12', '10:00:00', 18, 150, 4, 'planifie'),
(4, 1, 1, 'Lille', 'Paris', '2026-04-13', '07:00:00', 20, 180, 3, 'planifie'),
(5, 2, 2, 'Nantes', 'Rennes', '2026-04-14', '09:00:00', 12, 90, 4, 'planifie'),
(6, 3, 3, 'Strasbourg', 'Metz', '2026-04-15', '17:45:00', 10, 60, 2, 'planifie'),
(7, 2, 2, 'Lyon', 'Montpellier', '2026-04-16', '13:00:00', 22, 180, 4, 'planifie'),
(8, 1, 1, 'Nice', 'Cannes', '2026-04-17', '11:00:00', 5, 45, 1, 'planifie');

-- 5. Quelques Réservations pour peupler
INSERT INTO reservations (trajet_id, passager_id, statut) VALUES
(1, 4, 'valide'),
(2, 4, 'en_attente'),
(3, 5, 'valide');

-- 6. Quelques Avis
INSERT IGNORE INTO avis (trajet_id, auteur_id, cible_id, note, commentaire, statut) VALUES
(1, 4, 1, 5, 'Trajet parfait ! Conductrice très sympa et ponctuelle. Je recommande.', 'approuve'),
(2, 4, 2, 4, 'Bonne conduite, véhicule propre. Quelques petits retards mais rien de grave.', 'approuve'),
(3, 5, 3, 5, 'Julie est une conductrice exemplaire, voyage très agréable et musique au top !', 'approuve'),
(4, 4, 1, 4, 'Super trajet Paris-Lille. Voiture électrique, sans bruit. Très écologique !', 'approuve'),
(5, 4, 2, 5, 'Marc est excellent, je covoiture avec lui régulièrement désormais !', 'approuve');


