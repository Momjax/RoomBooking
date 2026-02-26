# Guide de Déploiement en Production - RoomBooking

Ce guide détaille les étapes pour déployer l'application RoomBooking sur la machine virtuelle (VM) Linux de l'école MediaSchool IRIS Nice (Jalons 18 à 20).

## Prérequis sur le serveur (VM Linux)

Avant de commencer, assurez-vous que le serveur dispose des éléments suivants installés :
- Serveur Web (Apache ou Nginx)
- PHP 8.x (avec les extensions : intl, pdo_mysql, zip, opcache, mbstring, dom, curl)
- MariaDB / MySQL
- Composer
- Git

## Étape 1 : Récupération du projet

Connectez-vous à la VM Linux en SSH, puis placez-vous dans le dossier `/var/www/` (ou le dossier racine défini pour vos projets web) :

```bash
cd /var/www/
# Clonez le dépôt git de votre projet (remplacez l'URL par la vôtre)
git clone https://github.com/votre-nom/RoomBooking.git
cd RoomBooking
```

## Étape 2 : Configuration de l'environnement

1. Créez un fichier d'environnement local destiné à la production :
```bash
cp .env .env.local
```

2. Éditez le fichier `.env.local` (`nano .env.local`) et modifiez ces variables essentielles :
```ini
APP_ENV=prod
APP_SECRET=Votre_Super_Cle_Secrete_Generee_123!
# URL de connexion à la base de données (modifiez avec vos identifiants SQL)
DATABASE_URL="mysql://utilisateur_mysql:mot_de_passe_mysql@127.0.0.1:3306/room_booking?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

## Étape 3 : Installation des dépendances

Installez uniquement les librairies nécessaires à la production (sans PHPUnit, MakerBundle, etc.) :
```bash
composer install --no-dev --optimize-autoloader
```

## Étape 4 : Base de données et Fixtures

Si ce n'est pas déjà fait, créez la base de données et exécutez les migrations, puis chargez le jeu d'essai initial (utilisateurs, salles) :

```bash
# 1. Création de la base si elle n'existe pas
php bin/console doctrine:database:create --env=prod

# 2. Exécution des migrations (création des tables)
php bin/console doctrine:migrations:migrate -n --env=prod

# 3. (Optionnel) Ajout du jeu de données initial (Admin, Salles de cours, etc.)
php bin/console doctrine:fixtures:load -n --env=prod
```

## Étape 5 : Optimisation de Symfony

Nettoyez et préchauffez le cache pour que l'application soit très rapide en production :
```bash
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
```

## Étape 6 : Droits et Permissions sur les dossiers

Pour qu'Apache/Nginx puisse écrire dans les logs et le cache, ajustez les permissions. (Exemple pour `www-data`, l'utilisateur par défaut d'Apache/Nginx sur Debian/Ubuntu) :

```bash
# Donne la propriété des fichiers au process du serveur web
sudo chown -R www-data:www-data var/
sudo chown -R www-data:www-data public/

# Assure que le dossier var/ est inscriptible
sudo chmod -R 775 var/
```

## Étape 7 : Configuration du Serveur Web (Exemple Apache)

Créez un VirtualHost Apache pour le projet (ex: `/etc/apache2/sites-available/roombooking.conf`) :

```apache
<VirtualHost *:80>
    ServerName roombooking.mediaschool.local
    ServerAlias www.roombooking.mediaschool.local

    DocumentRoot /var/www/RoomBooking/public
    DirectoryIndex /index.php

    <Directory /var/www/RoomBooking/public>
        AllowOverride None
        Order Allow,Deny
        Allow from All

        FallbackResource /index.php
    </Directory>

    ErrorLog /var/log/apache2/roombooking_error.log
    CustomLog /var/log/apache2/roombooking_access.log combined
</VirtualHost>
```

Activez-le et redémarrez Apache :
```bash
sudo a2ensite roombooking.conf
sudo systemctl reload apache2
```

## Étape 8 : Sécurité (HTTPS / SSL)
Conformément au "Contraintes techniques" du cahier des charges, utilisez **Certbot** (Let's Encrypt) pour passer votre site en HTTPS si vous avez un nom de domaine valide, ou générez un certificat auto-signé pour le réseau local.

---
**Félicitations**, l'application est maintenant en ligne et opérationnelle pour le Jalon 20 !
