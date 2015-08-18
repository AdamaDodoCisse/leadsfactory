<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Form\Type\FormType;
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
 * @Route("/entity")
 */
class EntityFormController extends CoreController {

    public function __construct () {
        parent::__construct();

    }

    /**
     *
     * @Route("/form/list/{page}/{limit}/{keyword}", name="_form_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction($page=1, $limit=10, $keyword='')
    {

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList ('TellawLeadsFactoryBundle:Form', $page, $limit, $keyword, array ('user'=>$this->getUser()));
        $bookmarks = $this->get('leadsfactory.form_repository')->getBookmarkedFormsForUser( $this->getUser()->getId() );

        $formatedBookmarks = array();
        foreach ($bookmarks as $bookmark) {
            $formatedBookmarks[ $bookmark->getForm()->getId() ] = true;
        }

        return $this->render(
	        'TellawLeadsFactoryBundle:entity/Form:entity_form_list.html.twig',
            array(
                    'elements'      => $list['collection'],
                    'pagination'    => $list['pagination'],
                    'limit_options' => $list['limit_options'],
                    'bookmarks'     => $formatedBookmarks
            )
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

            if (!$this->get("core_manager")->isNewFormAccepted ()) {
                return $this->redirect($this->generateUrl('_security_licence_error'));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_form_list'));
        }
        return $this->render(
            'TellawLeadsFactoryBundle:entity/Form:entity_form_edit.html.twig',
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
        $formEntity = $this->get('leadsfactory.form_repository')->find($id);
        $form = $this->createForm(
            new FormType(),
            $formEntity,
            array('method' => 'POST')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {

            $cacheFileName = "../app/cache/templates/".$id.".js";
            if (file_exists( $cacheFileName )) {
                unlink ($cacheFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
//            return $this->redirect($this->generateUrl('_form_list'));
        }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Form:entity_form_edit.html.twig',
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
        $object = $this->get('leadsfactory.form_repository')->find($id);

        $cacheFileName = "../app/cache/templates/".$id.".js";
        if (file_exists( $cacheFileName )) {
            unlink ($cacheFileName);
        }

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
        $old = $this->get('leadsfactory.form_repository')->find($id);

        $em = $this->getDoctrine()->getManager();
        $new = clone $old;
        $new->setCode('');
        $em->persist($new);
        $em->flush();

        return $this->redirect($this->generateUrl('_form_edit', array('id' => $new->getId())));
    }
}
