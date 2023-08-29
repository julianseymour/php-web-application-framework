<?php

namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\NonceDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\AsymmetricEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\MessageEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\UrlDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\image\ImageData;
use JulianSeymour\PHPWebApplicationFramework\image\ImageElement;
use Exception;
use mysqli;

abstract class EncryptedFile extends FileData implements StaticElementClassInterface{

	protected $skipWrite = false;

	protected $counterpartKey;

	protected $height;

	protected $width;

	public function hasMessageObject():bool{
		return $this->hasForeignDataStructure("messageKey");
	}

	public function getCounterpartKey(){
		return $this->counterpartKey;
	}

	public function hasCounterpartKey():bool{
		return isset($this->counterpartKey);
	}

	public function hasFileAesKey():bool{
		return $this->hasColumnValue("fileAesKey");
	}

	public final function getSubtypeValue(): string{
		return "encrypted";
	}

	public function getWebFileDirectory():string{
		$f = __METHOD__;
		$type = $this->getMimeType();
		switch ($type) {
			case MIME_TYPE_GIF:
			case MIME_TYPE_JPG:
			case MIME_TYPE_PNG:
				return '/image/' . $this->getIdentifierValue();
			case MIME_TYPE_OCTET_STREAM:
				return '/attach_file/' . $this->getIdentifierValue();
			default:
				$key = $this->getIdentifierValue();
				Debug::warning("{$f} object with key \"{$key}\" has invalid mime type \"{$type}\"");
				return null;
		}
	}

	public function getWebFilePath(){
		$f = __METHOD__;
		try {
			$uri = $this->getWebFileDirectory();
			return "{$uri}/" . $this->getOriginalFilename();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setCounterpartKey($key){
		return $this->counterpartKey = $key;
	}

	public function getCorrespondentObject(){
		return $this->getUserData()->getCorrespondentObject();
	}

	protected function afterDeleteHook(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$status = parent::afterDeleteHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} parent function returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$counterpartKey = $this->getCounterpartKey();
			$correspondent = $this->getCorrespondentObject();
			$counterpart = SenderEncryptedFile::getObjectFromKey($mysqli, $counterpartKey);
			if (! isset($counterpart)) {
				Debug::warning("{$f} counterpart object returned null");
				return SUCCESS;
			}
			$counterpart->setUserData($correspondent);
			$filename = $this->getFilename();
			$counterpart->setFilename($filename);
			$status = $counterpart->delete($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} deleting counterpart object returned error status \"{$err}\"");
			}

			Debug::print("{$f} deletion successful");
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getName():string{
		return $this->getOriginalFilename();
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		$f = __METHOD__;
		$mime = $that->getMimeType();
		switch ($mime) {
			case MIME_TYPE_GIF:
			case MIME_TYPE_JPEG:
			case MIME_TYPE_PNG:
				return ImageElement::class;
			default:
				Debug::error("{$f} invlaid mime type \"{$mime}\"");
		}
	}

	public function getImageHeight(){
		$f = __METHOD__;
		if (empty($this->height)) {
			$key = $this->getIdentifierValue();
			Debug::error("{$f} image height is null or empty string for file with key \"{$key}\"");
		}
		// Debug::print("{$f} returning \"{$this->height}\"");
		return $this->height;
	}

	public function getImageWidth(){
		$f = __METHOD__;
		if (empty($this->width)) {
			$key = $this->getIdentifierValue();
			Debug::error("{$f} image width is null or empty string for file with key \"{$key}\" and debug ID \"{$this->debugId}\"");
		}
		// Debug::print("{$f} returning \"{$this->width}\"");
		return $this->width;
	}

	public function setImageHeight($h){
		$f = __METHOD__;
		try {
			$print = false;
			if (! is_int($h) && ! is_float($h)) {
				$type = gettype($h);
				Debug::error("{$f} height \"{$h}\" is \"{$type}\", not an integer");
				return 0;
			} elseif (is_double($h) && $h % 1 > 0) {
				Debug::error("{$f} height \"{$h}\" is a double with significant digits");
				$h = floor($h);
			}
			if ($print) {
				$key = $this->hasIdentifierValue() ? $this->getIdentifierValue() : "undefined";
				Debug::print("{$f} set image height to \"{$h}\" for file with key \"{$key}\" and debug ID \"{$this->debugId}\"");
			}
			return $this->height = $h;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setImageWidth($w){
		$f = __METHOD__;
		try {
			if (! is_numeric($w)) {
				Debug::error("{$f} width \"{$w}\" is not an integer");
				Debug::printStackTrace();
				return 0;
			} elseif (is_double($w) && $w % 1 > 0) {
				Debug::error("{$f} width \"{$w}\" is a double with significant digits");
				$w = floor($w);
			}
			// Debug::print("{$f} returning \"{$w}\"");
			return $this->width = $w;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getFullFileDirectory():string{
		return $this->getUploadDirectory();
	}

	public function getFileAesNonce():string{
		return $this->getColumnValue('fileAesNonce');
	}

	public function setOriginalFilename(string $name):string{
		$f = __METHOD__;
		if (empty($name)) {
			Debug::error("{$f} parameter is undefined");
			Debug::printStackTrace();
		} elseif (substr($name, 0, 5) == "/tmp/") {
			Debug::error("{$f} wrong one dipshit");
			Debug::printStackTrace();
		}
		return $this->setColumnValue('originalFilename', $name);
	}

	public function getFullFilePath():string{
		return $this->getFilename();
	}

	public function getFileToWrite(){
		ErrorMessage::unimplemented(__METHOD__);
		return null;
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__; //EncryptedFile::getShortClass()."(".static::getShortClass().")::reconfigureColumns()";
		try {
			parent::reconfigureColumns($columns, $ds);
			$indices = [
				"userAccountType",
				"userKey"
			];
			foreach ($indices as $index) {
				$columns[$index]->volatilize();
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getAesKeyCipherIndex(string $vn):string{
		return "{$vn}_aesKeyCipher";
	}

	public function getMimeType():string{
		return $this->getColumnValue('mimeType');
	}

	public function setFilename(string $name):string{
		return $this->setColumnValue('filename', $name);
	}

	public function setMimeType(string $type):string{
		return $this->setColumnValue('mimeType', $type);
	}

	public function getArrayMembershipConfiguration($config_id): ?array{
		$f = __METHOD__;
		$config = parent::getArrayMembershipConfiguration($config_id);
		$mime = $this->getMimeType();
		switch ($mime) {
			case MIME_TYPE_OCTET_STREAM:
				$config['extension'] = true; // $this->getOriginalFileExtension();
				return $config;
			case MIME_TYPE_GIF:
			case MIME_TYPE_PNG:
			case MIME_TYPE_JPEG:
				$config = array_merge($config, ImageData::getArrayMembershipConfigurationStatic($config_id, $this));
				// $config['correspondentKey'] = true;
				return $config;
			default:
				Debug::warning("{$f} invalid mime type \"{$mime}\"");
				return $config;
		}
	}

	public function getMessageBox(){
		return $this->getMessageObject()->getMessageBox();
	}

	public function getOriginalFilename():string{
		return $this->getColumnValue("originalFilename");
	}

	public function setFileAesNonce(string $nonce):string{
		$f = __METHOD__;
		$length = strlen($nonce);
		if ($length !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES) {
			Debug::error("{$f} nonce is incorrect length ({$length}, should be " . SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES . ")");
			Debug::printStackTrace();
		}
		return $this->setColumnValue('fileAesNonce', $nonce);
	}

	public function getFileAesKey():string{
		$f = __METHOD__;
		$aes_key = $this->getColumnValue('fileAesKey');
		$length = strlen($aes_key);
		$shouldbe = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
		if ($length !== $shouldbe) {
			Debug::error("{$f} file AES key is wrong length ({$length}, should be {$shouldbe})");
		}
		return $aes_key;
	}

	public function setFileAesKey(string $aes_key):string{	
		$f = __METHOD__;
		if (! isset($aes_key) || $aes_key == "") {
			Debug::error("{$f} file AES key is null or empty string");
		}
		$length = strlen($aes_key);
		$shouldbe = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
		if ($length == 7) {
			Debug::error("{$f} \"{$aes_key}\"");
		} elseif ($length !== $shouldbe) {
			Debug::error("{$f} file AES key is wrong length ({$length}, should be {$shouldbe})");
		}
		$this->setColumnValue('fileAesKey', $aes_key);
		return $aes_key;
	}

	public function setMessageObject($obj){
		return $this->setForeignDataStructure("messageKey", $obj);
	}

	public function getMessageObject(){
		return $this->getForeignDataStructure("messageKey");
	}

	public function getMetadataJson(){
		return $this->getColumnValue('metadataJson');
	}

	public function setMetadataJson($mjc){
		$f = __METHOD__;
		if (empty($mjc)) {
			Debug::warning("{$f} parameter is undefined");
		}
		return $this->setColumnValue('metadataJson', $mjc);
	}

	/**
	 * returns cleartext name of the file cipher; for the original filename see getOriginalFilename
	 *
	 * @return NULL|string
	 */
	public function getFilename():string{
		return $this->getColumnValue("filename");
	}

	public function hasFileIndexNonce():bool{
		return $this->hasColumnValue("fileIndexNonce");
	}

	public function setFileIndexNonce(string $nonce):string{
		return $this->setColumnValue('fileIndexNonce', $nonce);
	}

	public function getFileIndexNonce():string{
		return $this->getColumnValue('fileIndexNonce');
	}

	public function getVirtualColumnValue(string $index){
		$f = __METHOD__;
		try {
			switch ($index) {
				case "height":
					return $this->getImageHeight();
				case "width":
					return $this->getImageWidth();
				case "orientation":
					return $this->getOrientation();
				default:
					return parent::getVirtualColumnValue($index);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try {
			parent::declareColumns($columns, $ds);

			$columns["mimeType"]->setEncryptionScheme(AsymmetricEncryptionScheme::class);
			$columns["filename"]->setEncryptionScheme(AsymmetricEncryptionScheme::class);
			$columns["originalFilename"]->setEncryptionScheme(MessageEncryptionScheme::class);
			$columns["size"]->setEncryptionScheme(AsymmetricEncryptionScheme::class);
			$columns['fileHash']->setEncryptionScheme(AsymmetricEncryptionScheme::class);

			$file_aes_nonce = new Base64Datum("fileAesNonce");
			$file_aes_key = new Base64Datum("fileAesKey");
			// $file_aes_key->setDefaultValue(null);
			$file_aes_key->setEncryptionScheme(AsymmetricEncryptionScheme::class);
			$file_index_nonce = new NonceDatum("fileIndexNonce");
			$file_index_nonce->setEncryptionScheme(AsymmetricEncryptionScheme::class);
			$file_index_nonce->setGenerationClosure(function (NonceDatum $datum) {
				return random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);
			});
			$metadataJson = new UrlDatum("metadataJson");
			$metadataJson->setEncryptionScheme(MessageEncryptionScheme::class);
			$height = new VirtualDatum("height");
			$width = new VirtualDatum("width");
			$orientation = new VirtualDatum("orientation");
			$mime_string = new VirtualDatum("mimeTypeString");
			$correspondentKey = new VirtualDatum("correspondentKey");
			static::pushTemporaryColumnsStatic($columns, $file_aes_nonce, $file_aes_key, $file_index_nonce, $metadataJson, $height, $width, $orientation, $mime_string, $correspondentKey);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setSize($size){
		return $this->setColumnValue("size", $size);
	}

	public function hasSize():bool{
		return $this->hasColumnValue("size");
	}

	public function getSize(){
		$f = __METHOD__;
		if (! $this->hasSize()) {
			Debug::error("{$f} size is undefined");
			return 0;
		}
		return $this->getColumnValue("size");
	}

	public function hasMimeType():bool{
		return $this->hasColumnValue("mimeType");
	}

	public function hasOriginalFilename():bool{
		return $this->hasColumnValue("originalFilename");
	}

	public function hasFilename():bool{
		return $this->hasColumnValue("filename");
	}

	public static function getPrettyClassName():string{
		return _("Encrypted file");
	}

	public static function getPrettyClassNames():string{
		return _("Encrypted files");
	}

	public static function getTableNameStatic(): string{
		return "encrypted_files";
	}
}

