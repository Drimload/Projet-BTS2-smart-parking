<?php
// ============================================================
// public/actions/signaler.php
// Action AJAX — création d'un signalement
// Accessible : Employé + Administrateur uniquement
// ============================================================

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/db.php';

header('Content-Type: application/json; charset=utf-8');

// Vérifications accès
verifierConnexion();
verifierRole(['Employé', 'Administrateur']);

// Vérification méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'erreur' => 'Méthode non autorisée']);
    exit();
}

// Récupération et nettoyage des données
$idPlace     = filter_input(INPUT_POST, 'id_place',    FILTER_VALIDATE_INT);
$description = trim($_POST['description'] ?? '');
$user        = getUser();

// Validation
if (!$idPlace || $idPlace <= 0) {
    echo json_encode(['success' => false, 'erreur' => 'Place invalide']);
    exit();
}

if (empty($description)) {
    echo json_encode(['success' => false, 'erreur' => 'Description obligatoire']);
    exit();
}

if (strlen($description) > 1000) {
    echo json_encode(['success' => false, 'erreur' => 'Description trop longue (max 1000 caractères)']);
    exit();
}

// Vérification que la place existe
$stmt = $pdo->prepare("SELECT id_place FROM place WHERE id_place = ?");
$stmt->execute([$idPlace]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'erreur' => 'Place introuvable']);
    exit();
}

// Récupération de l'id_capteur lié à la place
$stmt = $pdo->prepare("SELECT id_capteur FROM capteur WHERE id_place = ?");
$stmt->execute([$idPlace]);
$capteur = $stmt->fetch();

if (!$capteur) {
    echo json_encode(['success' => false, 'erreur' => 'Aucun capteur associé à cette place']);
    exit();
}

// Insertion du signalement
try {
    $stmt = $pdo->prepare("
        INSERT INTO signalement (description, id_capteur, id_utilisateur)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $description,
        $capteur['id_capteur'],
        $user['id_utilisateur']
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'erreur' => 'Erreur base de données']);
}