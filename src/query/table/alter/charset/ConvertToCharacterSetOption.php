<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\charset;

class ConvertToCharacterSetOption extends CharacterSetOption
{

	public function toSQL(): string
	{
		return "convert to " . parent::toSQL();
	}
}
