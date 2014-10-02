<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tellaw\LeadsFactoryBundle\Entity\Scope;
use Tellaw\LeadsFactoryBundle\Form\Type\ScopeType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;


/**
 * Scope controller.
 *
 * @Route("/entity")
 */
class ScopeController extends AbstractLeadsController
{

    /**
     * Lists all Scope entities.
     *
     * @Route("/scope/list", name="_scope_list")
     * @Template("TellawLeadsFactoryBundle:entity/Scope:index.html.twig")
     *
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TellawLeadsFactoryBundle:Scope')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Scope entity.
     *
     * @Route("/scope/new", name="_scope_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Scope();

        $form = $this->createForm(new ScopeType(), $entity, array(
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('_scope_list'));
        }

        $this->render( "TellawLeadsFactoryBundle:entity:Scope/edit.html.twig", array(
            'title' => 'Ajouter un scope',
            'form'   => $form->createView(),
        ));
    }

    /**
     * @Route("/scope/edit/{id}", name="_scope_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {
        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $scopeData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Scope')->find($id);

        $form = $this->createForm(  new ScopeType(),
            $scopeData,
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

            return $this->redirect($this->generateUrl('_scope_list'));
        }

        $this->render("TellawLeadsFactoryBundle:entity/Scope:edit.html.twig", array(  'form' => $form->createView(),
                       'title' => "Edition d'un scope"
        ));

    }


    /**
     * @Route("/scope/delete/{id}", name="_scope_delete")
     * @Secure(roles="ROLE_USER")
     */
    public function deleteAction ( $id )
    {
        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Scope')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_scope_list'));

    }
}
