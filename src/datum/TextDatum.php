<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;

class TextDatum extends FullTextStringDatum{

	use CharacterSetTrait;

	public function getHumanWritableValue(){
		if($this->getNeverLeaveServer()){
			return null;
		}
		return $this->getValue();
	}

	public function getColumnTypeString(): string{
		$string = "text";
		if($this->hasCharacterSet()){
			$charset = $this->getCharacterSet();
			$string .= " character set {$charset}";
		}
		return $string;
	}

	public function getConstructorParams(): ?array{
		return [
			$this->getName()
		];
	}

	public function getUrlEncodedValue(){
		return urlencode($this->getValue());
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->characterSet, $deallocate);
	}
}
