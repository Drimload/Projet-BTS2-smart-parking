// ============================================================
// dashboard.js
// ============================================================

const REFRESH_INTERVAL = 5000;
const ROLE = document.body.dataset.role;


// ── POLLING ÉTAT ────────────────────────────────────────────
function fetchEtat() {
    fetch('/etat.php')
        .then(r => {
            if (!r.ok) throw new Error('Erreur HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.erreur);
            mettreAJourResume(data.resume);
            mettreAJourGrille(data.places);
            majHorodatage(data.timestamp);
        })
        .catch(err => {
            document.getElementById('derniere-maj').textContent =
                '⚠️ Connexion perdue — ' + err.message;
        });
}


// ── RÉSUMÉ COMPTEURS ────────────────────────────────────────
function mettreAJourResume(resume) {
    resume.forEach(pk => {
        const card = document.getElementById('resume-' + pk.id_parking);
        if (!card) return;
        card.querySelector('.stat-libres').textContent   = pk.places_libres;
        card.querySelector('.stat-occupees').textContent = pk.places_occupees;
        card.querySelector('.stat-taux').textContent     = pk.taux_occupation + '%';
        card.querySelector('.stat-pmr').textContent      = pk.pmr_libres;
    });
}


// ── GRILLE DES PLACES ────────────────────────────────────────
function mettreAJourGrille(places) {
    places.forEach(p => {
        const el = document.getElementById('place-' + p.id_place);
        if (!el) return;

        el.className = 'place'
            + (p.id_capteur ? ' ' + p.etat : ' sans-capteur')
            + (p.type_place === 'pmr' ? ' pmr'       : '')
            + (p.verrouille           ? ' verrouille' : '')
            + (p.reservee             ? ' reservee'   : '');

        el.dataset.etat         = p.etat;
        el.dataset.typePlace    = p.type_place;
        el.dataset.reservee     = p.reservee;
        el.dataset.idCapteur    = p.id_capteur      ?? '';
        el.dataset.devEui       = p.dev_eui         ?? '';
        el.dataset.capteurActif = p.capteur_actif   ?? '';
        el.dataset.verrouille   = p.verrouille      ?? '';
        el.dataset.batterie     = p.niveau_batterie ?? '';
        el.dataset.lastSeen     = p.last_seen_at    ?? '';
    });
}


// ── HORODATAGE ───────────────────────────────────────────────
function majHorodatage(timestamp) {
    const el = document.getElementById('derniere-maj');
    if (el) el.textContent = 'Dernière mise à jour : ' + timestamp;
}


// ── CLIC SUR UNE PLACE ───────────────────────────────────────
document.addEventListener('click', function(e) {
    const place = e.target.closest('.place');
    if (!place) return;

    const panneau   = document.getElementById('panneau-detail');
    const contenu   = document.getElementById('panneau-contenu');
    const numero    = place.dataset.numero;
    const etat      = place.dataset.etat;
    const type      = place.dataset.typePlace;
    const batterie  = place.dataset.batterie;
    const devEui    = place.dataset.devEui;
    const lastSeen  = place.dataset.lastSeen;
    const idPlace   = place.dataset.idPlace;
    const idCapteur = place.dataset.idCapteur;

    const batValeur   = batterie !== '' ? batterie + '%' : 'N/A';
    const batClass    = batterie !== '' && batterie < 20 ? 'batterie-faible' : 'batterie-ok';
    const lastSeenFmt = lastSeen || 'Jamais';

    let html = `
        <h3>Place ${numero}</h3>
        <div class="detail-ligne">
            <span>Type</span>
            <strong>${type === 'pmr' ? '♿ PMR' : 'Standard'}</strong>
        </div>
        <div class="detail-ligne">
            <span>État</span>
            <span class="badge badge-${etat}">${etat}</span>
        </div>
    `;

    if (!idCapteur) {
        html += `
            <div class="detail-ligne">
                <span>Capteur</span>
                <span style="color:var(--couleur-hors-service)">Aucun capteur</span>
            </div>
        `;
    }

    if (ROLE.includes('employ')) {
        html += `
            <hr>
            <div class="detail-ligne">
                <span>Batterie</span>
                <span class="${batClass}">${batValeur}</span>
            </div>
            <div class="detail-ligne">
                <span>Dernière trame</span>
                <small>${lastSeenFmt}</small>
            </div>
            <hr>
            <button class="btn btn-warning"
                onclick="ouvrirSignalement('${idPlace}', '${numero}')">
                ⚠️ Signaler un problème
            </button>
        `;
    }

    if (ROLE.includes('admin')) {
        html += `
            <hr>
            <div class="detail-ligne">
                <span>Batterie</span>
                <span class="${batClass}">${batValeur}</span>
            </div>
            <div class="detail-ligne">
                <span>Dernière trame</span>
                <small>${lastSeenFmt}</small>
            </div>
            <div class="detail-ligne">
                <span>DevEUI</span>
                <code>${devEui || 'Aucun capteur'}</code>
            </div>
            <hr>
            <h4>Gestion capteur</h4>
            <button class="btn btn-secondary"
                onclick="ouvrirModalCapteur('${idPlace}', '${idCapteur}', '${devEui}')">
                ✏️ Modifier / Associer un capteur
            </button>
            <button class="btn btn-danger"
                onclick="supprimerCapteur('${idCapteur}', '${numero}')">
                🗑️ Supprimer de la BDD
            </button>
            <hr>
            <h4>⚠️ Signalements</h4>
            <div id="panneau-signalements">
                <em style="color:var(--texte-secondaire);font-size:0.82rem">Chargement…</em>
            </div>
        `;
    }

    contenu.innerHTML = html;
    panneau.classList.add('actif');

    if (ROLE.includes('admin') && idCapteur) {
        chargerSignalements(idCapteur);
    }
});


// ── FERMER LE PANNEAU ────────────────────────────────────────
document.getElementById('panneau-close')?.addEventListener('click', () => {
    document.getElementById('panneau-detail').classList.remove('actif');
});


// ── MODAL SIGNALEMENT ────────────────────────────────────────
function ouvrirSignalement(idPlace, numero) {
    document.getElementById('signal-id-place').value        = idPlace;
    document.getElementById('signal-place-nom').textContent = numero;
    document.getElementById('modal-signalement').classList.add('actif');
}

document.getElementById('form-signalement')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const data = new FormData(this);
    fetch('/actions/signaler.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                fermerModal('modal-signalement');
                this.reset();
                afficherNotif('✅ Signalement enregistré');
            } else {
                afficherNotif('❌ Erreur : ' + res.erreur, true);
            }
        });
});


// ── MODAL CAPTEUR ────────────────────────────────────────────
function ouvrirModalCapteur(idPlace, idCapteur, devEui) {
    document.getElementById('edit-id-place').value   = idPlace;
    document.getElementById('edit-id-capteur').value = idCapteur;
    document.getElementById('edit-dev-eui').value    = devEui;
    // AppKey toujours vide à l'ouverture (non stockée côté front par sécurité)
    document.getElementById('edit-app-key').value    = '';
    document.getElementById('modal-capteur').classList.add('actif');
}

function supprimerCapteur(idCapteur, numero) {
    if (!idCapteur) {
        afficherNotif('❌ Aucun capteur associé à cette place', true);
        return;
    }
    if (!confirm(`Supprimer le capteur de la place ${numero} ?`)) return;

    const data = new FormData();
    data.append('action',     'supprimer');
    data.append('id_capteur', idCapteur);

    fetch('/actions/capteur.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                fermerModal('panneau-detail');
                afficherNotif('✅ Capteur supprimé');
                fetchEtat();
            } else {
                afficherNotif('❌ Erreur : ' + res.erreur, true);
            }
        });
}

document.getElementById('form-capteur')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const data = new FormData(this);
    data.append('action', 'modifier');

    fetch('/actions/capteur.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                fermerModal('modal-capteur');
                this.reset();
                afficherNotif('✅ Capteur mis à jour');
                fetchEtat();
            } else {
                afficherNotif('❌ Erreur : ' + res.erreur, true);
            }
        });
});


// ── SIGNALEMENTS DANS LE PANNEAU ─────────────────────────────
function chargerSignalements(idCapteur) {
    const conteneur = document.getElementById('panneau-signalements');
    if (!conteneur) return;

    fetch('/actions/signalements-place.php?id_capteur=' + idCapteur)
        .then(r => r.json())
        .then(liste => {
            if (!liste.length) {
                conteneur.innerHTML = '<p style="color:var(--texte-secondaire);font-size:0.82rem">Aucun signalement.</p>';
                return;
            }
            conteneur.innerHTML = liste.map(s => `
                <div class="signalement-item ${s.statut_signalement === 'resolu' ? 'resolu' : ''}">
                    <div class="signalement-auteur">⚠️ ${s.auteur}</div>
                    <div class="signalement-msg">${s.description}</div>
                    <div class="signalement-date">${s.date_signalement}</div>
                    ${s.statut_signalement !== 'resolu'
                        ? `<button class="btn btn-success"
                                style="margin-top:0.4rem;padding:0.25rem 0.6rem;font-size:0.75rem;width:auto;"
                                onclick="resoudreSignalement(${s.id_signalement}, this)">
                                ✅ Marquer résolu
                           </button>`
                        : `<span style="color:var(--couleur-libre);font-size:0.75rem">✅ Résolu</span>`
                    }
                </div>
            `).join('');
        })
        .catch(() => {
            conteneur.innerHTML = '<p style="color:var(--couleur-occupee);font-size:0.82rem">Erreur de chargement.</p>';
        });
}

function resoudreSignalement(id, btn) {
    fetch('/actions/resoudre-signalement.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id_signalement: id })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const item = btn.closest('.signalement-item');
            item.classList.add('resolu');
            btn.outerHTML = '<span style="color:var(--couleur-libre);font-size:0.75rem">✅ Résolu</span>';
            afficherNotif('✅ Signalement résolu');
        } else {
            afficherNotif('❌ Erreur : ' + (res.erreur ?? 'inconnue'), true);
        }
    });
}


// ── LANCEMENT ────────────────────────────────────────────────
fetchEtat();
setInterval(fetchEtat, REFRESH_INTERVAL);