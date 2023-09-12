<?php

namespace JulianSeymour\PHPWebApplicationFramework\file\compress;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\CleartextFileData;

abstract class CompressedFileData extends CleartextFileData{

	public abstract static function extractAll(string $compressed_filename, ?string $directory = null): array;

	public abstract static function getExtensionStatic(): string;

	public static function extractTempFilenames(string $compressed_filename, ?string $directory = null): array{
		$f = __METHOD__;
		$print = false;
		$contents = static::extractAll($compressed_filename, $directory);
		if($directory === null) {
			if($print) {
				Debug::print("{$f} directory is null");
			}
			$directory = "tmp";
		}elseif($print) {
			Debug::print("{$f} directory \"{$directory}\"");
		}
		$tempfilenames = [];
		foreach($contents as $filename => $data) {
			@$tempfilename = tempnam($directory, static::getExtensionStatic());
			if($print) {
				Debug::print("{$f} tempfilename \"{$tempfilename}\"");
			}
			file_put_contents($tempfilename, $data);
			$tempfilenames[$filename] = $tempfilename;
		}
		return $tempfilenames;
	}

	public final function getSubtype(): string{
		return $this->getExtensionStatic();
	}

	public function getWebFileDirectory():string{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function getFileToWrite(){
		ErrorMessage::unimplemented(__METHOD__);
	}
}
