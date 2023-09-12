<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DelimiterTrait{
	
	protected $delimiter;
	
	public function getDelimiter(): string{
		$f = __METHOD__;
		if(!$this->hasDelimiter()) {
			Debug::error("{$f} undefined delimiter");
		}
		return $this->delimiter;
	}
	
	public function hasDelimiter(): bool{
		return isset($this->delimiter) && is_string($this->delimiter) && !empty($this->delimiter);
	}
	
	public function setDelimiter(?string $delimiter): ?string{
		if($delimiter == null) {
			unset($this->delimiter);
			return null;
		}
		return $this->delimiter = $delimiter;
	}
	
	public function delimit(?string $delimiter):object{
		$this->setDelimiter($delimiter);
		return $this;
	}
}
