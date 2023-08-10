<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

class DropIndexOption extends IndexNameOption
{

	public function toSQL(): string
	{
		return "drop index " . $this->getIndexName();
	}
}
