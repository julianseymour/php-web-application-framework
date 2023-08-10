<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;
use mysqli;

abstract class CleartextFileData extends FileData{

	public function getOriginalFilename(){
		$f = __METHOD__;
		if (! $this->hasOriginalFilename()) {
			Debug::error("{$f} original filename is undefined");
			return null;
		}
		return $this->getColumnValue("originalFilename");
	}

	public function hasFilename(){
		return $this->hasColumnValue("filename");
	}

	public function getFilename(){
		$f = __METHOD__;
		if (! $this->hasFilename()) {
			$class = $this->getClass();
			$key = $this->getIdentifierValue();
			// $username = $this->getUserName();
			// Debug::printStackTraceNoExit();
			$decl = $this->getDeclarationLine();
			$this->debugPrintColumns("{$f} filename is undefined for {$class} with key \"{$key}\". Instantiated {$decl}"); // for user \"{$username}\"");
		}
		return $this->getColumnValue("filename");
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		try {
			$print = false;
			$splat = explode('.', $this->getOriginalFilename());
			$original = NameDatum::normalize($splat[0]);
			$ext = $this->getExtension();
			$key = $this->getIdentifierValue();
			$filename = "{$original}-{$key}.{$ext}";
			if ($print) {
				Debug::print("{$f} setting filename to \"{$filename}\"");
			}
			$this->setFilename($filename);
			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return parent::afterGenerateInitialValuesHook();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setFilename($name){
		return $this->setColumnValue("filename", $name);
	}

	public function hasMimeType(){
		return $this->hasColumnValue("mimeType");
	}

	public function hasOriginalFilename(){
		return $this->hasColumnValue("originalFilename");
	}

	public function getMimeType(){
		$f = __METHOD__;
		if (! $this->hasMimeType()) {
			Debug::error("{$f} mime type is undefined");
			return MIME_TYPE_OCTET_STREAM;
		}
		return $this->getColumnValue("mimeType");
	}

	public function setMimeType($mime){
		return $this->setColumnValue("mimeType", $mime);
	}

	public function setOriginalFilename($name){
		return $this->setColumnValue("originalFilename", $name);
	}

	public function setSize($size){
		return $this->setColumnValue("size", $size);
	}

	public function hasSize(){
		return $this->hasColumnValue("size");
	}

	public function getSize(){
		$f = __METHOD__;
		if (! $this->hasSize()) {
			Debug::error("{$f} size is undefined");
		}
		return $this->getColumnValue("size");
	}

	public function getWebFilePath(){
		return $this->getWebFileDirectory() . '/' . $this->getFilename();
	}

	public function getFullFileDirectory(){
		return "/var/www/html" . $this->getWebFileDirectory();
	}

	protected function beforeInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$write_me = $this->getFileToWrite();
			$size = strlen($write_me);
			$this->setSize($size);
			// $file_hash = sha1($write_me);
			// $this->setFileHash($file_hash);
			return parent::beforeInsertHook($mysqli);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$status = parent::afterInsertHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} parent function returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			Debug::print("{$f} parent function executed successfully");
			return $this->writeFile();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getWebFileDirectory(){
		return "/files";
	}

	public function getFileToWrite(){
		iF ($this->hasFileContents()) {
			return $this->getFileContents();
		}
		return $this->fileContents = file_get_contents($this->getUploadedTempFilename());
	}

	public static function getPrettyClassName(?string $lang = null){
		return _("File");
	}

	public static function getTableNameStatic(): string{
		return "files";
	}

	public static function getPrettyClassNames(?string $lang = null){
		return _("Files");
	}
}
