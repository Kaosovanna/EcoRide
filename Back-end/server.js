// -----------------------------------------------
//  EcoRide — API Express en français
// -----------------------------------------------
import express from 'express';
import cors from 'cors';

const app = express();
const PORT = process.env.PORT || 3000;

// 🔓 Autoriser le front (GitHub Pages / localhost)
app.use(cors({ origin: true }));
app.use(express.json());

// 🗃️ Données de démonstration (remplaçables par une BD)
let trajets = [
  { id:1, departVille:'Paris', arriveeVille:'Lyon', date:'2025-09-25', heureDepart:'08:00', heureArrivee:'12:30',
    chauffeur:{ pseudo:'Nina', note:4.8, avatar:'N' }, places:2, prix:28, duree:270, eco:true,
    vehicule:{marque:'Renault', modele:'Mégane e-Tech', energie:'Électrique'} },
  { id:2, departVille:'Paris', arriveeVille:'Lille', date:'2025-09-25', heureDepart:'09:10', heureArrivee:'11:00',
    chauffeur:{ pseudo:'Marc', note:4.2, avatar:'M' }, places:0, prix:14, duree:110, eco:false,
    vehicule:{marque:'Peugeot', modele:'308', energie:'Essence'} },
  { id:3, departVille:'Bordeaux', arriveeVille:'Toulouse', date:'2025-09-26', heureDepart:'07:45', heureArrivee:'10:05',
    chauffeur:{ pseudo:'Zoé', note:5.0, avatar:'Z' }, places:3, prix:16, duree:140, eco:true,
    vehicule:{marque:'Tesla', modele:'Model 3', energie:'Électrique'} },
  { id:4, departVille:'Paris', arriveeVille:'Lyon', date:'2025-09-25', heureDepart:'13:00', heureArrivee:'17:00',
    chauffeur:{ pseudo:'Ali', note:4.6, avatar:'A' }, places:1, prix:25, duree:240, eco:false,
    vehicule:{marque:'Citroën', modele:'C4', energie:'Diesel'} }
];

// ✅ Endpoint: liste/filtre de trajets
app.get('/api/covoiturages', (req, res) => {
  const { depart = '', arrivee = '', date = '' } = req.query;
  const d = depart.toString().toLowerCase();
  const a = arrivee.toString().toLowerCase();
  const filtered = trajets.filter(t =>
    t.departVille.toLowerCase().includes(d) &&
    t.arriveeVille.toLowerCase().includes(a) &&
    (date ? t.date === date : true)
  );

  // Si aucun résultat exact par date, proposer la date la plus proche sur le même trajet
  let resultats = filtered;
  if(resultats.length === 0 && d && a){
    const memeTrajet = trajets.filter(t => t.departVille.toLowerCase().includes(d) && t.arriveeVille.toLowerCase().includes(a));
    if(memeTrajet.length){
      const cible = new Date(date);
      const tri = [...memeTrajet].sort((x,y)=> Math.abs(new Date(x.date)-cible) - Math.abs(new Date(y.date)-cible));
      const dateProche = tri[0].date;
      resultats = memeTrajet.filter(t=>t.date===dateProche);
      return res.json({ info:`Aucun trajet ce jour. Prochaine disponibilité le ${new Date(dateProche).toLocaleDateString('fr-FR')}.`, trajets: resultats });
    }
  }
  res.json({ trajets: resultats });
});

// ✅ Endpoint: participer (décrémenter places) — démo
app.post('/api/participer/:id', (req,res)=>{
  const id = Number(req.params.id);
  const idx = trajets.findIndex(t=>t.id===id);
  if(idx<0) return res.status(404).json({ message:'Trajet introuvable' });
  if(trajets[idx].places < 1) return res.status(400).json({ message:'Plus de place disponible' });
  trajets[idx].places -= 1;
  res.json({ message:'Participation confirmée', trajet: trajets[idx]});
});

app.get('/', (req,res)=>{
  res.type('text').send('EcoRide API en ligne ✅');
});

app.listen(PORT, ()=>{
  console.log(`✅ API EcoRide démarrée sur http://localhost:${PORT}`);
});
