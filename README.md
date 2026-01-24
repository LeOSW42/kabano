[Kabano website](https://kabano.org/)

## Arborescence

- `public/` : assets frontend et point d'entrée web (`index.php`, `views/`, `_ressources/`)
- `src/` : code backend commun
  - `Core/` : configuration et utilitaires communs (ancien `includes/`)
  - `Controllers/` : contrôleurs MVC backend (ancien `controllers/`)
  - `Models/` : modèles MVC backend (ancien `models/`)
  - `Thirds/` : dépendances tierces backend (ancien `third/`)

Note: configure the web server at the repository root; requests to `/` are routed to `public/` via `.htaccess`, and URLs are generated from the app root.
