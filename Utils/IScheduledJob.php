<?php

namespace Tellaw\LeadsFactoryBundle\Utils;

interface IScheduledJob {

    public function getExpression ();
    public function getName();
    public function getCommands();
    public function getEnabled();

}