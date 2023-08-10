<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\select;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

trait RetainResultFlagBearingTrait
{

	use FlagBearingTrait;

	protected $result;

	public function setRetainResultsFlag(bool $value = true): bool
	{
		return $this->setFlag("retainResult");
	}

	public function getRetainResultsFlag(): bool
	{
		return $this->getFlag("retainResult");
	}

	public function retainResults(bool $value = true)
	{
		$this->setRetainResultsFlag($value);
		return $this;
	}
}
