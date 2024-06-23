<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

class MaximumCommand extends ExtremeValueCommand{
	
	public static function getCommandId(){
		return "max";
	}
	
	public static function compare($param, $max){
		return $param > $max;
	}
}