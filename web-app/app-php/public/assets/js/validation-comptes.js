// ============================================================
// validation-comptes.js — Chargé sur validation-comptes.php
// Gère la modal de modification de compte et le champ employé
// ============================================================


// ── MODAL MODIFIER COMPTE ────────────────────────────────────
// Pré-remplit la modal avec les données du compte sélectionné
function ouvrirModalModifier(btn) {
    document.getElementById('mod-id').value      = btn.dataset.id;
    document.getElementById('mod-prenom').value  = btn.dataset.prenom;
    document.getElementById('mod-nom').value     = btn.dataset.nom;
    document.getElementById('mod-email').value   = btn.dataset.email;
    document.getElementById('mod-role').value    = btn.dataset.role;
    document.getElementById('mod-employe').value = btn.dataset.employe;
    toggleModEmploye();
    document.getElementById('modal-modifier').classList.add('actif');
}

function fermerModalModifier() {
    document.getElementById('modal-modifier').classList.remove('actif');
}

// Affiche/masque le champ N° employé selon le rôle sélectionné
function toggleModEmploye() {
    const role  = document.getElementById('mod-role').value;
    const bloc  = document.getElementById('mod-bloc-employe');
    const input = document.getElementById('mod-employe');
    if (role === '1' || role === '2') {
        bloc.style.display = 'block';
        input.required     = true;
    } else {
        bloc.style.display = 'none';
        input.required     = false;
        input.value        = '';
    }
}