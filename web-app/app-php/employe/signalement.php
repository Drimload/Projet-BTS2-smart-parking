<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

verifierConnexion();
verifierRole(['Employé', 'Administrateur']);

$succes = null;
$erreur = null;

// Pré-remplir capteur si vient de capteurs.php
$capteur_preselect = isset($_GET['capteur']) ? (int)$_GET['capteur'] : null;

// Créer un signalement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_capteur   = (int) ($_POST['id_capteur'] ?? 0);
    $description  = trim($_POST['description'] ?? '');

    if (!$id_capteur || empty($description)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO signalement
                (id_capteur, id_utilisateur, description_signalement,
                 statut_signalement, date_signalement)
            VALUES
                (:capteur, :user, :desc, 'ouvert', NOW())
        ");
        $stmt->execute([
            ':capteur' => $id_capteur,
            ':user'    => $_SESSION['user']['id'],
            ':desc'    => $description,
        ]);
        $succes = 'Signalement créé avec succès.';
    }
}

// Liste des capteurs pour le formulaire
$stmt = $pdo->prepare("
    SELECT c.id_capteur, c.dev_eui, p.libelle_parking
    FROM capteur c
    JOIN parking p ON p.id_parking = c.id_parking
    ORDER BY p.libelle_parking
");
$stmt->execute();
$capteurs = $stmt->fetchAll();

// Liste des signalements existants
$stmt = $pdo->prepare("
    SELECT s.*, c.dev_eui, p.libelle_parking,
           u.nom_users, u.prenom_users
    FROM signalement s
    JOIN capteur c ON c.id_capteur = s.id_capteur
    JOIN parking p ON p.id_parking = c.id_parking
    JOIN utilisateur u ON u.id_utilisateur = s.id_utilisateur
    ORDER BY s.date_signalement DESC
    LIMIT 20
");
$stmt->execute();
$signalements = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<main class="dashboard-main">

    <div class="dashboard-titre">
        <h1>Signalements</h1>
        <p>Signaler une panne · consulter les tickets ouverts</p>
    </div>

    <!-- Formulaire nouveau signalement -->
    <div class="tableau-container" style="padding: 1.5rem; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem;">Nouveau signalement</h2>

        <?php if ($erreur): ?>
            <div class="alert alert-erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        <?php if ($succes): ?>
            <div class="alert alert-succes"><?= htmlspecialchars($succes) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Capteur concerné</label>
                <select name="id_capteur" required
                        style="width:100%;padding:0.75rem;border:1.5px solid var(--border);border-radius:8px;">
                    <option value="">-- Sélectionner un capteur --</option>
                    <?php foreach ($capteurs as $c): ?>
                        <option value="<?= $c['id_capteur'] ?>"
                            <?= $capteur_preselect == $c['id_capteur'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['libelle_parking']) ?>
                            — <?= htmlspecialchars($c['dev_eui']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Description du problème</label>
                <textarea name="description" rows="4" required
                    style="width:100%;padding:0.75rem;border:1.5px solid var(--border);
                           border-radius:8px;font-family:inherit;font-size:0.95rem;"
                    placeholder="Décrivez le problème observé..."></textarea>
            </div>

            <button type="submit" class="btn-primary" style="width:auto;padding:0.75rem 2rem;">
                Envoyer le signalement
            </button>
        </form>
    </div>

    <!-- Liste des signalements -->
    <h2 style="margin-bottom: 1rem;">Signalements récents</h2>
    <div class="tableau-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Parking</th>
                    <th>Capteur</th>
                    <th>Signalé par</th>
                    <th>Description</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($signalements as $s): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($s['date_signalement'])) ?></td>
                    <td><?= htmlspecialchars($s['libelle_parking']) ?></td>
                    <td><code><?= htmlspecialchars($s['dev_eui']) ?></code></td>
                    <td><?= htmlspecialchars($s['prenom_users'] . ' ' . $s['nom_users']) ?></td>
                    <td><?= htmlspecialchars(substr($s['description_signalement'], 0, 60)) ?>...</td>
                    <td>
                        <?php
                        $badge = match($s['statut_signalement']) {
                            'ouvert'    => 'badge-rouge',
                            'en_cours'  => 'badge-orange',
                            'resolu'    => 'badge-vert',
                            default     => 'badge-orange'
                        };
                        ?>
                        <span class="badge <?= $badge ?>">
                            <?= ucfirst($s['statut_signalement']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
