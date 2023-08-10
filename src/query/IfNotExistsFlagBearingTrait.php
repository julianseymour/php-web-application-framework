<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

trait IfNotExistsFlagBearingTrait
{

	use FlagBearingTrait;

	public function getIfNotExistsFlag(): bool
	{
		return $this->getFlag("ifNotExists");
	}

	public function setIfNotExistsFlag(bool $value = true): bool
	{
		return $this->setFlag("ifNotExists", $value);
	}

	public function ifNotExists(bool $value = true)
	{
		$this->setIfNotExistsFlag($value);
		return $this;
	}
}
