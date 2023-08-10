<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticHumanReadableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\input\FileInput;

class FilenameDatum extends TextDatum implements StaticElementClassInterface, StaticHumanReadableNameInterface{

	public function processInput($input){
		return STATUS_UNCHANGED;
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return FileInput::class;
	}

	public static function getHumanReadableNameStatic(?StaticHumanReadableNameInterface $that = null){
		return _("Filename");
	}
}
