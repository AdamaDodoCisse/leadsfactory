<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 03/02/15
 */

namespace Task;

use Mage\Task\AbstractTask;

class Chmod extends AbstractTask
{
	public function getName()
	{
		return 'Chmod -R 775 on remote directory with sudo';
	}

	public function run()
	{
		$result1 = $this->runCommandRemote('sudo chmod -R 775 .');
		return $result1;
	}
}
