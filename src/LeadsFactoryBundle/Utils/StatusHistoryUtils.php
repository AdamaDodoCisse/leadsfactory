<?php
namespace LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use LeadsFactoryBundle\Shared\StatusHistoryUtilsShared;


/**
 * Class StatusHistoryUtils
 * @package LeadsFactoryBundle\Utils
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
