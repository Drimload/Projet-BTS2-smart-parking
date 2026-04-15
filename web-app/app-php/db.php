<?php
// ============================================================
// config/db.php - Connexion à la base de données PostgreSQL
//
// PDO = PHP Data Objects
// C'est la façon moderne et sécurisée de se connecter
// à une base de données en PHP.
// PDO fonctionne avec PostgreSQL, MySQL, SQLite etc.
// ============================================================

// Les paramètres de connexion
// On lit les variables d'environnement Docker
// (définies dans docker-compose.yml)
// getenv() = lire une variable d'environnement
$host     = getenv('DB_HOST');      // "db" = nom du container PostgreSQL
$port     = getenv('DB_PORT');      // "5432"
$dbname   = getenv('DB_NAME');      // "parking_db"
$user     = getenv('DB_USER');      // "admin"
$password = getenv('DB_PASSWORD');  // "Password123"

// ============================================================
// Création de la connexion PDO
//
// try/catch = essaie de faire quelque chose
//             si ça plante, attrape l'erreur
// ============================================================
try {

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    // new PDO() = crée la connexion
    // $pdo est l'objet qu'on utilisera pour faire des requêtes
    $pdo = new PDO($dsn, $user, $password, [

        // Si une requête échoue → lance une exception
        // Sans ça, les erreurs SQL passent silencieusement
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        // Retourne les résultats sous forme de tableau associatif
        // Ex: $row['nom'] au lieu de $row[0]
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // Désactive l'émulation des requêtes préparées
        // Plus sécurisé contre les injections SQL
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

} catch (PDOException $e) {
    // Si la connexion échoue, on arrête tout
    // et on affiche un message d'erreur
    // En production on logguerait l'erreur plutôt
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}