<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Journal;

use ICanBoogie\Operation;

class Module extends \Icybee\Module
{
	public function log_operation(Operation $operation, $severity=Entry::SEVERITY_INFO, $link=null)
	{
		$entry = Entry::from($operation);
		$entry->severity = $severity;
		$entry->link = $link;
		$entry->save();
	}
}