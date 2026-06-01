<?php
// ============================================================
// public/actions/capteur.php
// Action AJAX — CRUD capteurs
// Crée le device dans ChirpStack ET en BDD simultanément
// Accessible : Administrateur uniquement
// ============================================================

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/db.php';

header('Content-Type: application/json; charset=utf-8');

verifierConnexion();
verifierRole(['Administrateur']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'erreur' => 'Méthode non autorisée']);
    exit();
}

$action    = trim($_POST['action']          ?? '');
$idCapteur = filter_input(INPUT_POST, 'id_capteur', FILTER_VALIDATE_INT);
$idPlace   = filter_input(INPUT_POST, 'id_place',   FILTER_VALIDATE_INT);
$devEui    = strtolower(trim($_POST['dev_eui']       ?? ''));
$libelle   = trim($_POST['libelle_capteur']          ?? '');
$appKey    = strtolower(trim($_POST['app_key']        ?? ''));
$statut    = isset($_POST['statut']) && $_POST['statut'] === '1';



// ── CRÉER DEVICE DANS CHIRPSTACK ─────────────────────────────
// Retourne true si succès (200/201) ou déjà existant (409)
function chirpstackCreerDevice(string $devEui, string $libelle): bool {

    $payload = json_encode([
        'device' => [
            'devEui'          => $devEui,
            'name'            => $libelle ?: 'Capteur ' . $devEui,
            'applicationId'   => CHIRPSTACK_APP_ID,
            'deviceProfileId' => CHIRPSTACK_PROFILE_ID,
            'isDisabled'      => false,
        ]
    ]);

    $ch = curl_init(CHIRPSTACK_URL . '/api/devices');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer '               . CHIRPSTACK_API_KEY,
            'grpc-metadata-authorization: Bearer ' . CHIRPSTACK_API_KEY,
        ],
        CURLOPT_TIMEOUT => 5,
    ]);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return in_array($httpCode, [200, 201, 409]);
}



// ── DÉFINIR L'APPKEY DANS CHIRPSTACK (OTAA) ──────────────────
// Second appel obligatoire pour que le join OTAA fonctionne
// En ChirpStack v4, nwkKey = AppKey LoRaWAN 1.0.x
function chirpstackSetAppKey(string $devEui, string $appKey): bool {

    if (empty($appKey)) return true; // pas de clé fournie, on ignore

    $payload = json_encode([
        'deviceKeys' => [
            'devEui' => $devEui,
            'nwkKey' => $appKey,
        ]
    ]);

    $ch = curl_init(CHIRPSTACK_URL . '/api/devices/' . $devEui . '/keys');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer '               . CHIRPSTACK_API_KEY,
            'grpc-metadata-authorization: Bearer ' . CHIRPSTACK_API_KEY,
        ],
        CURLOPT_TIMEOUT => 5,
    ]);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 200/201 = clé définie, 409 = clé déjà existante → ok dans les deux cas
    return in_array($httpCode, [200, 201, 409]);
}



// ── SUPPRIMER DEVICE DANS CHIRPSTACK ─────────────────────────
// Non bloquant — la BDD prime en cas d'erreur ChirpStack
function chirpstackSupprimerDevice(string $devEui): void {

    if (empty($devEui)) return;

    $ch = curl_init(CHIRPSTACK_URL . '/api/devices/' . $devEui);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer '               . CHIRPSTACK_API_KEY,
            'grpc-metadata-authorization: Bearer ' . CHIRPSTACK_API_KEY,
        ],
        CURLOPT_TIMEOUT => 5,
    ]);

    curl_exec($ch);
    curl_close($ch);
}



// ── ROUTAGE ──────────────────────────────────────────────────
switch ($action) {



    // ── MODIFIER (ou créer si aucun capteur sur cette place) ──
    case 'modifier':

        if (!$idPlace || $idPlace <= 0) {
            echo json_encode(['success' => false, 'erreur' => 'Place invalide']);
            exit();
        }

        // Validation DevEUI — 16 caractères hexadécimaux obligatoires
        if (empty($devEui) || !preg_match('/^[0-9a-f]{16}$/', $devEui)) {
            echo json_encode(['success' => false, 'erreur' => 'DevEUI invalide (16 caractères hexadécimaux)']);
            exit();
        }

        // Validation AppKey — 32 hex si fournie
        if (!empty($appKey) && !preg_match('/^[0-9a-f]{32}$/', $appKey)) {
            echo json_encode(['success' => false, 'erreur' => 'AppKey invalide (32 caractères hexadécimaux)']);
            exit();
        }

        // DevEUI déjà utilisé sur une autre place ?
        $stmt = $pdo->prepare("
            SELECT id_capteur FROM capteur
            WHERE dev_eui = ? AND id_place != ?
        ");
        $stmt->execute([$devEui, $idPlace]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'erreur' => 'Ce DevEUI est déjà utilisé sur une autre place']);
            exit();
        }

        try {
            // Vérifie si un capteur existe déjà sur cette place
            $stmt = $pdo->prepare("SELECT id_capteur, dev_eui FROM capteur WHERE id_place = ?");
            $stmt->execute([$idPlace]);
            $existant = $stmt->fetch();

            // Si modification avec changement de DevEUI → supprimer l'ancien dans ChirpStack
            if ($existant && $existant['dev_eui'] !== $devEui) {
                chirpstackSupprimerDevice($existant['dev_eui']);
            }

            // 1. Créer le (nouveau) device dans ChirpStack
            if (!chirpstackCreerDevice($devEui, $libelle)) {
                echo json_encode(['success' => false, 'erreur' => 'Échec création dans ChirpStack — vérifiez la config API']);
                exit();
            }

            // 2. Définir l'AppKey si fournie (OTAA)
            if (!empty($appKey)) {
                chirpstackSetAppKey($devEui, $appKey);
            }

            // 3. INSERT ou UPDATE en BDD
            if ($existant) {
                $pdo->prepare("
                    UPDATE capteur
                    SET dev_eui         = ?,
                        libelle_capteur = ?,
                        statut          = ?
                    WHERE id_place = ?
                ")->execute([$devEui, $libelle, $statut, $idPlace]);
            } else {
                $pdo->prepare("
                    INSERT INTO capteur (dev_eui, libelle_capteur, statut, id_place)
                    VALUES (?, ?, ?, ?)
                ")->execute([$devEui, $libelle, $statut, $idPlace]);
            }

            echo json_encode(['success' => true]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'erreur' => 'Erreur base de données']);
        }
        break;



    // ── SUPPRIMER ─────────────────────────────────────────────
    case 'supprimer':

        if (!$idCapteur || $idCapteur <= 0) {
            echo json_encode(['success' => false, 'erreur' => 'Capteur invalide']);
            exit();
        }

        try {
            // Récupère le DevEUI avant suppression
            $stmt = $pdo->prepare("SELECT dev_eui FROM capteur WHERE id_capteur = ?");
            $stmt->execute([$idCapteur]);
            $capteur = $stmt->fetch();

            if (!$capteur) {
                echo json_encode(['success' => false, 'erreur' => 'Capteur introuvable']);
                exit();
            }

            // 1. Supprime dans ChirpStack (non bloquant)
            chirpstackSupprimerDevice($capteur['dev_eui']);

            // 2. Supprime en BDD
            $pdo->prepare("DELETE FROM capteur WHERE id_capteur = ?")
                ->execute([$idCapteur]);

            echo json_encode(['success' => true]);

        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'foreign key')) {
                echo json_encode([
                    'success' => false,
                    'erreur'  => 'Impossible de supprimer : des signalements sont liés à ce capteur'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'erreur' => 'Erreur base de données']);
            }
        }
        break;



    // ── ACTION INCONNUE ───────────────────────────────────────
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'erreur' => 'Action inconnue']);
}