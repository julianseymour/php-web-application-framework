<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;

class TextDatum extends FullTextStringDatum{

	use CharacterSetTrait;

	public function getHumanWritableValue(){
		if($this->getNeverLeaveServer()) {
			return null;
		}
		return $this->getValue();
	}

	public function getColumnTypeString(): string{
		$string = "text";
		if($this->hasCharacterSet()) {
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

	public function setValue($v){
		$f = __METHOD__;
		$print = false;
		if($v == "[object Object]") {
			$cn = $this->getName();
			Debug::print("{$f} Value \"[object Object]\". Column name is \"{$cn}\". Remote IP address is {$_SERVER['REMOTE_ADDR']}");
		}
		return parent::setValue($v);
	}
}
