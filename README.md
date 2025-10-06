[README.md](https://github.com/user-attachments/files/22728641/README.md)
# EcoRide — Démo pédagogique (HTML/CSS/JS + PHP/MySQL)

Projet **simple** pour montrer une séparation front-end / back-end en français.  
Le but est d'illustrer les US principales du sujet (recherche, filtres, détail, inscription, participation).

## Structure
```
EcoRide/
├── frontend/            # HTML/CSS/JS statique
│   ├── index.html       # Accueil (histoire, carrousel, avis)
│   ├── covoiturages.html# Recherche + filtres
│   ├── detail.html      # Détail d'un trajet + avis
│   ├── connexion.html   # Connexion / Inscription
│   ├── contact.html     # Formulaire de contact (statique)
│   ├── mentions-legales.html
│   ├── styles.css
│   ├── script.js
│   ├── logo.svg         # Logo simple (SVG)
│   └── images/
└── backend/
    ├── config.php
    ├── utils.php
    ├── api/
    │   ├── search.php
    │   ├── trip.php
    │   ├── register.php
    │   ├── login.php
    │   ├── participate.php
    │   └── testimonials.php
    ├── db.sql
    └── seed.sql
```

## Lancer en local (exemple XAMPP/MAMP/WAMP)
1. Placez le dossier **EcoRide** dans le dossier du serveur PHP (par ex. `htdocs`).
2. Créez une base **MySQL** puis exécutez `backend/db.sql` puis `backend/seed.sql`.
3. Modifiez `backend/config.php` si besoin (identifiants MySQL).
4. Ouvrez `http://localhost/EcoRide/frontend/index.html` dans votre navigateur.

> Astuce : pour éviter les soucis CORS, vous pouvez aussi servir **frontend** via le serveur local (ex: `http://localhost/EcoRide/frontend/...`) et le back sera accessible via `../backend/...` comme dans le projet.

## Déploiement (piste simple)
- Front (statique) : GitHub Pages, Vercel (Static), Netlify, etc.
- Back (PHP/MySQL) : un hébergement mutualisé PHP ou un PaaS compatible PHP/MySQL.

## Notes pédagogiques
- À l'inscription, l'utilisateur reçoit **20 crédits**.  
- Un trajet est **écologique** si la voiture est **électrique**.  
- La recherche utilise la **ville** et la **date** avec filtres simples (prix, durée, note).  
- La participation vérifie places et crédits puis met à jour les compteurs.

## Crédits images
- Ajoutez vos propres images libres de droits dans `frontend/images/` (les fichiers actuels sont des placeholders).

## Sécurité (rappels)
- Requêtes préparées (PDO) : ok.
- Sessions : très minimal. À renforcer (CSRF, validation côté serveur, etc.).
- Mots de passe : `password_hash` / `password_verify`.

Bon code !
