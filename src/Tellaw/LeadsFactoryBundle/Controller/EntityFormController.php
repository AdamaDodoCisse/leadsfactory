<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
use Tellaw\LeadsFactoryBundle\Utils\LFUtils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/entity")
 */
class EntityFormController extends Controller
{

    /**
     *
     * @Route("/form/list", name="_form_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction(Request $request)
    {

        $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Form:entity_form_list.html.twig',
            array(  'forms' => $forms )
        );

    }

    /**
     * @Route("/form/new", name="_form_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction( Request $request )
    {

        $type = new FormType();

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

            return $this->redirect($this->generateUrl('_form_list'));
        }

        return $this->render('TellawLeadsFactoryBundle:entity/Form:entity_form_edit.html.twig', array(  'form' => $form->createView(),
                                                                                                    'title' => "Création d'un formulaire"));
    }

    /**
     * @Route("/form/edit/{id}", name="_form_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($id);

        $type = new FormType();

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

            return $this->redirect($this->generateUrl('_form_list'));
        }

        return $this->render('TellawLeadsFactoryBundle:entity/Form:entity_form_edit.html.twig', array(  'form' => $form->createView(),
                                                                                                    'title' => "Edition d'un formulaire"));

    }

    /**
     * @Route("/form/delete/id/{id}", name="_form_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ( $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_form_list'));

    }

}
