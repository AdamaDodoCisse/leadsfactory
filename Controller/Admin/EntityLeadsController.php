<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;
use Tellaw\LeadsFactoryBundle\Form\Type\LeadsType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;

/**
 * @Route("/entity")
 */
class EntityLeadsController extends CoreController
{

    public function __construct () {
        parent::__construct();
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/leads/list/{page}/{limit}/{keyword}", name="_leads_list")
     */
    public function indexAction(Request $request, $page=1, $limit=25, $keyword='')
    {

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }



        $filterForm = $this->getLeadsFilterForm();
	    $filterForm->handleRequest($request);

	    $filterParams =  array ('user'=>$this->getUser());

	    if ($filterForm->isValid()) {
		    $filterParams[] = $filterForm->getData();
		    $list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
	    }else{
		    $list = $this->getList('TellawLeadsFactoryBundle:Leads', $page, $limit, $keyword, $filterParams);
	    }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Leads:list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options'],
	            'filters_form'  => $filterForm->createView(),
	            'export_form'   => $this->getReportForm($filterParams)->createView()
            )
        );
    }

    /**
     * @Route("/leads/edit/{id}", name="_leads_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->find($id);

        $type = new LeadsType();

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

            return $this->redirect($this->generateUrl('_leads_list'));
        }

        return $this->render('TellawLeadsFactoryBundle:entity/Leads:edit.html.twig', array(  'form' => $form->createView(),
                                                                                             'title' => "Edition d'un leads"));
    }

	/**
	 * Returns the leads list filtering form
	 *
	 * @return Form
	 */
	protected function getLeadsFilterForm()
	{
		$form = $this->createFormBuilder(array())
			->setMethod('GET')
			->setAction($this->generateUrl('_leads_list'))
			->add('form', 'choice', array(
					'choices'   => $this->getUserFormsOptions(),
					'label'     => 'Formulaire',
					'required'  => false
				)
			)
			->add('firstname', 'text', array('label' => 'Prénom', 'required' => false))
			->add('lastname', 'text', array('label' => 'Nom', 'required' => false))
			->add('email', 'text', array('label' => 'E-mail', 'required' => false))
			->add('datemin', 'date', array('label' => 'Date de début', 'widget'=>'single_text', 'required' => false))
			->add('datemax', 'date', array('label' => 'Date de fin', 'widget'=>'single_text', 'required' => false))
			->add('keyword', 'text', array('label' => 'Mot-clé', 'required' => false))
			->add('valider', 'submit', array('label' => 'Valider'))
		    ->getForm();

		return $form;
	}

	/**
	 * Returns the user's scope forms list options
	 *
	 * @return array
	 */
	protected function getUserFormsOptions()
	{
		$forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->getUserForms($this->getUser());
		$options = array('' => 'Sélectionnez un formulaire');
		foreach($forms as $form){
			$options[$form->getId()] = $form->getName();
		}
		return $options;
	}

	/**
	 * Builds the report form
	 *
	 * @param array $filterParams
	 *
	 * @return Form
	 */
	protected function getReportForm($filterParams)
	{
		$form = $this->createFormBuilder(array())
			->setMethod('GET')
		    ->setAction($this->generateUrl('_leads_report'))
		    ->add('format', 'choice', array(
	            'choices' => array('csv' => 'CSV'),
	            'label' => 'Format',
	        )
	    )
			->add('filterparams', 'hidden', array('data' => json_encode($filterParams)))
			->add('valider', 'submit', array('label' => 'Valider'))
	        ->getForm();

		return $form;
	}

	/**
	 * Generates the report
	 *
	 * @Secure(roles="ROLE_USER")
	 * @Route("/leads/report", name="_leads_report")
	 */
	public function reportAction(Request $request)
	{
		$params = $request->query->get('form');
		$filterParams = json_decode($params['filterparams'], true);
		$format = $params['format'];

		$reportMethod = 'generate'.ucfirst($format);
		 return $this->$reportMethod($filterParams);
	}

	/**
	 * Generates CSV report
	 *
	 * @param array $filterParams
	 *
	 * @return Response
	 */
	public function generateCsv($filterParams)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$leads = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Leads')->getIterableList($filterParams);

		$handle = fopen('php://memory', 'w');

		fputcsv($handle, array('id', 'Form', 'Date', 'Firstname', 'LastName', 'Email', 'Phone', 'Content'), ';');

		while (false !== ($row = $leads->next())) {
			fputcsv($handle, array(
					$row[0]->getId(),
					$row[0]->getForm()->getName(),
					$row[0]->getCreatedAt()->format('Y-m-d H:i:s'),
					$row[0]->getFirstname(),
					$row[0]->getLastname(),
					$row[0]->getEmail(),
					$row[0]->getTelephone(),
					$row[0]->getData()
				),
				';');

			$em->detach($row[0]);
		}

		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);

		$response =  new Response($content);
		$response->headers->set('content-type', 'text/csv');
		$response->headers->set('Content-Disposition', 'attachment; filename=leads_report.csv');

		return $response;
	}
}
