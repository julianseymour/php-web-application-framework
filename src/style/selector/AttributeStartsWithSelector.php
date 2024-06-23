<?php

namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

class AttributeStartsWithSelector extends AttributeSelector{

	public static function echoOperator():void{
		echo "^";
		parent::echoOperator();
	}
}