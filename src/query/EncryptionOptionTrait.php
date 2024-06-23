<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait EncryptionOptionTrait{

	use DatabaseVersionTrait;

	protected $encryptionOption;

	public function setEncryption($value){
		$f = __METHOD__;
		if($value === null){
			unset($this->encryptionOption);
		}elseif(is_bool($value)){
			if($value){
				$value = "y";
			}else{
				$value = "n";
			}
		}elseif(!is_string($value)){
			Debug::error("{$f} invalid non-string value");
		}
		$value = strtolower($value);
		if($value !== 'y' && $value !== 'n'){
			Debug::error("{$f} invalid string value \"{$value}\"");
		}elseif($this->hasEncryption()){
			$this->release($this->encryptionOption);
		}
		$this->setRequiredMySQLVersion("8.0.16");
		return $this->encryptionOption = $this->claim($value);
	}

	public function hasEncryption():bool{
		return isset($this->encryption);
	}

	public function getEncryption(){
		$f = __METHOD__;
		if(!$this->hasEncryption()){
			Debug::error("{$f} encryption option is undefined");
		}
		return $this->encryptionOption;
	}

	public function encryption($value){
		$this->setEncryption($value);
		return $this;
	}
}