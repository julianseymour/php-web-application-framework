<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

trait UniqueFlagBearingTrait
{

	use FlagBearingTrait;

	public function setUniqueFlag(bool $value = true): bool
	{
		return $this->setFlag('unique', $value);
	}

	public function getUniqueFlag(): bool
	{
		return $this->getFlag('unique');
	}
}