<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Shared\JsUtilsShared;

class JsUtils extends JsUtilsShared
{

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    public $container;

    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }

}
