<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

verifierConnexion();
verifierRole(['Employé', 'Administrateur']);

// Basculer le statut d'un parking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_parking'])) {
    $id = (int) $_POST['id_parking'];

    // Récupérer statut actuel puis inverser
    $stmt = $pdo->prepare('SELECT statut_infrastructure FROM parking WHERE id_parking = :id');
    $stmt->execute([':id' => $id]);
    $parking = $stmt->fetch();

    if ($parking) {
        $nouveau_statut = $parking['statut_infrastructure'] ? 'FALSE' : 'TRUE';
        $stmt = $pdo->prepare("
            UPDATE parking SET statut_infrastructure = $nouveau_statut
            WHERE id_parking = :id
        ");
        $stmt->execute([':id' => $id]);
    }

    header('Location: /employe/infrastructure.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT * FROM parking ORDER BY libelle_parking
");
$stmt->execute();
$parkings = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<main class="dashboard-main">

    <div class="dashboard-titre">
        <h1>Gestion de l'infrastructure</h1>
        <p>Activer ou désactiver les parkings</p>
    </div>

    <div class="tableau-container">
        <table>
            <thead>
                <tr>
                    <th>Parking</th>
                    <th>Places totales</th>
                    <th>Places PMR</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parkings as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['libelle_parking']) ?></strong></td>
                    <td><?= $p['nombre_places'] ?></td>
                    <td>♿ <?= $p['nombre_places_pmr'] ?></td>
                    <td>
                        <?php if ($p['statut_infrastructure']): ?>
                            <span class="badge badge-vert">Actif</span>
                        <?php else: ?>
                            <span class="badge badge-rouge">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="id_parking"
                                   value="<?= $p['id_parking'] ?>">
                            <?php if ($p['statut_infrastructure']): ?>
                                <button type="submit" class="btn-danger">
                                    Désactiver
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn-primary"
                                        style="width:auto;padding:0.5rem 1rem">
                                    Activer
                                </button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
