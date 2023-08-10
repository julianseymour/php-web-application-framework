<?php
namespace JulianSeymour\PHPWebApplicationFramework\style;

class CssMediaQuery extends CssRule
{

	public function __toString(): string
	{
		$string = "@media and only screen and ({$property}){";
		foreach ($this->getChildNodes() as $rule) {
			$string .= $rule->__toString();
		}
		$string .= "}\n";
		return $string;
	}
}
