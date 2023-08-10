<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use Exception;

class DummyElement extends DivElement
{

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //DummyElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		try {
			$context = $this->getContext();
			$innerHTML = $context->getPrettyClassName() . " \"" . $context->getName() . "\" with ID \"" . $context->getIdentifierValue() . "\"";
			$this->setInnerHTML($innerHTML);
			return [
				$innerHTML
			];
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
