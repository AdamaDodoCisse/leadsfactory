<?php
namespace LeadsFactoryBundle\Controller\Admin;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use LeadsFactoryBundle\Controller\AbstractController\ApplicationCrudController;
use LeadsFactoryBundle\Form\Type\FieldType;
use LeadsFactoryBundle\Utils\PreferencesUtils;

/**
 * @Route("/entity/field")
 */
class EntityFieldController extends ApplicationCrudController
{
    public $_list_title;
    public $_edition_title;
    public $_create_title;
    public $_description;
    public $_list_actions;

    public function __construct()
    {
        $this->_list_title = "Champs";
        $this->_description = "Liste des champs reférencés et indexés pour les tests fonctionnels.";
        $this->_edition_title = "Edition d'un champ";
        $this->_create_title = "Création d'un champ";
        $this->_list_actions = array(
            ["title" => "Editer", "route" => "_field_edit", "color" => "blue"],
            ["title" => "Supprimer", "route" => "_field_delete", "color" => "pink", "alert" => "Confirmez vous la suppression ?"]
        );

        PreferencesUtils::registerKey(
            'EXPORT_NOTIFICATION_FROM',
            "Notification email sender",
            PreferencesUtils::$_PRIORITY_OPTIONNAL
        );

        parent::__construct();
    }

    protected function setEntity()
    {
        return "TellawLeadsFactoryBundle:Field";
    }

    protected function setFormType()
    {
        return new FieldType();
    }


    protected function setNewRoute()
    {
        return "_field_new";
    }

    protected function setRedirectRoute()
    {
        return "_field_list";
    }

    protected function setListColumns()
    {
        return array(
            "Identifiant du champ" => "code",
            "Valeurs par défaut (par scope)" => "testValue"
        );
    }

    /**
     * @Route("/list/{page}/{limit}/{keyword}", name="_field_list")
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction($page = 1, $limit = 10, $keyword = '')
    {
        return parent::indexAction($page, $limit, $keyword);
    }

    /**
     * @Route("/new", name="_field_new")
     * @Secure(roles="ROLE_USER")
     */
    public function newAction(Request $request)
    {
        return parent::newAction($request);
    }

    /**
     * @Route("/edit/{id}", name="_field_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        return parent::editAction($request, $id);
    }

    /**
     * @Route("/delete/id/{id}", name="_field_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction($id)
    {
        return parent::deleteAction($id);
    }

}
