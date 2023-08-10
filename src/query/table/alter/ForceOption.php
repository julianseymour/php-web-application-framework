<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

class ForceOption extends AlterOption
{

	public function toSQL(): string
	{
		return "force";
	}
}
