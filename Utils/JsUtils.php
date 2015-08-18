<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Shared\JsUtilsShared;
use Tellaw\LeadsFactoryBundle\Utils\Fields\EmailFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\TextFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\ReferenceListFieldType;
use Tellaw\LeadsFactoryBundle\Entity\Form as FormEntity;
use Tellaw\LeadsFactoryBundle\Utils\StringHelper;

class JsUtils extends JsUtilsShared {

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    public $container;

    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
    }

}
