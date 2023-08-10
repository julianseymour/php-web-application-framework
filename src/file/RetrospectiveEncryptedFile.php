<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\file\compress\ZipFileData;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateContextInterface;
use Exception;
use mysqli;

class RetrospectiveEncryptedFile extends EncryptedFile implements TemplateContextInterface
{

	public function getFileCleartextString()
	{
		$f = __METHOD__; //RetrospectiveEncryptedFile::getShortClass()."(".static::getShortClass().")->getFileCleartextString()";
		try {
			// Debug::print("{$f} entered");
			// 1. get encrypted file
			// Debug::print("{$f} about to get name of encrypted zip file");
			$filename = $this->getFilename();
			if ($filename == null) {
				Debug::error("{$f} filename is null");
			}
			Debug::print("{$f} about to get contents of encrypted zip file \"{$filename}\"");
			$zipped_file_cipher = file_get_contents($filename);
			if ($zipped_file_cipher == null) {
				Debug::error("{$f} file cipher is undefined");
				return null;
			}
			// Debug::print("{$f} successfully acquired contents of encrypted zipped file \"{$filename}\"");
			// 2. decrypt file
			// Debug::print("{$f} about to get file AES nonce");
			$nonce = $this->getFileAesNonce();
			if ($nonce == null) {
				Debug::error("{$f} file AES nonce is undefined");
				return null;
			}
			// Debug::print("{$f} got file AES nonce");
			// Debug::print("{$f} about to get file AES key");
			$aes_key = $this->getFileAesKey();
			if ($aes_key == null) {
				Debug::error("{$f} AES key returned null");
				return null;
			}
			$length = strlen($aes_key);
			$shouldbe = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
			if ($length !== $shouldbe) {
				Debug::error("{$f} file AES key is wrong length ({$length}, should be {$shouldbe})");
			}
			// Debug::print("{$f} got file AES key");
			// Debug::print("{$f} about to decrypt zipped file cipher");
			$zipped_file_cleartext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($zipped_file_cipher, null, $nonce, $aes_key);
			if ($zipped_file_cleartext == null) {
				Debug::error("{$f} decrypting zipped file from file cipher returned null");
				return null;
			}
			// Debug::print("{$f} decrypted zipped file from cipher");
			// 3. unzip file
			// Debug::print("{$f} about to put the zipped file's contents into a temp file");
			$zip_filename = tempnam("tmp", "zip");
			file_put_contents($zip_filename, $zipped_file_cleartext);
			// Debug::print("{$f} put the zipped file's contents into temporary file \"{$zip_filename}\"");
			// Debug::print("{$f} about to get original filename for temporary zipped file \"{$zip_filename}\"");
			$original_filename = $this->getOriginalFilename();
			// Debug::print("{$f} original filename is \"{$original_filename}\"");
			// Debug::print("{$f} about to unzip contents of file \"{$original_filename}\" from archive \"{$zip_filename}\"");
			$unzipped_file_contents = ZipFileData::unzipSingleFile($zip_filename, $original_filename);
			unlink($zip_filename);
			if ($unzipped_file_contents == null) {
				Debug::error("{$f} unzipped file contents returned null");
				Debug::printStackTrace();
				return null;
			}
			// Debug::print("{$f} successfully unzipped file contents");
			// Debug::print("{$f} returning normally");
			return $unzipped_file_contents;
		} catch (Exception $x) {
			x($f, $x);
			return null;
		}
	}

	public function outputFileToBrowser()
	{
		$f = __METHOD__; //RetrospectiveEncryptedFile::getShortClass()."(".static::getShortClass().")->outputFileToBrowser()";
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			$mime = $this->getMimeType();
			$cleartext = $this->getFileCleartextString();
			if (! is_string($mime)) {
				Debug::error("{$f} mime type \"{$mime}\" is the wrong data type");
			}
			if ($mime === MIME_TYPE_OCTET_STREAM) {
				echo $cleartext;
			} else {
				if ($print) {
					Debug::print("{$f} image type is \"{$mime}\"");
				}
				$image = imagecreatefromstring($cleartext);
				if ($image == false) {
					Debug::error("{$f} imagecreatefromstring returned false");
				}
				switch ($mime) {
					case MIME_TYPE_JPEG:
						$worked = imagejpeg($image);
						break;
					case MIME_TYPE_PNG:
						imagesavealpha($image, true);
						$worked = imagepng($image);
						break;
					case MIME_TYPE_GIF:
						$worked = imagegif($image);
						break;
					default:
						Debug::error("{$f} illegal mime type \"{$mime}\"");
						return $this->echoResponse(ERROR_MIME_TYPE);
				}
				if (! $worked) {
					Debug::error("{$f} imagejpeg/png/gif failed");
				}
				if ($print) {
					Debug::print("{$f} about to flush image");
				}
				ob_flush();
				// imagedestroy($image);
			}
			// exit();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getMetadataJson()
	{
		return $this->getColumnValue("metadataJson");
	}

	public function processMetadataJson($json)
	{
		$f = __METHOD__; //RetrospectiveEncryptedFile::getShortClass()."(".static::getShortClass().")->processMetadataJson()";
		try {
			$print = false;
			if ($json == null) {
				Debug::warning("{$f} metadata JSON is null");
				return null;
			} elseif ($print) {
				Debug::print("{$f} valid metadata JSON is \"{$json}\"");
			}
			$arr = [];
			parse_str($json, $arr);
			if ($arr == null) {
				Debug::warning("{$f} parsed metadata array is null");
				Debug::print("{$f} metadata string is \"{$json}\"");
				Debug::printStackTrace();
				return null;
			} elseif (! is_array($arr)) {
				Debug::warning("{$f} arr is not an array");
				Debug::print($arr);
				Debug::printStackTrace();
				return null;
			}
			$mime = $this->getMimeType();
			switch ($mime) {
				case MIME_TYPE_JPEG:
				case MIME_TYPE_PNG:
				case MIME_TYPE_GIF:
					if ($print) {
						Debug::print("{$f} setting image dimensions");
					}
					$height = intval($arr['height']);
					$width = intval($arr['width']);
					$this->setImageHeight($height);
					$this->setImageWidth($width);
					break;
				default:
					if ($print) {
						Debug::print("{$f} file has mime type \"{$mime}\"");
					}
					break;
			}

			if (isset($arr['counterpartKey'])) {
				$this->setCounterpartKey($arr['counterpartKey']);
				sodium_memzero($arr['counterpartKey']);
			}
			return $arr;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function afterLoadHook(mysqli $mysqli): int
	{
		$f = __METHOD__; //RetrospectiveEncryptedFile::getShortClass()."(".static::getShortClass().")->afterLoadHook()";
		try {
			$print = false;
			$json = $this->getMetadataJson();
			$this->processMetadataJson($json);
			if ($print) {
				if (! $this->hasCounterpartKey()) {
					Debug::warning("{$f} counterpart key is undefined");
				} else {
					Debug::print("{$f} counterpart key: \"" . $this->getCounterpartKey() . "\"");
				}
			}
			return parent::afterLoadHook($mysqli);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function template()
	{
		return;
	}
}
