<?php

namespace Task;

use Mage\Task\AbstractTask;

class HackVendor extends AbstractTask
{
    public function getName()
    {
        return 'Hack vendors';
    }

    public function run()
    {
        //Fix Twilio SSL bug
        $result = $this->runCommand('cp /data/apps/leads-factory/shared/vendor/twilio/sdk/Services/Twilio.php /data/apps/leads-factory/current/vendor/twilio/sdk/Services/Twilio.php');

        return $result;
    }
}