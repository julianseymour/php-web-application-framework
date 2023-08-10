<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

trait UpdateFlagBearingTrait
{

	use FlagBearingTrait;

	public function getUpdateFlag(): bool
	{
		return $this->getFlag(DIRECTIVE_UPDATE);
	}

	public function setUpdateFlag(bool $flag = true): bool
	{
		return $this->setFlag(DIRECTIVE_UPDATE, $flag);
	}
}