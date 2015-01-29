<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 28/01/15
 */

namespace Tellaw\LeadsFactoryBundle\Twig;

use \Tellaw\LeadsFactoryBundle\Utils\FormUtils;

class LeadsFactoryExtension extends \Twig_Extension
{
	/** @var  FormUtils */
	private $form_helper;

	public function __construct(FormUtils $form_helper)
	{
		$this->form_helper = $form_helper;
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('field', array($this, 'field'), array('is_safe' => array('html'))),
		);
	}

	public function field($params)
	{
		return $this->form_helper->renderTag(null, $params);
	}

	public function getName()
	{
		return 'leadsfactory_extension';
	}
}
