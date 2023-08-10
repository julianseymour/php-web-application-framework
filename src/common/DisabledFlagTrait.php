<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

trait DisabledFlagTrait
{

	use FlagBearingTrait;

	public function setDisabledFlag(bool $value = true): bool
	{
		return $this->setFlag("disabled", $value);
	}

	public function getDisabledFlag(): bool
	{
		return $this->getFlag("disabled");
	}

	public function disable(bool $value = true): object
	{
		$this->setDisabledFlag($value);
		return $this;
	}
}