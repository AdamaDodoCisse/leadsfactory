<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 16/04/15
 */

namespace Task;

use Mage\Task\AbstractTask;

class ComposerInstall extends AbstractTask
{
    public function getName()
    {
        return 'composer install';
    }

    public function run()
    {
        $dev = $this->getParameter('dev', true);
        return $this->runCommand('composer install --ignore-platform-reqs -o' . ($dev ? ' --dev' : ' --no-dev'));
    }
}
