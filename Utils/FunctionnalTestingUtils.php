<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\KibanaSearch;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement;
use Tellaw\LeadsFactoryBundle\Entity\SearchResult;
use Tellaw\LeadsFactoryBundle\Entity\UserPreferences;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Tellaw\LeadsFactoryBundle\Shared\SearchShared;

class FunctionnalTestingUtils extends SearchShared {


    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    private $logger;


    public function __construct () {


    }

    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
        $this->logger = $this->container->get("logger");
    }

    public function testForm ( $client, $form, $fields ) {

        // 2/ Vérification des champs
        if ( trim($form->getUrl()) != "" ) {
            $targetUrl = trim( $form->getUrl() );
        } else {
            $targetUrl = $this->container->get('router')->generate('_client_twig_preview', array('code' => $form->getCode()));;
        }

        $this->logger->info("Target URL : ".$targetUrl);

        // 3/ Connexion au front sur l'url du formulaire. Si inexistante, utilisation de la preview
        //$crawler = $client->request('GET', $targetUrl);


        // 4/ Remplissage des champs et intégrrogation des fields pour obtenir les valeurs
        foreach ( $fields as $field ) {
            $form[$field["id"]] = 'Lucas';
        }

        // 5/ Post
        // 6/ vérification en base du post
        // 7/ Vérification de la création des taches d'exports

    }

    private function isFieldCorrect ( $form, $field ) {

        return true;

    }

}

