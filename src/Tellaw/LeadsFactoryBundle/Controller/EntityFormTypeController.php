<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Tellaw\LeadsFactoryBundle\Form\Type\FormTypeType;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/entity")
 * @Cache(expires="tomorrow")
 */
class EntityFormTypeController extends Controller
{

    /**
     *
     * @Route("/formType/list", name="_formType_list")
     *
     */
    public function indexAction(Request $request)
    {

        $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->findAll();

        return $this->render(
            'TellawLeadsFactoryBundle:entity/formType:entity_referenceList_list.html.twig',
            array(  'forms' => $forms )
        );

    }

    /**
     * @Route("/formType/new", name="_formType_new")
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

        return $this->render('TellawLeadsFactoryBundle:entity/formType:entity_referenceList_edit.html.twig', array(  'form' => $form->createView(),
                                                                                                         'title' => "Création d'un type"));
    }

    /**
     * @Route("/formType/edit/{id}", name="_formType_edit")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->find($id);

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

        return $this->render('TellawLeadsFactoryBundle:entity/formType:entity_referenceList_edit.html.twig', array(  'form' => $form->createView(),
                                                                                                        'title' => "Edition d'un type"));

    }

    /**
     * @Route("/formType/delete/id/{id}", name="_formType_delete")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ( $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_formType_list'));

    }

}
