<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use JulianSeymour\PHPWebApplicationFramework\query\column\VisibilityTrait;

class SetColumnVisibilityOption extends AlterColumnOption
{

	use VisibilityTrait;

	public function __construct($columnName, $visibility)
	{
		parent::__construct($columnName);
		$this->setVisibility($visibility);
	}

	public function toSQL(): string
	{
		return parent::toSQL() . "set " . $this->getVisibility();
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->visibility);
	}
}
