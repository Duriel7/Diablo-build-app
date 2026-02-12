# Mise en place du conteneur Docker de travail

## Commandes à faire pour le conteneur

### Construire le conteneur

Pour construire le conteneur :
- `docker compose build`
Pour le lancer :
- `docker compose up -d`

### Installer composer pour les dépendances et gérer le fichier composer.json

Pour installer composer directement dans le conteneur :
- `docker compose exec web sh -lc "cd /api && composer init"`

Le cd /api désigne le répertoire de travail, ou working directory, pour notre projet. Commande équivalente à `docker compose exec web composer init` en plus sécurisé car on force explicitement le working directory pour être sûr de l'endroit d'exécution. Aussi équivalent à `docker exec -it <nom conteneur> bash` + `(cd /api) composer init` dans le conteneur, qui est la version interactive.

Pour créer le fichier d'autoload :
- `docker compose exec web sh -lc "cd /api && composer dump-autoload -o"`

Commande à relancer à chaque ajout dans l'arborescence, car on doit mettre à jour l'autoload non dynamique (option "-o", voir plus bas).

Pour vérification :
- `docker compose exec web sh -lc "ls -la /api/vendor && ls -la /api/vendor/autoload.php"`
Cette commande permet de voir que les fichiers existent bel et bien côté conteneur autant que côté hôte.

#### Inclure l'autoload :

Rajouter la ligne dans le fichier point d'entrée :
- `require __DIR__ . '/vendor/autoload.php';`

Si le fichier point d'entrée est au même niveau que le dossier vendor.

### Installations des dépendances :

Pour dotenv pour PHP :
- `docker compose exec web sh -lc "cd /api && composer require vlucas/phpdotenv"`
Pour Twig :
- `docker compose exec web sh -lc "cd /api && composer require twig/twig"`

Bonne pratique, permet d'utiliser les variables d'environnement de manière sécurisée, en PHP brut, plutôt que de le faire directement sans bibliothèque.

### Explications des options

- `sh` => lance le shell
- `-l` => login shell, initialise l'environnement du shell
- `-c` => permet l'exécution des commandes fournies entre guillemets
- `-o` => autoload optimisé, recommandé dans le cadre d'une API stable