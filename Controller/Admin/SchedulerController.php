<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tellaw\LeadsFactoryBundle\Entity\CronTask;
use Tellaw\LeadsFactoryBundle\Entity\Scope;
use Tellaw\LeadsFactoryBundle\Form\Type\SchedulerType;
use Tellaw\LeadsFactoryBundle\Form\Type\SchedulerNewType;
use Tellaw\LeadsFactoryBundle\Form\Type\SchedulerReadOnlyType;
use Tellaw\LeadsFactoryBundle\Form\Type\ScopeType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;


/**
 * Scope controller.
 *
 * @Route("/scheduler")
 */
class SchedulerController extends CoreController
{

    public function __construct () {
        parent::__construct();

    }

    /**
     * Lists all Scope entities.
     *
     * @Route("/list/{page}/{limit}/{keyword}", name="_scheduler_list")
     *
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction($page=1, $limit=10, $keyword='')
    {

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList ('TellawLeadsFactoryBundle:CronTask', $page, $limit, $keyword, array ('user_id'=>$this->getUser()->getId()));

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Scheduler:index.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );

    }
    /**
     * Creates a new Scope entity.
     *
     * @Route("/new", name="_scheduler_new")
     * @Secure(roles="ROLE_USER")
     */
    public function newAction(Request $request)
    {
        $entity = new CronTask();

        $form = $this->createForm(new SchedulerNewType(), $entity, array(
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('_scheduler_list'));
        }

        return $this->render( "TellawLeadsFactoryBundle:entity:Scheduler/edit.html.twig", array(
            'title' => 'Ajouter un scope',
            'form'   => $form->createView(),
            'item'   => new CronTask()
        ));

    }

    /**
     * @Route("/edit/{id}", name="_scheduler_edit")
     * @Secure(roles="ROLE_USER")
     */
    public function editAction( Request $request, $id )
    {
        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $data = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:CronTask')->find($id);

        if ($data->getServiceName() != "") {

            $form = $this->createForm(  new SchedulerReadOnlyType(),
                $data,
                array(
                    'method' => 'POST'
                )
            );

        } else {

            $form = $this->createForm(  new SchedulerType(),
                $data,
                array(
                    'method' => 'POST'
                )
            );

        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            // fait quelque chose comme sauvegarder la tâche dans la bdd

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_scheduler_list'));
        }

        return $this->render("TellawLeadsFactoryBundle:entity/Scheduler:edit.html.twig",
                array(  'form' => $form->createView(),
                        'title' => "Edition d'une tâche planifiée",
                        'item' => $data
        ));

    }


    /**
     * @Route("/delete/{id}", name="_scheduler_delete")
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

        return $this->redirect($this->generateUrl('_scheduler_list'));

    }
}
