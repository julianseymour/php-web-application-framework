<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
ErrorMessage::unimplemented(__FILE__);

class GeometryDatum extends Datum
{
	public static function getTypeSpecifier()
	{}

	protected function getConstructorParams(): ?array
	{}

	public static function validateStatic($value): int
	{}

	public function parseValueFromQueryResult($raw)
	{}

	public function getHumanWritableValue()
	{}

	public function parseValueFromSuperglobalArray($value)
	{}

	public function getHumanReadableValue()
	{}

	public function getColumnTypeString(): string
	{}

	public static function parseString(string $string)
	{}

	public function getUrlEncodedValue()
	{}

}