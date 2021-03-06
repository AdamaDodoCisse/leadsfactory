<?php
namespace Tellaw\LeadsFactoryBundle\Controller\AbstractController;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;

/**
 * Class AbstractGenericCruController
 *
 * Class used to wrap CRUD Controllers in one generic method
 *
 */
abstract class AbstractGenericCrudController extends CoreController
{

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

    abstract protected function setEntity();

    abstract protected function setFormType();

    abstract protected function setFormTemplate();

    abstract protected function setListTemplate();

    abstract protected function setRedirectRoute();

    abstract protected function setListColumns();

    abstract protected function setNewRoute();

    function __construct()
    {

        $this->_entity = $this->setEntity();
        $this->_formType = $this->setFormType();
        $this->_form_template = $this->setFormTemplate();
        $this->_list_template = $this->setListTemplate();
        $this->_redirect_route = $this->setRedirectRoute();
        $this->_list_columns = $this->setListColumns();

        if ($this->_entity == null || trim($this->_entity) == "") {
            throw new \Exception ("Target Entity must be set, please implement setEntity to return a correct value");
        }

        if ($this->_formType == null) {
            throw new \Exception ("Target FORM TYPE must be set, please implement setFormType to return a correct value");
        }

        if ($this->_form_template == null || trim($this->_form_template) == "") {
            throw new \Exception ("Target FORM TEMPLATE must be set, please implement setFormTemplate to return a correct value");
        }

        if ($this->_list_template == null || trim($this->_list_template) == "") {
            throw new \Exception ("Target LIST TEMPLATE must be set, please implement setListTemplate to return a correct value");
        }

        if ($this->_redirect_route == null || trim($this->_redirect_route) == "") {
            throw new \Exception ("REDIRECT ROUTE must be set, please implement setRedirectRoute to return a correct value");
        }

        parent::__construct();

    }

    public function indexAction($page = 1, $limit = 10, $keyword = '')
    {
        if ($this->get("core_manager")->isDomainAccepted()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList($this->_entity, $page, $limit, $keyword, array());
        $columnNames = array_keys($this->_list_columns);

        return $this->render(
            $this->_list_template,
            array(
                'title' => $this->_list_title,
                'description' => $this->_description,
                'newRoute' => $this->setNewRoute(),
                'columnNames' => $columnNames,
                'listColumns' => $this->_list_columns,
                'listActions' => $this->_list_actions,
                'elements' => $list['collection'],
                'pagination' => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );
    }

    public function newAction(Request $request)
    {

        $form = $this->createForm($this->_formType,
            null,
            array(
                'method' => $this->_method
            )
        );


        $form->handleRequest($request);

        if ($form->isValid()) {

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($form->getData());
                $em->flush();

                return $this->redirect($this->generateUrl($this->_redirect_route));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->render($this->_form_template, array('form' => $form->createView(), 'title' => $this->_create_title));
    }

    public function editAction(Request $request, $id)
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository($this->_entity)->find($id);

        $form = $this->createForm($this->_formType,
            $formData,
            array(
                'method' => $this->_method
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($form->getData());
                $em->flush();

                return $this->redirect($this->generateUrl($this->_redirect_route));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->render($this->_form_template,
            array(
                'form' => $form->createView(),
                'helpMessage' => $this->_description,
                'title' => $this->_edition_title)
        );

    }

    public function deleteAction($id)
    {

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository($this->_entity)->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl($this->_redirect_route));

    }

}
