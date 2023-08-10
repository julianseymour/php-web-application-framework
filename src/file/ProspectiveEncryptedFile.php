<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSetForeignDataStructureEvent;
use Exception;
use mysqli;

abstract class ProspectiveEncryptedFile extends EncryptedFile
{

	public function __construct()
	{
		parent::__construct();
		$this->setReceptivity(DATA_MODE_RECEPTIVE);
	}

	public function setMimeType($mime)
	{
		$f = __METHOD__; //ProspectiveEncryptedFile::getShortClass()."(".static::getShortClass().")->setMimeType()";
		switch ($mime) {
			case MIME_TYPE_GIF:
			case MIME_TYPE_JPG:
			case MIME_TYPE_PNG:
				$type = MESSAGE_TYPE_IMAGE;
				break;
			default:
				$type = MESSAGE_TYPE_FILE;
				break;
				Debug::error("{$f} invalid mime type \"{$mime}\"");
		}
		if ($this->hasMessageObject()) {
			$this->getMessageObject()->setMessageType($type);
		} else {
			$this->addEventListener(EVENT_AFTER_SET_FOREIGN, function (AfterSetForeignDataStructureEvent $event, ProspectiveEncryptedFile $target) use ($type) {
				$column_name = $event->getProperty("columnName");
				$struct = $event->getProperty("data");
				if ($column_name === "messageKey") {
					$target->removeEventListener(EVENT_AFTER_SET_FOREIGN, $event->getListenerId());
					$struct->setMessageType($type);
				}
			});
		}
		return parent::setMimeType($mime);
	}

	public function getMetadataJson()
	{
		$f = __METHOD__; //ProspectiveEncryptedFile::getShortClass()."(".static::getShortClass().")->getMetadataJson()";
		try {
			$config = $this->getArrayMembershipConfiguration("metadata");
			Debug::printArray($config);
			$this->configureArrayMembership($config);
			$arr = $this->toArray();
			Debug::printArray($arr);
			if (! isset($arr)) {
				Debug::error("{$f} array is undefined");
			} elseif (! is_array($arr)) {
				Debug::error("{$f} that's not an array");
			}
			$mime = $this->getMimeType();
			switch ($mime) {
				case MIME_TYPE_JPG:
				case MIME_TYPE_PNG:
				case MIME_TYPE_GIF:
					if (! isset($arr['width']) || ! isset($arr['height'])) {
						Debug::error("{$f} invalid dimensions");
					}
					break;
				default:
					break;
			}
			// $arr['size'] = $this->getFileSize();
			$arr['counterpartKey'] = $this->getCounterpartKey();
			$json = http_build_query($arr);
			/*
			 * if(!is_json($json)){
			 * Debug::error("{$f} invalid JSON");
			 * }
			 */
			Debug::print("{$f} returning normally");
			return $json;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterGenerateInitialValuesHook(): int
	{
		$f = __METHOD__; //ProspectiveEncryptedFile::getShortClass()."(".static::getShortClass().")->afterGenerateKeyHook()";
		try {
			$json = $this->getMetadataJson();
			if (! isset($json)) {
				Debug::error("{$f} metadata JSON is undefined");
			}
			$this->setMetadataJson($json);
			return parent::afterGenerateInitialValuesHook();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/*
	 * public function generateFileIndexNonce(){
	 * $f = __METHOD__; //ProspectiveEncryptedFile::getShortClass()."(".static::getShortClass().")->generateFileIndexNonce()";
	 * return $this->setFileIndexNonce(random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES));
	 * }
	 */
	protected function beforeInsertHook(mysqli $mysqli): int
	{
		$f = __METHOD__; //ProspectiveEncryptedFile::getShortClass()."(".static::getShortClass().")->beforeInsertHook()";
		try {
			$ret = parent::beforeInsertHook($mysqli);
			if (! $this->hasMimeType()) {
				Debug::error("{$f} mime type is undefined");
			} /*
			   * elseif($this->getMimeType() !== MIME_TYPE_JPEG){
			   * Debug::error("{$f} only accepting JPEGs atm");
			   * }
			   */
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
