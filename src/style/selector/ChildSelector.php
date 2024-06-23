<?php

namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

class ChildSelector extends BinarySelector{

	public static function echoOperator():void{
		echo "> ";
	}
}