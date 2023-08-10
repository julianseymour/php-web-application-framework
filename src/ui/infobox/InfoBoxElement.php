<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui\infobox;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\ui\CloseMenuLabel;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

class InfoBoxElement extends DivElement implements JavaScriptCounterpartInterface
{

	use JavaScriptCounterpartTrait;
	use StyleSheetPathTrait;
	
	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("info_box_container");
		$this->addClassAttribute("absolute");
		$this->setIdAttribute("info_box_container");
		// $this->setStyleProperty("display", "none");
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //InfoBoxElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		try {
			$info_box_check = new CheckboxInput();
			$info_box_check->addClassAttribute("hidden");
			$info_box_check->setIdAttribute("info_box_check");
			$this->appendChild($info_box_check);
			$info_box_contents = new DivElement();
			$info_box_contents->addClassAttribute("info_box_contents");
			$info_box_contents->addClassAttribute("background_color_2");
			$info_box_replace = new DivElement();
			$info_box_replace->setIdAttribute("info_box_replace");
			$info_box_replace->addClassAttribute("info_box_replace");
			$info_box_replace->setAllowEmptyInnerHTML(true);
			$info_box_contents->appendChild($info_box_replace);
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$info_box_label = new CloseMenuLabel($mode, $context);
			$info_box_label->addClassAttribute("info_box_label");
			$info_box_label->setOnClickAttribute("InfoBoxElement.resetInfoBox(event, this);");
			$info_box_label->setForAttribute("info_box_check");
			$info_box_contents->appendChild($info_box_label);
			$this->appendChild($info_box_contents);
			$info_box_bg = new LabelElement();
			$info_box_bg->setForAttribute("info_box_check");
			$info_box_bg->addClassAttribute("info_box_bg");
			$info_box_bg->addClassAttribute("absolute");
			$info_box_bg->setAllowEmptyInnerHTML(true);
			$this->appendChild($info_box_bg);
			// $script = new ScriptElement();
			// $script->setInnerHTML("InfoBoxElement.initializeInfoBox();");
			// $this->appendChild($script);
			return $this->getChildNodes();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
