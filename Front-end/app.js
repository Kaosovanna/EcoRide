/* -----------------------------------------------------------
   Fichier : app.js (FR)
   Rôle   : logique front-end + appels API vers le back-end
   ----------------------------------------------------------- */

// ⚙️ À ADAPTER APRÈS DÉPLOIEMENT : URL publique de votre API
const API_URL = window.API_URL || "http://localhost:3000"; // ex: https://votre-backend.onrender.com

/* ---------- Diaporama d’arrière-plan (section héro) ---------- */
(function(){
  const hero = document.getElementById('hero');
  if(!hero) return;
  const holder = hero.querySelector('.bg-slideshow');
  let urls = [];
  try { urls = JSON.parse(hero.getAttribute('data-bg') || '[]'); } catch(e){ urls = []; }
  if(!urls.length || !holder) return;
  urls.forEach((u,i)=>{
    const d = document.createElement('div');
    d.className = 'bg-slide' + (i===0 ? ' active' : '');
    d.style.backgroundImage = `url("${u}")`;
    holder.appendChild(d);
  });
  const slides = [...holder.querySelectorAll('.bg-slide')];
  let idx = 0;
  setInterval(()=>{
    slides[idx].classList.remove('active');
    idx = (idx + 1) % slides.length;
    slides[idx].classList.add('active');
  }, 5000);
})();

/* --------------------------- Données & UI --------------------------- */
const conteneurCartes = document.getElementById('cartes');
let baseCourante = [];

function carteModele(trajet){
  return `
  <article class="card" data-id="${trajet.id}">
    <div class="card-header">
      <div class="driver">
        <div class="avatar" aria-hidden="true">${trajet.chauffeur.avatar}</div>
        <div>
          <strong>${trajet.chauffeur.pseudo}</strong>
          <div style="color:var(--ink-400); font-size:13px">⭐ ${trajet.chauffeur.note.toFixed(1)} • ${trajet.vehicule.marque} ${trajet.vehicule.modele} (${trajet.vehicule.energie})</div>
        </div>
        <div class="spacer"></div>
        ${trajet.eco ? '<span class="pill">🌱 Éco</span>' : ''}
      </div>
    </div>
    <div class="card-body">
      <div class="meta">📍 ${trajet.departVille} → ${trajet.arriveeVille}</div>
      <div class="meta">🗓️ ${new Date(trajet.date).toLocaleDateString('fr-FR',{weekday:'short', day:'2-digit', month:'short'})} • ⏱️ ${trajet.heureDepart} → ${trajet.heureArrivee} (${trajet.duree} min)</div>
      <div class="meta">🚗 Places restantes : <strong>${trajet.places}</strong></div>
      <div class="price">${trajet.prix} €</div>
    </div>
    <div class="card-footer">
      <button class="btn btn-ghost" onclick="details(${trajet.id})">Détail</button>
      <button class="btn btn-primary" ${trajet.places<1? 'disabled aria-disabled="true"' : ''} onclick="participer(${trajet.id})">Participer</button>
    </div>
  </article>`;
}

function rendre(liste){
  const html = (liste.filter(t=>t.places>0).map(carteModele).join('')) ||
    `<p>Aucun covoiturage disponible. Essayez d'ajuster votre date (nous proposons le plus proche disponible si besoin).</p>`;
  conteneurCartes.innerHTML = html;
}

/* --------------------------- Recherche --------------------------- */
const form = document.getElementById('formRecherche');
form.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const depart = e.target.depart.value.trim();
  const arrivee = e.target.arrivee.value.trim();
  const date = e.target.date.value;

  try {
    const url = new URL('/api/covoiturages', API_URL);
    url.searchParams.set('depart', depart);
    url.searchParams.set('arrivee', arrivee);
    url.searchParams.set('date', date);
    const res = await fetch(url, { headers: { 'Accept':'application/json' }});
    const data = await res.json();
    baseCourante = data.trajets || [];
    appliquerFiltres(baseCourante);
  } catch (err) {
    console.error('Erreur API', err);
    alert('Impossible de contacter le serveur. Vérifiez API_URL ou le back-end.');
  }
});

/* --------------------------- Filtres --------------------------- */
['fEco','fPrix','fDuree','fNote'].forEach(id=>{
  document.getElementById(id).addEventListener('input', ()=> appliquerFiltres(baseCourante));
});

function appliquerFiltres(base){
  baseCourante = base || [];
  const eco = document.getElementById('fEco').checked;
  const prix = parseFloat(document.getElementById('fPrix').value || Infinity);
  const duree = parseFloat(document.getElementById('fDuree').value || Infinity);
  const note = parseFloat(document.getElementById('fNote').value || 0);

  const filtres = baseCourante.filter(t=>
    (!eco || t.eco) &&
    (t.prix <= prix) &&
    (t.duree <= duree) &&
    (t.chauffeur.note >= note)
  );
  rendre(filtres);
}

/* --------------------------- Détails & Participation (démo) --------------------------- */
window.details = function(id){
  const t = baseCourante.find(x=>x.id===id);
  if(!t) return;
  const avis = [
    {auteur:'Élise', note:5, texte:'Trajet impeccable, conducteur ponctuel.'},
    {auteur:'Samir', note:4.5, texte:'Confortable et charge rapide en route.'}
  ];
  const prefs = ['Non-fumeur','Animaux acceptés','Musique douce'];
  const message = `Trajet: ${t.departVille} → ${t.arriveeVille}\nDate: ${t.date} • ${t.heureDepart} → ${t.heureArrivee}\nVéhicule: ${t.vehicule.marque} ${t.vehicule.modele} (${t.vehicule.energie})\nChauffeur: ${t.chauffeur.pseudo} (⭐ ${t.chauffeur.note})\nPréférences: ${prefs.join(', ')}\nAvis: ${avis.map(a=>`${a.auteur} ⭐${a.note} — ${a.texte}`).join(' | ')}`;
  alert(message);
}

window.participer = async function(id){
  const t = baseCourante.find(x=>x.id===id);
  if(!t) return;
  if(t.places<1){ alert('Plus de place disponible.'); return; }
  const ok1 = confirm(`Participer à ce trajet (${t.departVille} → ${t.arriveeVille}) pour ${t.prix}€ ?`);
  if(!ok1) return;
  const ok2 = confirm('Confirmez l\'utilisation de vos crédits.');
  if(!ok2) return;
  try {
    const res = await fetch(`${API_URL}/api/participer/${id}`, { method:'POST' });
    if(!res.ok) throw new Error('Réponse non OK');
    alert('Participation confirmée ! Vos crédits seront mis à jour.');
    // rafraîchir la recherche courante
    appliquerFiltres(baseCourante.map(x=> x.id===id ? {...x, places: Math.max(0, x.places-1)} : x));
  } catch(err){
    alert('Échec de la participation (démo).');
  }
}
