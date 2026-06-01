<?php
// ============================================================
// public/connexion-inscription.php
// ============================================================

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth.php';

if (estConnecte()) {
    header('Location: /dashboard.php');
    exit();
}

$erreur_connexion   = null;
$erreur_inscription = null;
$succes_inscription = null;

// ── CONNEXION ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'connexion') {

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    $stmt = $pdo->prepare("
        SELECT
            u.id_utilisateur,
            u.nom_users,
            u.prenom_users,
            u.email_users,
            u.mdp_users,
            u.actif,
            u.id_type_utilisateur,
            t.libelle_type_utilisateur
        FROM utilisateur u
        JOIN type_utilisateur t ON u.id_type_utilisateur = t.id_type_utilisateur
        WHERE u.email_users = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mdp_users'])) {
        if (!$user['actif']) {
            $erreur_connexion = "Votre compte est désactivé.";
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id_utilisateur'      => $user['id_utilisateur'],
                'nom_users'           => $user['nom_users'],
                'prenom_users'        => $user['prenom_users'],
                'email_users'         => $user['email_users'],
                'id_type_utilisateur' => $user['id_type_utilisateur'],
                'role'                => $user['libelle_type_utilisateur'],
            ];
            header('Location: /dashboard.php');
            exit();
        }
    } else {
        $erreur_connexion = "Email ou mot de passe incorrect.";
    }
}

// ── INSCRIPTION ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscription') {

    $prenom              = trim($_POST['prenom']              ?? '');
    $nom                 = trim($_POST['nom']                 ?? '');
    $email               = trim($_POST['email_inscription']   ?? '');
    $password            = $_POST['password_inscription']     ?? '';
    $id_type_utilisateur = (int)($_POST['role']               ?? 3);
    $numero_employe      = trim($_POST['numero_employe']      ?? '');
    $rolesAutorises      = [1, 2, 3];

    if ($prenom === '' || $nom === '' || $email === '' || $password === '') {
        $erreur_inscription = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur_inscription = "Adresse email invalide.";
    } elseif (!in_array($id_type_utilisateur, $rolesAutorises, true)) {
        $erreur_inscription = "Rôle invalide.";
    } elseif (in_array($id_type_utilisateur, [1, 2], true) && $numero_employe === '') {
        $erreur_inscription = "Le numéro d'employé est obligatoire pour ce type de compte.";
    } else {
        $stmtCheck = $pdo->prepare("
            SELECT id_utilisateur FROM utilisateur WHERE email_users = ? LIMIT 1
        ");
        $stmtCheck->execute([$email]);

        if ($stmtCheck->fetch()) {
            $erreur_inscription = "Cet email est déjà utilisé.";
        } else {
            $hash   = password_hash($password, PASSWORD_DEFAULT);
            $actif  = ($id_type_utilisateur === 3) ? 'true' : 'false'; // ← CORRECTION
            $numEmp = $numero_employe !== '' ? $numero_employe : null;

            $pdo->prepare("
                INSERT INTO utilisateur
                    (nom_users, prenom_users, email_users, mdp_users,
                     actif, id_type_utilisateur, numero_employe)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ")->execute([$nom, $prenom, $email, $hash, $actif, $id_type_utilisateur, $numEmp]);

            $succes_inscription = $id_type_utilisateur === 3
                ? "Compte créé ! Vous pouvez vous connecter."
                : "Demande envoyée, en attente de validation par un administrateur.";
        }
    }
}

require_once __DIR__ . '/../core/header.php';
require_once __DIR__ . '/../views/connexion-inscription.view.php';
require_once __DIR__ . '/../core/footer.php';