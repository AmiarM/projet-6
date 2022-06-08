# SnowTricks
Snow Trick est un site collaboratif à destination des passionnés du snowboard. Son objectif faire connaître ce sport auprès du grand public et aider à l'apprentissage des figures (tricks).

# Création de la base de données 
symfony console doctrine:database:create

# création des migrations
symfony console make:migration

# création des tables dans la base de données 
symfony console doctrine:migrations:migrate

# charger les fixtures
symfony console doctrine:fixtures:load
