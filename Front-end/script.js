
// script.js : logique de base (niveau débutant) en français
const API_URL = "../backend/api";

// Carousel simple
function initCarousel(id){
  const root = document.getElementById(id);
  if(!root) return;
  const track = root.querySelector('.carousel-track');
  const items = root.querySelectorAll('.carousel-item');
  let index = 0;
  function update(){ track.style.transform = `translateX(-${index * 100}%)`; }
  root.querySelector('.prev').addEventListener('click', ()=>{ index = (index - 1 + items.length) % items.length; update(); });
  root.querySelector('.next').addEventListener('click', ()=>{ index = (index + 1) % items.length; update(); });
}

async function rechercher(event){
  event?.preventDefault();
  const depart = document.getElementById('depart').value.trim();
  const arrivee = document.getElementById('arrivee').value.trim();
  const date = document.getElementById('date').value;
  const url = `${API_URL}/search.php?depart=${encodeURIComponent(depart)}&arrivee=${encodeURIComponent(arrivee)}&date=${encodeURIComponent(date)}`;
  const res = await fetch(url);
  const data = await res.json();
  const zone = document.getElementById('resultats');
  zone.innerHTML = '';
  if(data.suggestion){
    const p = document.createElement('p');
    p.textContent = `Aucun trajet trouvé à cette date. Essayez le ${data.suggestion.date} (prochain trajet disponible).`;
    zone.appendChild(p);
  }
  (data.trajets || []).forEach(t => zone.appendChild(carteTrajet(t)));
}

function carteTrajet(t){
  const card = document.createElement('div');
  card.className = 'card';
  card.innerHTML = `
    <h3>${t.ville_depart} → ${t.ville_arrivee} <span class="badge">${t.ecologique ? 'ÉCO' : 'Classique'}</span></h3>
    <p>${t.date_depart} • ${t.heure_depart} → ${t.heure_arrivee}</p>
    <p>Chauffeur : ${t.chauffeur.pseudo} • Note : ${t.chauffeur.note}★</p>
    <p>Places restantes : ${t.places_restantes} • Prix : ${t.prix} crédits</p>
    <button class="btn" onclick="detailTrajet(${t.id})">Détail</button>
  `;
  return card;
}

async function detailTrajet(id){
  const res = await fetch(`${API_URL}/journeys.php?id=${id}`);
  const data = await res.json();
  const zone = document.getElementById('modal');
  zone.innerHTML = `
    <div class="card">
      <h3>Détail du trajet</h3>
      <p><b>${data.ville_depart} → ${data.ville_arrivee}</b> (${data.date_depart} • ${data.heure_depart} → ${data.heure_arrivee})</p>
      <p>Véhicule : ${data.vehicule.marque} ${data.vehicule.modele} (${data.vehicule.energie})</p>
      <p>Préférences du conducteur : ${data.preferences.join(', ') || 'Non précisées'}</p>
      <h4>Avis sur le conducteur</h4>
      <ul>${data.avis.map(a => `<li>${a.auteur} : ${a.note}★ – "${a.commentaire}"</li>`).join('')}</ul>
      <button class="btn" onclick="this.parentElement.remove()">Fermer</button>
    </div>
  `;
  zone.scrollIntoView({behavior:'smooth'});
}

// Charger témoignages
async function chargerTemoignages(){
  const res = await fetch(`${API_URL}/testimonials.php`);
  const data = await res.json();
  const zone = document.getElementById('temoignages');
  zone.innerHTML = '';
  data.forEach(t => {
    const card = document.createElement('div');
    card.className = 'card';
    card.innerHTML = `<p>"${t.message}"</p><p class="small">— ${t.auteur}, ${t.ville}</p>`;
    zone.appendChild(card);
  });
}

document.addEventListener('DOMContentLoaded', ()=>{
  initCarousel('carousel-itineraires');
  chargerTemoignages();
  const form = document.getElementById('form-recherche');
  form?.addEventListener('submit', rechercher);
});
