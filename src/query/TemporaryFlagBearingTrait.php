<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

trait TemporaryFlagBearingTrait
{

	use FlagBearingTrait;

	public function setTemporaryFlag(bool $value = true): bool
	{
		return $this->setFlag("temporary", $value);
	}

	public function getTemporaryFlag(): bool
	{
		return $this->getFlag("temporary");
	}

	public function temporary(bool $value = true)
	{
		$this->setTemporaryFlag($value);
		return $this;
	}
}
