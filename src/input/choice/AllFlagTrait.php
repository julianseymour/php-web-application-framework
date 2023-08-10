<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

trait AllFlagTrait
{

	use FlagBearingTrait;

	public function setAllFlag(bool $value = true): bool
	{
		return $this->setFlag("all", $value);
	}

	public function getAllFlag(): bool
	{
		return $this->getFlag("all");
	}
}
