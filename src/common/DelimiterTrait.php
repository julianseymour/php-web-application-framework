<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DelimiterTrait{
	
	protected $delimiter;
	
	public function getDelimiter(): string{
		$f = __METHOD__;
		if(!$this->hasDelimiter()){
			Debug::error("{$f} undefined delimiter");
		}
		return $this->delimiter;
	}
	
	public function hasDelimiter(): bool{
		return isset($this->delimiter) && is_string($this->delimiter) && !empty($this->delimiter);
	}
	
	public function setDelimiter(?string $delimiter): ?string{
		if($this->hasDelimiter()){
			$this->release($this->delimiter);
		}
		return $this->delimiter = $this->claim($delimiter);
	}
	
	public function delimit(?string $delimiter):object{
		$this->setDelimiter($delimiter);
		return $this;
	}
}
