<?php
// ============================================================
// inscription.php
// Page d'inscription pour les visiteurs uniquement
//
// GET  → affiche le formulaire
// POST → traite l'inscription
//        crée un compte avec le rôle Visiteur
//        redirige vers login.php si succès
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si déjà connecté → rediriger
if (isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit();
}

require_once 'config/db.php';

$erreur  = null;
$succes  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom      = trim($_POST['nom']      ?? '');
    $prenom   = trim($_POST['prenom']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $mdp      = trim($_POST['mdp']      ?? '');
    $mdp_conf = trim($_POST['mdp_conf'] ?? '');

    // Validation basique
    if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
        $erreur = 'Veuillez remplir tous les champs.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var() = vérifie le format de l'email
        $erreur = 'Adresse email invalide.';

    } elseif ($mdp !== $mdp_conf) {
        $erreur = 'Les mots de passe ne correspondent pas.';

    } elseif (strlen($mdp) < 8) {
        $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';

    } else {

        // Vérifier que l'email n'existe pas déjà
        $stmt = $pdo->prepare('SELECT id_utilisateur FROM utilisateur WHERE email_users = :email');
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $erreur = 'Cette adresse email est déjà utilisée.';

        } else {

            // Récupérer l'id du type Visiteur
            $stmt = $pdo->prepare('SELECT id_type_utilisateur FROM type_utilisateur WHERE libelle_type_utilisateur = :libelle');
            $stmt->execute([':libelle' => 'Visiteur']);
            $type = $stmt->fetch();

            if (!$type) {
                $erreur = 'Erreur interne : type utilisateur introuvable.';
            } else {

                // Hasher le mot de passe avec bcrypt
                // password_hash() = génère un hash sécurisé
                // PASSWORD_DEFAULT = algorithme bcrypt recommandé
                $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);

                // Insérer le nouvel utilisateur
                $stmt = $pdo->prepare('
                    INSERT INTO utilisateur
                        (nom_users, prenom_users, email_users, mdp_users,
                         id_type_utilisateur, actif)
                    VALUES
                        (:nom, :prenom, :email, :mdp, :type, TRUE)
                ');
                $stmt->execute([
                    ':nom'    => $nom,
                    ':prenom' => $prenom,
                    ':email'  => $email,
                    ':mdp'    => $mdp_hash,
                    ':type'   => $type['id_type_utilisateur'],
                ]);

                $succes = 'Compte créé avec succès ! Vous pouvez vous connecter.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<main class="login-container">
    <div class="login-box">

        <h1>Créer un compte</h1>
        <p>Accès visiteur — Smart Parking CREPS</p>

        <?php if ($erreur): ?>
            <div class="alert alert-erreur">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alert alert-succes">
                <?= htmlspecialchars($succes) ?>
                <br><a href="/login.php">Se connecter</a>
            </div>
        <?php else: ?>

        <form action="/inscription.php" method="POST">

            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom"
                    value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                    placeholder="Votre nom" required>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom"
                    value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                    placeholder="Votre prénom" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    placeholder="votre@email.com" required>
            </div>

            <div class="form-group">
                <label for="mdp">Mot de passe</label>
                <input type="password" id="mdp" name="mdp"
                    placeholder="8 caractères minimum" required>
            </div>

            <div class="form-group">
                <label for="mdp_conf">Confirmer le mot de passe</label>
                <input type="password" id="mdp_conf" name="mdp_conf"
                    placeholder="Répéter le mot de passe" required>
            </div>

            <button type="submit" class="btn-primary">
                Créer mon compte
            </button>

        </form>

        <?php endif; ?>

        <div class="login-links">
            <a href="/login.php">Déjà un compte ? Se connecter</a>
        </div>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
