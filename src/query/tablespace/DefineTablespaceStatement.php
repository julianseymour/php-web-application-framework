<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\EncryptionOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\EngineAttributeTrait;

abstract class DefineTablespaceStatement extends TablespaceStatement
{

	use AutoextendSizeTrait;
	use EncryptionOptionTrait;
	use EngineAttributeTrait;

	// Reserved for future use: [ENGINE_ATTRIBUTE [=] 'string']
	protected $datafilename;

	protected $initialSizeValue;

	public function dispose(): void
	{
		parent::dispose();
		unset($this->autoextendSizeValue);
		unset($this->datafilename);
		unset($this->encryptionOption);
		unset($this->engineAttributeString);
		unset($this->initialSizeValue);
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"wait"
		]);
	}

	public function setEncryption($enc)
	{
		$f = __METHOD__; //DefineTablespaceStatement::getShortClass()."(".static::getShortClass().")->setEncryption()";
		if($enc == null) {
			unset($this->encryptionOption);
			return null;
		}elseif(!is_string($enc)) {
			Debug::error("{$f} encryption option must be a string");
		}
		$enc = strtolower($enc);
		if($enc !== 'y' && $enc !== 'n') {
			Debug::error("{$f} encryption can only be y or n for this statement");
		}
		return $this->encryptionOption = $enc;
	}

	public function setDatafilename($df)
	{
		$f = __METHOD__; //DefineTablespaceStatement::getShortClass()."(".static::getShortClass().")->setDatafilename()";
		if($df == null) {
			unset($this->datafilename);
			return null;
		}elseif(!is_string($df)) {
			Debug::error("{$f} datafile name must be a string");
		} // XXX An InnoDB tablespace supports only a single data file, whose name must include a .ibd extension.
		return $this->datafilename = $df;
	}

	public function hasDatafilename()
	{
		return isset($this->datafilename);
	}

	public function getDatafilename()
	{
		$f = __METHOD__; //DefineTablespaceStatement::getShortClass()."(".static::getShortClass().")->getDatafilename()";
		if(!$this->hasDatafilename()) {
			Debug::error("{$f} datafilename is undefined");
		}
		return $this->datafilename;
	}

	public function addDatafile($name)
	{
		$this->setDatafilename($name);
		return $this;
	}

	public function setInitialSize($value)
	{
		$f = __METHOD__; //DefineTablespaceStatement::getShortClass()."(".static::getShortClass().")->setInitialSize()";
		if($value !== 0 && $value == null) {
			unset($this->initialSizeValue);
			return null;
		}elseif(is_string($value)) {
			if(! preg_match('/^[1-9]+[0-9]*[TtGgMmKk]?/', $value)) {
				Debug::error("{$f} pattern mismatch");
			}
		}elseif(!is_int($value)) {
			Debug::error("{$f} initial size must be a positive integer");
		}elseif($value < 0) { // INITIAL_SIZE is rounded down to the nearest whole multiple of 32K; this result is rounded up to the nearest whole multiple of EXTENT_SIZE (after any rounding).
			Debug::error("{$f} initial size must be positive");
		}
		return $this->initialSizeValue = $value;
	}

	public function hasInitialSize()
	{
		return isset($this->initialSizeValue);
	}

	public function getInitialSize()
	{
		$f = __METHOD__; //DefineTablespaceStatement::getShortClass()."(".static::getShortClass().")->getInitialSize()";
		if(!$this->hasInitialSize()) {
			Debug::error("{$f} initial size is undefined");
		}
		return $this->initialSizeValue;
	}

	public function initialSize($value)
	{
		$this->setInitialSize($value);
		return $this;
	}

	public function setWaitFlag($value = true)
	{
		return $this->setFlag("wait", $value);
	}

	public function getWaitFlag()
	{
		return $this->getFlag("wait");
	}

	public function wait()
	{
		$this->setWaitFlag(true);
		return $this;
	}
}