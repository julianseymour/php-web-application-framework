<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;

trait ComputerLanguageTrait{

	protected $computerLanguage;

	public function hasComputerLanguage(): bool{
		return isset($this->computerLanguage) && is_string($this->computerLanguage) && !empty($this->computerLanguage);
	}

	public function getComputerLanguage(): ?string{
		if(!$this->hasComputerLanguage()){
			return null;
		}
		return $this->computerLanguage;
	}

	public function setComputerLanguage(?string $l): ?string{
		if($this->hasComputerLanguage()){
			$this->release($this->computerLanguage);
		}
		return $this->computerLanguage = $this->claim($l);
	}
}
