<?php

namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

class AttributeContainsSelector extends AttributeSelector{

	public static function echoOperator():void{
		echo "*";
		parent::echoOperator();
	}
}