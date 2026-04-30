<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

verifierConnexion();
verifierRole(['Employé', 'Administrateur']);

// Verrouiller / déverrouiller un capteur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id  = (int) $_POST['id_capteur'];
    $ver = $_POST['action'] === 'verrouiller' ? 'TRUE' : 'FALSE';

    $stmt = $pdo->prepare("
        UPDATE capteur SET verrouille = $ver
        WHERE id_capteur = :id
    ");
    $stmt->execute([':id' => $id]);

    header('Location: /employe/capteurs.php');
    exit();
}

// Récupérer tous les capteurs avec leur dernière mesure
$stmt = $pdo->prepare("
    SELECT
        c.id_capteur,
        c.dev_eui,
        c.niveau_batterie,
        c.verrouille,
        p.libelle_parking,
        (
            SELECT m.etat_occupation
            FROM mesure_capteur m
            WHERE m.id_capteur = c.id_capteur
            ORDER BY m.date_heure DESC
            LIMIT 1
        ) AS etat_actuel,
        (
            SELECT m.date_heure
            FROM mesure_capteur m
            WHERE m.id_capteur = c.id_capteur
            ORDER BY m.date_heure DESC
            LIMIT 1
        ) AS derniere_mesure
    FROM capteur c
    JOIN parking p ON p.id_parking = c.id_parking
    ORDER BY p.libelle_parking, c.id_capteur
");
$stmt->execute();
$capteurs = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<main class="dashboard-main">

    <div class="dashboard-titre">
        <h1>État des capteurs</h1>
        <p>Batterie · statut · verrouillage des places</p>
    </div>

    <div class="tableau-container">
        <table>
            <thead>
                <tr>
                    <th>Parking</th>
                    <th>DevEUI</th>
                    <th>État place</th>
                    <th>Batterie</th>
                    <th>Dernière mesure</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($capteurs as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['libelle_parking']) ?></td>
                    <td><code><?= htmlspecialchars($c['dev_eui']) ?></code></td>
                    <td>
                        <?php if ($c['etat_actuel'] === null): ?>
                            <span class="badge badge-orange">Inconnu</span>
                        <?php elseif ($c['etat_actuel']): ?>
                            <span class="badge badge-rouge">Occupé</span>
                        <?php else: ?>
                            <span class="badge badge-vert">Libre</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $bat = $c['niveau_batterie'];
                        $bat_class = $bat < 20 ? 'badge-rouge' : ($bat < 50 ? 'badge-orange' : 'badge-vert');
                        ?>
                        <span class="badge <?= $bat_class ?>">
                            <?= $bat !== null ? $bat . '%' : 'N/A' ?>
                        </span>
                    </td>
                    <td>
                        <?= $c['derniere_mesure']
                            ? date('d/m/Y H:i', strtotime($c['derniere_mesure']))
                            : '—' ?>
                    </td>
                    <td>
                        <?php if ($c['verrouille']): ?>
                            <span class="badge badge-rouge">Verrouillé</span>
                        <?php else: ?>
                            <span class="badge badge-vert">Actif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="id_capteur"
                                   value="<?= $c['id_capteur'] ?>">
                            <?php if ($c['verrouille']): ?>
                                <input type="hidden" name="action" value="deverrouiller">
                                <button type="submit" class="btn-secondary">
                                    Déverrouiller
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="verrouiller">
                                <button type="submit" class="btn-danger">
                                    Verrouiller
                                </button>
                            <?php endif; ?>
                        </form>
                        <a href="/employe/signalement.php?capteur=<?= $c['id_capteur'] ?>">
                            ⚠️ Signaler
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
