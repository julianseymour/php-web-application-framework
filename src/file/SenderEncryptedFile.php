<?php

namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\compress\ZipFileData;
use Exception;
use mysqli;

class SenderEncryptedFile extends ProspectiveEncryptedFile{

	public function __construct(){
		parent::__construct();
		$this->setUserData(user());
	}

	/**
	 *
	 * @param RecipientEncryptedFile $their_file
	 * @return string
	 */
	public function generateSharedMetadata($their_file){
		$f = __METHOD__;
		try{
			Debug::print("{$f} entered");
			$their_file->setMimeType($this->getMimeType());
			$their_file->setOriginalFilename($this->getOriginalFilename());
			// AES key
			$aes_key = $this->generateFileAesKey();
			$aes_key = $this->getFileAesKey();
			$length = strlen($aes_key);
			$shouldbe = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
			if($length !== $shouldbe) {
				Debug::error("{$f} file AES key is wrong length ({$length}, should be {$shouldbe})");
			}
			$their_file->setFileAesKey($aes_key);
			// filename cipher
			$filename = $this->generateFilename();
			$their_file->setFilename($filename);
			// AES nonce (cleartext)
			$nonce = $this->generateFileAesNonce();
			$length = strlen($nonce);
			$shouldbe = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
			if($length !== $shouldbe) {
				Debug::error("{$f} nonce is incorrect length ({$length}, should be {$shouldbe})");
			}
			$their_file->setFileAesNonce($nonce);
			$their_file->setUploadedTempFilename($this->getUploadedTempFilename());
			$mime = $this->getMimeType();
			switch ($mime) {
				case MIME_TYPE_GIF:
				case MIME_TYPE_JPEG:
				case MIME_TYPE_PNG:
					$h = $this->getImageHeight();
					$w = $this->getImageWidth();
					if(! isset($h) || ! isset($w)) {
						Debug::error("{$f} invalid dimensions {$h}x{$w}");
						Debug::printStackTrace();
					}
					Debug::print("{$f} dimensions {$w}x{$h}");
					$their_file->setImageHeight($h);
					$their_file->setImageWidth($w);
					break;
				default:
					break;
			}
			$aes_key = $this->getFileAesKey();
			$length = strlen($aes_key);
			$shouldbe = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
			if($length !== $shouldbe) {
				Debug::error("{$f} file AES key is wrong length ({$length}, should be {$shouldbe})");
			}
			Debug::print("{$f} returning normally");
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * Do not call this except in writeFile -- it is generated based off of the current date
	 */
	public function getUploadDirectory():string{
		if($this->hasUploadDirectory()) {
			return parent::getUploadDirectory();
		}
		return "/var/www/uploads/encrypted";
	}

	public function generateFilename():string{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} entered");
			}
			if($this->getMessageBox() === MESSAGE_BOX_INBOX) {
				Debug::print("{$f} this is the recipient's copy of the header; filename should already be set");
				return $this->getFilename();
			}
			Debug::print("{$f} this is the outbox message; about to generate filename");
			$dir2 = $this->getFullFileDirectory();
			$filename_base = sha1(random_bytes(32));
			$full_path = "{$dir2}/{$filename_base}.zap";
			// $this->setFullFileDirectory();
			$this->setFilename($full_path);
			Debug::print("{$f} successfully generated filename \"{$full_path}\"");
			return $full_path;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function generateFileAesNonce(){
		return $this->setFileAesNonce(random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES));
	}

	public function generateFileAesKey(){
		$f = __METHOD__;
		try{
			if($print){
				Debug::print("{$f} entered");
			}
			$aes_key = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES);
			$this->setFileAesKey($aes_key);
			$aes_key = $this->getFileAesKey();
			$length = strlen($aes_key);
			$shouldbe = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
			if($length !== $shouldbe) {
				Debug::error("{$f} file AES key is wrong length ({$length}, should be {$shouldbe})");
			}
			Debug::print("{$f} returning normally");
			return $aes_key;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function processMimeType(){
		$f = __METHOD__;
		try{
			Debug::print("{$f} entered; about to call parent function");
			$status = parent::processMimeType(); // Files($arr, $index);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} parent function returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			Debug::print("{$f} parent function executed successfully");
			$mime = $this->getMimeType();
			switch ($mime) {
				case MIME_TYPE_GIF:
				case MIME_TYPE_PNG:
				case MIME_TYPE_JPEG:
					return $this->processImageDimensions();
				case MIME_TYPE_OCTET_STREAM:
					$client = $this->getUserData();
					if($client instanceof Administrator) {
						Debug::print("{$f} user is admin");
					}else{
						Debug::warning("{$f} user is not admin and thus is not allowed to upload arbitrary files");
						return $this->setObjectStatus(ERROR_MIME_TYPE);
					}
					break;
				default:
					Debug::error("{$f} invalid mime type \"{$mime}\"");
			}
			Debug::print("{$f} returning normally");
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * return binary string containing compressed file
	 *
	 * @return string
	 */
	protected function compressFile(){
		$f = __METHOD__;
		try{
			$original_filename = $this->getOriginalFilename();
			$mime = $this->getMimeType();
			$compressed_filename = $this->getCompressedFilename();
			Debug::print("{$f} about to zip file \"{$compressed_filename}\"");
			$zip_filename = ZipFileData::zipSingleFile($compressed_filename, $original_filename);
			if(false !== array_search($mime, [
				MIME_TYPE_PNG,
				MIME_TYPE_JPEG
			])) {
				Debug::print("{$f} this is a png or jpg -- about to unlink compressed tempfile name");
				if(! unlink($compressed_filename)) {
					Debug::error("{$f} failed to delete compressed tempfile \"{$compressed_filename}\"");
				}
			}
			$file_zip = file_get_contents($zip_filename);
			if(unlink($zip_filename)) {
				Debug::print("{$f} deleted temp zipfile");
			}else{
				Debug::error("{$f} tempfile deletion unsuccessful");
				return $this->setObjectStatus(ERROR_UNLINK);
			}
			return $file_zip;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * return binary string containing the encrypted plaintext of the file
	 *
	 * @return string
	 */
	protected function encryptFile(){
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::printStackTraceNoExit("{$f} entered");
			}
			$file_zip = $this->compressFile();
			$aes_key = $this->getFileAesKey();
			$nonce = $this->getFileAesNonce();
			$file_cipher = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($file_zip, null, $nonce, $aes_key);
			return $file_cipher;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * return binary string containing the file to be written
	 *
	 * @return string
	 */
	public function getFileToWrite(){
		$f = __METHOD__;
		if($this->hasFileContents()) {
			return $this->getFileContents();
		}
		$fc = $this->encryptFile();
		$size = strlen($fc);
		$this->setSize($size);
		return $this->fileContents = $fc;
	}

	protected function beforeInsertHook(mysqli $mysqli): int{ // XXX need case for recipient to use same file
		$f = __METHOD__;
		try{
			Debug::print("{$f} entered");
			$this->getFileToWrite();
			$ret = parent::beforeInsertHook($mysqli);
			if($mysqli == null) {
				Debug::error("{$f} mysql connection failed");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			Debug::print("{$f} about to write file");
			$status = $this->writeFile();
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} writing file returned error status \"{$err}\"");
			}
			Debug::print("{$f} wrote file; about to call parent function");
			return $ret;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
