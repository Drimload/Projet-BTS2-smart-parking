
CREATE TABLE type_utilisateur (
    id_type_utilisateur SERIAL PRIMARY KEY,
    libelle_type VARCHAR(50)
);

CREATE TABLE utilisateur (
    id_utilisateur SERIAL PRIMARY KEY,
    nom_user VARCHAR(100),
    prenom_user VARCHAR(100),
    email_user VARCHAR(150),
    mdp_user VARCHAR(255),
    id_type_utilisateur INT REFERENCES type_utilisateur(id_type_utilisateur)
);

CREATE TABLE parking (
    id_parking SERIAL PRIMARY KEY,
    libelle_parking VARCHAR(100),
    nombre_places INT,
    nombre_places_pmr INT
);

CREATE TABLE capteur (
    id_capteur SERIAL PRIMARY KEY,
    libelle_capteur VARCHAR(100),
    statut BOOLEAN,
    id_parking INT REFERENCES parking(id_parking)
);

CREATE TABLE mesure_capteur (
    id_mesure SERIAL PRIMARY KEY,
    date_heure TIMESTAMP,
    etat_occupation BOOLEAN,
    id_capteur INT REFERENCES capteur(id_capteur)
);
