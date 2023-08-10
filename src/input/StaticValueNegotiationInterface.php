<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

interface StaticValueNegotiationInterface
{

	static function negotiateValueStatic(InputInterface $input, Datum $column);
}
