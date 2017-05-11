# LEADS FACTORY


## Commandes utiles

Cette séction regroupe les commandes utilisables en console. Ce mémo va vous servir autant pour l'exploitation que pour la mise en oeuvre des services

###Installation des vendors :

`$ php composer.phar install`

###Mise à jour de composer :

`$ php composer.phar self-update`

###Mise à jour des vendors :

`$ php composer.phar update`


###Mise à jour du modèle de base de données :

`$ php app/console doctrine:schema:update --force`


###Générer des données de démo

`$ php app/console leadsfactory :demomode <formid>`


###Vider le cache Symfony

`$ php app/console cache :clear –env=prod`

###Mise à jour de l'historique de status

`php app/console leadsfactory:statusHistory:update`



