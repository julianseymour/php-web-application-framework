<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\inline;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\DownloadAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\TypeAttributeTrait;

class AnchorElement extends Element
{

	use DownloadAttributeTrait;
	use TypeAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "a";
	}

	public function withHrefAttribute($href): AnchorElement
	{
		$this->setHrefAttribute($href);
		return $this;
	}
}
