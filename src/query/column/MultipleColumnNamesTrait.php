<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait MultipleColumnNamesTrait
{

	use ArrayPropertyTrait;

	public function setColumnNames($columnNames): ?array
	{
		return $this->setArrayProperty("columnNames", $columnNames);
	}

	public function withColumnNames($columnNames): object
	{
		$this->setColumnNames($columnNames);
		return $this;
	}

	public function pushColumnNames(...$columnNames): int
	{
		return $this->pushArrayProperty("columnNames", ...$columnNames);
	}

	public function hasColumnNames(): bool
	{
		return $this->hasArrayProperty("columnNames");
	}

	public function getColumnNames(): array
	{
		return $this->getProperty("columnNames");
	}

	public function mergeColumnNames($columnNames): array
	{
		return $this->mergeArrayProperty("columnNames", $columnNames);
	}

	public function getColumnNameCount(): int
	{
		return $this->getArrayPropertyCount("columnNames");
	}
}
