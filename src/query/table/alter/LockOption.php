<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class LockOption extends AlterOption{

	protected $lock;

	public function __construct($lock = LOCK_OPTION_DEFAULT){
		parent::__construct();
		$this->setLock($lock);
	}

	public function setLock($lock){
		$f = __METHOD__;
		if(!is_string($lock)){
			Debug::error("{$f} lock is not a string");
		}
		$lock = strtolower($lock);
		switch($lock){
			case LOCK_OPTION_DEFAULT:
			case LOCK_OPTION_EXCLUSIVE:
			case LOCK_OPTION_NONE:
			case LOCK_OPTION_SHARED:
				break;
			default:
				Debug::error("{$f} invalid lock \"{$lock}\"");
		}
		if($this->hasLock()){
			$this->release($this->lock);
		}
		return $this->lock = $this->claim($lock);
	}

	public function hasLock():bool{
		return isset($this->lock);
	}

	public function getLock(){
		if(!$this->hasLock()){
			return LOCK_OPTION_DEFAULT;
		}
		return $this->lock;
	}

	public function toSQL(): string{
		return "lock " . $this->getLock();
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->lock, $deallocate);
	}
}