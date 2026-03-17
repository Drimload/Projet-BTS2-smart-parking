# 🅿️ Smart Parking - CREPS Hauts-de-France

## Présentation
Modernisation de la gestion des parkings du CREPS Hauts-de-France.
Détection des places occupées via capteurs LoRaWAN Fleximodo et affichage en temps réel.

## Équipe
- Hamza YAICHE
- Malicia GUILLERAT
- Agathe NGUIDANG

## Architecture
- **ChirpStack** : Serveur LoRaWAN (port 8080)
- **Mosquitto** : Broker MQTT (port 1883)
- **PostgreSQL** : Base de données (port 5432)
- **Redis** : Cache ChirpStack
- **pgAdmin** : Interface BDD (port 5050)
- **Homepage** : Tableau de bord (port 3000)
- **Portainer** : Gestion Docker (port 9000)

## Prérequis
- Raspberry Pi avec Docker et Docker Compose
- Matériel LoRa (HAT ou clé USB)

## Installation
1. Cloner le repo
git clone https://github.com/Drimload/Projet-BTS2-smart-parking.git
cd Projet-BTS2-smart-parking

2. Créer le fichier .env
cp .env.example .env
nano .env  # Remplir les mots de passe

3. Créer la base ChirpStack
sudo docker compose up -d db
sudo docker exec -it smart-parking_db psql -U admin -d postgres
# Dans psql :
CREATE ROLE chirpstack WITH LOGIN PASSWORD 'VOTRE_MOT_DE_PASSE';
CREATE DATABASE chirpstack WITH OWNER chirpstack;
\c chirpstack
CREATE EXTENSION pg_trgm;
\q

4. Lancer tous les services
sudo docker compose up -d

