<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

class DocumentFragment extends Element{

	public function echo(bool $destroy = false): void{
		echo "new DocumentFragment()";
	}

	public static function getElementTagStatic(): string{
		return "fragment";
	}
	
	protected function generateAttributes():int{
		return SUCCESS;
	}
}
