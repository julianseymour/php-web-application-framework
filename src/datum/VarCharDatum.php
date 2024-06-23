<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;

class VarCharDatum extends CharDatum
{

	use CharacterSetTrait;

	public function getColumnTypeString(): string
	{
		$string = "var" . parent::getColumnTypeString();
		if($this->hasCharacterSet()){
			$charset = $this->getCharacterSet();
			$string .= " character set {$charset}";
		}
		return $string;
	}
}
