# projet-6

# Création de la base de données 
symfony console doctrine:database:create

# création des migrations
symfony console make:migration

# création des tables dans la base de données 
symfony console doctrine:migrations:migrate

# charger les fixtures
symfony console doctrine:fixtures:load
