# LEAD'S FACTORY
================

# Poser les tags sur votre site
===============================

Cette section à pour objectif de documenter l'utilisation de tags dans vos sites internet

## Le tag de base


	<html>
	<head>

	    <script src="/weka-leadsfactory/web/bundles/tellawleadsfactory/js/libs/jquery-1.11.3.min.js"></script>

	</head>
	<body>

	<div id="formulaireleads" class="loading-leads">
	    <p class="loading-leads-txt">Merci de patienter ...</p>
	</div>
	<script type="text/javascript">
	    baseUrl = '[[INSEREZ_L_URL_DE_LA_LEADS]]'; // Se termine par un slash
	    codeAction = '[[INSEREZ_VOTRE_CODE_ACTION]]';
	    codeFormulaire = '[[INSEREZ_VOTRE_CODE_FORMULAIRE]]';
	    params = [];
	    jQuery.when(
	        jQuery.getScript(baseUrl+'web/bundles/tellawleadsfactory/js/lf.js'),
	        jQuery.getScript(baseUrl+'web/bundles/tellawleadsfactory/js/libs/formValidator/jquery.validationEngine.js'),
	        jQuery.getScript(baseUrl+'web/bundles/tellawleadsfactory/js/libs/formValidator/languages/jquery.validationEngine-fr.js'),
	        jQuery.getScript(baseUrl+'web/app_dev.php/client/form/twig/'+ codeFormulaire),
	        jQuery.getScript(baseUrl+'web/bundles/tellawleadsfactory/js/libs/phoneformat.js'),
	        jQuery(document).data("readyDeferred")
	    ).done(function() {
	        leadsfactory.setCodeAction (codeAction);
	        jQuery('#formulaireleads').html(leadsfactory.html);
	        jQuery('#formulaireleads').removeClass('loading-leads');
	        var params = {};
	        var options = {};
	        leadsfactory.init(codeFormulaire, params, options);
	    });
	</script>

	</script>
	</body>
	</html>Ce tag utilise trois variables pour fonctionner :

**URL\_LEADS\_FACTORY**

C'est l'url vers votre installation de la lead's factory. Cette URL se termine par un slash.

**CODE\_ACTION**

C'est le code action que le site souhaite enregistrer pour cet affichage de la lead's factory. Ce code action est important mais pas obligatoire, il permet à l'application de suivre la performance des formulaire et des campagnes marketing via vos sites.

**CODE\_FORMULAIRE_LEADS**

C'est le code technique que vous définissez dans le back office de la lead's. Il permet de nommer ce formulaire pour cet emplacement.

## Precharger des valeurs dans les formulaires

Le tableau de données Javascript **'params'** permet le préchargement de votre formulaire avec vos données.

Il suffit, pour ajouter des valeurs, d'utiliser en index de tableau l'identifiant du champs de formulaire.

Exemple : 

	params['salutation'] = 'mr';
	params['lastName'] = 'Wallet';
	params['firstName'] = 'Eric';
	params['email'] = 'demo@demo.fr';

Le formulaire affichera alors des valeurs pré-remplie pour les champs Salutation / lastName / firstName et email.

## Le cache des formulaires

Le javascript des formulaire est caché sur le serveur de l'application dans le dossier symfony app/cache/formulaires/ avec code identifiant : 

	[[CODE_FORMULAIRE]].js

Chaque modification de l'objet formulaire entraine un effacement du cache. Le cache se génère au premier appel du formulaire.

Le cache des formulaire se vide via les actions edit ou delete du controlleur de gestion de l'objet 'Form'.

## Compter les affichages

L'application enregistre les affichages des formulaires et des codes actions dans l'objectif de fournir des statistiques d'utilisations.

Un appel à l'application via une image 1x1 en transferant les données de code action et formulaire. Cet appel est généré automatiquement par le code d'appel javascript.


## Les codes actions

Les codes actions doivent etre passés au formulaire via l'appel javascript et la variable **codeAction**.

En cas d'absence de code action, l'application fera l'association avec le code action par défaut configuré dans le back office.

Le site internet d'appel, est en charge de choisir le code action correct et de le transferer à la lead's factory.

cf code d'appel javascript :

	codeAction = '[[INSEREZ_VOTRE_CODE_ACTION]]';






