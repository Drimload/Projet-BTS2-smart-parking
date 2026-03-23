-- ============================================================
-- Smart Parking CREPS - Base de données PostgreSQL
-- Version complète - Cahier des charges complet
-- ============================================================

-- ============================================================
-- TABLE : type_utilisateur
-- ============================================================
CREATE TABLE IF NOT EXISTS type_utilisateur (
    id_type_utilisateur      SERIAL PRIMARY KEY,
    libelle_type_utilisateur VARCHAR(50) NOT NULL
);

-- ============================================================
-- TABLE : utilisateur
-- ============================================================
CREATE TABLE IF NOT EXISTS utilisateur (
    id_utilisateur      SERIAL PRIMARY KEY,
    nom_users           VARCHAR(100) NOT NULL,
    prenom_users        VARCHAR(100) NOT NULL,
    email_users         VARCHAR(150) NOT NULL UNIQUE,
    mdp_users           VARCHAR(255) NOT NULL,
    actif               BOOLEAN NOT NULL DEFAULT TRUE,
    id_type_utilisateur INTEGER NOT NULL,
    CONSTRAINT fk_utilisateur_type
        FOREIGN KEY (id_type_utilisateur)
        REFERENCES type_utilisateur(id_type_utilisateur)
);

-- ============================================================
-- TABLE : parking
-- ============================================================
CREATE TABLE IF NOT EXISTS parking (
    id_parking            SERIAL PRIMARY KEY,
    libelle_parking       VARCHAR(100) NOT NULL,
    nombre_places         INTEGER NOT NULL,
    nombre_places_pmr     INTEGER NOT NULL,
    statut_infrastructure BOOLEAN NOT NULL DEFAULT TRUE
);

-- ============================================================
-- TABLE : capteur
-- ============================================================
CREATE TABLE IF NOT EXISTS capteur (
    id_capteur      SERIAL PRIMARY KEY,
    libelle_capteur VARCHAR(100) NOT NULL,
    dev_eui         VARCHAR(16) UNIQUE,
    statut          BOOLEAN NOT NULL DEFAULT TRUE,
    verrouille      BOOLEAN NOT NULL DEFAULT FALSE,
    niveau_batterie INTEGER CHECK (niveau_batterie BETWEEN 0 AND 100),
    id_parking      INTEGER NOT NULL,
    CONSTRAINT fk_capteur_parking
        FOREIGN KEY (id_parking)
        REFERENCES parking(id_parking)
);

-- ============================================================
-- TABLE : mesure_capteur
-- ============================================================
CREATE TABLE IF NOT EXISTS mesure_capteur (
    id_mesure       SERIAL PRIMARY KEY,
    date_heure      TIMESTAMP NOT NULL DEFAULT NOW(),
    etat_occupation BOOLEAN NOT NULL,
    id_capteur      INTEGER NOT NULL,
    CONSTRAINT fk_mesure_capteur
        FOREIGN KEY (id_capteur)
        REFERENCES capteur(id_capteur)
);

-- ============================================================
-- TABLE : signalement
-- ============================================================
CREATE TABLE IF NOT EXISTS signalement (
    id_signalement     SERIAL PRIMARY KEY,
    date_signalement   TIMESTAMP NOT NULL DEFAULT NOW(),
    description        TEXT NOT NULL,
    statut_signalement VARCHAR(20) NOT NULL DEFAULT 'ouvert'
                       CHECK (statut_signalement IN ('ouvert', 'en_cours', 'resolu')),
    date_resolution    TIMESTAMP,
    id_capteur         INTEGER NOT NULL,
    id_utilisateur     INTEGER NOT NULL,
    CONSTRAINT fk_signalement_capteur
        FOREIGN KEY (id_capteur)
        REFERENCES capteur(id_capteur),
    CONSTRAINT fk_signalement_utilisateur
        FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
);

-- ============================================================
-- TABLE : session
-- ============================================================
CREATE TABLE IF NOT EXISTS session (
    id_session       SERIAL PRIMARY KEY,
    token            VARCHAR(255) NOT NULL UNIQUE,
    date_connexion   TIMESTAMP NOT NULL DEFAULT NOW(),
    date_deconnexion TIMESTAMP,
    ip_address       VARCHAR(45),
    id_utilisateur   INTEGER NOT NULL,
    CONSTRAINT fk_session_utilisateur
        FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
);

-- ============================================================
-- DONNÉES INITIALES : types d'utilisateurs
-- ============================================================
INSERT INTO type_utilisateur (libelle_type_utilisateur) VALUES
    ('Administrateur'),
    ('Employé'),
    ('Visiteur')
ON CONFLICT DO NOTHING;

-- ============================================================
-- DONNÉES INITIALES : 3 parkings du CREPS
-- ============================================================
INSERT INTO parking (libelle_parking, nombre_places, nombre_places_pmr, statut_infrastructure) VALUES
    ('Parking Principal',  80, 5, TRUE),
    ('Parking Secondaire', 30, 3, TRUE),
    ('Parking PMR',        10, 2, TRUE)
ON CONFLICT DO NOTHING;

-- ============================================================
-- DONNÉES INITIALES : compte admin technicien
-- Email : admin@smartparking.com
-- MDP   : Password123 (hashé bcrypt)
-- ============================================================
INSERT INTO utilisateur (nom_users, prenom_users, email_users, mdp_users, id_type_utilisateur) VALUES
    ('Admin', 'Technicien', 'admin@smartparking.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     1)
ON CONFLICT DO NOTHING;

-- ============================================================
-- Base de données ChirpStack
-- DOIT être à la fin car \connect change de base active
-- ============================================================
CREATE DATABASE chirpstack;
CREATE USER chirpstack WITH PASSWORD 'Password123';
GRANT ALL PRIVILEGES ON DATABASE chirpstack TO chirpstack;
\connect chirpstack
CREATE EXTENSION IF NOT EXISTS pg_trgm;
GRANT ALL ON SCHEMA public TO chirpstack;