<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;

abstract class MenuSlideElement extends DivElement
{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("slide_contained");
	}

	public function getSlideSelectInput()
	{
		$mode = $this->getAllocationMode();
		$input = new RadioButtonInput($mode);
		$input->addClassAttribute("hidden");
		/*
		 * $context = $this->getContext();
		 * $key = $context->getIdentifierValue();
		 * $input->setIdAttribute("radio_{$key}");
		 */
		$input->setNameAttribute("select_slides");
		return $input;
	}

	protected function generatePredecessors(): ?array
	{
		return [
			$this->getSlideSelectInput()
		];
	}
}
