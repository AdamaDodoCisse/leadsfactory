<?php
namespace Tellaw\LeadsFactoryBundle\Controller\AbstractController;

use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractGenericCruController
 *
 * Class used to wrap CRUD Controllers in one generic method
 *
 */
abstract class AbstractGenericCrudController extends CoreController {

    /**
     * List of REQUIRED configurations
     * SPECIFIC TO FORM
     */
    public $_entity = null;
    public $_formType = null;
    public $_redirect_route = null;

    public $_list_columns = null;

    /**
     * List of REQUIRED configurations
     * GLOBAL TO APPLICATION
     */
    public $_form_template = null;
    public $_list_template = null;

    /**
     * List of optionnal configurations
     */
    public $_method = "POST";
    public $_list_actions = array();
    public $_edit_actions = array();

    public $_list_title = "Liste";
    public $_edition_title = "Edition";
    public $_create_title = "Création";

    abstract function setEntity ();
    abstract function setFormType ();
    abstract function setFormTemplate ();
    abstract function setListTemplate ();
    abstract function setRedirectRoute ();
    abstract function setListColumns ();
    abstract function setNewRoute();

    function __construct () {

        $this->_entity = $this->setEntity();
        $this->_formType = $this->setFormType();
        $this->_form_template = $this->setFormTemplate();
        $this->_list_template = $this->setListTemplate();
        $this->_redirect_route = $this->setRedirectRoute();
        $this->_list_columns = $this->setListColumns();

        if ( $this->_entity == null || trim($this->_entity) == "" ) {
            throw new \Exception ("Target Entity must be set, please implement setEntity to return a correct value");
        }

        if ( $this->_formType == null  ) {
            throw new \Exception ("Target FORM TYPE must be set, please implement setFormType to return a correct value");
        }

        if ( $this->_form_template == null || trim($this->_form_template) == "" ) {
            throw new \Exception ("Target FORM TEMPLATE must be set, please implement setFormTemplate to return a correct value");
        }

        if ( $this->_list_template == null || trim($this->_list_template) == "" ) {
            throw new \Exception ("Target LIST TEMPLATE must be set, please implement setListTemplate to return a correct value");
        }

        if ( $this->_redirect_route == null || trim($this->_redirect_route) == "" ) {
            throw new \Exception ("REDIRECT ROUTE must be set, please implement setRedirectRoute to return a correct value");
        }

        parent::__construct();

    }

    public function indexAction($page=1, $limit=10, $keyword='')
    {
        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList ($this->_entity , $page, $limit, $keyword, array () );
        $listTitles = array_keys( $this->_list_columns );

        return $this->render(
            $this->_list_template,
            array(
                'newRoute'      => $this->setNewRoute(),
                'listTitle'     => $listTitles,
                'listColumns'   => $this->_list_columns,
                'listActions'   => $this->_list_actions,
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );
    }

    public function newAction( Request $request )
    {

        $form = $this->createForm(  $this->_formType,
            null,
            array(
                'method' => $this->_method
            )
        );



        $form->handleRequest($request);

        if ($form->isValid()) {
            // fait quelque chose comme sauvegarder la tâche dans la bdd

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();


            return $this->redirect($this->generateUrl('_users_list'));
        }

        return $this->render( $this->_form_template , array(  'form' => $form->createView(),
            'title' => "Création d'un utilisateur"));
    }

    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->find($id);

        $form = $this->createForm(  $this->_formType,
            $formData,
            array(
                'method' => $this->_method
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            // fait quelque chose comme sauvegarder la tâche dans la bdd

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_users_list'));
        }

        return $this->render($this->_form_template,
            array(
                'form' => $form->createView(),
                'helpMessage' => $this->_help_message,
                'title' => "Edition d'un profil utilisateur")
            );

    }

    public function deleteAction ( $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_users_list'));

    }

}