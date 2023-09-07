<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

interface StaticSubtypeInterface extends SubtypeInterface{
	
	static function getSubtypeStatic():string;
}
