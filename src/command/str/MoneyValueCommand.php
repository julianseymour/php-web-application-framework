<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class MoneyValueCommand extends StringTransformationCommand
{

	protected $symbol;

	public function __construct($symbol, $subject)
	{
		parent::__construct($subject);
		$this->setSymbol($symbol);
	}

	public function setSymbol($symbol)
	{
		return $this->symbol = $symbol;
	}

	public function hasSymbol()
	{
		return isset($this->symbol);
	}

	public function getSymbol()
	{
		$f = __METHOD__; //MoneyValueCommand::getShortClass()."(".static::getShortClass().")->getSymbol()";
		if (! $this->hasSymbol()) {
			Debug::error("{$f} symbol is undefined");
		}
		return $this->symbol;
	}

	public static function getCommandId(): string
	{
		return "MoneyValue";
	}

	public function evaluate(?array $params = null)
	{
		return (new ConcatenateCommand($this->getSymbol(), new FloatPrecisionCommand($this->getSubject(), 2)))->evaluate();
	}
}
