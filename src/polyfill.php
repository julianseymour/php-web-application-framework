<?php

namespace JulianSeymour\PHPWebApplicationFramework;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

if (! function_exists("array_key_last")) {

	function array_key_last(array $array)
	{
		if (! is_array($array) || empty($array)) {
			return NULL;
		}
		return array_keys($array)[count($array) - 1];
	}
}

if (! function_exists('str_contains')){
	function str_contains(string $haystack, string $needle): bool{
		Debug::limitExecutionDepth(255);
		Debug::checkMemoryUsage("", 64000000);
		return $needle !== '' && mb_strpos($haystack, $needle) !== false;
	}
}
