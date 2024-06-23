<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\FloatPrecisionCommand;

class MoneyDatum extends DoubleDatum
{

	public function getValue()
	{
		if(!$this->hasValue()){
			return 0.0;
		}
		return parent::getValue();
	}

	public function getHumanReadableValue($symbol = null)
	{
		if($symbol === null){
			return parent::getHumanReadableValue();
		}
		$value = $this->getValue();
		if($value < 0){
			$value *= - 1;
			return new ConcatenateCommand("-{$symbol}", new FloatPrecisionCommand($value, 2));
		}
		return new ConcatenateCommand($symbol, new FloatPrecisionCommand($value, 2));
	}
}
