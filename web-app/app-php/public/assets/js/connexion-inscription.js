// ============================================================
// connexion-inscription.js — Chargé sur connexion-inscription.php
// Affiche/masque le champ N° employé selon le rôle choisi
// ============================================================


// ── CHAMP N° EMPLOYÉ ─────────────────────────────────────────
// Visible uniquement pour les rôles Administrateur (1) et Employé (2)
function toggleNumeroEmploye() {
    const role  = document.getElementById('role').value;
    const bloc  = document.getElementById('bloc-numero-employe');
    const input = document.getElementById('numero_employe');
    if (role === '1' || role === '2') {
        bloc.style.display = 'block';
        input.required     = true;
    } else {
        bloc.style.display = 'none';
        input.required     = false;
        input.value        = '';
    }
}