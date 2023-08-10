<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\input\URLInput;

class UrlDatum extends TextDatum implements StaticElementClassInterface
{

	/*
	 * public function __construct($name){
	 * parent::__construct($name);
	 * $this->setElementClass(URLInput::class);
	 * }
	 */
	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string
	{
		return URLInput::class;
	}
}
