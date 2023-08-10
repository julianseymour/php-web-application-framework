<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

trait HumanReadableNameTrait
{

	protected $humanReadableName;

	public function hasHumanReadableName()
	{
		return ! empty($this->humanReadableName) || $this instanceof StaticHumanReadableNameInterface;
	}

	public function setHumanReadableName($hrvn)
	{
		return $this->humanReadableName = $hrvn;
	}

	public function getHumanReadableName()
	{
		$f = __METHOD__; //"HumanReadableNameTrait(".static::getShortClass().")->getHumanReadableName()";
		if (! isset($this->humanReadableName)) {
			if ($this instanceof StaticHumanReadableNameInterface) {
				return $this->getHumanReadableNameStatic($this);
			}
			if ($this instanceof Datum) {
				$cn = $this->getColumnName();
				$dsc = $this->getDataStructureClass();
				Debug::error("{$f} human readable name is undefined for column \"{$cn}\" of class \"{$dsc}\"");
			}
			Debug::error("{$f} human readable name is undefined");
		}
		return $this->humanReadableName;
	}
}
