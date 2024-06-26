<?php
namespace JulianSeymour\PHPWebApplicationFramework\file\compress;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;
use ZipArchive;

class ZipFileData extends CompressedFileData{

	public static function getPrettyClassName():string{
		return _("Zip file");
	}

	public static function getPrettyClassNames():string{
		return _("Zip files");
	}

	public static function zipStatus($zip_status){
		$f = __METHOD__;
		try{
			switch($zip_status){
				case true:
					// Debug::print("{$f} Success!");
					return true;
				case ZipArchive::ER_EXISTS:
					Debug::error("{$f} File already exists.");
					break;
				case ZipArchive::ER_INCONS:
					Debug::error("{$f} Zip archive inconsistent.");
					break;
				case ZipArchive::ER_INVAL:
					Debug::error("{$f} Invalid argument.");
					break;
				case ZipArchive::ER_MEMORY:
					Debug::error("{$f} Malloc failure.");
					break;
				case ZipArchive::ER_NOENT:
					Debug::error("{$f} No such file.");
					break;
				case ZipArchive::ER_NOZIP:
					Debug::error("{$f} Not a zip archive.");
					break;
				case ZipArchive::ER_OPEN:
					Debug::error("{$f} Can't open file.");
					break;
				case ZipArchive::ER_READ:
					Debug::error("{$f} Read error.");
					break;
				case ZipArchive::ER_SEEK:
					Debug::error("{$f} Seek error.");
					break;
				default:
					Debug::error("{$f} opening zip archive returned error status \"{$zip_status}\"");
					Debug::printStackTrace();
					return $zip_status;
			}
			Debug::error("{$f} failed to open zip file");
			Debug::printStackTrace();
			return $zip_status;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * Returns the contents of a single file $original_filename from the zip file $zip_filename
	 * @param string $zip_filename
	 * @param string $original_filename
	 * @return string|NULL
	 */
	public static function unzipSingleFile(string $zip_filename, string $original_filename):?string{
		$f = __METHOD__;
		try{
			$print = false;
			if($original_filename == null || $original_filename == ""){
				Debug::error("{$f} original filename is null or empty string");
			}elseif($print){
				Debug::print("{$f} entered; about to create open zip archive from file \"{$zip_filename}\"");
			}
			$zip = new ZipArchive();
			$zip_status = static::zipStatus($zip->open($zip_filename));
			if(true !== $zip_status){
				Debug::error("{$f} opening zip file \"{$zip_filename}\"");
			}
			if($print){
				Debug::print("{$f} successfully opened zip file \"{$zip_filename}\". About to unzip file \"{$original_filename}\" from zip zip archive \"{$zip_filename}\"");
			}
			$unzipped = $zip->getFromName($original_filename);
			$zip->close();
			if($unzipped === false){
				Debug::error("{$f} failed to unzip file \"{$original_filename}\" from archive \"{$zip_filename}\"");
				return null;
			}elseif($unzipped === null || $unzipped === ""){
				Debug::error("{$f} unzipped \"{$original_filename}\" from archive \"{$zip_filename}\", but the file itself is null/empty string");
			}elseif($print){
				$length = strlen($unzipped);
				Debug::print("{$f} successfully unzipped file \"{$original_filename}\" from archive \"{$zip_filename}\". File size is {$length} bytes.");
			}
			return $unzipped;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function extractAll(string $zip_filename, ?string $directory = null): array{
		$f = __METHOD__;
		$zip = new ZipArchive();
		$zip_status = static::zipStatus($zip->open($zip_filename));
		if(true !== $zip_status){
			Debug::error("{$f} opening zip file \"{$zip_filename}\"");
		}
		$count = $zip->count();
		$unzipped = [];
		for ($index = 0; $index < $count; $index ++){
			$unzipped[$zip->getNameIndex($index)] = $zip->getFromIndex($index);
		}
		$zip->close();
		return $unzipped;
	}

	/**
	 *
	 * @param string $temp_filename
	 * @param string $original_filename
	 * @return string
	 */
	public static function zipSingleFile(string $temp_filename, string $original_filename, ?string $directory = null): string{
		$f = __METHOD__;
		try{
			$print = false;
			if($directory === null){
				$directory = "tmp";
			}
			$file = file_get_contents($temp_filename);
			if(!isset($file) || $file == ""){
				Debug::error("{$f} file's contents are null or empty string");
			}elseif(!isset($original_filename)){
				Debug::error("{$f} original filename is undefined");
			}elseif($print){
				$md5 = md5($file);
				Debug::print("{$f} about to create tempfile. File contents have md5 {$md5}");
			}
			$zip_filename = tempnam($directory, "zip");
			$zip = new ZipArchive();
			$zip_status = static::zipStatus($zip->open($zip_filename, ZipArchive::OVERWRITE));
			if(true !== $zip_status){
				Debug::error("{$f} opening zip file \"{$zip_filename}\"");
			}
			if($print){
				Debug::print("{$f} successfully opened zip file \"{$zip_filename}\"");
				Debug::print("{$f} about to add file \"{$original_filename}\" to archive \"{$zip_filename}\"");
			} 
			$worked = $zip->addFromString($original_filename, $file);
			$zip->close();
			if($worked){
				if($print){
					Debug::print("{$f} successfully added file \"{$original_filename}\" to zip file \"{$zip_filename}\"");
					$unzipped = static::unzipSingleFile($zip_filename, $original_filename);
					$newhash = md5($unzipped);
					if($md5 !== $newhash){
						Debug::error("{$f} error, unzipped file has hash {$newhash}");
					}else{
						Debug::print("{$f} md5s match up, if there is a problem it is happening elsewhere");
					}
				}
				return $zip_filename;
			}
			Debug::error("{$f} failed to add file \"{$original_filename}\" to zip archive \"{$zip_filename}\"");
			return null;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getExtensionStatic(): string
	{
		return "zip";
	}
}
