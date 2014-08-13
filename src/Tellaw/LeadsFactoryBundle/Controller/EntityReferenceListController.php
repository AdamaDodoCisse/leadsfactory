<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\ReferenceListType;
use Tellaw\LeadsFactoryBundle\Form\Type\ReferenceListElementType;
use Tellaw\LeadsFactoryBundle\Utils\LFUtils;

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
class EntityReferenceListController extends Controller
{

    /**
     *
     * @Route("/referenceList/list", name="_referenceList_list")
     *
     */
    public function indexAction(Request $request)
    {

        $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->findAll();

        return $this->render(
            'TellawLeadsFactoryBundle:entity/ReferenceList:entity_referenceList_list.html.twig',
            array(  'forms' => $forms )
        );

    }

    /**
     * @Route("/referenceList/new", name="_referenceList_new")
     * @Template()
     */
    public function newAction( Request $request )
    {

        $type = new ReferenceListType();

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

            return $this->redirect($this->generateUrl('_referenceList_list'));
        }

        return $this->render('TellawLeadsFactoryBundle:entity/ReferenceList:entity_referenceList_edit.html.twig', array(  'form' => $form->createView(),
                                                                                                    'title' => "Création d'une liste de référence"));
    }

    /**
     * @Route("/referenceList/edit/{id}", name="_referenceList_edit")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->find($id);

        $type = new ReferenceListType();

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

            // Traitement des éléments de la liste
            $jsonElements = $form->get('json')->getData();

            return $this->redirect($this->generateUrl('_referenceList_list'));
        }

        $form->get('json')->setData("Test");

        return $this->render('TellawLeadsFactoryBundle:entity/ReferenceList:entity_referenceList_edit.html.twig',
                                array(  'form' => $form->createView(),
                                        'elements'=> $formData->getElements(),
                                        'title' => "Edition d'une liste de référence"));

    }

    /**
     * @Route("/referenceList/delete/id/{id}", name="_referenceList_delete")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ( $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_referenceList_list'));

    }

    private function saveJsonFeed ( $json ) {

        $elements = json_decode ( $json, true );

        foreach ($elements as $element) {

        }

    }



}
