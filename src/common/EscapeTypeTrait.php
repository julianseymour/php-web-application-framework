<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait EscapeTypeTrait{

	protected $escapeType;
	
	public function hasEscapeType(): bool{
		return isset($this->escapeType);
	}

	public function getEscapeType(): ?string{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasEscapeType()){
			if($print){
				Debug::warning("{$f} escape type is undefined");
			}
			return null;
		}
		return $this->escapeType;
	}

	public function setEscapeType(?string $type): ?string{
		if($this->hasEscapeType()){
			$this->release($this->escapeType);
		}
		return $this->escapeType = $this->claim($type);
	}

	public function escape(?string $type){
		$this->setEscapeType($type);
		return $this;
	}
}
