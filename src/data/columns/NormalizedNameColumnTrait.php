<?php
namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;

trait NormalizedNameColumnTrait
{

	use NameColumnTrait;

	public function getNormalizedName(): string
	{
		$f = __METHOD__; //"NormalizedNameColumnTrait(".static::getShortClass().")->getNormalizedName()";
		if(!$this->hasName()){
			$num = $this->getSerialNumber();
			Debug::error("{$f} name is undefined; number is \"{$num}\"");
		}
		return NameDatum::normalize($this->getName());
	}

	public function hasNormalizedName(): bool
	{
		return $this->hasColumnValue("normalizedName") && $this->getColumnValue("normalizedName") !== "";
	}

	public function setNormalizedName(string $sn): string
	{
		return $this->setColumnValue('normalizedName', $sn);
	}
}
