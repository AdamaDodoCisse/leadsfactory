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

/**
 * @Route("/entity")
 * @Cache(expires="tomorrow")
 */
class EntityFormTypeController extends AbstractLeadsController
{

    /**
     *
     * @Route("/formType/list", name="_formType_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('
            SELECT DISTINCT f.*, (b.id > 0) as bookmark FROM FormType f
            LEFT JOIN (SELECT * FROM bookmark WHERE user= :user_id AND entity_name="FormType") AS b ON f.id = b.entity_id
        ');
        $query->bindValue('user_id', $user->getId());
        $query->execute();
        $forms = $query->fetchAll();

        //$forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->findAll();

        return $this->render(
            $this->getBaseTheme().':entity/FormType:entity_formType_list.html.twig',
            array(  'forms' => $forms )
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

        return $this->render($this->getBaseTheme().':entity/FormType:entity_formType_edit.html.twig', array(  'form' => $form->createView(),
                                                                                                         'title' => "Création d'un type"));
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

        return $this->render($this->getBaseTheme().':entity/FormType:entity_formType_edit.html.twig', array(  'form' => $form->createView(),
                                                                                                        'title' => "Edition d'un type"));

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
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:FormType')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_formType_list'));

    }

}
