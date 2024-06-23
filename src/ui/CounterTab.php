<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;

class CounterTab extends LabelElement{

	public function setInnerHTML($innerHTML){
		$f = __METHOD__;
		$this->appendChild($innerHTML);
		$counter = new SpanElement();
		$counter->addClassAttribute("notification_counter");
		if(is_object($innerHTML) && !$innerHTML instanceof StringifiableInterface){
			$type = $innerHTML->getClass();
			Debug::print("{$f} innerHTML is type \"{$type}\"");
		}
		$prefix = $this->getIdAttribute();
		$counter->setIdAttribute("{$prefix}-counter");
		$counter->setInnerHTML(0);
		$counter->setStyleProperty("opacity", "0");
		$this->appendChild($counter);
		return $innerHTML;
	}
}
