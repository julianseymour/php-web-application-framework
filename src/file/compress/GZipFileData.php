<?php
namespace JulianSeymour\PHPWebApplicationFramework\file\compress;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class GZipFileData extends CompressedFileData{

	public static function getExtensionStatic(): string{
		return "gz";
	}

	public static function getPrettyClassName():string{
		return _("GZip file");
	}

	public static function getPrettyClassNames():string{
		return _("GZip files");
	}

	public static function extractAll(string $compressed_filename, ?string $directory = null): array{
		$f = __METHOD__;
		ob_start();
		if(false === readgzfile($compressed_filename)){
			Debug::error("{$f} GZip decompression failed");
		}
		return [
			ob_get_clean()
		];
	}
}
