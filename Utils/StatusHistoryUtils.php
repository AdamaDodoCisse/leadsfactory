<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Tellaw\LeadsFactoryBundle\Shared\StatusHistoryUtilsShared;


/**
 * Class StatusHistoryUtils
 * @package Tellaw\LeadsFactoryBundle\Utils
 *
 * Utils class which makes easier to manipulate StatusHistory objects
 */
class StatusHistoryUtils extends StatusHistoryUtilsShared
{

    public function __construct()
    {
        parent::__construct();
    }


}