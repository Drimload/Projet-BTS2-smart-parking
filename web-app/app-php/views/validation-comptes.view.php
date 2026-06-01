<div class="validation-wrapper">

    <!-- ── COMPTES EN ATTENTE ─────────────────────────────── -->
    <section class="validation-section">
        <h1>Comptes en attente de validation</h1>

        <?php if (empty($comptesEnAttente)): ?>
            <p class="empty-message">Aucun compte en attente.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th>Prénom</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle demandé</th>
                            <th>N° employé</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comptesEnAttente as $compte): ?>
                        <tr>
                            <td><?= htmlspecialchars($compte['prenom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($compte['nom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($compte['email'] ?? '') ?></td>
                            <td>
                                <span class="badge-role badge-<?= strtolower($compte['role'] ?? '') ?>">
                                    <?= htmlspecialchars($compte['role'] ?? '') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($compte['numero_employe'] ?? '-') ?></td>
                            <td>
                                <div class="actions-form">
                                    <form method="post">
                                        <input type="hidden" name="id_utilisateur"
                                               value="<?= (int)($compte['id_utilisateur'] ?? 0) ?>">
                                        <button type="submit" name="action" value="valider"
                                                class="btn btn-success">Valider</button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="id_utilisateur"
                                               value="<?= (int)($compte['id_utilisateur'] ?? 0) ?>">
                                        <button type="submit" name="action" value="refuser"
                                                class="btn btn-danger"
                                                onclick="return confirm('Confirmer le refus ?')">
                                            Refuser
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- ── COMPTES ACTIFS PAR RÔLE ────────────────────────── -->
    <section class="validation-section">
        <h1>Comptes actifs</h1>

        <?php if (empty($comptesActifs)): ?>
            <p class="empty-message">Aucun compte actif.</p>
        <?php else: ?>
            <?php foreach ($comptesActifs as $role => $comptes): ?>
            <div class="role-group">
                <h2>
                    <span class="badge-role badge-<?= strtolower($role) ?>">
                        <?= htmlspecialchars($role) ?>
                    </span>
                    <span class="role-count">
                        <?= count($comptes) ?> compte<?= count($comptes) > 1 ? 's' : '' ?>
                    </span>
                </h2>

                <div class="table-wrapper">
                    <table class="accounts-table">
                        <thead>
                            <tr>
                                <th>Prénom</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>N° employé</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comptes as $compte): ?>
                            <tr>
                                <td><?= htmlspecialchars($compte['prenom']) ?></td>
                                <td><?= htmlspecialchars($compte['nom']) ?></td>
                                <td><?= htmlspecialchars($compte['email']) ?></td>
                                <td><?= htmlspecialchars($compte['numero_employe'] ?? '-') ?></td>
                                <td>
                                    <div class="actions-form">
                                        <button type="button" class="btn btn-secondary"
                                            data-id="<?= $compte['id_utilisateur'] ?>"
                                            data-prenom="<?= htmlspecialchars($compte['prenom'], ENT_QUOTES) ?>"
                                            data-nom="<?= htmlspecialchars($compte['nom'], ENT_QUOTES) ?>"
                                            data-email="<?= htmlspecialchars($compte['email'], ENT_QUOTES) ?>"
                                            data-role="<?= $compte['id_role'] ?>"
                                            data-employe="<?= htmlspecialchars($compte['numero_employe'] ?? '', ENT_QUOTES) ?>"
                                            onclick="ouvrirModalModifier(this)">
                                            Modifier
                                        </button>
                                        <form method="post">
                                            <input type="hidden" name="id_utilisateur"
                                                   value="<?= (int)$compte['id_utilisateur'] ?>">
                                            <button type="submit" name="action" value="supprimer"
                                                    class="btn btn-danger"
                                                    onclick="return confirm('Supprimer ce compte ?')">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <!-- ── MODAL MODIFIER COMPTE ──────────────────────────── -->
    <div id="modal-modifier" class="modal" role="dialog" aria-modal="true">
        <div class="modal-content">
            <button class="modal-close" onclick="fermerModalModifier()"
                    aria-label="Fermer">✕</button>
            <h3>Modifier le compte</h3>

            <form method="POST" action="/validation-comptes.php">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id_utilisateur" id="mod-id">

                <div class="form-row">
                    <div>
                        <label for="mod-prenom">Prénom</label>
                        <input type="text" id="mod-prenom" name="prenom" required>
                    </div>
                    <div>
                        <label for="mod-nom">Nom</label>
                        <input type="text" id="mod-nom" name="nom" required>
                    </div>
                </div>

                <label for="mod-email">Email</label>
                <input type="email" id="mod-email" name="email" required>

                <label for="mod-role">Type de compte</label>
                <select id="mod-role" name="role" required onchange="toggleModEmploye()">
                    <?php foreach ($typesUtilisateur as $t): ?>
                    <option value="<?= $t['id_type_utilisateur'] ?>">
                        <?= htmlspecialchars($t['libelle_type_utilisateur']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <div id="mod-bloc-employe">
                    <label for="mod-employe">Numéro d'employé</label>
                    <input type="text" id="mod-employe" name="numero_employe"
                           placeholder="Ex : EMP-0042" maxlength="50">
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:1.2rem">
                    Enregistrer les modifications
                </button>
            </form>
        </div>
    </div>

</div><!-- /.validation-wrapper -->