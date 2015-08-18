<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Tellaw\LeadsFactoryBundle\Form\Type\FormTypeType;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;

/**
 * @Route("/entity")
 * @Cache(expires="tomorrow")
 */
class EntityFormTypeController extends CoreController
{

    public function __construct () {
        parent::__construct();

    }

    /**
     *
     * @Route("/formType/list/{page}/{limit}/{keyword}", name="_formType_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction($page=1, $limit=10, $keyword='')
    {

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList ('TellawLeadsFactoryBundle:FormType', $page, $limit, $keyword, array ('user'=>$this->getUser()));
        $bookmarks = $this->get('leadsfactory.form_type_repository')->getBookmarkedFormsForUser( $this->getUser()->getId() );

        $formatedBookmarks = array();
        foreach ($bookmarks as $bookmark) {
            $formatedBookmarks[ $bookmark->getFormType()->getId() ] = true;
        }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/FormType:entity_formType_list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options'],
                'bookmarks'     => $formatedBookmarks
            )
        );
    }

    /**
     * @Route("/formType/new", name="_formType_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction( Request $request )
    {
        $type = new FormTypeType();

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

            return $this->redirect($this->generateUrl('_formType_list'));
        }

        return $this->render('TellawLeadsFactoryBundle:entity/FormType:entity_formType_edit.html.twig', array(  'form' => $form->createView(),
                                                                                                         'title' => "Création d'un groupe"));
    }

    /**
     * @Route("/formType/edit/{id}", name="_formType_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->get('leadsfactory.form_type_repository')->find($id);

        $type = new FormTypeType();

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

            return $this->redirect($this->generateUrl('_formType_list'));
        }

        return $this->render('TellawLeadsFactoryBundle:entity/FormType:entity_formType_edit.html.twig', array(  'form' => $form->createView(),
                                                                                                        'title' => "Edition d'un groupe"));

    }

    /**
     * @Route("/formType/delete/id/{id}", name="_formType_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ( $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->get('leadsfactory.form_type_repository')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_formType_list'));

    }

}
