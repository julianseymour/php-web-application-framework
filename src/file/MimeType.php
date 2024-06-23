<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\file\compress\BZip2FileData;
use JulianSeymour\PHPWebApplicationFramework\file\compress\GZipFileData;
use JulianSeymour\PHPWebApplicationFramework\file\compress\RarFileData;
use JulianSeymour\PHPWebApplicationFramework\file\compress\ZipFileData;

abstract class MimeType
{

	public static function isCompressed($mime_type)
	{
		return in_array($mime_type, [
			MIME_TYPE_7ZIP,
			MIME_TYPE_BZIP2,
			MIME_TYPE_GZIP,
			MIME_TYPE_RAR,
			MIME_TYPE_ZIP
		], true);
	}

	public static function getFileDataClass($mime_type)
	{
		$f = __METHOD__; //MIMEType::getShortClass()."(".static::getShortClass().")::getFileDataClass()";
		switch($mime_type){
			// case MIME_TYPE_7ZIP:
			// return 7ZipFileData::class;
			case MIME_TYPE_BZIP2:
				return BZip2FileData::class;
			case MIME_TYPE_GZIP:
				return GZipFileData::class;
			case MIME_TYPE_RAR:
				return RarFileData::class;
			case MIME_TYPE_ZIP:
				return ZipFileData::class;
			default:
				Debug::error("{$f} invalid MIME type \"{$mime_type}\"");
		}
	}
}
