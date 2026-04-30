<?php
// ============================================================
// employe/dashboard.php
// Tableau de bord de l'employé
//
// Affiche :
//   - Nombre de places libres / occupées
//   - État des capteurs (batterie, statut)
//   - Signalements en cours
//   - Liens vers les autres pages employé
// ============================================================

require_once '../includes/auth.php';
require_once '../config/db.php';

// Vérifier connexion + rôle
verifierConnexion();
verifierRole(['Employé', 'Administrateur']);

require_once '../includes/header.php';

// ── Statistiques globales ──
$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total_capteurs,
        COUNT(CASE WHEN verrouille = TRUE THEN 1 END) AS capteurs_verr,
        COUNT(CASE WHEN niveau_batterie < 20 THEN 1 END) AS batterie_faible
    FROM capteur
");
$stmt->execute();
$stats = $stmt->fetch();

// Places libres en temps réel
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS libres
    FROM capteur c
    WHERE c.verrouille = FALSE
    AND (
        SELECT m.etat_occupation
        FROM mesure_capteur m
        WHERE m.id_capteur = c.id_capteur
        ORDER BY m.date_heure DESC
        LIMIT 1
    ) = FALSE
");
$stmt->execute();
$libres = $stmt->fetch()['libres'];

// Signalements ouverts
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS nb
    FROM signalement
    WHERE statut_signalement IN ('ouvert', 'en_cours')
");
$stmt->execute();
$signalements = $stmt->fetch()['nb'];

// État des parkings
$stmt = $pdo->prepare("
    SELECT libelle_parking, statut_infrastructure
    FROM parking
    ORDER BY libelle_parking
");
$stmt->execute();
$parkings = $stmt->fetchAll();
?>

<main class="dashboard-main">

    <div class="dashboard-titre">
        <h1>Bonjour <?= htmlspecialchars($_SESSION['user']['prenom_users']) ?> 👋</h1>
        <p>Tableau de bord employé · <?= date('d/m/Y H:i') ?></p>
    </div>

    <!-- Cartes statistiques -->
    <div class="cards-grid">

        <div class="stat-card">
            <h3>Places disponibles</h3>
            <span class="nb"><?= $libres ?></span>
        </div>

        <div class="stat-card" style="border-color: var(--rouge)">
            <h3>Capteurs verrouillés</h3>
            <span class="nb"><?= $stats['capteurs_verr'] ?></span>
        </div>

        <div class="stat-card" style="border-color: var(--orange)">
            <h3>Batterie faible (&lt; 20%)</h3>
            <span class="nb"><?= $stats['batterie_faible'] ?></span>
        </div>

        <div class="stat-card" style="border-color: var(--bleu)">
            <h3>Signalements ouverts</h3>
            <span class="nb"><?= $signalements ?></span>
        </div>

    </div>

    <!-- État des parkings -->
    <h2 style="margin-bottom: 1rem;">État des parkings</h2>
    <div class="tableau-container" style="margin-bottom: 2rem;">
        <table>
            <thead>
                <tr>
                    <th>Parking</th>
                    <th>Infrastructure</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parkings as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['libelle_parking']) ?></td>
                    <td>
                        <?php if ($p['statut_infrastructure']): ?>
                            <span class="badge badge-vert">Actif</span>
                        <?php else: ?>
                            <span class="badge badge-rouge">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/employe/infrastructure.php">
                            Gérer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Liens rapides -->
    <h2 style="margin-bottom: 1rem;">Accès rapide</h2>
    <div class="cards-grid">
        <a href="/employe/capteurs.php" class="lien-card">
            <span>📡</span>
            <strong>État des capteurs</strong>
            <p>Batterie · statut · verrouillage</p>
        </a>
        <a href="/employe/signalement.php" class="lien-card">
            <span>⚠️</span>
            <strong>Signalements</strong>
            <p>Signaler une panne · voir les tickets</p>
        </a>
        <a href="/employe/infrastructure.php" class="lien-card">
            <span>⚙️</span>
            <strong>Infrastructure</strong>
            <p>Activer / désactiver les parkings</p>
        </a>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
