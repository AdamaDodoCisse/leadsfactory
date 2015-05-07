# LEAD'S FACTORY

[toc]

## API Taches planifiées

L'api de taches planifiées de la lead's factory permet la programmation et le monitoring de commandes symfony.

### Les commandes Symfony

Symfony 2 permet la création de commandes dans sa console. Ses commandes utilisent le framework et le container Symfony comme environnement d'execution.

Dans la lead's factory, une commande se trouve par convention dans un dossier **Command**.

C'est une classe qui etend généralement une des classes suivantes :

`ContainerAwareCommand (Symfony\Bundle\FrameworkBundle\Command)`
`Command (Symfony\Component\Console\Command)`

Vous pouvez lister les commandes existantes en utilisant en ligne de commande :

`php app/console`


### Rendre un commande compatible

Toutes les commandes Symfony 2 sont compatibles avec le planificateur de taches de la lead's factory. Seul le chargement automatique dans la lead's factory demande la configuration d'un service supplémentaire (voir chapitre : Pré-configurer une tache)

### Pré-configurer une tache dans l'admin

Il suffit de créer un service implémentant l'interface __IScheduledJob (Tellaw\LeadsFactoryBundle\Utils)__ taggé avec le tag __scheduled.job__

L'execution du cronjob de la lead's factory __CronRunnerCommand__ va automatiquement initialiser ce nouveau Job.

__Exemple :__

Pour initialiser automatiquement une tâche planifiée avec monitoring sur le cronjob __StatusHistoryUpdateCommand__, le service suivant doit etre déclaré.

<em>Le service d'initialisation :</em>

	<?php
	namespace Tellaw\LeadsFactoryBundle\Utils\ScheduledJobs;

	use Symfony\Bundle\FrameworkBundle\Routing\Router;
	use Tellaw\LeadsFactoryBundle\Utils\IScheduledJob;

	class StatusHistoryScheduler implements IScheduledJob
	{

	    public function getExpression()
	    {
	        return "1 * * * * *";
	    }

	    public function getName()
	    {
	        return "Core_AlertStatusHistory_Job";
	    }

	    public function getCommands()
	    {
	        return array ('leadsfactory:statusHistory:update');
	    }

	    public function getEnabled()
	    {
	        return true;
	    }

	}

**La configuration du service (services.yml) :**

	leadsfactory.scheduled_status_history:
		class: Tellaw\LeadsFactoryBundle\Utils\ScheduledJobs\StatusHistoryScheduler
		tags:
			- { name: scheduled.job }


Après la première initilisation, les valeurs sont modifiables dans l'administration de la lead's. Pour éviter l'écrasement de paramétrage, seule la 'commande' sera mise à jour si sa valeur change dans le service d'initialisation.

### Les Crons & la planification

Le planificateur utilise un format cron expression pour parametrer l'interval d'execution de la tâche.

Certains intervals sont pré-configuré dans la lead's :
<pre>
@yearly, @annually) - Run once a year, midnight, Jan. 1 - 0 0 1 1 *
@monthly - Run once a month, midnight, first of month - 0 0 1 * *
@weekly - Run once a week, midnight on Sun - 0 0 * * 0
@daily - Run once a day, midnight - 0 0 * * *
@hourly - Run once an hour, first minute - 0 * * * *
</pre>

Syntaxe d'une cron expression :

Chaque entrée de la table (chaque ligne) correspond à une tâche à exécuter et doit respecter cette notation :
<pre>
mm hh jj MMM JJJ tâche
mm représente les minutes (de 0 à 59)
hh représente l'heure (de 0 à 23)
jj représente le numéro du jour du mois (de 1 à 31)
MMM représente l'abréviation du nom du mois (jan, feb, ...) ou bien le numéro du mois (de 1 à 12)
JJJ représente l'abréviation du nom du jour ou bien le numéro du jour dans la semaine :
0 = Dimanche
1 = Lundi
2 = Mardi
...
6 = Samedi
7 = Dimanche (représenté deux fois pour les deux types de semaine)
</pre>

Pour chaque valeur numérique (mm, hh, jj, MMM, JJJ) les notations possibles sont :

<pre>
* : à chaque unité (0, 1, 2, 3, 4...)
5,8 : les unités 5 et 8
2-5 : les unités de 2 à 5 (2, 3, 4, 5)
*/3 : toutes les 3 unités (0, 3, 6, 9...)
10-20/3 : toutes les 3 unités, entre la dixième et la vingtième (10, 13, 16, 19)
</pre>

<em>Extrait de Wikipedia</em>

### Les logs des commandes

Les logs des commandes sont visibles dans l'interface d'administration.





