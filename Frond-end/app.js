// Front-end EcoRide (fr)
const API_BASE = window.__API_BASE__ || '/api';

(function(){
  const hero = document.getElementById('hero');
  const conteneur = document.createElement('div');
  conteneur.className = 'bg-slideshow';
  hero.prepend(conteneur);
  const urls = JSON.parse(hero.getAttribute('data-bg') || '[]');
  urls.forEach((u,i)=>{
    const d = document.createElement('div');
    d.className = 'bg-slide' + (i===0 ? ' active' : '');
    d.style.backgroundImage = `url("${u}")`;
    conteneur.appendChild(d);
  });
  const diapositives = [...conteneur.querySelectorAll('.bg-slide')];
  let index = 0;
  setInterval(()=>{
    if(!diapositives.length) return;
    diapositives[index].classList.remove('active');
    index = (index + 1) % diapositives.length;
    diapositives[index].classList.add('active');
  }, 5000);
})();

const listeCartes = document.getElementById('cartes');
const champs = { depart: document.getElementById('depart'), arrivee: document.getElementById('arrivee'), date: document.getElementById('date') };
const filtres = { eco: document.getElementById('filtreEco'), prix: document.getElementById('filtrePrix'), duree: document.getElementById('filtreDuree'), note: document.getElementById('filtreNote') };
let baseCourante = [];

function carteModele(r){
  return `
  <article class="card" data-id="${r.id}">
    <div class="card-header">
      <div class="driver">
        <div class="avatar" aria-hidden="true">${r.driver.avatar}</div>
        <div>
          <strong>${r.driver.pseudo}</strong>
          <div style="color:var(--ink-400); font-size:13px">⭐ ${r.driver.note.toFixed(1)} • ${r.vehicule.marque} ${r.vehicule.modele} (${r.vehicule.energie})</div>
        </div>
        <div class="spacer"></div>
        ${r.eco ? '<span class="pill">🌱 Éco</span>' : ''}
      </div>
    </div>
    <div class="card-body">
      <div class="meta">📍 ${r.from} → ${r.to}</div>
      <div class="meta">🗓️ ${new Date(r.date).toLocaleDateString('fr-FR',{weekday:'short', day:'2-digit', month:'short'})} • ⏱️ ${r.depart} → ${r.arrive} (${r.duree} min)</div>
      <div class="meta">🚗 Places restantes : <strong>${r.places}</strong></div>
      <div class="price">${r.prix} €</div>
    </div>
    <div class="card-footer">
      <button class="btn btn-ghost" data-action="details" data-id="${r.id}">Détail</button>
      <button class="btn btn-primary" data-action="participer" data-id="${r.id}" ${r.places<1? 'disabled aria-disabled="true"' : ''}>Participer</button>
    </div>
  </article>`;
}

function rendre(liste){
  const html = (liste.filter(r=>r.places>0).map(carteModele).join('')) ||
    `<p>Aucun covoiturage disponible.</p>`;
  listeCartes.innerHTML = html;
}

async function chargerTrajets(params = {}){
  const query = new URLSearchParams(params);
  const url = `${API_BASE}/covoiturages?${query.toString()}`;
  const res = await fetch(url);
  if(!res.ok) throw new Error('Erreur lors du chargement des trajets');
  const donnees = await res.json();
  baseCourante = donnees;
  appliquerFiltres();
}

document.getElementById('formulaireRecherche').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const depart = champs.depart.value.trim();
  const arrivee = champs.arrivee.value.trim();
  const date = champs.date.value;
  await chargerTrajets({ depart, arrivee, date });
});

['eco','prix','duree','note'].forEach(cle=>{
  filtres[cle].addEventListener('input', appliquerFiltres);
});

function appliquerFiltres(){
  const eco = filtres.eco.checked;
  const prix = parseFloat(filtres.prix.value || Infinity);
  const duree = parseFloat(filtres.duree.value || Infinity);
  const note = parseFloat(filtres.note.value || 0);
  const filtre = baseCourante.filter(r=>
    (!eco || r.eco) &&
    (r.prix <= prix) &&
    (r.duree <= duree) &&
    (r.driver.note >= note)
  );
  rendre(filtre);
}

listeCartes.addEventListener('click', async (e)=>{
  const btn = e.target.closest('[data-action]');
  if(!btn) return;
  const action = btn.dataset.action;
  const id = btn.dataset.id;
  if(action === 'details'){
    const res = await fetch(`${API_BASE}/covoiturages/${id}`);
    if(!res.ok) return alert('Trajet introuvable');
    const r = await res.json();
    const avis = r.avis || [];
    const prefs = r.preferences || [];
    const texte = `Trajet: ${r.from} → ${r.to}
Date: ${r.date} • ${r.depart} → ${r.arrive}
Véhicule: ${r.vehicule.marque} ${r.vehicule.modele} (${r.vehicule.energie})
Chauffeur: ${r.driver.pseudo} (⭐ ${r.driver.note})
Préférences: ${prefs.join(', ')}
Avis: ${avis.map(a=>`${a.auteur} ⭐${a.note} — ${a.texte}`).join(' | ')}`;
    alert(texte);
  }
  if(action === 'participer'){
    const res = await fetch(`${API_BASE}/covoiturages/${id}/participer`, { method:'POST' });
    if(!res.ok) return alert('Impossible de participer');
    const data = await res.json();
    alert(data.message || 'Participation confirmée !');
    await chargerTrajets({ depart: champs.depart.value.trim(), arrivee: champs.arrivee.value.trim(), date: champs.date.value });
  }
});

chargerTrajets().catch(()=>rendre([]));
