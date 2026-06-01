<!-- ============================================================
     views/dashboard.view.php
     ============================================================ -->

<div class="dashboard-wrapper">

    <!-- ── TOPBAR ─────────────────────────────────────────── -->
    <div class="topbar">
        <span id="derniere-maj" class="maj-info">Chargement...</span>
        <span class="badge-role badge-<?= strtolower(iconv("UTF-8", "ASCII//TRANSLIT", $role)) ?>">
            <?= htmlspecialchars($role) ?>
        </span>
    </div>

    <!-- ── RÉSUMÉ COMPTEURS ───────────────────────────────── -->
    <div class="resume-grille">
        <?php foreach ($resume as $r): ?>
        <div class="resume-card" id="resume-<?= $r['id_parking'] ?>">
            <h3><?= htmlspecialchars($r['libelle_parking']) ?></h3>
            <div class="resume-stats">
                <div class="stat">
                    <span class="stat-libres"><?= $r['places_libres'] ?></span>
                    <small>Libres</small>
                </div>
                <div class="stat">
                    <span class="stat-occupees"><?= $r['places_occupees'] ?></span>
                    <small>Occupées</small>
                </div>
                <div class="stat">
                    <span class="stat-pmr"><?= $r['pmr_libres'] ?></span>
                    <small>PMR libres</small>
                </div>
                <div class="stat">
                    <span class="stat-taux"><?= $r['taux_occupation'] ?>%</span>
                    <small>Occupation</small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
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

                    <span class="place-numero">
                        <?= htmlspecialchars($p['numero']) ?>
                    </span>

                    <?php if ($p['type_place'] === 'pmr'): ?>
                        <span class="place-icone">♿</span>
                    <?php endif; ?>

                    <?php if ($p['verrouille']): ?>
                        <span class="place-icone">🔒</span>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
            </div>

        </section>
        <?php endforeach; ?>
    </div>

    <!-- ── PANNEAU LATÉRAL ────────────────────────────────── -->
    <aside id="panneau-detail" class="panneau-detail">
        <button id="panneau-close" class="panneau-close" aria-label="Fermer">✕</button>
        <div id="panneau-contenu"></div>
    </aside>

    <!-- ── MODAL SIGNALEMENT (Employé + Admin) ────────────── -->
    <?php if (in_array($role, ['Employé', 'Administrateur'])): ?>
    <div id="modal-signalement" class="modal" role="dialog" aria-modal="true">
        <div class="modal-content">
            <button class="modal-close"
                    onclick="fermerModal('modal-signalement')"
                    aria-label="Fermer">✕</button>
            <h3>Signaler un problème</h3>
            <p>Place : <strong id="signal-place-nom"></strong></p>
            <form id="form-signalement" novalidate>
                <input type="hidden" name="id_place" id="signal-id-place">
                <label for="signal-description">Description</label>
                <textarea id="signal-description" name="description" rows="4"
                          required
                          placeholder="Ex : Capteur desserré, véhicule abusif..."></textarea>
                <button type="submit" class="btn btn-primary">
                    Envoyer le signalement
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
                    aria-label="Fermer">✕</button>
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

                <label for="edit-app-key">AppKey <small style="font-weight:normal;color:var(--texte-secondaire)">(32 hex — étiquette du capteur)</small></label>
                <input type="text" id="edit-app-key" name="app_key"
                       maxlength="32" placeholder="ex : 00000000000000000000000000000000">

                <label class="label-checkbox">
                    <input type="checkbox" name="statut" id="edit-statut" value="1">
                    Capteur actif
                </label>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── TOAST ──────────────────────────────────────────── -->
    <div id="notif" class="notif" role="status" aria-live="polite"></div>

</div>

<!-- Transmet le rôle PHP au JS via data-attribute sur <body> -->
<script>
    document.body.dataset.role = '<?= strtolower(iconv("UTF-8", "ASCII//TRANSLIT", $role)) ?>';
</script>