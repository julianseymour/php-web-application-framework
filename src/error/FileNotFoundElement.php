<?php
namespace JulianSeymour\PHPWebApplicationFramework\error;

use JulianSeymour\PHPWebApplicationFramework\element\DivElement;

class FileNotFoundElement extends DivElement
{

	public function generateChildNodes(): ?array
	{
		$div = new DivElement();
		$div->setInnerHTML("Placeholder 404 notice");
		$this->appendChild($div);
		return $this->getChildNodes();
	}
}
