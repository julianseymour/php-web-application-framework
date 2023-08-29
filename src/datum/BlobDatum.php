<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

class BlobDatum extends StringDatum{

	public function getHumanWritableValue(){
		if ($this->getNeverLeaveServer()) {
			return null;
		}
		return $this->getValue();
	}

	public function getUrlEncodedValue(){
		return urlencode($this->getValue());
	}

	public function getConstructorParams(): ?array{
		return [
			$this->getColumnName()
		];
	}

	public function getColumnTypeString(): string{
		return "BLOB";
	}
}
