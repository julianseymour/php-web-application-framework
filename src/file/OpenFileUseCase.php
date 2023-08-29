<?php

namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use mysqli;

abstract class OpenFileUseCase extends UseCase{

	protected $requiredMimeType;

	public abstract function acquireFileObject(mysqli $mysqli);

	public function setRequiredMimeType(?string $mime):?string{
		return $this->requiredMimeType = $mime;
	}

	public function hasRequiredMimeType():bool{
		return isset($this->requiredMimeType);
	}

	public function getRequiredMimeType():string{
		$f = __METHOD__;
		if (! $this->hasRequiredMimeType()) {
			Debug::error("{$f} required mime type is undefined");
		}
		return $this->requiredMimeType;
	}

	public function sendHeaders(Request $request): bool{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if($this->hasObjectStatus() && $this->getObjectStatus() !== SUCCESS){
				if($print){
					$err = ErrorMessage::getResultMessage($this->getObjectStatus());
					Debug::print("{$f} error status \"{$err}\". Returning parent function.");
				}
				return parent::sendHeaders($request);
			}elseif($print){
				Debug::print("{$f} sending generic file headers");
			}
			$segments = request()->getRequestURISegments();
			if (empty($segments)) {
				Debug::warning("{$f} request URI segments array is empty");
				Debug::printArray($segments);
				Debug::printStackTrace();
			}
			$filename = $segments[count($segments) - 1];
			if($this->hasRequiredMimeType()){
				$mime_code = $this->getRequiredMimeType();
				FileData::sendHeader($filename, $mime_code);
			}else{
				$mime_code = FileData::sendHeader($filename);
				$this->setRequiredMimeType($mime_code);
			}
			return parent::sendHeaders($request);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoResponse(): void{
		$f = __METHOD__;
		try {
			$print = false;
			if($this->hasObjectStatus() && $this->getObjectStatus() !== SUCCESS){
				if($print){
					$status = $this->getObjectStatus();
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("{$f} error status \"{$err}\"");
				}
				parent::echoResponse();
				return;
			}
			$user = user();
			if ($user == null) {
				Debug::error("{$f} user data returned null");
				$this->echoResponse(ERROR_NULL_USER_OBJECT);
			}
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			$file = $this->acquireFileObject($mysqli);
			if ($file == null) {
				$this->setObjectStatus(ERROR_FILE_NOT_FOUND);
				parent::echoResponse();
				return;
				Debug::error("{$f} whoops, file attachment returned null");
			} elseif ($file->getObjectStatus() == ERROR_NOT_FOUND) {
				Debug::error("{$f} file not found");
			}
			$mime = $file->getMimeType();
			$required = $this->getRequiredMimeType();
			if ($mime !== $required) {
				Debug::error("{$f} file's mime type \"{$mime}\" differs from required type \"{$required}\"");
			}
			$file->outputFileToBrowser($mysqli);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
}
