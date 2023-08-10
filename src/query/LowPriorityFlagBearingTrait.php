<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

/**
 * a trait for classes with the flag PRIORITY_LOW
 *
 * @author j
 */
trait LowPriorityFlagBearingTrait
{

	use FlagBearingTrait;

	public function setLowPriorityFlag(bool $value = true): bool
	{
		return $this->setFlag(PRIORITY_LOW, $value);
	}

	public function getLowPriorityFlag(): bool
	{
		return $this->getFlag(PRIORITY_LOW);
	}

	public function lowPriority()
	{
		$this->setLowPriorityFlag(true);
		return $this;
	}

	public function toggleLowPriorityFlag()
	{
		return $this->toggleFlag(PRIORITY_LOW);
	}
}
