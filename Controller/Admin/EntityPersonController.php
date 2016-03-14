<?php
namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Tellaw\LeadsFactoryBundle\Entity\Person;
use Tellaw\LeadsFactoryBundle\Form\Type\PersonType;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Controller\AbstractController\ApplicationCrudController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/entity/person")
 */
class EntityPersonController extends CoreController {


    /**
     *
     * @Route("/list/{page}/{limit}/{keyword}", name="_person_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction($page=1, $limit=10, $keyword='')
    {

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList ('TellawLeadsFactoryBundle:Person', $page, $limit, $keyword, array ('user'=>$this->getUser()));

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Person:entity_list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );

    }

    /**
     * @Route("/new", name="_person_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction( Request $request )
    {

        $form = $this->createForm(
            new PersonType(),
            null,
            array('method' => 'POST')
        );

        $formEntity = new Person();

        $form->handleRequest($request);
        if ($form->isValid()) {

            if (!$this->get("core_manager")->isNewFormAccepted ()) {
                return $this->redirect($this->generateUrl('_security_licence_error'));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_person_list'));
        }
        return $this->render(
            'TellawLeadsFactoryBundle:entity/Person:entity_edit.html.twig',
            array(
                'form' => $form->createView(),
                'formObj' => $formEntity,
                'title' => "Création d'une Personne"
            )
        );

    }

    /**
     * @Route("/edit/{id}", name="_person_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        $formEntity = $this->get('leadsfactory.person_repository')->find($id);

        $form = $this->createForm(
            new PersonType(),
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
            'TellawLeadsFactoryBundle:entity/Person:entity_edit.html.twig',
            array(
                'id' => $id,
                'formObj' => $formEntity,
                'form' => $form->createView(),
                'title' => "Edition d'une personne"
            )
        );

    }

    /**
     * @Route("/delete/id/{id}", name="_person_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ( $id ) {

    }

    /**
     * @Route("/fragment/list/{entrepriseId}/{page}/{limit}/{keyword}", name="_person_fragment_list")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function fragmentPersonsTableAction ( $entrepriseId = 0,$page=1, $limit=10, $keyword='' ) {

        $list = $this->getList ('TellawLeadsFactoryBundle:Person', $page, $limit, $keyword, array ('user'=>$this->getUser()));

        // Get array of persons
        return $this->render(
            'TellawLeadsFactoryBundle:entity/Person:fragment_list.html.twig',
            array(
                'entrepriseId'  => $entrepriseId,
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options'],
                'keyword'       => $keyword
            )
        );

    }

    /**
     * @Route("/fragment/list-in-entreprise/{entrepriseId}", name="_person_fragment_list_in_entreprise")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function fragmentPersonsInEntrepriseTableAction ( $entrepriseId ) {



        $formEntity = $this->get('leadsfactory.entreprise_repository')->find($entrepriseId);
        $list = $formEntity->getPersons();

        // Get array of persons

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Person:fragment_list_in_entreprise.html.twig',
            array(
                'elements'      => $list
            )
        );

    }

}
