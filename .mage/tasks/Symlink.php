<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 30/01/15
 */

namespace Task;

use Mage\Task\AbstractTask;

class Symlink extends AbstractTask
{
	public function getName()
	{
		return 'Put symlinks back';
	}

	public function run()
	{
		$result = $this->runCommandLocal('rm -Rf web/bundles/tellawleadsfactory');
		$result = $this->runCommandLocal('rm -Rf vendor/tellaw/LeadsFactoryBundle/Tellaw/LeadsFactoryBundle');

		$result = $this->runCommandLocal('ln -s ../../../leadsfactory/Resources/public web/bundles/tellawleadsfactory');
		$result = $this->runCommandLocal('mkdir -p vendor/tellaw/LeadsFactoryBundle/Tellaw');
		$result = $this->runCommandLocal('ln -s ../../../../../leadsfactory vendor/tellaw/LeadsFactoryBundle/Tellaw/LeadsFactoryBundle');

		return true;
	}
}
