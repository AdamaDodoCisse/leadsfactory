<?php
namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FieldType;
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
 * @Route("/entity/field")
 */
class EntityFieldController extends ApplicationCrudController
{
    public $_list_title;
    public $_edition_title;
    public $_create_title;
    public $_help_message;
    public $_list_actions;

    public function __construct ()
    {
        $this->_list_title = "Champs";
        $this->_description = "Description de l'admin des champs";
        $this->_edition_title = "Edition d'un champ";
        $this->_create_title = "Création d'un champ";
        $this->_list_actions = array (
            ["title" => "Editer",      "route"=>"_field_edit",    "color" => "blue"],
            ["title" => "Supprimer",   "route"=>"_field_delete",  "color" => "pink",   "alert" => "Confirmez vous la suppression ?"]
        );

        PreferencesUtils::registerKey(
            'EXPORT_NOTIFICATION_FROM',
            "Notification email sender",
            PreferencesUtils::$_PRIORITY_OPTIONNAL
        );

        parent::__construct();
    }

    public function setEntity () {
        return "TellawLeadsFactoryBundle:Field";
    }

    public function setFormType () {
        return new FieldType();
    }


    public function setNewRoute () {
        return "_field_new";
    }

    public function setRedirectRoute () {
        return "_field_list";
    }

    public function setListColumns () {
        return array(
                        "Id"        => "id",
                        "Code"      => "code"
                    );
    }

    /**
     * @Route("/list/{page}/{limit}/{keyword}", name="_field_list")
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction($page=1, $limit=10, $keyword='') {
        return parent::indexAction($page, $limit, $keyword);
    }

    /**
     * @Route("/new", name="_field_new")
     * @Secure(roles="ROLE_USER")
     */
    public function newAction( Request $request ){
        return parent::newAction( $request );
    }

    /**
     * @Route("/edit/{id}", name="_field_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction (  Request $request, $id ) {
        return parent::editAction(  $request, $id );
    }

    /**
     * @Route("/delete/id/{id}", name="_field_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ($id) {
        return parent::deleteAction($id);
    }

}
