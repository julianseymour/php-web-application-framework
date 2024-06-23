<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

class NameDatum extends TextDatum{

	public static function normalize(string $name): string{
		if($name === null){
			return '';
		}
		return preg_replace('/[^a-z0-9]+/', '_', strtolower($name));
	}
}
