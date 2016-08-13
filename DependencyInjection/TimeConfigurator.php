<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 02/03/15
 */


namespace Tellaw\LeadsFactoryBundle\DependencyInjection;


class TimeConfigurator
{
    public function configure(TimeConfiguratorAwareInterface $service)
    {
        $service->setTime(date_create());
    }
}
