# SnowTricks
Snow Trick est un site collaboratif à destination des passionnés du snowboard. Son objectif faire connaître ce sport auprès du grand public et aider à l'apprentissage des figures (tricks).
# installer le rgestionnaire de dépendances Composer
https://getcomposer.org/download/
# installer Git
https://getcomposer.org/download/
# installer la CLI de symfony
```https://symfony.com/download```
# cloner le projet
  - git clone  https://github.com/AmiarM/projet-6.git  ou  
  - télécharger l'archive
# installer les différentes dépendances du projet
```
CD projet-6
composer install
```
# configuration de l'application 
  modifier le ficher .env pour ajuster les valeurs:
  - **MAILER_DSN** pour le server de mail 
  - **DATABASE_URL** pour l'accès à la base de données 
# Création de la base de données 
```symfony console doctrine:database:create```

# création des tables dans la base de données 
```symfony console doctrine:migrations:migrate```

# charger les fixtures
```symfony console doctrine:fixtures:load```

# lancer le servar symfony
```symfony serve```

# acceder à  l'application
http://localhost:8000
