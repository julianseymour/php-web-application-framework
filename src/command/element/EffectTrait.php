<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

trait EffectTrait
{

	protected $effect;

	public function hasEffect()
	{
		return isset($this->effect);
	}

	public function getEffect()
	{
		return $this->hasEffect() ? $this->effect : EFFECT_NONE;
	}

	public function setEffect($effect)
	{
		if ($effect == null) {
			unset($this->effect);
			return null;
		}
		return $this->effect = $effect;
	}
}
