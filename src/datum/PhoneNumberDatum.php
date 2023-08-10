<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\input\PhoneNumberInput;

class PhoneNumberDatum extends TextDatum implements StaticElementClassInterface
{

	/*
	 * public function __construct($name){
	 * parent::__construct($name);
	 * $this->setElementClass(PhoneNumberInput::class);
	 * }
	 */
	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string
	{
		return PhoneNumberInput::class;
	}
}
