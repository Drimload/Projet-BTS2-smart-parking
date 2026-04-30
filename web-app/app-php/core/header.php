<?php
// ============================================================
// includes/header.php
// Entête HTML commune à toutes les pages
//
// Ce fichier contient tout ce qui apparaît en haut
// de chaque page : balises HTML de base, CSS, menu navigation
//
// Utilisation dans une page :
// require_once 'includes/header.php';
// ============================================================

// session_start() démarre le système de sessions PHP
// DOIT être appelé avant tout affichage HTML
// On vérifie d'abord si une session n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parking CREPS</title>

    <!-- Notre fichier CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav>
    <div class="nav-logo">
        <a href="/index.php">🅿️ Smart Parking CREPS</a>
    </div>

    <div class="nav-links">

        <?php if (isset($_SESSION['user'])): ?>
            <?php
            // $_SESSION['user'] contient les infos de l'utilisateur connecté
            // On affiche son prénom et son rôle
            ?>
            <span>Bonjour <?= htmlspecialchars($_SESSION['user']['prenom_users']) ?></span>
            <span class="role"><?= htmlspecialchars($_SESSION['user']['role']) ?></span>

            <?php if ($_SESSION['user']['role'] === 'Employé' || $_SESSION['user']['role'] === 'Administrateur'): ?>
                <a href="/employe/dashboard.php">Mon espace</a>
            <?php endif; ?>

            <?php if ($_SESSION['user']['role'] === 'Administrateur'): ?>
                <a href="/admin/dashboard.php">Administration</a>
            <?php endif; ?>

            <a href="/logout.php">Se déconnecter</a>

        <?php else: ?>
            <!-- Pas connecté → affiche les liens connexion/inscription -->
            <a href="/login.php">Se connecter</a>
            <a href="/inscription.php">S'inscrire</a>
        <?php endif; ?>

    </div>
</nav>