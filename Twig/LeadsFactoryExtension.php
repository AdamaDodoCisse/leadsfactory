<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 28/01/15
 */

namespace Tellaw\LeadsFactoryBundle\Twig;

use \Tellaw\LeadsFactoryBundle\Utils\FormUtils;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListRepository;

class LeadsFactoryExtension extends \Twig_Extension
{
	/** @var  FormUtils */
	private $form_helper;

	/** @var  ReferenceListRepository */
	private $reference_list_repository;

	public function __construct(FormUtils $form_helper, ReferenceListRepository $reference_list_repository)
	{
		$this->form_helper = $form_helper;
		$this->reference_list_repository = $reference_list_repository;
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('field', array($this, 'field'), array('is_safe' => array('html'))),
		);
	}

	public function field($params)
	{
		if (isset($params['attributes']['data-list'])) {

			if (isset($params['attributes']['data-parent'])) {
				$params['options'] = false;
			} else {
				$listCode = $params['attributes']['data-list'];
				$list = $this->reference_list_repository->findOneBy(array('code' => $listCode));
				if ($list !== null) {
					$options = $list->getElements()->getValues();
					$params['options'] = $options;
				}
			}

		}

		//classes du champ
		$class = 'input input-'.$params['type'];
		if(isset($params['attributes']['class'])){
			$class = $params['attributes']['class'] . ' '. $class;
		}

		if(!empty($params['attributes']['data-parent'])){
			$class = 'child-list ' . $class;
		}
		$params['attributes']['class'] = $class;

		//classe(s) de validation
		if(isset($params['attributes']['validator']))
			$params['attributes']['class'] .= ' validate['.$params['attributes']['validator'].']';

		return $this->form_helper->renderTag(null, $params);

	}

	public function getName()
	{
		return 'leadsfactory_extension';
	}
}
