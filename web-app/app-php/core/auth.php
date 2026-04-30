<?php
// ============================================================
// core/auth.php  ← était "includes/auth.php"
// Vérification des droits d'accès
//
// UTILISATION :
//
// Page employé :
//   require_once __DIR__ . '/../core/auth.php';
//   verifierConnexion();
//   verifierRole(['Employé', 'Administrateur']);
//
// Page admin :
//   require_once __DIR__ . '/../core/auth.php';
//   verifierConnexion();
//   verifierRole(['Administrateur']);
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verifierConnexion() {
    if (!isset($_SESSION['user'])) {
        header('Location: /acces/login.php');  // ← chemin corrigé
        exit();
    }
}

function verifierRole($rolesAutorises) {
    $roleUtilisateur = $_SESSION['user']['role'];
    if (!in_array($roleUtilisateur, $rolesAutorises)) {
        header('Location: /index.php');
        exit();
    }
}

function estConnecte() {
    return isset($_SESSION['user']);
}

function getRole() {
    return $_SESSION['user']['role'] ?? null;  // ← simplifié avec ??
}