<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Shared\LFUtilsShared;

class LFUtils extends LFUtilsShared
{

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function calculateUid($value)
    {

        return $value;

    }

}

