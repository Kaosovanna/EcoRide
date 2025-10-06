[README.md](https://github.com/user-attachments/files/22729058/README.md)

# EcoRide (démo simple)

Site vitrine + mini API pour un covoiturage **écologique et fun**. Code volontairement simple et commenté (style débutant).

## Arborescence

```
EcoRide-site/
  frontend/
    index.html
    covoiturages.html
    connexion.html
    mentions.html
    contact.html
    css/styles.css
    js/script.js
    assets/img/logo-ecoride.svg
  backend/
    api/
      config.php
      search.php
      journeys.php
      register.php
      testimonials.php
    schema.sql
    seed.sql
```

## Lancer en local

1. **Front-end** : ouvrez `frontend/index.html` dans votre navigateur.
2. **Back-end (PHP + MySQL)** :
   - Créez une base `ecoride` dans MySQL.
   - Importez `backend/schema.sql` puis `backend/seed.sql`.
   - Dans `backend/api/config.php`, mettez vos variables de connexion BD.
   - Lancez un serveur PHP dans `backend/` :

     ```bash
     php -S localhost:8000
     ```

   - Le front appelle l’API depuis `../backend/api/...`. Si vous servez le front via un serveur, ajustez `API_URL` dans `frontend/js/script.js`.

## Déploiement simple

- **Frontend sur GitHub Pages** :
  1. Créez un dépôt `ecoride` sur GitHub.
  2. Placez le contenu du dossier `frontend/` à la racine du dépôt ou dans un dossier `docs/`.
  3. Dans *Settings → Pages*, choisissez la branche `main` et le dossier racine (ou `docs`). Votre site sera accessible via GitHub Pages.

- **Backend (PHP) sur Render/Railway/Heroku-like)** :
  1. Poussez le dossier `backend/` dans un dépôt séparé (ex. `ecoride-api`).
  2. Connectez le dépôt à Render/Railway (service gratuit).
  3. Ajoutez les variables d’environnement : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
  4. Exécutez `schema.sql` puis `seed.sql` sur une base MySQL hébergée (Railway propose MySQL managé).

- **Note** : si vous préférez tout en un, servez `frontend/` via un petit serveur (ex: `php -S`) et ajustez les chemins.

## Git : organisation recommandée

- Branche `main` (production) et `develop` (intégration).
- Pour chaque fonctionnalité, créez une branche depuis `develop` (ex: `feature/recherche`), testez puis mergez vers `develop`. Ensuite, quand tout est stable, mergez `develop` → `main`.

## Fonctionnalités couvertes

- Page d’accueil avec présentation, images “nature”, logo, **carrousel** d’itinéraires.
- Barre de **recherche** (ville départ/arrivée + date) → consomme `api/search.php`. Suggestion de date si aucun résultat.
- **Détail** d’un trajet (véhicule, préférences, avis).
- **Témoignages** clients (API `testimonials.php`).
- **Inscription** basique avec 20 crédits initiaux (`register.php`).

## Sécurité (bases)

- Requêtes préparées PDO contre injection SQL.
- `password_hash()` pour les mots de passe.
- CORS ouvert pour la démo (à restreindre en production).
