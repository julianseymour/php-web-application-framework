<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait TablespaceNameTrait{

	protected $tablespaceName;

	public function tablespace($name){
		$this->setTablespaceName($name);
		return $this;
	}

	public function setTablespaceName($name){
		if($this->hasTablespaceName()){
			$this->release($this->tablespaceName);
		}
		return $this->tablespaceName = $this->claim($name);
	}

	public function hasTablespaceName():bool{
		return isset($this->tablespaceName);
	}

	public function getTablespaceName(){
		$f = __METHOD__;
		if(!$this->hasTablespaceName()){
			Debug::error("{$f} tablespace name is undefined");
		}
		return $this->tablespaceName;
	}
}