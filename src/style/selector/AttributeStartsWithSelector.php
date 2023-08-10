<?php
namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

class AttributeStartsWithSelector extends AttributeSelector
{

	public static function echoOperator()
	{
		echo "^";
		parent::echoOperator();
	}
}