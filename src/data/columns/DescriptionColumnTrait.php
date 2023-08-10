<?php
namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;

trait DescriptionColumnTrait
{

	use MultipleColumnDefiningTrait;

	public function setDescription($value)
	{
		return $this->setColumnValue("description", $value);
	}

	public function getDescription()
	{
		return $this->getColumnValue("description");
	}

	public function hasDescription()
	{
		return $this->hasColumnValue("description");
	}
}
