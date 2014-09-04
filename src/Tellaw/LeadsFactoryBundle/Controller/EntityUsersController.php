<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
use Tellaw\LeadsFactoryBundle\Form\Type\UsersType;
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
class EntityUsersController extends Controller
{

    /**
     * @Route("/users/list", name="_users_list")
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction(Request $request)
    {
        $elements = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->findAll();

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Users:list.html.twig',
            array(  'elements' => $elements )
        );
    }

    /**
     * @Route("/users/new", name="_users_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction( Request $request )
    {

        $type = new UsersType();

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
     * @Route("/users/edit/{id}", name="_users_edit")
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
     * @Route("/users/delete/id/{id}", name="_users_delete")
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
