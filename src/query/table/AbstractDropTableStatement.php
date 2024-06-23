<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\IfExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use function JulianSeymour\PHPWebApplicationFramework\release;

abstract class AbstractDropTableStatement extends QueryStatement{

	use IfExistsFlagBearingTrait;
	use MultipleTableNamesTrait;

	protected $referenceOption;

	public function setReferenceOption($refopt){
		$f = __METHOD__;
		if(!is_string($refopt)){
			Debug::error("{$f} reference option must be a string");
		}
		$refopt = strtolower($refopt);
		switch($refopt){
			case REFERENCE_OPTION_CASCADE:
			case REFERENCE_OPTION_RESTRICT:
				break;
			default:
				Debug::error("{$f} invalid reference option \"{$refopt}\"");
				return $this->setReferenceOption(null);
		}
		if($this->hasReferenceOption()){
			$this->release($this->referenceOption);
		}
		return $this->referenceOption = $this->claim($refopt);
	}

	public function hasReferenceOption():bool{
		return isset($this->referenceOption);
	}

	public function getReferenceOption(){
		$f = __METHOD__;
		if(!$this->hasReferenceOption()){
			Debug::error("{$f} reference option is undefined");
		}
		return $this->referenceOption;
	}

	public function restrict()
	{
		$this->setReferenceOption(REFERENCE_OPTION_RESTRICT);
		return $this;
	}

	public function cascade():AbstractDropTableStatement{
		$this->setReferenceOption(REFERENCE_OPTION_CASCADE);
		return $this;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->referenceOption, $deallocate);
	}
}
