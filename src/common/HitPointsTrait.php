<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait HitPointsTrait{
	
	protected $cyclicalReferenceCount;
	
	protected $hp;
	
	public function incrementCyclicalReferenceCount(int $v=1):int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasCyclicalReferenceCount()){
			if($print){
				Debug::print("{$f} setting cyclical reference count to {$v} for ".$this->getDebugString());
			}
			return $this->cyclicalReferenceCount = $v;
		}elseif($print){
			Debug::print("{$f} adding {$v} to cyclical reference count for this ".$this->getDebugString());
		}
		return $this->cyclicalReferenceCount += $v;
	}
	
	public function getCyclicalReferenceCount():int{
		if($this->hasCyclicalReferenceCount()){
			return $this->cyclicalReferenceCount;
		}
		return 0;
	}
	
	public function setCyclicalReferenceCount(int $v):int{
		return $this->cyclicalReferenceCount = $v;
	}
	
	public function hasCyclicalReferenceCount():bool{
		return isset($this->cyclicalReferenceCount);
	}
	
	public function decrementCyclicalReferenceCount(int $dmg=1):int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasCyclicalReferenceCount()){
			Debug::error("{$f} cyclical reference count is undefined");
			return $this->cyclicalReferenceCount = 0;
		}elseif($dmg > $this->cyclicalReferenceCount){
			Debug::error("{$f} parameter {$dmg} exceeds cyclical reference count {$this->cyclicalReferenceCount}");
		}elseif($print){
			Debug::printStackTraceNoExit("{$f} subtracting {$dmg} from cyclical reference count for this ".$this->getDebugString());
		}
		return $this->cyclicalReferenceCount -= $dmg;
	}
	
	public function getHitPoints():int{
		if($this->hasHitPoints()){
			return $this->hp;
		}
		return 0;
	}
	
	public function setHitPoints(int $hp):int{
		return $this->hp = $hp;
	}
	
	public function hasHitPoints():bool{
		return isset($this->hp);
	}
	
	public function hpUp(int $hp=1):int{
		if(!isset($this->hp)){
			return $this->hp = $hp;
		}
		return $this->hp += $hp;
	}
	
	public function damageHP(int $dmg=1):int{
		if(!isset($this->hp)){
			return $this->hp = 0;
		}
		return $this->hp -= $dmg;
	}
	
	public function hasOnlyCyclicalReferences():bool{
		$hp = $this->getHitPoints();
		return $hp > 0 && $this->hasCyclicalReferenceCount() && $hp === $this->getCyclicalReferenceCount();
	}
}
