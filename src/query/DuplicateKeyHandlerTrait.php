<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DuplicateKeyHandlerTrait{

	protected $duplicateKeyHandler;

	public function hasDuplicateKeyHandler():bool{
		return isset($this->duplicateKeyHandler);
	}

	public function getDuplicateKeyHandler():string{
		$f = __METHOD__;
		if(!$this->hasDuplicateKeyHandler()){
			Debug::error("{$f} duplicate key handler is undefined");
		}
		return $this->duplicateKeyHandler;
	}

	public function setDuplicateKeyHandler(?string $s):?string{
		$f = __METHOD__;
		if($s == null){
			unset($this->duplicateKeyHandler);
			return null;
		}elseif(!is_string($s)){
			Debug::error("{$f} duplicate key handler must be a string");
		}
		$s = strtolower($s);
		switch($s){
			case DIRECTIVE_IGNORE:
			case DIRECTIVE_REPLACE:
				return $this->duplicateKeyHandler = $s;
			default:
				Debug::error("{$f} invalid duplicate key handler \"{$s}\"");
		}
	}

	public function ignore(){
		$this->setDuplicateKeyHandler(DIRECTIVE_IGNORE);
		return $this;
	}

	public function replace(){
		$this->setDuplicateKeyHandler(DIRECTIVE_REPLACE);
		return $this;
	}
}