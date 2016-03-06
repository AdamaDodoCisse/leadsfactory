<?php
namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\Entreprise;
use Tellaw\LeadsFactoryBundle\Form\Type\EntrepriseType;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Tellaw\LeadsFactoryBundle\Controller\AbstractController\ApplicationCrudController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/entity/entreprise")
 */
class EntityEntrepriseController extends CoreController {

    /**
     *
     * @Route("/list/{page}/{limit}/{keyword}", name="_entreprise_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction($page=1, $limit=10, $keyword='')
    {

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList ('TellawLeadsFactoryBundle:Entreprise', $page, $limit, $keyword, array ('user'=>$this->getUser()));

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Entreprise:entity_list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );

    }

    /**
     * @Route("/new", name="_entreprise_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction( Request $request )
    {

        $form = $this->createForm(
            new EntrepriseType(),
            null,
            array('method' => 'POST')
        );

        $formEntity = new Entreprise();

        $form->handleRequest($request);
        if ($form->isValid()) {

            if (!$this->get("core_manager")->isNewFormAccepted ()) {
                return $this->redirect($this->generateUrl('_security_licence_error'));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_entreprise_list'));
        }
        return $this->render(
            'TellawLeadsFactoryBundle:entity/Entreprise:entity_edit.html.twig',
            array(
                'form' => $form->createView(),
                'formObj' => $formEntity,
                'title' => "Création d'une Société"
            )
        );

    }

    /**
     * @Route("/edit/{id}", name="_entreprise_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        $formEntity = $this->get('leadsfactory.entreprise_repository')->find($id);

        $form = $this->createForm(
            new EntrepriseType(),
            $formEntity,
            array('method' => 'POST')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

        }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Entreprise:entity_edit.html.twig',
            array(
                'id' => $id,
                'formObj' => $formEntity,
                'form' => $form->createView(),
                'title' => "Edition d'une société"
            )
        );

    }

    /**
     * @Route("/delete/id/{id}", name="_entreprise_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ( $id ) {

    }

}
