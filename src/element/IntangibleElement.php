<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

abstract class IntangibleElement extends Element
{

	public function hasOnClickAttribute(): bool
	{
		return false;
	}
}
