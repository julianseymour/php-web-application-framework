<?php

namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;
use mysqli;

class RecipientEncryptedFile extends ProspectiveEncryptedFile{

	public static function getPermissionStatic($name, $data){
		switch($name){
			case DIRECTIVE_INSERT:
				return SUCCESS;
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	public function beforeInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($print){
				Debug::print("{$f} entered");
			}
			if($mysqli == null){
				Debug::error("{$f} mysql connection failed");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}elseif(!$this->preventDuplicateEntry($mysqli)){
				Debug::print("{$f} file already exists");
				return $this->setObjectStatus(SUCCESS);
			}elseif($print){
				Debug::print("{$f} this is the recipient's copy of the file metadata");
			}
			// the other party needs the following items:
			// AES key cipher coded for recipient
			$aes = $this->getFileAesKey();
			// filename cipher
			$name = $this->getFilename();
			// AES nonce (cleartext)
			$nonce = $this->getFileAesNonce();
			if(strlen($nonce) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES){
				Debug::error("{$f} nonce is incorrect length");
			}
			// the other party does NOT require the following information directly from the sender's copy of the metadata:
			// original filename (still in $_FILES superglobal)
			// mime type cipher (same)
			if($print){
				$original = $this->getOriginalFilename();
				$mime = $this->getMimeType();
				$arr = [
					"AES key" => base64_encode($aes),
					"Filename" => $name,
					"Nonce" => base64_encode($nonce),
					"Original filename" => $original,
					"MIME type" => $mime
				];
				foreach(array_keys($arr) as $key){
					if($arr[$key] == null){
						Debug::error("{$f} array key \"{$key}\" is undefined");
						Debug::printStackTrace();
					}
				}
				Debug::print("{$f} returning parent function");
			}
			return parent::beforeInsertHook($mysqli);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
