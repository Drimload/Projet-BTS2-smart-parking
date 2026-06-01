<?php
// ============================================================
// core/auth.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── CONFIGURATION CHIRPSTACK ─────────────────────────────────
define('CHIRPSTACK_URL', 'http://chirpstack-rest-api:8090');
define('CHIRPSTACK_API_KEY',    'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjVhY2VlMWI3LWJkZTYtNGUzZS05NGRiLTlhNzJhYjAxMTA3MSIsInR5cCI6ImtleSJ9.u5Avzgab0XoG9iyMzQ2d8aLQzf2EufyRwtYotkprAJw');
define('CHIRPSTACK_APP_ID',     'd56b9f07-9f1e-4f5d-b024-ba5891058a73');
define('CHIRPSTACK_PROFILE_ID', '8038bb54-4b1f-4ed9-acf1-b89e5c5b850a');
// ── SESSION & RÔLES ──────────────────────────────────────────

// Redirige vers login si pas connecté
function verifierConnexion(): void {
    if (!isset($_SESSION['user'])) {
        header('Location: /connexion-inscription.php');
        exit();
    }
}

// Redirige vers dashboard si le rôle n'est pas autorisé
function verifierRole(array $rolesAutorises): void {
    verifierConnexion();
    $role = $_SESSION['user']['role'] ?? null;
    if (!in_array($role, $rolesAutorises, true)) {
        header('Location: /dashboard.php');
        exit();
    }
}

function estConnecte(): bool {
    return isset($_SESSION['user']);
}

function getRole(): ?string {
    return $_SESSION['user']['role'] ?? null;
}

function getUser(): ?array {
    return $_SESSION['user'] ?? null;
}