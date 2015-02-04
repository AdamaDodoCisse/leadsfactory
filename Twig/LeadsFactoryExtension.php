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
				$options = $list->getElements()->getValues();
				$params['options'] = $options;
			}
		}

		return $this->form_helper->renderTag(null, $params);
	}

	public function getName()
	{
		return 'leadsfactory_extension';
	}
}
