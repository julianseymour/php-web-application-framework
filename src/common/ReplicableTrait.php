<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\event\AfterReplicateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeReplicateEvent;

trait ReplicableTrait
{

	use FlagBearingTrait;

	public abstract function replicate();

	public function setReplicaFlag(bool $value = true): bool
	{
		return $this->setFlag("replica", $value);
	}

	public function isReplica(): bool
	{
		return $this->getFlag("replica");
	}

	protected function beforeReplicateHook(): int
	{
		$this->dispatchEvent(new BeforeReplicateEvent());
		return SUCCESS;
	}

	protected function afterReplicateHook($replica): int
	{
		$this->dispatchEvent(new AfterReplicateEvent($replica));
		return SUCCESS;
	}
}
