<?php
// ============================================================
// public/actions/signalements-place.php
// Retourne les signalements ouverts d'une place (Admin)
// ============================================================

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/db.php';

header('Content-Type: application/json; charset=utf-8');

verifierConnexion();
verifierRole(['Administrateur']);

$id_capteur = filter_input(INPUT_GET, 'id_capteur', FILTER_VALIDATE_INT);

if (!$id_capteur) {
    echo json_encode([]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT
        s.id_signalement,
        s.description,
        s.statut_signalement,
        s.date_signalement,
        u.prenom_users || ' ' || u.nom_users AS auteur
    FROM public.signalement s
    JOIN public.utilisateur u ON s.id_utilisateur = u.id_utilisateur
    WHERE s.id_capteur = ?
    ORDER BY s.date_signalement DESC
    LIMIT 10
");
$stmt->execute([$id_capteur]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));