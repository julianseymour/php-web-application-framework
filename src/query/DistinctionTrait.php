<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DistinctionTrait{

	protected $distinction;

	public function setDistinction(?string $distinction):?string{
		$f = __METHOD__;
		if($distinction == null){
			unset($this->distinction);
			return null;
		}elseif(!is_string($distinction)){
			Debug::error("{$f} distinction must be a string");
		}
		$distinction = strtolower($distinction);
		switch($distinction){
			case DISTINCTION_DISTINCTROW:
			// XXX TODO valid for select statement but not union clause
			case DISTINCTION_ALL:
			case DISTINCTION_DISTINCT:
				break;
			default:
				Debug::error("{$f} invalid distinction \"{$distinction}\"");
		}
		return $this->distinction = $distinction;
	}

	public function hasDistinction():bool{
		return isset($this->distinction) && is_string($this->distinction) && !empty($this->distinction);
	}

	public function getDistinction():string{
		$f = __METHOD__;
		if(!$this->hasDistinction()){
			Debug::error("{$f} distinction is undefined");
		}
		return $this->distinction;
	}

	public function withDistinction(?string $distinction):object{
		$this->setDistinction($distinction);
		return $this;
	}
}