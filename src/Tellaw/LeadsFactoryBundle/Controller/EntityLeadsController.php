<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\LeadsType;

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
class EntityLeadsController extends Controller
{

    /**
     *
     * @Route("/leads/list", name="_leads_list")
     *
     */
    public function indexAction(Request $request)
    {

        $forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->findAll();

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Leads:list.html.twig',
            array(  'elements' => $forms )
        );

    }

    /**
     * @Route("/leads/edit/{id}", name="_leads_edit")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($id);

        $type = new LeadsType();

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

            return $this->redirect($this->generateUrl('_leads_list'));
        }

        return $this->render('TellawLeadsFactoryBundle:entity/Leads:edit.html.twig', array(  'form' => $form->createView(),
                                                                                             'title' => "Edition d'un leads"));

    }

}
