<?php
namespace JulianSeymour\PHPWebApplicationFramework\file\compress;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\CleartextFileData;

abstract class CompressedFileData extends CleartextFileData
{

	public abstract static function extractAll(string $compressed_filename, ?string $directory = null): array;

	public abstract static function getExtensionStatic(): string;

	public static function extractTempFilenames(string $compressed_filename, ?string $directory = null): array
	{
		$f = __METHOD__; //CompressedFileData::getShortClass()."(".static::getShortClass().")::extractAll()";
		$print = false;
		$contents = static::extractAll($compressed_filename, $directory);
		if ($directory === null) {
			if ($print) {
				Debug::print("{$f} directory is null");
			}
			$directory = "tmp";
		} elseif ($print) {
			Debug::print("{$f} directory \"{$directory}\"");
		}
		$tempfilenames = [];
		foreach ($contents as $filename => $data) {
			@$tempfilename = tempnam($directory, static::getExtensionStatic());
			if ($print) {
				Debug::print("{$f} tempfilename \"{$tempfilename}\"");
			}
			file_put_contents($tempfilename, $data);
			$tempfilenames[$filename] = $tempfilename;
		}
		return $tempfilenames;
	}

	public final function getSubtypeValue(): string
	{
		return $this->getExtensionStatic();
	}

	public function getWebFileDirectory()
	{
		$f = __METHOD__; //CompressedFileData::getShortClass()."(".static::getShortClass().")->getWebFileDirectory()";
		ErrorMessage::unimplemented($f);
	}

	public function getFileToWrite()
	{
		$f = __METHOD__; //CompressedFileData::getShortClass()."(".static::getShortClass().")->getFileToWrite()";
		ErrorMessage::unimplemented($f);
	}

	public static function getTableNameStatic(): string
	{
		$f = __METHOD__; //CompressedFileData::getShortClass()."(".static::getShortClass().")::getTableNameStatic()";
		ErrorMessage::unimplemented($f);
	}
}
