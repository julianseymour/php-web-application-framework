<?php

namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;

trait NameColumnTrait{

	public function getName():string{
		$f = __METHOD__;
		if(!$this->hasName()){
			Debug::error("{$f} name is undefined");
		}
		return $this->getColumnValue('name');
	}

	public function setName(string $name): string{
		$f = __METHOD__;
		if($this->hasColumn("normalizedName")) {
			$this->setNormalizedName(NameDatum::normalize($name));
		}
		return $this->setColumnValue("name", $name);
	}

	public function hasName(): bool{
		return $this->hasColumnValue("name") && $this->getColumnValue("name") !== "";
	}
}
