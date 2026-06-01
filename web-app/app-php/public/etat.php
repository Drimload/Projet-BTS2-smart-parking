<?php
// ============================================================
// core/etat.php
// Endpoint JSON — appelé par dashboard.js toutes les 5 secondes
// ============================================================

require_once __DIR__ . '/../core/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

date_default_timezone_set('Europe/Paris');

try {

    // ── 1. État détaillé de chaque place ─────────────────────
    $places = $pdo->query("
        SELECT
            p.id_place,
            p.numero,
            p.type_place,
            p.etat,
            p.reservee,
            pk.id_parking,
            pk.libelle_parking,
            c.id_capteur,
            c.dev_eui,
            c.statut        AS capteur_actif,
            c.verrouille,
            c.niveau_batterie,
            (c.last_seen_at AT TIME ZONE 'UTC' AT TIME ZONE 'Europe/Paris')::timestamp(0)
                            AS last_seen_at
        FROM place p
        JOIN parking pk     ON p.id_parking = pk.id_parking
        LEFT JOIN capteur c ON c.id_place   = p.id_place
        ORDER BY pk.libelle_parking, p.numero
    ")->fetchAll();

    // ── 2. Résumé compteurs par parking ──────────────────────
    $resume = $pdo->query("
        SELECT
            pk.id_parking,
            pk.libelle_parking,
            pk.nombre_places_pmr                                               AS pmr_total,
            COUNT(p.id_place)                                                  AS total_places,
            COUNT(p.id_place) FILTER (WHERE p.etat = 'libre')                 AS places_libres,
            COUNT(p.id_place) FILTER (WHERE p.etat = 'occupee')               AS places_occupees,
            COUNT(p.id_place) FILTER (WHERE p.etat = 'hors_service')          AS places_hors_service,
            COUNT(p.id_place) FILTER (WHERE p.type_place = 'pmr'
                                      AND p.etat = 'libre')                   AS pmr_libres,
            ROUND(
                COUNT(p.id_place) FILTER (WHERE p.etat = 'occupee')::numeric
                / NULLIF(COUNT(p.id_place), 0) * 100, 1
            )                                                                  AS taux_occupation
        FROM parking pk
        LEFT JOIN place p ON p.id_parking = pk.id_parking
        GROUP BY pk.id_parking, pk.libelle_parking, pk.nombre_places_pmr
        ORDER BY pk.libelle_parking
    ")->fetchAll();

    // ── 3. Réponse JSON ───────────────────────────────────────
    echo json_encode([
        'success'   => true,
        'timestamp' => date('H:i:s'),
        'places'    => $places,
        'resume'    => $resume,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'erreur'  => $e->getMessage()
    ]);
}