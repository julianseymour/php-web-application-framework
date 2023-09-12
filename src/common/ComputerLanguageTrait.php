<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

trait ComputerLanguageTrait
{

	protected $computerLanguage;

	public function hasComputerLanguage(): bool
	{
		return isset($this->computerLanguage) && is_string($this->computerLanguage) && ! empty($this->computerLanguage);
	}

	public function getComputerLanguage(): ?string
	{
		if(!$this->hasComputerLanguage()) {
			return null;
		}
		return $this->computerLanguage;
	}

	public function setComputerLanguage(?string $l): ?string
	{
		if($l == null) {
			unset($this->computerLanguage);
			return null;
		}
		return $this->computerLanguage = $l;
	}
}
