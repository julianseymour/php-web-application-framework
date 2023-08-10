<?php

namespace JulianSeymour\PHPWebApplicationFramework\style;

use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;

trait StyleSheetPathTrait{
	
	public static function getStyleSheetPath():?string{
		$fn = get_class_filename(static::class);
		return substr($fn, 0, strlen($fn) - 3) . "css";
	}
}

