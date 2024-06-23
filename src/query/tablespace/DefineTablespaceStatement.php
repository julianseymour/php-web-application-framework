<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\EncryptionOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\EngineAttributeTrait;
use function JulianSeymour\PHPWebApplicationFramework\release;

abstract class DefineTablespaceStatement extends TablespaceStatement{

	use AutoextendSizeTrait;
	use EncryptionOptionTrait;
	use EngineAttributeTrait;

	protected $datafilename;

	protected $initialSizeValue;

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->autoextendSizeValue, $deallocate);
		$this->release($this->datafilename, $deallocate);
		$this->release($this->encryptionOption, $deallocate);
		$this->release($this->engineAttributeString, $deallocate);
		$this->release($this->initialSizeValue, $deallocate);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"wait"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"wait"
		]);
	}
	
	public function setEncryption(?string $enc):?string{
		$f = __METHOD__;
		if(!is_string($enc)){
			Debug::error("{$f} encryption option must be a string");
		}
		$enc = strtolower($enc);
		if($enc !== 'y' && $enc !== 'n'){
			Debug::error("{$f} encryption can only be y or n for this statement");
		}elseif($this->hasEncryption()){
			$this->release($this->encryptionOption);
		}
		return $this->encryptionOption = $this->claim($enc);
	}
	
	// XXX An InnoDB tablespace supports only a single data file, whose name must include a .ibd extension.
	public function setDatafilename(?string $df):?string{
		$f = __METHOD__;
		if(!is_string($df)){
			Debug::error("{$f} datafile name must be a string");
		}elseif($this->hasDatafilename()){
			$this->release($this->datafilename);
		}
		return $this->datafilename = $this->claim($df);
	}

	public function hasDatafilename():bool{
		return isset($this->datafilename);
	}

	public function getDatafilename():string{
		$f = __METHOD__;
		if(!$this->hasDatafilename()){
			Debug::error("{$f} datafilename is undefined");
		}
		return $this->datafilename;
	}

	public function addDatafile(?string $name):DefineTablespaceStatement{
		$this->setDatafilename($name);
		return $this;
	}

	public function setInitialSize($value){
		$f = __METHOD__;
		if(is_string($value)){
			if(! preg_match('/^[1-9]+[0-9]*[TtGgMmKk]?/', $value)){
				Debug::error("{$f} pattern mismatch");
			}
		}elseif(!is_int($value)){
			Debug::error("{$f} initial size must be a positive integer");
		}elseif($value < 0){ // INITIAL_SIZE is rounded down to the nearest whole multiple of 32K; this result is rounded up to the nearest whole multiple of EXTENT_SIZE (after any rounding).
			Debug::error("{$f} initial size must be positive");
		}
		if($this->hasInitialSize()){
			$this->release($this->initialSizeValue);
		}
		return $this->initialSizeValue = $this->claim($value);
	}

	public function hasInitialSize():bool{
		return isset($this->initialSizeValue);
	}

	public function getInitialSize(){
		$f = __METHOD__;
		if(!$this->hasInitialSize()){
			Debug::error("{$f} initial size is undefined");
		}
		return $this->initialSizeValue;
	}

	public function initialSize($value):DefineTablespaceStatement{
		$this->setInitialSize($value);
		return $this;
	}

	public function setWaitFlag(bool $value = true):bool{
		return $this->setFlag("wait", $value);
	}

	public function getWaitFlag():bool{
		return $this->getFlag("wait");
	}

	public function wait():DefineTablespaceStatement{
		$this->setWaitFlag(true);
		return $this;
	}
}