<?php
namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\UsersType;
use Tellaw\LeadsFactoryBundle\Controller\AbstractController\ApplicationCrudController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Tellaw\LeadsFactoryBundle\Utils\PreferencesUtils;

/**
 * @Route("/entity/user")
 */
class EntityUsersController extends ApplicationCrudController
{

    public $_list_title = "utilisteurs";
    public $_edition_title = "Edition d'une fiche utilisateur";
    public $_create_title = "Création d'un utilisateur";

    public $_description = "Il est possible de regrouper les utilisateurs dans des scopes pour leur mettre à disposition uniquement les données les concernant. Vous pouvez alors séparer vos utilisateurs par entités.";

    public $_list_actions = array (
                                    ["title" => "Editer",                   "route"=>"_users_edit",                 "color" => "blue"],
                                    ["title" => "Générer un mot de passe",  "route"=>"_users_generate_password",    "color" => "green"],
                                    ["title" => "Supprimer",                "route"=>"_users_delete",               "color" => "pink",      "alert" => "Confirmez vous la suppression ?"]
                                  );

    public function __construct () {
        PreferencesUtils::registerKey( 'EXPORT_NOTIFICATION_FROM',
            "Notification email sender",
            PreferencesUtils::$_PRIORITY_OPTIONNAL );
        parent::__construct();
    }

    public function setEntity () {
        return "TellawLeadsFactoryBundle:Users";
    }

    public function setFormType () {
        return new UsersType();
    }


    public function setNewRoute () {
        return "_users_new";
    }

    public function setRedirectRoute () {
        return "_users_list";
    }

    public function setListColumns () {
        return array(
                        "Id"        => "id",
                        "Prenom"    => "firstname",
                        "Nom"       => "lastname",
                        "Login"       => "login",
                        "Scope"     => "scope.name",
                    );
    }

    /**
     * @Route("/list/{page}/{limit}/{keyword}", name="_users_list")
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction($page=1, $limit=10, $keyword='') {
        return parent::indexAction($page, $limit, $keyword);
    }

    /**
     * @Route("/new", name="_users_new")
     * @Secure(roles="ROLE_USER")
     */
    public function newAction( Request $request ){
        return parent::newAction( $request );
    }

    /**
     * @Route("/edit/{id}", name="_users_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction (  Request $request, $id ) {
        return parent::editAction(  $request, $id );
    }

    /**
     * @Route("/delete/id/{id}", name="_users_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ($id) {
        return parent::deleteAction($id);
    }


    /**
     * @Route("/users/generatepassword/{id}", name="_users_generate_password")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function generatepasswordAction ( $id ) {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr( str_shuffle( $chars ), 0, 8 );

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->find($id);

        $object->setPassword ( $password );

        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();

        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setTo($object->getEmail())
            ->setFrom($this->container->get("preferences_utils")->getUserPreferenceByKey("EXPORT_NOTIFICATION_FROM"))
            ->setBody($this->renderView('TellawLeadsFactoryBundle:emails:password.txt.twig', array('password' => $password, 'login' => $object->getLogin())))
        ;
        $this->get('mailer')->send($message);

        return $this->render('TellawLeadsFactoryBundle:entity/Users:password.html.twig', array(     'login' => $object->getLogin(),
                                                                                                    'password' => $password,
                                                                                                    'title' => "Génération d'un mot de passe utilisateur"));

    }

}
