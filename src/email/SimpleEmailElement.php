<?php

namespace JulianSeymour\PHPWebApplicationFramework\email;


use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\inline\AnchorElement;
use Exception;

class SimpleEmailElement extends DivElement{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setStyleProperty("width", "100%");
	}

	protected function getBodyElement(): Element{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$user = $context->getRecipient();
			// $theme = $user->getTheme();
			// $theme_class = mods()->getThemeClass($theme);
			// $theme_data = new $theme_class();
			$middle = new DivElement($mode);
			$middle->setStyleProperties([
				"width" => "100%",
				"background-color" => "#000", // $theme_data->getBackgroundColor3(),
				"color" => "#e7e7e7", // $theme_data->getTextColor3(),
				"padding-top" => "8px",
				"padding-bottom" => "8px"
			]);
			$div = new DivElement($mode);
			$div->setInnerHTML($context->getPlaintextBody());
			$middle->appendChild($div);
			return $middle;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public final function generateChildNodes(): ?array
	{
		$f = __METHOD__; //SimpleEmailElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		$mode = $this->getAllocationMode();
		$context = $this->getContext();
		$header = new EmailHeaderElement($mode, $context);
		$body = $this->getBodyElement();
		$footer = new EmailFooterElement($mode, $context);
		$this->appendChild($header, $body, $footer);
		return $this->hasChildNodes() ? $this->getChildNodes() : [];
	}
}
