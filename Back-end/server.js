// Serveur Node.js (Express) — API EcoRide (fr)
/* eslint-disable no-console */
import express from 'express';
import cors from 'cors';
import path from 'path';
import fs from 'fs';

const app = express();
app.use(cors());
app.use(express.json());

// Chargement des données de démonstration
const cheminDonnees = path.resolve(process.cwd(), 'backend', 'covoiturages.json');
let covoiturages = JSON.parse(fs.readFileSync(cheminDonnees, 'utf-8'));

// Santé
app.get('/api/sante', (_req, res)=> res.json({ statut:'ok' }));

// Liste des covoiturages (avec filtres simples via query)
app.get('/api/covoiturages', (req, res)=>{
  const { depart = '', arrivee = '', date = '' } = req.query;
  let liste = [...covoiturages];
  if(depart)  liste = liste.filter(r => r.from.toLowerCase().includes(String(depart).toLowerCase()));
  if(arrivee) liste = liste.filter(r => r.to.toLowerCase().includes(String(arrivee).toLowerCase()));
  if(date)    liste = liste.filter(r => r.date === date);
  res.json(liste.filter(r => r.places > 0));
});

// Détail d'un covoiturage
app.get('/api/covoiturages/:id', (req, res)=>{
  const id = Number(req.params.id);
  const r = covoiturages.find(x=>x.id===id);
  if(!r) return res.status(404).json({ message:'Trajet introuvable' });
  const avis = [
    {auteur:'Élise', note:5, texte:'Trajet impeccable, conducteur ponctuel.'},
    {auteur:'Samir', note:4.5, texte:'Confortable et charge rapide en route.'}
  ];
  const preferences = ['Non-fumeur','Animaux acceptés','Musique douce'];
  res.json({ ...r, avis, preferences });
});

// Participer à un covoiturage (décrémente une place)
app.post('/api/covoiturages/:id/participer', (req, res)=>{
  const id = Number(req.params.id);
  const idx = covoiturages.findIndex(x=>x.id===id);
  if(idx === -1) return res.status(404).json({ message:'Trajet introuvable' });
  if(covoiturages[idx].places < 1) return res.status(400).json({ message:'Plus de place disponible' });
  covoiturages[idx].places -= 1;
  fs.writeFileSync(cheminDonnees, JSON.stringify(covoiturages, null, 2), 'utf-8');
  res.json({ message:'Participation confirmée ! Vos crédits seront mis à jour.' });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, ()=> console.log(`✅ API EcoRide démarrée sur http://localhost:${PORT}`));
