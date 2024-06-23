<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait LimitOffsetTrait{

	use LimitedTrait;

	protected $offsetRowCount;

	public function offset($offset): QueryStatement{
		$this->setOffset($offset);
		return $this;
	}

	public function setOffset($offsetRowCount){
		$f = __METHOD__;
		if($this->hasOffset()){
			$this->release($this->offsetRowCount);
		}
		if($offsetRowCount === null){
			return null;
		}
		if(!$this->hasLimit()){
			Debug::error("{$f} assign limit before assigning offset plz");
		}elseif(!is_int($offsetRowCount)){
			Debug::error("{$f} invalid offset value");
		}
		return $this->offsetRowCount = $this->claim($offsetRowCount);
	}

	public function hasOffset(): bool{
		return isset($this->offsetRowCount);
	}

	public function getOffset(): int{
		$f = __METHOD__;
		if(!$this->hasLimit()){
			Debug::error("{$f} should not be here without a limit");
		}elseif(!$this->hasOffset()){
			Debug::error("{$f} offset is undefined");
		}
		return $this->offsetRowCount;
	}
}
