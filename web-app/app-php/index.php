<?php
// ============================================================
// index.php
// Page d'accueil — accessible sans connexion (visiteur)
//
// Affiche en temps réel :
//   - Nombre total de places disponibles
//   - Nombre de places PMR disponibles
//   - État de chaque parking (A, B, C)
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';
require_once 'includes/header.php';

// Récupérer tous les parkings avec leurs capteurs
// On compte les places libres par parking
$stmt = $pdo->prepare("
    SELECT
        p.id_parking,
        p.libelle_parking,
        p.nombre_places,
        p.nombre_places_pmr,
        p.statut_infrastructure,
        COUNT(CASE WHEN c.verrouille = FALSE THEN 1 END) AS total_capteurs,
        COUNT(CASE
            WHEN c.verrouille = FALSE
            AND (
                SELECT m.etat_occupation
                FROM mesure_capteur m
                WHERE m.id_capteur = c.id_capteur
                ORDER BY m.date_heure DESC
                LIMIT 1
            ) = FALSE
            THEN 1
        END) AS places_libres
    FROM parking p
    LEFT JOIN capteur c ON c.id_parking = p.id_parking
    GROUP BY p.id_parking, p.libelle_parking,
             p.nombre_places, p.nombre_places_pmr,
             p.statut_infrastructure
    ORDER BY p.libelle_parking
");
$stmt->execute();
$parkings = $stmt->fetchAll();

// Calcul totaux
$total_places  = 0;
$total_libres  = 0;
$total_pmr     = 0;

foreach ($parkings as $p) {
    $total_places += $p['nombre_places'];
    $total_libres += $p['places_libres'];
    $total_pmr    += $p['nombre_places_pmr'];
}
?>

<main class="index-main">

    <!-- Bannière titre -->
    <div class="hero-banner">
        <h1>🅿️ Smart Parking CREPS</h1>
        <p>Hauts-de-France — Wattignies</p>
    </div>

    <!-- Compteurs globaux -->
    <div class="compteurs-globaux">

        <div class="compteur-card compteur-total">
            <span class="compteur-nombre"><?= $total_libres ?></span>
            <span class="compteur-label">Places disponibles</span>
            <span class="compteur-sous">sur <?= $total_places ?> au total</span>
        </div>

        <div class="compteur-card compteur-pmr">
            <span class="compteur-nombre">♿ <?= $total_pmr ?></span>
            <span class="compteur-label">Places PMR</span>
            <span class="compteur-sous">réservées aux personnes à mobilité réduite</span>
        </div>

    </div>

    <!-- Grille des parkings -->
    <div class="parkings-grid">

        <?php foreach ($parkings as $parking): ?>

        <div class="parking-card <?= $parking['statut_infrastructure'] ? 'actif' : 'inactif' ?>">

            <div class="parking-header">
                <h2><?= htmlspecialchars($parking['libelle_parking']) ?></h2>
                <?php if (!$parking['statut_infrastructure']): ?>
                    <span class="badge-inactif">Fermé</span>
                <?php endif; ?>
            </div>

            <div class="parking-compteur">
                <span class="nombre-libres">
                    <?= $parking['places_libres'] ?>
                </span>
                <span class="sur-total">
                    / <?= $parking['nombre_places'] ?> places
                </span>
            </div>

            <!-- Barre de progression -->
            <?php
            $pct_occupe = $parking['nombre_places'] > 0
                ? round((($parking['nombre_places'] - $parking['places_libres']) / $parking['nombre_places']) * 100)
                : 0;

            // Couleur selon taux d'occupation
            $couleur = 'vert';
            if ($pct_occupe >= 80) $couleur = 'rouge';
            elseif ($pct_occupe >= 50) $couleur = 'orange';
            ?>
            <div class="barre-occupation">
                <div class="barre-remplie barre-<?= $couleur ?>"
                     style="width: <?= $pct_occupe ?>%">
                </div>
            </div>
            <p class="taux-occupation"><?= $pct_occupe ?>% occupé</p>

            <p class="parking-pmr">
                ♿ <?= $parking['nombre_places_pmr'] ?> places PMR
            </p>

        </div>

        <?php endforeach; ?>

    </div>

    <!-- Mise à jour automatique toutes les 30 secondes -->
    <p class="derniere-maj">
        Données mises à jour automatiquement · dernière actualisation :
        <span id="heure-maj"><?= date('H:i:s') ?></span>
    </p>

</main>

<script>
// Recharger la page toutes les 30 secondes
// pour afficher les données en temps réel
setTimeout(function() {
    location.reload();
}, 30000);

// Mettre à jour l'heure affichée chaque seconde
setInterval(function() {
    const now = new Date();
    document.getElementById('heure-maj').textContent =
        now.toLocaleTimeString('fr-FR');
}, 1000);
</script>

<?php require_once 'includes/footer.php'; ?>

