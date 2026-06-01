<?php
// ============================================================
// core/header.php
// Inclus en premier dans chaque page publique.
// Gère : session, DOCTYPE, CSS conditionnel, JS conditionnel, header de nav.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parking CREPS</title>

    <!-- CSS global : variables, reset, header, footer, boutons, formulaires -->
    <link rel="stylesheet" href="/assets/css/global.css">

    <!-- CSS spécifique à la page courante -->
    <?php if ($page === 'dashboard.php' || $page === 'index.php'): ?>
        <link rel="stylesheet" href="/assets/css/dashboard.css">
    <?php elseif ($page === 'connexion-inscription.php'): ?>
        <link rel="stylesheet" href="/assets/css/connexion-inscription.css">
    <?php elseif ($page === 'validation-comptes.php'): ?>
        <link rel="stylesheet" href="/assets/css/validation-comptes.css">
    <?php endif; ?>

    <!-- Police Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">

    <!-- JS global : fermerModal, afficherNotif (toutes les pages) -->
    <script src="/assets/js/global.js" defer></script>

    <!-- JS spécifique à la page courante -->
    <?php if ($page === 'dashboard.php' || $page === 'index.php'): ?>
        <script src="/assets/js/dashboard.js" defer></script>
    <?php elseif ($page === 'validation-comptes.php'): ?>
        <script src="/assets/js/validation-comptes.js" defer></script>
    <?php elseif ($page === 'connexion-inscription.php'): ?>
        <script src="/assets/js/connexion-inscription.js" defer></script>
    <?php endif; ?>
</head>
<body>

<header class="site-header">

    <a href="/index.php" class="header-logo">
        🅿️ <span>Smart Parking</span> CREPS
    </a>

    <nav class="header-nav">

        <?php if (isset($_SESSION['user'])): ?>

            <span class="header-user">
                👤 <?= htmlspecialchars($_SESSION['user']['prenom_users']) ?>
                — <?= htmlspecialchars($_SESSION['user']['role']) ?>
            </span>

            <?php if (in_array($_SESSION['user']['role'], ['Employé', 'Administrateur'])): ?>
                <a href="/dashboard.php"
                   <?= str_contains($page, 'dashboard') ? 'class="active"' : '' ?>>
                    Dashboard
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['user']['role'] === 'Administrateur'): ?>
                <a href="/validation-comptes.php"
                   <?= str_contains($page, 'validation') ? 'class="active"' : '' ?>>
                    Validation comptes
                </a>
            <?php endif; ?>

            <form method="POST" action="/logout.php">
                <button type="submit">🚪 Déconnexion</button>
            </form>

        <?php else: ?>

            <a href="/index.php"
               <?= str_contains($page, 'index') ? 'class="active"' : '' ?>>
                Carte parkings
            </a>
            <a href="/connexion-inscription.php"
               <?= str_contains($page, 'connexion') ? 'class="active"' : '' ?>>
                Connexion
            </a>

        <?php endif; ?>

    </nav>

</header>