<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

trait LocalFlagBearingTrait
{

	use FlagBearingTrait;

	public function setLocalFlag(bool $value = true): bool
	{
		return $this->setFlag("local", $value);
	}

	public function getLocalFlag(): bool
	{
		return $this->getFlag("local");
	}

	public function local(bool $value = true)
	{
		$this->setLocalFlag($value);
		return $this;
	}
}
