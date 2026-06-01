<!-- ============================================================
     views/connexion-inscription.view.php
     ============================================================ -->

<main class="auth-main">
    <div class="auth-container">

        <!-- ── CONNEXION ──────────────────────────────────── -->
        <section class="auth-bloc">
            <h2>Se connecter</h2>

            <?php if (!empty($erreur_connexion)): ?>
                <p class="auth-message auth-erreur">
                    <?= htmlspecialchars($erreur_connexion) ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="/connexion-inscription.php">
                <input type="hidden" name="action" value="connexion">

                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       autocomplete="email" required>

                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>

                <button type="submit" class="btn btn-primary btn-full">
                    Se connecter
                </button>
            </form>
        </section>

        <!-- ── SÉPARATEUR ─────────────────────────────────── -->
        <div class="auth-separateur"></div>

        <!-- ── INSCRIPTION ────────────────────────────────── -->
        <section class="auth-bloc">
            <h2>Créer un compte</h2>

            <?php if (!empty($erreur_inscription)): ?>
                <p class="auth-message auth-erreur">
                    <?= htmlspecialchars($erreur_inscription) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($succes_inscription)): ?>
                <p class="auth-message auth-succes">
                    <?= htmlspecialchars($succes_inscription) ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="/connexion-inscription.php">
                <input type="hidden" name="action" value="inscription">

                <div class="form-row">
                    <div>
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                    <div>
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                </div>

                <label for="email_inscription">Email</label>
                <input type="email" id="email_inscription"
                       name="email_inscription"
                       autocomplete="email" required>

                <label for="password_inscription">Mot de passe</label>
                <input type="password" id="password_inscription"
                       name="password_inscription"
                       autocomplete="new-password" required>

                <label for="role">Type de compte</label>
                <select id="role" name="role" required
                        onchange="toggleNumeroEmploye()">
                    <option value="3">Visiteur</option>
                    <option value="2">Employé</option>
                    <option value="1">Administrateur</option>
                </select>

                <div id="bloc-numero-employe" style="display:none">
                    <label for="numero_employe">Numéro d'employé</label>
                    <input type="text" id="numero_employe"
                           name="numero_employe"
                           placeholder="Ex : EMP-0042" maxlength="50">
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Créer le compte
                </button>
            </form>
        </section>

    </div>
</main>