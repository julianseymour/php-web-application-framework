<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;

trait EffectTrait{

	protected $effect;

	public function hasEffect():bool{
		return isset($this->effect);
	}

	public function getEffect(){
		return $this->hasEffect() ? $this->effect : EFFECT_NONE;
	}

	public function setEffect($effect){
		if($this->hasEffect()){
			$this->release($this->effect);
		}
		return $this->effect = $this->claim($effect);
	}
}
