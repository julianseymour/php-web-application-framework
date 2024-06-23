<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

class MinimumCommand extends ExtremeValueCommand{
	
	public static function getCommandId(){
		return "min";
	}
	
	public static function compare($param, $min){
		return $param < $min;
	}
}