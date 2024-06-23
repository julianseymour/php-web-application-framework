<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

class TestElement extends DivElement{
	
	public function generateChildNodes():?array{
		$this->setInnerHTML("Test element for context ".$this->getContext()->getDebugString());
		return [];
	}
}