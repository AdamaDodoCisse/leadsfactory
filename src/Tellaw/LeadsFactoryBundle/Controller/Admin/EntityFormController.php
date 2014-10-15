<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

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
class EntityFormController extends AbstractLeadsController
{
    /**
     *
     * @Route("/form/list", name="_form_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('
            SELECT DISTINCT f.*, (b.id > 0) as bookmark FROM Form f
            LEFT JOIN (SELECT * FROM bookmark WHERE user= :user_id AND entity_name="Form") AS b ON f.id = b.entity_id
        ');
        $query->bindValue('user_id', $user->getId());
        $query->execute();
        $forms = $query->fetchAll();

        //$forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findAll();

        return $this->render(
            $this->getBaseTheme().':entity/Form:entity_form_list.html.twig',
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
        $form = $this->createForm(
            new FormType(),
            null,
            array('method' => 'POST')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
//            return $this->redirect($this->generateUrl('_form_list'));
            return $this->render(
                $this->getBaseTheme().':entity/Form:entity_form_edit.html.twig',
                array(
                    'id' => $form->getId(),
                    'form' => $form->createView(),
                    'title' => "Edition d'un formulaire"
                )
            );
        }
        return $this->render(
            $this->getBaseTheme().':entity/Form:entity_form_edit.html.twig',
            array(
                'form' => $form->createView(),
                'title' => "Création d'un formulaire"
            )
        );
    }

    /**
     * @Route("/form/edit/{id}", name="_form_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {
        $formEntity = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($id);
        $form = $this->createForm(
            new FormType(),
            $formEntity,
            array('method' => 'POST')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
//            return $this->redirect($this->generateUrl('_form_list'));
        }

        return $this->render(
            $this->getBaseTheme().':entity/Form:entity_form_edit.html.twig',
            array(
                'id' => $id,
                'form' => $form->createView(),
                'title' => "Edition d'un formulaire"
            )
        );
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

    /**
     * @Route("/form/duplicate/id/{id}", name="_form_duplicate")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function duplicateAction ($id)
    {
        $old = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->find($id);

        $em = $this->getDoctrine()->getManager();
        $new = clone $old;
        $new->setCode('');
        $em->persist($new);
        $em->flush();

        return $this->redirect($this->generateUrl('_form_edit', array('id' => $new->getId())));
    }
}
