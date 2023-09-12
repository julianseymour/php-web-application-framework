<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

class DecimalDatum extends FloatingPointDatum
{

	public function getColumnTypeString(): string
	{
		$string = "decimal";
		if($this->hasPrecision()) {
			$string .= "(" . $this->getPrecision();
			if($this->hasScale()) {
				$string .= "," . $this->getScale();
			}
			$string .= ")";
		}
		return $string;
	}
}