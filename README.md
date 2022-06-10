# SnowTricks
Snow Trick est un site collaboratif à destination des passionnés du snowboard. Son objectif faire connaître ce sport auprès du grand public et aider à l'apprentissage des figures (tricks).
# installer le gestionnaire de dependance Composer
php -r "eval('?>'.file_get_contents('http://getcomposer.org/installer'));"

# installer les différentes dependaces du projet
composer install

# Création de la base de données 
symfony console doctrine:database:create

# création des migrations
symfony console make:migration

# création des tables dans la base de données 
symfony console doctrine:migrations:migrate

# charger les fixtures
symfony console doctrine:fixtures:load

# lancer le servar symfony
symfony serve

# acceder à  l'application
localhost:8000
