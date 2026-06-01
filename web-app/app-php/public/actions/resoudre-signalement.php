<?php
// ============================================================
// public/actions/resoudre-signalement.php
// Marque un signalement comme résolu (Admin)
// ============================================================

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/db.php';

header('Content-Type: application/json; charset=utf-8');

verifierConnexion();
verifierRole(['Administrateur']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id   = filter_var($data['id_signalement'] ?? 0, FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['success' => false, 'erreur' => 'ID invalide']);
    exit();
}

$pdo->prepare("
    UPDATE public.signalement
    SET statut_signalement = 'resolu',
        date_resolution    = NOW()
    WHERE id_signalement = ?
")->execute([$id]);

echo json_encode(['success' => true]);