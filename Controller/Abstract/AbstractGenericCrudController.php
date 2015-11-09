<?php

use Tellaw\LeadsFactoryBundle\Shared\CoreController;

/**
 * Class AbstractGenericCruController
 *
 * Class used to wrap CRUD Controllers in one generic method
 *
 */
abstract class AbstractGenericCruController extends CoreController {

    public function __construct () {
        parent::__construct();
    }

    /**
     * List of REQUIRED configurations
     * SPECIFIC TO FORM
     */
    private $_entity = null;
    private $_formType = null;

    /**
     * List of REQUIRED configurations
     * GLOBAL TO APPLICATION
     */
    private $_form_template = null;
    private $_list_template = null;

    /**
     * List of optionnal configurations
     */
    private $_method = "POST";
    private $_list_actions = array();
    private $_edit_actions = array();

    abstract function setEntity ();
    abstract function setFormType ();
    abstract function setFormTemplate ();
    abstract function setListTemplate ();

    function __construct () {

        $this->_entity = $this->setEntity();
        $this->_formType = $this->setFormType();
        $this->_form_template = $this->setFormTemplate();
        $this->_list_template = $this->setListTemplate();

        if ( $this->_entity == null || trim($this->_entity) == "" ) {
            throw new \Exception ("Target Entity must be set, please implement setEntity to return a correct value");
        }

        if ( $this->_formType == null || trim($this->_formType) == "" ) {
            throw new \Exception ("Target FORM TYPE must be set, please implement setFormType to return a correct value");
        }

        if ( $this->_form_template == null || trim($this->_form_template) == "" ) {
            throw new \Exception ("Target FORM TEMPLATE must be set, please implement setFormTemplate to return a correct value");
        }

        if ( $this->_list_template == null || trim($this->_list_template) == "" ) {
            throw new \Exception ("Target LIST TEMPLATE must be set, please implement setListTemplate to return a correct value");
        }

    }

    /**
     * @Route("/list/{page}/{limit}/{keyword}", name="_users_list")
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction($page=1, $limit=10, $keyword='')
    {
        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList ('TellawLeadsFactoryBundle:Users', $page, $limit, $keyword, array () );

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Users:list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );
    }

    /**
     * @Route("/new", name="_users_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction( Request $request )
    {

        $type = new UsersCreationType();

        $form = $this->createForm(  $type,
            null,
            array(
                'method' => 'POST'
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


        return $this->render('TellawLeadsFactoryBundle:entity/Users:edit.html.twig', array(  'form' => $form->createView(),
            'title' => "Création d'un utilisateur"));
    }

    /**
     * @Route("/edit/{id}", name="_users_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->find($id);

        $type = new UsersType();

        $form = $this->createForm(  $type,
            $formData,
            array(
                'method' => 'POST'
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

        return $this->render('TellawLeadsFactoryBundle:entity/Users:edit.html.twig', array(  'form' => $form->createView(),
            'title' => "Edition d'un profil utilisateur"));

    }

    /**
     * @Route("/delete/id/{id}", name="_users_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
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