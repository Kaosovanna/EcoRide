// script.js - logique front simple en français

// Carrousel d'exemples (page d'accueil)
(function(){
  const track = document.getElementById('carousel-track');
  if(!track) return;
  let index = 0;
  const next = document.getElementById('next');
  const prev = document.getElementById('prev');
  function update(){ track.style.transform = 'translateX(' + (-index*100) + '%)'; }
  next.onclick = ()=>{ index = (index+1)%track.children.length; update(); };
  prev.onclick = ()=>{ index = (index-1+track.children.length)%track.children.length; update(); };
})();

// Redirection rapide depuis l'accueil vers la page de recherche
(function(){
  const btn = document.getElementById('home-search');
  if(!btn) return;
  btn.addEventListener('click', () => {
    const d = document.getElementById('home-depart').value;
    const a = document.getElementById('home-arrivee').value;
    const dt = document.getElementById('home-date').value;
    window.location.href = `covoiturages.html?depart=${encodeURIComponent(d)}&arrivee=${encodeURIComponent(a)}&date=${encodeURIComponent(dt)}`;
  });
})();

// Page covoiturages : recherche + filtres
(function(){
  const b = document.getElementById('btn-search');
  if(!b) return;
  const res = document.getElementById('resultats');
  const msg = document.getElementById('message');

  async function chercher(){
    msg.textContent = "Recherche en cours...";
    const depart = document.getElementById('depart').value;
    const arrivee = document.getElementById('arrivee').value;
    const date = document.getElementById('date').value;
    const eco = document.getElementById('filtre-eco').checked ? 1 : 0;
    const prix = document.getElementById('filtre-prix').value;
    const duree = document.getElementById('filtre-duree').value;
    const note = document.getElementById('filtre-note').value;

    const params = new URLSearchParams({depart, arrivee, date, eco, prix_max: prix, duree_max: duree, note_min: note});
    try{
      const r = await fetch('../backend/api/search.php?' + params.toString());
      const data = await r.json();
      res.innerHTML = '';
      msg.textContent = '';
      if(data.suggestion){
        msg.textContent = 'Aucun trajet à cette date. Prochaine date disponible : ' + data.suggestion;
      }
      (data.trajets||[]).forEach(t => {
        const card = document.createElement('div');
        card.className = 'card';
        card.innerHTML = `
          <h3>${t.ville_depart} → ${t.ville_arrivee}</h3>
          <p><strong>Départ</strong> : ${t.date_depart} — <strong>Arrivée</strong> : ${t.date_arrivee}</p>
          <p>Chauffeur : ${t.chauffeur_pseudo} — Note : ${t.note_moyenne ?? 'N/A'}</p>
          <p>Prix : ${t.prix} € • Places restantes : ${t.places_restantes} ${t.eco ? '<span class="badge eco">Éco</span>':''}</p>
          <a class="btn" href="detail.html?id=${t.id}">Détail</a>
        `;
        res.appendChild(card);
      });
      if((data.trajets||[]).length===0 && !data.suggestion){
        msg.textContent = 'Aucun trajet trouvé.';
      }
    }catch(e){
      console.error(e);
      msg.textContent = 'Erreur lors de la recherche.';
    }
  }

  b.addEventListener('click', chercher);

  // Remplir les champs à partir de l'URL si présent
  const u = new URLSearchParams(location.search);
  if(u.get('depart')) document.getElementById('depart').value = u.get('depart');
  if(u.get('arrivee')) document.getElementById('arrivee').value = u.get('arrivee');
  if(u.get('date')) document.getElementById('date').value = u.get('date');
  if(u.get('depart') || u.get('arrivee') || u.get('date')) chercher();
})();

// Page détail : afficher un trajet + avis
(function(){
  const cont = document.getElementById('detail-contenu');
  if(!cont) return;
  const params = new URLSearchParams(location.search);
  const id = params.get('id');

  async function charger(){
    cont.textContent = "Chargement...";
    const r = await fetch('../backend/api/trip.php?id=' + encodeURIComponent(id));
    const data = await r.json();
    if(!data.ok){ cont.textContent = "Trajet introuvable."; return; }
    const t = data.trajet;
    cont.innerHTML = `
      <p><strong>${t.ville_depart} → ${t.ville_arrivee}</strong></p>
      <p>Départ : ${t.date_depart} — Arrivée : ${t.date_arrivee}</p>
      <p>Chauffeur : ${t.chauffeur_pseudo} • Note : ${t.note_moyenne ?? 'N/A'}</p>
      <p>Véhicule : ${t.marque} ${t.modele} (${t.energie})</p>
      <p>Préférences conducteur : ${t.preferences || 'Non renseigné'}</p>
      <p>Prix : ${t.prix} € • Places restantes : ${t.places_restantes} ${t.eco ? '<span class="badge eco">Éco</span>':''}</p>
    `;

    // avis
    const avisWrap = document.getElementById('avis-conducteur');
    (data.avis || []).forEach(a => {
      const d = document.createElement('div');
      d.className = 'card';
      d.innerHTML = `<p><strong>${a.auteur}</strong> — Note : ${a.note}/5</p><p>${a.commentaire}</p>`;
      avisWrap.appendChild(d);
    });
  }

  charger();

  // Participer (naïf)
  const btn = document.getElementById('btn-participer');
  const msg = document.getElementById('participation-msg');
  btn.addEventListener('click', async ()=>{
    msg.textContent = 'Vérification...';
    const r = await fetch('../backend/api/participate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ trip_id: id, places: 1 })
    });
    const data = await r.json();
    msg.textContent = data.message || 'Opération effectuée';
  });
})();

// Connexion / Inscription
(function(){
  const btnLogin = document.getElementById('btn-login');
  const btnRegister = document.getElementById('btn-register');
  if(btnLogin){
    btnLogin.addEventListener('click', async ()=>{
      const email = document.getElementById('login-email').value;
      const password = document.getElementById('login-password').value;
      const r = await fetch('../backend/api/login.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ email, password })
      });
      const data = await r.json();
      document.getElementById('login-msg').textContent = data.message || 'Ok';
    });
  }
  if(btnRegister){
    btnRegister.addEventListener('click', async ()=>{
      const pseudo = document.getElementById('register-pseudo').value;
      const email = document.getElementById('register-email').value;
      const password = document.getElementById('register-password').value;
      const r = await fetch('../backend/api/register.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ pseudo, email, password })
      });
      const data = await r.json();
      document.getElementById('register-msg').textContent = data.message || 'Compte créé.';
    });
  }
})();

// Page d'accueil : avis clients (simples + API)
(async function(){
  const avisDiv = document.getElementById('avis');
  if(!avisDiv) return;
  try{
    const r = await fetch('../backend/api/testimonials.php');
    const data = await r.json();
    (data.avis || [
      {auteur:'Léna', note:5, commentaire:'Super trajet, conducteur sympa !'},
      {auteur:'Marc', note:4, commentaire:'Ponctuel et voiture très propre.'},
      {auteur:'Sarah', note:5, commentaire:'J’adore l’option 100% électrique.'}
    ]).forEach(a => {
      const c = document.createElement('div');
      c.className = 'card';
      c.innerHTML = `<p><strong>${a.auteur}</strong> — ${'★'.repeat(a.note)}${'☆'.repeat(5-a.note)}</p><p>${a.commentaire}</p>`;
      avisDiv.appendChild(c);
    });
  }catch(e){ /* silencieux */ }
})();
