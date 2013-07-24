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

class ManageBlock extends \Icybee\ManageBlock
{
	public function __construct(Module $module, array $attributes=array())
	{
		parent::__construct
		(
			$module, $attributes + array
			(
				self::T_ORDER_BY => array('timestamp', 'desc')
			)
		);
	}

	protected function get_available_columns()
	{
		return array
		(
			'message' =>   __CLASS__ . '\MessageColumn',
			'severity' =>  __CLASS__ . '\SeverityColumn',
			'type' =>      __CLASS__ . '\TypeColumn',
			'class' =>     __CLASS__ . '\ClassColumn',
			'uid' =>       'Icybee\Modules\Users\ManageBlock\UserColumn',
			'timestamp' => 'Icybee\ManageBlock\DateTimeColumn'
		);
	}
}

namespace Icybee\Modules\Journal\ManageBlock;

use ICanBoogie\ActiveRecord\Query;

use Icybee\ManageBlock\Column;
use Icybee\ManageBlock\FilterDecorator;
use Icybee\Modules\Journal\Entry;

/**
 * Representation of the `message` column.
 */
class MessageColumn extends Column
{
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=array())
	{
		parent::__construct
		(
			$manager, $id, $options + array
			(
				'discreet' => true
			)
		);
	}

	public function render_cell($record)
	{
		return $record->{ $this->id };
	}
}

/**
 * Representation of the `severity` column.
 */
class SeverityColumn extends Column
{
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=array())
	{
		parent::__construct
		(
			$manager, $id, $options + array
			(
				'discreet' => true,
				'filters' => array
				(
					'options' => array
					(
						'=' . Entry::SEVERITY_DEBUG => 'Debug',
						'=' . Entry::SEVERITY_INFO => 'Info',
						'=' . Entry::SEVERITY_WARNING => 'Warning',
						'=' . Entry::SEVERITY_DANGER => 'Danger'
					)
				)
			)
		);
	}

	public function render_cell($record)
	{
		static $labels = array
		(
			Entry::SEVERITY_DEBUG => '<span class="label label-debug">debug</span>',
			Entry::SEVERITY_INFO => '<span class="label label-info">info</span>',
			Entry::SEVERITY_WARNING => '<span class="label label-warning">warning</span>',
			Entry::SEVERITY_DANGER => '<span class="label label-danger">danger</span>'
		);

		$value = $record->{ $this->id };
		$label = $labels[$value];

		return new FilterDecorator($record, $this->id, $this->is_filtering, $label);
	}
}

/**
 * Representation of the `type` column.
 */
class TypeColumn extends Column
{
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=array())
	{
		parent::__construct
		(
			$manager, $id, $options + array
			(
				'discreet' => true
			)
		);
	}

	public function render_cell($record)
	{
		return new FilterDecorator($record, $this->id, $this->is_filtering, $this->t($record->{ $this->id }, array(), array('scope' => 'type')));
	}
}

/**
 * Representation of the `class` column.
 */
class ClassColumn extends Column
{
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=array())
	{
		parent::__construct
		(
			$manager, $id, $options + array
			(
				'discreet' => true
			)
		);
	}

	public function alter_query_with_filter(Query $query, $filter_value)
	{
		if ($filter_value)
		{
			list($type, $name) = explode(':', $filter_value);

			if ($type == 'operation')
			{
				$query->and('class LIKE ?', '%\\' . $name . 'Operation');
			}
		}

		return $query;
	}

	public function render_cell($record)
	{
		$property = $this->id;
		$class_name = $record->$property;

		if (is_subclass_of($class_name, 'ICanBoogie\Operation'))
		{
			$path = strtr($class_name, '\\', '/');
			$basename = basename($path, 'Operation');

			return new FilterDecorator($record, $property, $this->is_filtering, $basename, 'operation:' . $basename);
		}
	}
}