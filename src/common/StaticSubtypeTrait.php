<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

trait StaticSubtypeTrait{
	
	public abstract static function getSubtypeStatic():string;
	
	public function getSubtype():string{
		return static::getSubtypeStatic();
	}
	
	public function hasSubtype():bool{
		return true;
	}
}
