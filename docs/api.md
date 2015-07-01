# LEADS FACTORY


## API Services

Cette séction va décrire l'API Services utilisable par les differentes applications pour communiquer avec la lead's.


### Méthode : getLeadAction : /lead/{id}/{key}

| Parametre      | Déscription                                                            | 
| -------------- |------------------------------------------------------------------------|
| id             | Identifiant de la lead à extraire                                      |
| key            | Clée de communication via le service entre la lead's et l'application. |


Cette méthode permet à une application de récupérer le contenu JSON d'une lead.

**Si l'identifiant ne correspond à aucune donnée**, la lead's va retourner un JSON vide.

**Si la clée est incorrecte**, la lead's va retourner une AccessDeniedHttpException avec le commentaire Invalid form key.

La lead's retourne un JSON contenant l'intégralité des données brutes.

### Méthode : validateEmailAction : /email/validate

Cette méthode permet de déclarer un email comme validé.

| Parametre      | Déscription                                                            | 
| -------------- |------------------------------------------------------------------------|
| email          | email a valider                                      |

Cette méthode configure un email client comme validé. Si cet email existe déjà dans le repository client_email il est indiqué comme valide. Si l'email n'est pas existant, il est ajouté au repository.

Si l'email voit son statut changé à validé, l'application va basculer les jobs d'export en attente (statut **EMAIL_NOT_CONFIRMED** à **NOT_PROCESSED**

### Méthode : getLeadsAction : /leads

| Parametre      | Déscription                                                            | 
| -------------- |-----------------------------------------------------------------|
| datemin        | date encadrant l'export de leads. (format Y-m-d)                  |
| datemax        | date encadrant l'export de leads. (format Y-m-d)|
| email          | Email à rechercher. |
| form           | Filtrage par formulaire (ID du formulaire). |
| scope          | Limitation des résultats à un scope. |
| form_code      | Limitation des résultats à un code de formulaire. |

Cette méthode permet à une application de recuperer les leads en specifiant des critères d'extractions.

### Méthode : postLeadsAction : /lead/post

Cette méthode donne la possibilité à une application tierce d'enregistrer des lead's sans passer par les formulaires.



### La clée de communication avec la lead's

La clée générée par la lead's se compose des éléments suivants :

- Le code de sécurité paramétré dans le backoffice de la lead's pour ce formulaire.
- L'identifiant automatique de la lead's pour ce formulaire.

La génération est basée sur un md5 de fac0ry_f0rm[secure-key]\_id\_[form-id]

