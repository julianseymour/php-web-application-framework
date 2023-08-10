<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

/**
 * a trait for classes that have a flag called 'ignore'
 *
 * @author j
 */
trait IgnoreFlagBearingTrait
{

	use FlagBearingTrait;

	public function setIgnoreFlag(bool $value = true): bool
	{
		return $this->setFlag("ignore", $value);
	}

	public function getIgnoreFlag(): bool
	{
		return $this->getFlag("ignore");
	}

	public function ignore(bool $value = true)
	{
		$this->setIgnoreFlag($value);
		return $this;
	}

	public function toggleIgnoreFlag()
	{
		return $this->toggleFlag("ignore");
	}
}
