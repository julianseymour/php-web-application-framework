<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\OpenFileUseCase;
use JulianSeymour\PHPWebApplicationFramework\file\RetrospectiveEncryptedFile;
use Exception;
use mysqli;

class EncryptedImageUseCase extends OpenFileUseCase{

	public function acquireFileObject(mysqli $mysqli){
		$f = __METHOD__;
		try {
			$user = user();
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			if (! isset($mysqli)) {
				Debug::error("{$f} mysqli object returned null");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
				return null;
			}
			$image = new RetrospectiveEncryptedFile();
			if ($image == null) {
				Debug::error("{$f} message returned null");
			}
			$image->setUserData($user);
			$image->setTableName($image->getTableNameStatic());
			$segments = request()->getRequestURISegments();
			if (empty($segments)) {
				Debug::error("{$f} request URI segments array is empty");
			}
			$image_key = $segments[1];
			if (empty($image_key)) {
				Debug::warning("{$f} image key is empty");
				Debug::printArray($segments);
				Debug::printStackTrace();
			}
			$status = $image->loadFromKey($mysqli, $image_key);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} loading image with key {$image_key} returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}
			$status = $image->getObjectStatus();
			if ($status === ERROR_NOT_FOUND) {
				Debug::error("{$f} image not found");
				$this->setObjectStatus($status);
				return null;
			} elseif ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} loaded image has error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}
			return $image;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoResponse(): void{
		$f = __METHOD__;
		try {
			$user = user();
			if ($user == null) {
				Debug::error("{$f} user data returned null");
				$this->echoResponse(ERROR_NULL_USER_OBJECT);
			} elseif ($user instanceof AnonymousUser || ! $user->isEnabled()) {
				Debug::warning("{$f} user is not enabled or you are not logged in");
				$image = imagecreatefrompng("rca.png");
				imagepng($image);
				return;
				// exit();
			}
			parent::echoResponse();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getErrorImageContents(){
		return file_get_contents(IMAGE_ERROR_URI);
	}

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return "/image";
	}
}
