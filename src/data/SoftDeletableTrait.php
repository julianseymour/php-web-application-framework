<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;

trait SoftDeletableTrait
{

	use MultipleColumnDefiningTrait;

	public function setSoftDeletionTimestamp(int $value): int
	{
		return $this->setColumnValue("softDeletionTimestamp", $value);
	}

	public function getSoftDeletionTimestamp(): int
	{
		return $this->getColumnValue("softDeletionTimestamp");
	}

	public function ejectSoftDeletionTimestamp(): ?int
	{
		return $this->ejectColumnValue("softDeletionTimestamp");
	}

	public function hasSoftDeletionTimestamp(): bool
	{
		return $this->hasColumnValue("softDeletionTimestamp");
	}
}
