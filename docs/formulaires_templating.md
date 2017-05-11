# LEAD'S FACTORY

[toc]

## Templating des formulaires


### Objet : Text

#### Description : 
C’est un champ texte à une ligne.

#### Appel TWIG :


	{{ field(
		{	'type': 'text', 
			'attributes': {
				'id': 'lastName'}
		}) 
	}}


#### Rendu HTML :

	<input type="text" id="lastName" name="lastName"/>
	


### Objet : TextArea

#### Description : 
C’est un champ texte à une ligne.

#### Appel TWIG :


	{{ field(
		{	'type': 'textarea', 
			'attributes': {
				'id': 'lastName'}
		}) 
	}}


#### Rendu HTML :

	<textarea id="lastName" name="lastName"></textarea>


### Objet : Téléphone

#### Description : 
C’est un champ texte à une ligne utilisable pour la saisie d’un numéro de téléphone.
Attention, n’oubliez l’ajout des contrôles de validation sur ce champ.

#### Appel TWIG :


	{{ field(
		{	'type': 'phone', 
			'attributes': {
				'id': 'myphone'}
		}) 
	}}


#### Rendu HTML :

	<input type="text" id="myphone"/>


### Objet : Email

#### Description : 
C’est un champ texte à une ligne utilisable pour la saisie d’un email.
Attention, n’oubliez l’ajout des contrôles de validation sur ce champ.

#### Appel TWIG :


	{{ field(
		{	'type': 'email', 
			'attributes': {
				'id': 'myEmail'}
		}) 
	}}


#### Rendu HTML :

	<input type="text" id="myphone"/>


### Objet : Checkbox

#### Description : 
C’est une case à cocher. Par défaut non cochée.

#### Appel TWIG :


	{{ field(
		{	'type': 'checkbox', 
			'attributes': {
				'id': 'myCheckbox',
				'checked' : 'checked'}
		}) 
	}}


#### Rendu HTML :

	<input type="checkbox" id="myCheckbox" checked="checked"/>

#### Attributs :


| Attribut           | Description      |
|--------------------|------------------|
|@checked='checked'  | Case cochée par défaut |


### Objet : Liste

#### Description : 
Ceci affiche une liste de référence contenant les valeurs de la liste configurée dans le backoffice sous l’identifiant ‘salutation’.

La liste va pouvoir etre affichée sous une des formes suivantes :

- Liste déroulante type ‘select’
- Liste de case à cocher type ‘checkbox’
- Liste de choix type ‘options’


#### Appel TWIG :


	{{ 
		field(
			{	
				'type': 'reference-list', 
				'attributes': {
					'id': 'salutation', 
					'data-list': 'salutation', 
					'validator': 'required'	}
				}
			) 
	}}


#### Rendu HTML :

	<select id="salutation">
		<option... valeurs de la liste de ref />
	</select>

#### Attributs :


| Attribut | Description                                     |
|----------|-------------------------------------------------|
|@display  | __select__ : Affichage de type liste déroulante |
|&nbsp;    | __checkbox__ : Affichage de type checkbox       |
|&nbsp;    | __radio__ : Affichage de type radio             |



### Objet : Listes liées


#### Description : 
Les listes liées permettent la mise à jour en cascade de listes de références suivant les choix de l’utilisateur.

#### Appel TWIG :


	{{ 
		field(	{
			'type': 'linked-reference-list', 
			'attributes': {
				'id': 'ville_id', 
				'data-parent': 'zip', 
				'data-list': 'ville', 
				'data-default': 'Saisissez d\'abord un code postal',
				'validator': 'required'}
				}) 
	}}
                			
	
#### Exemple d'utilisation avec code postal / ville

        <div>

            <p>
                <label for="zip">Code postal<span class="red">&nbsp;*</span></label>
                {{ 	field(	{
                		'type': 'text', 
                		'attributes': {
                			'id': 'zip', 
                			'data-list': 'zipcode', 
                			'validator': 'required'}
                			}) 
                	}}
            </p>

            <p>
                <label for="ville_text">Ville<span class="red">&nbsp;*</span></label>
                {{ field(	{
                		'type': 'linked-reference-list', 
                		'attributes': {
                			'id': 'ville_id', 
                			'data-parent': 'zip', 
                			'data-list': 'ville', 
                			'data-default': 'Saisissez d\'abord un code postal',
                			'validator': 'required'}}) }}
            </p>

        </div>	

Cet exemple assume que vous disposez de deux listes de références :

- zipcode : Liste des codes postaux.
- ville : Villes liées aux codes postaux.



#### Rendu HTML :

	<select id="salutation">
		<option... valeurs de la liste de ref />
	</select>
	
*un appel javascript ajouté se chargera de la mise à jour en cascade*

#### Attributs :


| Attribut | Description                                     |
|----------|-------------------------------------------------|
|@display  | __select__ : Affichage de type liste déroulante |
|&nbsp;    | __checkbox__ : Affichage de type checkbox       |
|&nbsp;    | __radio__ : Affichage de type radio             |



## Tag d'appel Twig

	<link rel="stylesheet" type="text/css" href="[url serveur lead's factory]/leads-factory/web/bundles/tellawleadsfactory/js/libs/formValidator/developr.validationEngine.css" />
	
	<div id="form-classic" class="loading-leads">
		<img src="/images/loader.gif" alt="Chargement" width="100" height="100" class="loading-leads-img" />
		<p class="loading-leads-txt">Merci de patienter ...</p>
	</div>

	<script type="text/javascript">
		baseUrl = '[url serveur lead's factory]/leads-factory/';
		jQuery.when(
			jQuery.getScript('[url serveur lead's factory]/leads-factory/web/bundles/tellawleadsfactory/js/lf.js'),
			jQuery.getScript('[url serveur lead's factory]/leads-factory/web/bundles/tellawleadsfactory/js/libs/formValidator/jquery.validationEngine.js'),
			jQuery.getScript('[url serveur lead's factory]/leads-factory/web/bundles/tellawleadsfactory/js/libs/formValidator/languages/jquery.validationEngine-fr.js'),
			jQuery.getScript('[url serveur lead's factory]/leads-factory/web/app_dev.php/client/form/twig/[ID Formulaire]'),
			jQuery(document).data("readyDeferred")
		).done(function() {
			jQuery('#form-classic').html(leadsfactory.html);
			jQuery('#form-classic').removeClass('loading-leads');
			var params = {};
			var options = {};
	    	leadsfactory.init('??', params, options);
		});
	</script>

