<?php

namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

class AttributeEndsWithSelector extends AttributeSelector{

	public static function echoOperator():void{
		echo "$";
		parent::echoOperator();
	}
}