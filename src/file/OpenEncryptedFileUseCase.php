<?php

namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;
use mysqli;

class OpenEncryptedFileUseCase extends OpenFileUseCase{

	public function getUriSegmentParameterMap(): ?array{
		return [
			"action",
			"fileKey"
		];
	}

	public function acquireFileObject(mysqli $mysqli){
		$f = __METHOD__;
		try {
			$file_key = request()->getInputParameter("fileKey");
			$client = user();
			$file = new RetrospectiveEncryptedFile();
			$file->setParentObject($client);
			$file->setUserData($client);
			$file->setTableName($file->getTableNameStatic());
			$status = $file->loadFromKey($mysqli, $file_key);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} loading file with key \"{$file_key}\" returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}
			Debug::print("{$f} returning normally");
			return $file;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return "/attach_file";
	}
}
