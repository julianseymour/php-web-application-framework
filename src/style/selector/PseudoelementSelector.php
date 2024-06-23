<?php

namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

class PseudoelementSelector extends PseudoclassSelector{

	public function echo(bool $destroy = false): void{
		echo ":";
		parent::echo($destroy);
	}
}
