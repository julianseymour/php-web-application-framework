<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DatabaseVersionTrait{

	protected $requiredMySQLVersion;

	public function setRequiredMySQLVersion($v){
		if($this->hasRequiredMySQLVersion()){
			$this->release($this->requiredMySQLVersion);
		}
		return $this->requiredMySQLVersion = $this->claim($v);
	}

	public function hasRequiredMySQLVersion():bool{
		return isset($this->requiredMySQLVersion);
	}

	public function getRequiredMySQLVersion(){
		$f = __METHOD__;
		if(!$this->hasRequiredMySQLVersion()){
			Debug::error("{$f} required MySQL version is undefined");
		}
		return $this->requiredMySQLVersion;
	}

	public function withRequiredSQLVersion($v){
		$this->setRequiredMySQLVersion($v);
		return $this;
	}
}
