<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

trait PrimaryKeyFlagBearingTrait
{

	use FlagBearingTrait;

	public function getPrimaryKeyFlag()
	{
		return $this->getFlag(COLUMN_FILTER_PRIMARY_KEY);
	}

	public function setPrimaryKeyFlag($value = true)
	{
		return $this->setFlag(COLUMN_FILTER_PRIMARY_KEY, $value);
	}

	public function isPrimaryKey(): bool
	{
		return $this->getPrimaryKeyFlag();
	}

	public function primaryKey()
	{
		$this->setPrimaryKeyFlag(true);
		return $this;
	}
}
