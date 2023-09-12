<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

class ColorInput extends InputElement
{

	use ListAttributeTrait;

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_COLOR;
	}

	public function getAllowEmptyInnerHTML()
	{
		return true;
	}

	public function configure(AjaxForm $form): int
	{
		$f = __METHOD__; //ColorInput::getShortClass()."(".static::getShortClass().")->configure()";
		$print = false;
		$ret = parent::configure($form);
		if($this->hasLabelString()) {
			if($print) {
				Debug::print("{$f} pushing predecessor");
			}
			$this->pushPredecessor(Document::createElement("span")->withInnerHTML($this->getLabelString()));
		}elseif($print) {
			Debug::print("{$f} label string is undefined");
		}
		return $ret;
	}
}
