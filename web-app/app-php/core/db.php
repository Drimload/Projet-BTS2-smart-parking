<?php
// ============================================================
// core/db.php
// ============================================================

$host     = getenv('DB_HOST');      // → db
$port     = getenv('DB_PORT');      // → 5432
$dbname   = getenv('DB_NAME');      // → parking_db
$user     = getenv('DB_USER');      // → admin
$password = getenv('DB_PASSWORD');  // → Password123

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Appel AJAX (depuis etat.php) → retourne JSON
    // Appel page normale → texte lisible
    $estAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
    if ($estAjax) {
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'erreur' => 'Connexion BDD impossible']));
    } else {
        die('Erreur de connexion BDD : ' . $e->getMessage());
    }
}