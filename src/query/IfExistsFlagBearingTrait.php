<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

trait IfExistsFlagBearingTrait
{

	use FlagBearingTrait;

	public function setIfExistsFlag(bool $value = true): bool
	{
		return $this->setFlag("if exists", $value);
	}

	public function getIfExistsFlag(): bool
	{
		return $this->getFlag("if exists");
	}

	public function ifExists(bool $value = true): bool
	{
		$this->setIfExistsFlag(true);
		return $this;
	}
}