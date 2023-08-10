<?php
namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class NegationSelector extends Selector
{

	private $negatedSelector;

	public function setNegatedSelector($negate_me)
	{
		return $this->negatedSelector = $negate_me;
	}

	public function __construct($negate_me)
	{
		parent::__construct();
		if ($negate_me !== null) {
			$this->setNegatedSelector($negate_me);
		}
	}

	public function hasNegatedSelector()
	{
		return isset($this->negatedSelector);
	}

	public function getNegatedSelector()
	{
		$f = __METHOD__; //NegationSelector::getShortClass()."(".static::getShortClass().")->getNegatedSelector()";
		try {
			if (! $this->hasNegatedSelector()) {
				Debug::error("{$f} negated selector is undefined");
			}
			return $this->negatedSelector;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echo(bool $destroy = false): void
	{
		echo ":not(";
		echo $this->getNegatedSelector();
		echo ")";
	}
}
