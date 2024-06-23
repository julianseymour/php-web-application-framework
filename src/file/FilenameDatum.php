<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\input\FileInput;

class FilenameDatum extends TextDatum implements StaticElementClassInterface{

	public function processInput($input){
		return STATUS_UNCHANGED;
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return FileInput::class;
	}
}
