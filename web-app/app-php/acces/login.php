<?php
// ============================================================
// login.php
// Page de connexion
//
// Cette page fait deux choses selon la méthode HTTP :
//
// GET  → affiche le formulaire de connexion
//        (quand l'utilisateur ouvre /login.php)
//
// POST → traite le formulaire
//        (quand l'utilisateur clique "Se connecter")
//        vérifie email + mot de passe dans la BDD
//        crée la session si ok
//        redirige selon le rôle
// ============================================================

// On démarre la session
// DOIT être en tout premier avant tout affichage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'utilisateur est déjà connecté
// inutile d'afficher le login → on redirige
if (isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit();
}

// On charge la connexion BDD
// $pdo sera disponible après cet include
require_once 'config/db.php';

// Variable pour stocker un message d'erreur
// null = pas d'erreur pour l'instant
$erreur = null;

// ============================================================
// TRAITEMENT DU FORMULAIRE
//
// $_SERVER['REQUEST_METHOD'] = méthode HTTP utilisée
// 'POST' = l'utilisateur a soumis le formulaire
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // On récupère les données du formulaire
    // trim() = enlève les espaces avant/après
    $email    = trim($_POST['email'] ?? '');
    $motDePasse = trim($_POST['mot_de_passe'] ?? '');

    // Vérification basique : les champs sont-ils remplis ?
    if (empty($email) || empty($motDePasse)) {
        $erreur = 'Veuillez remplir tous les champs.';

    } else {

        // On cherche l'utilisateur dans la BDD par son email
        // On fait aussi une jointure avec type_utilisateur
        // pour récupérer le libellé du rôle (Administrateur, Employé...)
        //
        // JOIN = fusion de deux tables
        // ON = condition de liaison entre les tables
        $stmt = $pdo->prepare("
            SELECT u.*, t.libelle_type_utilisateur AS role
            FROM utilisateur u
            JOIN type_utilisateur t
                ON u.id_type_utilisateur = t.id_type_utilisateur
            WHERE u.email_users = :email
            AND u.actif = TRUE
        ");

        // On exécute avec l'email saisi
        // :email = paramètre nommé → protège des injections SQL
        $stmt->execute([':email' => $email]);

        // On récupère l'utilisateur trouvé
        // fetch() = une seule ligne
        // false si aucun résultat
        $utilisateur = $stmt->fetch();

        if ($utilisateur) {

            // L'utilisateur existe dans la BDD
            // On vérifie le mot de passe
            //
            // password_verify() = compare le mot de passe saisi
            // avec le hash bcrypt stocké en BDD
            // Ne jamais comparer les mots de passe en clair !
            if (password_verify($motDePasse, $utilisateur['mdp_users'])) {

                // Mot de passe correct !
                // On crée la session avec les infos de l'utilisateur
                // $_SESSION['user'] sera disponible sur toutes les pages
                $_SESSION['user'] = [
                    'id'          => $utilisateur['id_utilisateur'],
                    'nom'         => $utilisateur['nom_users'],
                    'prenom_users'=> $utilisateur['prenom_users'],
                    'email'       => $utilisateur['email_users'],
                    'role'        => $utilisateur['role'],
                ];

                // On redirige selon le rôle
                if ($utilisateur['role'] === 'Administrateur') {
                    header('Location: /admin/dashboard.php');
                } elseif ($utilisateur['role'] === 'Employé') {
                    header('Location: /employe/dashboard.php');
                } else {
                    // Visiteur → page d'accueil
                    header('Location: /index.php');
                }
                exit();

            } else {
                // Mot de passe incorrect
                $erreur = 'Email ou mot de passe incorrect.';
            }

        } else {
            // Aucun utilisateur trouvé avec cet email
            // On affiche le même message que mot de passe incorrect
            // pour ne pas indiquer si l'email existe ou non
            $erreur = 'Email ou mot de passe incorrect.';
        }
    }
}

// ============================================================
// AFFICHAGE DE LA PAGE
// On arrive ici dans deux cas :
// 1. Méthode GET → première visite, afficher le formulaire
// 2. Méthode POST avec erreur → réafficher avec message d'erreur
// ============================================================
require_once 'includes/header.php';
?>

<main class="login-container">

    <div class="login-box">

        <h1>Connexion</h1>
        <p>Smart Parking CREPS</p>

        <?php if ($erreur): ?>
            <!-- Affiche le message d'erreur si présent -->
            <div class="alert alert-erreur">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <!-- Le formulaire -->
        <!-- action="/login.php" = envoie les données à login.php -->
        <!-- method="POST" = utilise la méthode POST -->
        <form action="/login.php" method="POST">

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="votre@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >
                <!-- value= remet l'email saisi si erreur -->
                <!-- required = champ obligatoire (HTML5) -->
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input
                    type="password"
                    id="mot_de_passe"
                    name="mot_de_passe"
                    placeholder="••••••••"
                    required
                >
            </div>

            <button type="submit" class="btn-primary">
                Se connecter
            </button>

        </form>

        <div class="login-links">
            <a href="/inscription.php">Pas encore de compte ? S'inscrire</a>
        </div>

    </div>

</main>

<?php require_once 'includes/footer.php'; ?>