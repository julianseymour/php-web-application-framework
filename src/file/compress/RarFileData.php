<?php
namespace JulianSeymour\PHPWebApplicationFramework\file\compress;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use RarArchive;

class RarFileData extends CompressedFileData{

	public static function extractAll(string $compressed_filename, ?string $directory = null): array{
		$f = __METHOD__;
		if($directory === null){
			$directory = "tmp";
		}
		$filenames = static::extractTempFilenames($compressed_filename, $directory);
		$files = [];
		foreach($filenames as $filename => $tempfilename){
			$file = file_get_contents($tempfilename);
			if($file === false){
				Debug::error("{$f} file_get_contents returned false");
			}
			$files[$filename] = $file;
		}
		return $files;
	}

	public static function extractTempFilenames(string $compressed_filename, ?string $directory = null): array{
		$f = __METHOD__;
		$rar = RarArchive::open($compressed_filename);
		if($rar === false){
			Debug::error("{$f} could not open rar file");
		}
		$entries = $rar->getEntries();
		if($entries === false){
			Debug::error("{$f} RarArchive->getEntries() returned false");
		}
		$filenames = [];
		foreach($entries as $entry){
			$tempfilename = tempnam($directory, static::getExtension());
			$entry->extract(false, $tempfilename);
			$filenames[$entry->getName()] = $tempfilename;
		}
		$rar->close();
		return $filenames;
	}

	public static function getPrettyClassName():string{
		return _("RAR file");
	}

	public static function getPrettyClassNames():string{
		return _("RAR files");
	}

	public static function getExtensionStatic(): string
	{
		return "rar";
	}
}
