<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\element\GhostElementInterface;

class GhostInput extends HiddenInput implements GhostElementInterface{

	public function echo(bool $destroy = false): void{
		return;
	}

	public function echoJson(bool $destroy = false): void{
		return;
	}

	public function skipJson(): bool{
		return true;
	}

	public function bindContext($context){
		if ($context instanceof Datum && $context->hasName()) {
			$this->setColumnName($this->setNameAttribute($context->getName()));
		}
		return $this->context = $context;
	}

	public function processArray(array $arr): int{
		return SUCCESS;
	}
}
