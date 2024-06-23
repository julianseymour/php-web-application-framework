<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\HTMLElement;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsData;
use Exception;

class RiggedHTMLElement extends HTMLElement{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$session = new LanguageSettingsData();
		$this->setLanguageAttribute($session->getLanguageCode());
		$this->setDirectionalityAttribute($session->getLanguageDirection());
		deallocate($session);
		$this->setIdAttribute("html");
	}
	
	protected static function allowUseCaseAsContext(): bool{
		return true;
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			$context = $this->getContext();
			// body must be bound first because its contents may affect the head element
			$body = new RiggedBodyElement($this->getAllocationMode());
			$body->bindContext($context);
			$head = new RiggedHeadElement($this->getAllocationMode());
			$head->bindContext($context);
			$this->appendChild($head, $body);
			if($print){
				Debug::print("{$f} generated child nodes");
			}
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
