<?php
namespace JulianSeymour\PHPWebApplicationFramework\style;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use Exception;

class CssKeyframesRule extends CssRule
{

	use NamedTrait;

	public function __construct($name = null, ...$rules)
	{
		$f = __METHOD__; //CssKeyframesRule::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct();
		if (! empty($name)) {
			$this->setName($name);
		}
		if ($rules !== null && count($rules) > 0) {
			foreach ($rules as $rule) {
				$this->appendChild($rule);
			}
		}
	}

	/*
	 * public function appendChild($child){
	 * $f = __METHOD__; //CssKeyframesRule::getShortClass()."(".static::getShortClass().")->appendChild()";
	 * if(!$child instanceof CssRule){
	 * Debug::error("{$f} child nodes must by instances of CssRule");
	 * }
	 * return parent::appendChild($child);
	 * }
	 */
	public function echo(bool $destroy = false): void
	{
		$f = __METHOD__; //CssKeyframesRule::getShortClass()."(".static::getShortClass().")->echo()";
		try {
			echo "@keyframes ";
			echo $this->getName();
			echo "{\n";
			foreach ($this->getChildNodes() as $rule) {
				echo "\t";
				$rule->echo($destroy);
				echo "\n";
			}
			echo "}\n";
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->name);
	}
}
