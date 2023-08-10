<?php
namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;

trait PriorityColumnTrait
{

	use MultipleColumnDefiningTrait;

	public function getPriority()
	{
		return $this->getColumnValue("priority");
	}

	public function hasPriority(): bool
	{
		return $this->hasColumnValue("priority");
	}

	public function setPriority($value)
	{
		return $this->setColumnValue("priority", $value);
	}

	public function ejectPriority()
	{
		return $this->ejectColumnValue("priority");
	}
}
