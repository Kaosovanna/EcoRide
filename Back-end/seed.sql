
-- seed.sql — données de démonstration
INSERT INTO utilisateurs (pseudo, email, motdepasse, credits, note) VALUES
('Jose', 'jose@ecoride.fr', '$2y$10$abcdefghijklmnopqrstuv', 100, 4.8),
('Alice', 'alice@example.com', '$2y$10$abcdefghijklmnopqrstuv', 40, 4.6),
('Benoit', 'benoit@example.com', '$2y$10$abcdefghijklmnopqrstuv', 35, 4.2);

INSERT INTO vehicules (utilisateur_id, marque, modele, energie, couleur, immatriculation) VALUES
(1,'Tesla','Model 3','électrique','noir','AA-123-AA'),
(2,'Renault','Clio','essence','bleu','BB-456-BB'),
(3,'Peugeot','e-208','électrique','jaune','CC-789-CC');

INSERT INTO preferences (utilisateur_id, intitule) VALUES
(1,'Non-fumeur'),(1,'Musique ok'),(1,'Animaux acceptés');

-- Trajets prochains
INSERT INTO trajets (chauffeur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, places_restantes, prix) VALUES
(1,1,'Paris','Nantes', DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 9 HOUR, DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 12 HOUR + INTERVAL 15 MINUTE, 2, 8),
(2,2,'Lyon','Grenoble', DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 18 HOUR, DATE_ADD(CURDATE(), INTERVAL 3 DAY) + INTERVAL 19 HOUR + INTERVAL 30 MINUTE, 1, 6),
(3,3,'Bordeaux','Toulouse', DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 14 HOUR, DATE_ADD(CURDATE(), INTERVAL 5 DAY) + INTERVAL 16 HOUR + INTERVAL 30 MINUTE, 3, 7);

INSERT INTO avis (chauffeur_id, auteur, note, commentaire, publie) VALUES
(1,'Camille',5,'Trajet parfait, voiture électrique silencieuse !',1),
(1,'Mehdi',4.5,'Ponctuel et agréable.',1);

INSERT INTO temoignages (auteur, ville, message) VALUES
('Camille','Paris','Super expérience, conducteur à l’heure et trajet électrique !'),
('Anis','Lyon','Prix corrects et ambiance sympa.'),
('Louise','Nantes','Très pratique pour mes week-ends, je recommande.');
