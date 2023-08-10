<?php
namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\AnchorElement;
use Exception;

class SearchResultElement extends AnchorElement
{

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //SearchResultElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		try {
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$name = new DivElement($mode);
			$name->setInnerHTML($context->getName());
			$this->appendChild($name);
			return $this->getChildNodes();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
