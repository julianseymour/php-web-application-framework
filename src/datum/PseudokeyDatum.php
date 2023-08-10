<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

class PseudokeyDatum extends Sha1HashDatum
{

	public function generate(): int
	{
		$this->setValue(sha1(uniqid($this->getDataStructure()
			->getDataType() . ".", true)));
		return SUCCESS;
	}
}
