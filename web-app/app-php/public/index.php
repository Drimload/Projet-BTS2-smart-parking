<?php
// ============================================================
// public/index.php
// Page publique — visiteur non connecté
// ============================================================

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/db.php';

// Si connecté → dashboard
if (estConnecte()) {
    header('Location: /dashboard.php');
    exit();
}

// Rôle visiteur pour la vue
$role = 'Visiteur';

// Chargement des places avec données capteur
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
        c.last_seen_at
    FROM place p
    JOIN parking pk     ON p.id_parking = pk.id_parking
    LEFT JOIN capteur c ON c.id_place   = p.id_place
    ORDER BY pk.libelle_parking, p.numero
")->fetchAll();

// Résumé compteurs
$resume = $pdo->query("
    SELECT
        pk.id_parking,
        pk.libelle_parking,
        pk.nombre_places_pmr                                           AS pmr_total,
        COUNT(p.id_place)                                              AS total_places,
        COUNT(p.id_place) FILTER (WHERE p.etat = 'libre'
                                  AND p.type_place != 'pmr')          AS places_libres,
        COUNT(p.id_place) FILTER (WHERE p.etat = 'occupee')           AS places_occupees,
        COUNT(p.id_place) FILTER (WHERE p.etat = 'hors_service')      AS places_hors_service,
        COUNT(p.id_place) FILTER (WHERE p.type_place = 'pmr'
                                  AND p.etat = 'libre')               AS pmr_libres,
        ROUND(
            COUNT(p.id_place) FILTER (WHERE p.etat = 'occupee')::numeric
            / NULLIF(COUNT(p.id_place), 0) * 100, 1
        )                                                              AS taux_occupation
    FROM parking pk
    LEFT JOIN place p ON p.id_parking = pk.id_parking
    GROUP BY pk.id_parking, pk.libelle_parking, pk.nombre_places_pmr
    ORDER BY pk.libelle_parking
")->fetchAll();

// Regroupement par parking
$parkings = [];
foreach ($places as $place) {
    $id = $place['id_parking'];
    $parkings[$id]['libelle']  = $place['libelle_parking'];
    $parkings[$id]['places'][] = $place;
}

require_once __DIR__ . '/../core/header.php';
require_once __DIR__ . '/../views/dashboard.view.php';
require_once __DIR__ . '/../core/footer.php';