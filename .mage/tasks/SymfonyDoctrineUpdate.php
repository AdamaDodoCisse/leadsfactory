<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 30/01/15
 */

namespace Task;

use Mage\Task\AbstractTask;

class SymfonyDoctrineUpdate extends AbstractTask
{
	public function getName()
	{
		return 'Update doctrine schema';
	}

	public function run()
	{
		$result = $this->runCommand('php app/console doctrine:schema:update --force');
		
		return $result;
	}
}
