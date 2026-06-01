<?php
// ============================================================
// core/header.php
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
    <title>Smart Parking — CREPS Hauts-de-France</title>

    <link rel="stylesheet" href="/assets/css/global.css">

    <?php if ($page === 'dashboard.php' || $page === 'index.php'): ?>
        <link rel="stylesheet" href="/assets/css/dashboard.css">
    <?php elseif ($page === 'connexion-inscription.php'): ?>
        <link rel="stylesheet" href="/assets/css/connexion-inscription.css">
    <?php elseif ($page === 'validation-comptes.php'): ?>
        <link rel="stylesheet" href="/assets/css/validation-comptes.css">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <script src="/assets/js/global.js" defer></script>

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

    <!-- ── LOGOS GAUCHE ──────────────────────────────────── -->
    <a href="/index.php" class="header-logo" aria-label="Accueil Smart Parking">
        <img src="/assets/img/logo-projet.png"
             alt="Smart Parking"
             class="header-logo-img header-logo-projet"
             width="150" height="38">
        <span class="header-logo-sep"></span>
        <img src="/assets/img/logo-creps.png"
             alt="CREPS Hauts-de-France"
             class="header-logo-img header-logo-creps"
             width="110" height="32">
    </a>

    <!-- ── NAVIGATION ────────────────────────────────────── -->
    <nav class="header-nav">

        <?php if (isset($_SESSION['user'])): ?>

            <span class="header-user">
                <i class="fa-regular fa-circle-user"></i>
                <?= htmlspecialchars($_SESSION['user']['prenom_users']) ?>
                <span class="header-role-badge header-role-<?= strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $_SESSION['user']['role'])) ?>">
                    <?= htmlspecialchars($_SESSION['user']['role']) ?>
                </span>
            </span>

            <?php if (in_array($_SESSION['user']['role'], ['Employé', 'Administrateur'])): ?>
                <a href="/dashboard.php"
                   class="nav-link <?= str_contains($page, 'dashboard') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['user']['role'] === 'Administrateur'): ?>
                <a href="/validation-comptes.php"
                   class="nav-link <?= str_contains($page, 'validation') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-check"></i> Validation comptes
                </a>
            <?php endif; ?>

            <form method="POST" action="/logout.php" class="header-logout-form">
                <button type="submit" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion
                </button>
            </form>

        <?php else: ?>

            <a href="/index.php"
               class="nav-link <?= ($page === 'index.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-map-location-dot"></i> Carte parkings
            </a>
            <a href="/connexion-inscription.php"
               class="nav-link <?= str_contains($page, 'connexion') ? 'active' : '' ?>">
                <i class="fa-solid fa-right-to-bracket"></i> Connexion
            </a>

        <?php endif; ?>

    </nav>

</header>