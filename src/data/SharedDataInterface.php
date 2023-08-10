<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

interface SharedDataInterface
{

	static function getForeignKeyNamesAsSharedDataStructure(): array;
}