<?php
namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

class AttributeContainsSelector extends AttributeSelector
{

	public static function echoOperator()
	{
		echo "*";
		parent::echoOperator();
	}
}