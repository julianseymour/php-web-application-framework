<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use Exception;

class MenuSlideLabel extends LabelElement{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("slide_menu_label");
		$this->addClassAttribute("background_color_1");
		$this->addClassAttribute("slide_select");
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$context = $this->getContext();
			$innerHTML = $context->getName();
			if(empty($innerHTML)) {
				$context_class = $context->getClass();
				$context_name = $context->getName();
				Debug::error("{$f} human readable name is undefined for {$context_class} \"{$context_name}\"");
			}
			$this->appendChild($innerHTML);
			return $this->getChildNodes();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function bindContext($context){
		$f = __METHOD__;
		try{
			$key = $context->getIdentifierValue();
			$this->setForAttribute("radio_menu-{$key}");
			return parent::bindContext($context);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}