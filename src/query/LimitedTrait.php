<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait LimitedTrait{

	protected $limitCount;

	public function limit($limit): object{
		$this->setLimit($limit);
		return $this;
	}

	public function setLimit(?int $limitCount):?int{
		$f = __METHOD__;
		if($this->hasLimit()){
			$this->release($this->limitCount);
		}
		if($limitCount === null){
			return null;
		}elseif(!is_int($limitCount)){
			Debug::error("{$f} non-integral limit value");
		}
		return $this->limitCount = $this->claim($limitCount);
	}

	public function hasLimit(): bool{
		return !empty($this->limitCount);
	}

	public function getLimit(): int{
		$f = __METHOD__;
		if(!$this->hasLimit()){
			Debug::error("{$f} limit is undefined");
		}
		return $this->limitCount;
	}
}
