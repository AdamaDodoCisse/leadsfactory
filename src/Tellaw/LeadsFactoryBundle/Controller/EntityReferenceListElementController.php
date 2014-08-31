<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\ReferenceListType;
use Tellaw\LeadsFactoryBundle\Form\Type\ReferenceListElementType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/entity")
 * @Cache(expires="tomorrow")
 */
class EntityReferenceListElementController extends Controller
{

    /**
     * @Route("/referenceListElement/list/{id}", name="_referenceListElement_list")
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        //$forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->findAll();
        //$repository = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceListElement');

        $referenceList = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->find( $id );;
        $collection = $referenceList->getElements();

        //$criteria = Criteria::create()->where(Criteria::expr()->eq("birthday", "1982-02-17"))->orderBy(array("username" => Criteria::ASC));

        return $this->render(
            'TellawLeadsFactoryBundle:entity/ReferenceListElement:list.html.twig',
            array(      'elements' => $collection )
        );

    }

    /**
     * @Route("/referenceListElement/new", name="_referenceListElement_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction( Request $request )
    {
        $type = new ReferenceListElementType();

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

            return $this->redirect($this->generateUrl('_referenceListElement_list'));

        }

        return $this->render('TellawLeadsFactoryBundle:entity/ReferenceListElement:edit.html.twig', array(  'form' => $form->createView(),
                                                                                                    'title' => "Création d'une liste de référence"));
    }

    /**
     * @Route("/referenceListElement/edit/{id}", name="_referenceListElement_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->find($id);

        $type = new ReferenceListElementType();

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

            return $this->redirect($this->generateUrl('_referenceListElement_list'));
        }

        $form->get('json')->setData("Test");

        return $this->render('TellawLeadsFactoryBundle:entity/ReferenceListElement:edit.html.twig', array(  'form' => $form->createView(),
                                                                                                    'title' => "Edition des valeurs d'une liste de référence"));

    }

    /**
     * @Route("/referenceListElement/delete/id/{id}", name="_referenceListElement_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ( $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_referenceListElement_list'));

    }



}
