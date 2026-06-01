<?php
// ============================================================
// views/dashboard.view.php
// ============================================================
?>

<div class="dashboard-wrapper">

    <!-- ── TOPBAR ─────────────────────────────────────────── -->
    <div class="topbar">
        <span id="derniere-maj" class="maj-info">
            <i class="fa-regular fa-clock"></i>
            Chargement...
        </span>
        <span class="badge-role badge-<?= strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $role)) ?>">
            <?= htmlspecialchars($role) ?>
        </span>
    </div>

    <!-- ── RÉSUMÉ COMPTEURS ───────────────────────────────── -->
    <div class="resume-grille">
        <?php foreach ($resume as $r): ?>
        <div class="resume-card" id="resume-<?= $r['id_parking'] ?>">
            <h3><?= htmlspecialchars($r['libelle_parking']) ?></h3>

            <div class="resume-stats">

                <!-- Standard -->
                <div class="stat-bloc stat-standard">
                    <div class="stat-header">
                        <i class="fa-solid fa-square-parking"></i>
                        <span class="stat-label">Standard</span>
                    </div>
                    <div class="stat-values">
                        <span class="stat-libre"><?= $r['places_libres'] - $r['pmr_libres'] ?></span>
                        <span class="stat-sep">/</span>
                        <span class="stat-total"><?= $r['total_places'] - ($r['pmr_total'] ?? 0) ?></span>
                    </div>
                    <small class="stat-sublabel">libres / total</small>
                </div>

                <!-- PMR -->
                <div class="stat-bloc stat-pmr">
                    <div class="stat-header">
                        <i class="fa-solid fa-wheelchair"></i>
                        <span class="stat-label">PMR</span>
                    </div>
                    <div class="stat-values">
                        <span class="stat-libre"><?= $r['pmr_libres'] ?></span>
                        <span class="stat-sep">/</span>
                        <span class="stat-total"><?= $r['pmr_total'] ?? '—' ?></span>
                    </div>
                    <small class="stat-sublabel">libres / total</small>
                </div>

                <!-- Disponible global -->
                <div class="stat-bloc stat-global">
                    <div class="stat-header">
                        <i class="fa-solid fa-circle-check"></i>
                        <span class="stat-label">Disponible</span>
                    </div>
                    <div class="stat-values">
                        <span class="stat-libre"><?= $r['places_libres'] ?></span>
                        <span class="stat-sep">/</span>
                        <span class="stat-total"><?= $r['total_places'] ?></span>
                    </div>
                    <small class="stat-sublabel">libres / total</small>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── LÉGENDE ────────────────────────────────────────── -->
    <div class="legende">
        <span class="legende-item">
            <span class="legende-dot dot-libre"></span> Libre
        </span>
        <span class="legende-item">
            <span class="legende-dot dot-occupe"></span> Occupé
        </span>
        <span class="legende-item">
            <span class="legende-dot dot-sans-capteur"></span> Sans capteur
        </span>
    </div>

    <!-- ── GRILLE DES PLACES ──────────────────────────────── -->
    <div class="parkings-container">
        <?php foreach ($parkings as $idParking => $parking): ?>
        <section class="parking-zone">
            <h2><?= htmlspecialchars($parking['libelle']) ?></h2>

            <div class="grille-places">
                <?php foreach ($parking['places'] as $p): ?>
                <div class="place
                     <?= empty($p['id_capteur']) ? 'sans-capteur' : $p['etat'] ?>
                     <?= $p['type_place'] === 'pmr' ? 'pmr' : '' ?>
                     <?= $p['verrouille'] ? 'verrouille' : '' ?>"
                     id="place-<?= $p['id_place'] ?>"
                     data-id-place="<?= $p['id_place'] ?>"
                     data-id-capteur="<?= $p['id_capteur'] ?? '' ?>"
                     data-numero="<?= htmlspecialchars($p['numero']) ?>"
                     data-etat="<?= $p['etat'] ?>"
                     data-type-place="<?= $p['type_place'] ?>"
                     data-reservee="<?= $p['reservee'] ? '1' : '0' ?>"
                     data-dev-eui="<?= htmlspecialchars($p['dev_eui'] ?? '') ?>"
                     data-capteur-actif="<?= $p['capteur_actif'] ? '1' : '0' ?>"
                     data-verrouille="<?= $p['verrouille'] ? '1' : '0' ?>"
                     data-batterie="<?= $p['niveau_batterie'] ?? '' ?>"
                     data-last-seen="<?= htmlspecialchars($p['last_seen_at'] ?? '') ?>">

                    <span class="place-numero"><?= htmlspecialchars($p['numero']) ?></span>

                    <?php if ($p['type_place'] === 'pmr'): ?>
                        <span class="place-icone" aria-label="Place PMR">
                            <i class="fa-solid fa-wheelchair"></i>
                        </span>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
            </div>

        </section>
        <?php endforeach; ?>
    </div>

    <!-- ── PANNEAU LATÉRAL ────────────────────────────────── -->
    <aside id="panneau-detail" class="panneau-detail">
        <button id="panneau-close" class="panneau-close" aria-label="Fermer">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div id="panneau-contenu"></div>
    </aside>

    <!-- ── MODAL SIGNALEMENT (Employé + Admin) ────────────── -->
    <?php if (in_array($role, ['Employé', 'Administrateur'])): ?>
    <div id="modal-signalement" class="modal" role="dialog" aria-modal="true">
        <div class="modal-content">
            <button class="modal-close"
                    onclick="fermerModal('modal-signalement')"
                    aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <h3>Signaler un problème</h3>
            <p>Place : <strong id="signal-place-nom"></strong></p>
            <form id="form-signalement" novalidate>
                <input type="hidden" name="id_place" id="signal-id-place">
                <label for="signal-description">Description</label>
                <textarea id="signal-description" name="description" rows="4"
                          required
                          placeholder="Ex : Capteur desserré, véhicule abusif..."></textarea>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> Envoyer le signalement
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── MODAL CAPTEUR (Admin uniquement) ───────────────── -->
    <?php if ($role === 'Administrateur'): ?>
    <div id="modal-capteur" class="modal" role="dialog" aria-modal="true">
        <div class="modal-content">
            <button class="modal-close"
                    onclick="fermerModal('modal-capteur')"
                    aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <h3>Gestion du capteur</h3>
            <form id="form-capteur" novalidate>
                <input type="hidden" name="id_place"   id="edit-id-place">
                <input type="hidden" name="id_capteur" id="edit-id-capteur">

                <label for="edit-dev-eui">DevEUI</label>
                <input type="text" id="edit-dev-eui" name="dev_eui"
                       maxlength="16" placeholder="ex : a840414a71836eec" required>

                <label for="edit-libelle">Libellé</label>
                <input type="text" id="edit-libelle" name="libelle_capteur"
                       placeholder="ex : Capteur A01">

                <label for="edit-app-key">
                    AppKey
                    <small style="font-weight:normal;color:var(--texte-secondaire)">
                        (32 hex — étiquette du capteur)
                    </small>
                </label>
                <input type="text" id="edit-app-key" name="app_key"
                       maxlength="32" placeholder="ex : 00000000000000000000000000000000">

                <label class="label-checkbox">
                    <input type="checkbox" name="statut" id="edit-statut" value="1">
                    Capteur actif
                </label>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── TOAST ──────────────────────────────────────────── -->
    <div id="notif" class="notif" role="status" aria-live="polite"></div>

</div>

<script>
    document.body.dataset.role = '<?= strtolower(iconv("UTF-8", "ASCII//TRANSLIT", $role)) ?>';
</script>