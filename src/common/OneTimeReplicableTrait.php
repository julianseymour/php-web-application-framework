<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

ErrorMessage::deprecated(__FILE__);

trait OneTimeReplicableTrait{
	
	use ReplicableTrait;
	
	protected $replica;
	
	public function hasReplica():bool{
		return isset($this->replica) && is_a($this->replica, static::getClass(), true);
	}
	
	public function setReplica(?ReplicableInterface $replica):?ReplicableInterface{
		if($replica === null){
			unset($this->replica);
			return null;
		}
		return $this->replica = $replica;
	}
	
	public function getReplica():?ReplicableInterface{
		if($this->hasReplica()){
			return $this->replica;
		}elseif($this->getReplicaFlag()){
			return $this;
		}
		$replica = $this->replicate();
		if($replica === null){
			return null;
		}
		$replica->setReplicaFlag(true);
		return $this->setReplica($replica);
	}
}