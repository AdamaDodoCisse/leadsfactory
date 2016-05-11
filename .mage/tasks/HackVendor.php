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
        //return $this->runCommand('sed -i "/"curlopts" => array(/ a\\CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0 // hack" vendor/twilio/sdk/Services/Twilio.php');

        $result = $this->runCommandLocal('rm -Rf vendor/twilio');
        $result = $this->runCommandLocal('ln -s vendor/twilio /data/apps/leads-factory/shared/twilio');

        return true;
    }
}