# Symfony Project

## Introduction

Ceci est un projet Symfony qui utilise une base de données SQLite.

## Prérequis

- PHP >= 7.4
- Composer
- SQLite (si vous utilisez la base de données fournie)

## Installation

1. Clonez le dépôt :
   ```bash
   git clone git@github.com:melan60/EventsAppPHP.git
   cd EventsAppPHP
   ```
   
2. Création du conteneur Docker :
   ```bash
   docker compose up -d --build
   ```
   
3. Connexion au conteneur Docker
   ```bash
   docker exec -it projet_symfony_php bash
   cd project
   wget https://get.symfony.com/cli/installer -O - | bash
   export PATH="$HOME/.symfony5/bin:$PATH"
   ```
   
4. Installer les dépendances :
   ```bash
   composer install
   ```

5. Création de la base de données :
   ```bash
   chmod ugo=rwx /var/data.db
   bin/console doctrine:database:create
   bin/console doc:sc:up -f
   bin/console doctrine:fixtures:load
   ```

6. Lancement du serveur :
   ```bash
   symfony server:start 
   ```

## Utilisation

Accédez à l'application via http://localhost:8000.
