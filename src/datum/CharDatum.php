<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

class CharDatum extends FullTextStringDatum{

	public function __construct($name=null, $i=null){
		parent::__construct($name);
		if($i !== null){
			$this->setMaximumLength($i);
		}
	}

	public function getConstructorParams(): ?array{
		return [
			$this->getName(),
			$this->getMaximumLength()
		];
	}

	public function getColumnTypeString(): string{
		$charCount = $this->getMaximumLength();
		$string = "char ({$charCount})";
		return $string;
	}

	public function getUrlEncodedValue(){
		return $this->getValue();
	}

	public function getHumanReadableValue(){
		return $this->getValue();
	}

	public function getHumanWritableValue(){
		return $this->getValue();
	}
}
