<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

abstract class Constraint extends Basic implements SQLInterface
{

	use SymbolicTrait;

	public function __construct($symbol = null)
	{
		parent::__construct();
		if($symbol !== null) {
			$this->setSymbol($symbol);
		}
	}

	public function toSQL(): string
	{
		if($this->hasSymbol()) {
			return "constraint " . $this->getSymbol() . " ";
		}
		return "";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->symbol);
	}
}
