# SnowTricks
Snow Trick est un site collaboratif à destination des passionnés du snowboard. Son objectif faire connaître ce sport auprès du grand public et aider à l'apprentissage des figures (tricks).
# installer le rgestionnaire de dépendances Composer
php -r "eval('?>'.file_get_contents('http://getcomposer.org/installer'));"

# installer Git
https://gitforwindows.org/

# cloner le projet
```git clone  https://github.com/AmiarM/projet-6.git```

# installer les différentes dépendances du projet
```CD projet-6
composer install```

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
