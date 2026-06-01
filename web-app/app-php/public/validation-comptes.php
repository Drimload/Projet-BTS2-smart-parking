<?php
// ============================================================
// public/validation-comptes.php
// ============================================================

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth.php';

verifierConnexion();
verifierRole(['Administrateur']);

// ---- TRAITEMENT DES ACTIONS ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_utilisateur = (int)($_POST['id_utilisateur'] ?? 0);
    $action         = $_POST['action'] ?? '';

    if ($id_utilisateur > 0) {

        if ($action === 'valider') {
            $stmt = $pdo->prepare("
                UPDATE public.utilisateur
                SET actif = true
                WHERE id_utilisateur = ?
            ");
            $stmt->execute([$id_utilisateur]);

        } elseif ($action === 'refuser' || $action === 'supprimer') {
            $stmt = $pdo->prepare("
                DELETE FROM public.utilisateur
                WHERE id_utilisateur = ?
            ");
            $stmt->execute([$id_utilisateur]);

        } elseif ($action === 'modifier') {
            $prenom         = trim($_POST['prenom']         ?? '');
            $nom            = trim($_POST['nom']            ?? '');
            $email          = trim($_POST['email']          ?? '');
            $role           = (int)($_POST['role']          ?? 0);
            $numero_employe = trim($_POST['numero_employe'] ?? '') ?: null;

            if ($prenom && $nom && $email && $role) {
                $stmt = $pdo->prepare("
                    UPDATE public.utilisateur
                    SET prenom_users        = ?,
                        nom_users           = ?,
                        email_users         = ?,
                        id_type_utilisateur = ?,
                        numero_employe      = ?
                    WHERE id_utilisateur = ?
                ");
                $stmt->execute([$prenom, $nom, $email, $role, $numero_employe, $id_utilisateur]);
            }
        }
    }

    header('Location: /validation-comptes.php');
    exit();
}

// ---- COMPTES EN ATTENTE ----
$stmt = $pdo->prepare("
    SELECT
        u.id_utilisateur,
        u.prenom_users  AS prenom,
        u.nom_users     AS nom,
        u.email_users   AS email,
        t.libelle_type_utilisateur AS role,
        u.numero_employe
    FROM public.utilisateur u
    JOIN public.type_utilisateur t ON u.id_type_utilisateur = t.id_type_utilisateur
    WHERE u.actif = false
    ORDER BY u.id_utilisateur
");
$stmt->execute();
$comptesEnAttente = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---- COMPTES ACTIFS GROUPÉS PAR RÔLE ----
$stmt = $pdo->prepare("
    SELECT
        u.id_utilisateur,
        u.prenom_users  AS prenom,
        u.nom_users     AS nom,
        u.email_users   AS email,
        t.libelle_type_utilisateur AS role,
        t.id_type_utilisateur      AS id_role,
        u.numero_employe
    FROM public.utilisateur u
    JOIN public.type_utilisateur t ON u.id_type_utilisateur = t.id_type_utilisateur
    WHERE u.actif = true
    ORDER BY t.id_type_utilisateur, u.nom_users
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$comptesActifs = [];
foreach ($rows as $row) {
    $comptesActifs[$row['role']][] = $row;
}

// ---- TYPES UTILISATEURS (pour le select de la modal) ----
$typesStmt = $pdo->query("
    SELECT id_type_utilisateur, libelle_type_utilisateur
    FROM public.type_utilisateur
    ORDER BY id_type_utilisateur
");
$typesUtilisateur = $typesStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../core/header.php';
require_once __DIR__ . '/../views/validation-comptes.view.php';
require_once __DIR__ . '/../core/footer.php';