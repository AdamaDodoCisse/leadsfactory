<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 02/03/15
 */


namespace Tellaw\LeadsFactoryBundle\DependencyInjection;


interface TimeConfiguratorAwareInterface
{
    public function setTime(\DateTime $date);
}
