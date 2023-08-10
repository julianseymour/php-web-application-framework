<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

class FadeElementCommand extends DeleteElementCommand
{

	public function getEffect()
	{
		return EFFECT_FADE;
	}

	public function hasEffect()
	{
		return true;
	}
}
