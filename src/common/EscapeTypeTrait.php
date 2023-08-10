<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait EscapeTypeTrait
{

	public function hasEscapeType(): bool
	{
		return isset($this->escapeType);
	}

	public function getEscapeType(): ?string
	{
		$f = __METHOD__; //"EscapeTypeTrait(".static::getShortClass().")->getEscapeType()";
		$print = false;
		if (! $this->hasEscapeType()) {
			if ($print) {
				Debug::warning("{$f} escape type is undefined");
			}
			return null;
		}
		return $this->escapeType;
	}

	public function setEscapeType(?string $type): ?string
	{
		if ($type === null) {
			unset($this->escapeType);
			return null;
		}
		return $this->escapeType = $type;
	}

	public function escape(?string $type)
	{
		$this->setEscapeType($type);
		return $this;
	}
}
