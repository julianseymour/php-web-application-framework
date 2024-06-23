<?php
namespace JulianSeymour\PHPWebApplicationFramework\file\compress;


class BZip2FileData extends CompressedFileData{

	public static function getExtensionStatic(): string{
		return "bz2";
	}

	public static function getPrettyClassName():string{
		return _("BZip2 file");
	}

	public static function getPrettyClassNames():string{
		return _("BZip2 files");
	}

	public static function extractAll(string $compressed_filename, ?string $directory = null): array{
		$f = __METHOD__;
		$bz = bzopen($compressed_filename, "r");
		$decompressed_file = "";
		while(! feof($bz)){
			$decompressed_file .= bzread($bz, 8192);
		}
		bzclose($bz);
		return [
			$decompressed_file
		];
	}
}
