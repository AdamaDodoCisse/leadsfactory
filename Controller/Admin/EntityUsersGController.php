<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
use Tellaw\LeadsFactoryBundle\Form\Type\UsersType;
use Tellaw\LeadsFactoryBundle\Form\Type\UsersCreationType;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Tellaw\LeadsFactoryBundle\Utils\LFUtils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/entity/user")
 */
class EntityUsersGController extends CoreController
{

    public function __construct () {
        parent::__construct();
    }

    /**
     * @Route("/users/list/{page}/{limit}/{keyword}", name="_users_list")
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
     * @Route("/users/new", name="_users_new")
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


    /**
     * @Route("/users/generatepassword/{id}", name="_users_generate_password")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function generatepasswordAction ( $id ) {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr( str_shuffle( $chars ), 0, 8 );

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Users')->find($id);

        $object->setPassword ( $password );

        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();

        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setTo($object->getEmail())
            ->setFrom($this->container->get("preferences_utils")->getUserPreferenceByKey("EXPORT_NOTIFICATION_FROM"))
            ->setBody($this->renderView('TellawLeadsFactoryBundle:emails:password.txt.twig', array('password' => $password, 'login' => $object->getLogin())))
        ;
        $this->get('mailer')->send($message);

        return $this->render('TellawLeadsFactoryBundle:entity/Users:password.html.twig', array(     'login' => $object->getLogin(),
                                                                                                    'password' => $password,
                                                                                                    'title' => "Génération d'un mot de passe utilisateur"));

    }

}
