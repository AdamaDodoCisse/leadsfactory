<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\Preference;
use Tellaw\LeadsFactoryBundle\Form\Type\PreferenceType;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;


/**
 * Scope controller.
 *
 * @Route("/entity")
 */
class PreferenceController extends CoreController
{

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Lists all Scope entities.
     *
     * @Route("/preference/list/{page}/{limit}/{keyword}", name="_preference_list")
     *
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction($page = 1, $limit = 10, $keyword = '')
    {

        if ($this->get("core_manager")->isDomainAccepted()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList('TellawLeadsFactoryBundle:Preference', $page, $limit, $keyword, array('user_id' => $this->getUser()->getId()));

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Preference:entity_list.html.twig',
            array(
                'elements' => $list['collection'],
                'pagination' => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );

    }

    /**
     * Creates a new Scope entity.
     *
     * @Route("/preference/new", name="_preference_new")
     * @Secure(roles="ROLE_USER")
     */
    public function newAction(Request $request)
    {
        $entity = new Preference();

        $form = $this->createForm(new PreferenceType(), $entity, array(
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setCreatedAt(new \DateTime());
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('_preference_list'));
        }

        return $this->render("TellawLeadsFactoryBundle:entity:Preference/entity_edit.html.twig", array(
            'title' => 'Ajouter une valeur',
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/preference/edit/{id}", name="_preference_edit")
     * @Secure(roles="ROLE_USER")
     */
    public function editAction(Request $request, $id)
    {
        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $scopeData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Preference')->find($id);

        $form = $this->createForm(new PreferenceType(),
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

            return $this->redirect($this->generateUrl('_preference_list'));
        }

        return $this->render("TellawLeadsFactoryBundle:entity/Preference:entity_edit.html.twig", array('form' => $form->createView(),
            'title' => "Edition d'une valeur"
        ));

    }


    /**
     * @Route("/preference/delete/{id}", name="_preference_delete")
     * @Secure(roles="ROLE_USER")
     */
    public function deleteAction($id)
    {
        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Preference')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_preference_list'));

    }
}
