<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

interface StaticValueNegotiationInterface
{

	static function negotiateValueStatic(InputlikeInterface $input, Datum $column);
}
