<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

class BitDatum extends UnsignedIntegerDatum
{

	public function getColumnTypeString()
	{
		$string = "bit";
		if($this->hasBitCount()){
			$string .= "(" . $this->getBitCount() . ")";
		}
		return $string;
	}
}