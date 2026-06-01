// ============================================================
// global.js — Chargé sur toutes les pages via header.php
// Fonctions utilitaires partagées : modal, toast
// ============================================================


// ── FERMER UNE MODAL ────────────────────────────────────────
// Retire la classe .actif sur n'importe quelle modal par son id
function fermerModal(id) {
    document.getElementById(id)?.classList.remove('actif');
}


// ── TOAST DE NOTIFICATION ───────────────────────────────────
// Affiche un message en bas à droite, disparaît après 3.5s
// erreur=true → bordure rouge via .notif-erreur
function afficherNotif(msg, erreur = false) {
    const notif = document.getElementById('notif');
    if (!notif) return;
    notif.textContent = msg;
    notif.className   = 'notif actif' + (erreur ? ' notif-erreur' : '');
    setTimeout(() => notif.classList.remove('actif'), 3500);
}