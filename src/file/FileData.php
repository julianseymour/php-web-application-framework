<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\array_key_last;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Sha1HashDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\image\ImageData;
use Exception;
use finfo;
use mysqli;

abstract class FileData extends UserOwned implements CacheableInterface
{

	use CacheableTrait;

	protected $fileContents;

	public abstract function setOriginalFilename($name);

	public abstract function hasOriginalFilename();

	public abstract function getOriginalFilename();

	public abstract function setMimeType($mime);

	public abstract function hasMimeType();

	public abstract function getMimeType();

	public abstract function setFilename($name);

	public abstract function hasFilename();

	public abstract function getFilename();

	public abstract function setSize($size);

	public abstract function hasSize();

	public abstract function getSize();

	public abstract function getFileToWrite();

	public abstract function getFullFileDirectory();

	public abstract function getWebFileDirectory();

	public function getFullFilePath()
	{
		return $this->getFullFileDirectory() . "/" . $this->getFilename();
	}

	public static final function getDataType(): string
	{
		return DATATYPE_FILE;
	}

	public final function hasSubtypeValue(): bool
	{
		return true;
	}

	public function hasUploadDirectory()
	{
		return $this->hasColumnValue("uploadDirectory");
	}

	public function setUploadDirectory($ud)
	{
		return $this->setColumnValue("uploadDirectory", $ud);
	}

	public function getUploadDirectory()
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasUploadDirectory()) {
			Debug::error("{$f} upload directory is undefined");
		}
		return $this->getColumnValue("uploadDirectory");
	}

	public static function getPhylumName(): string
	{
		return "files";
	}

	protected function beforeInsertHook(mysqli $mysqli): int
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasSize()) {
			Debug::error("{$f} size is undefined");
		}
		return parent::beforeInsertHook($mysqli);
	}

	/**
	 * Returns a boundary-separated string for attaching this file to a multipart/mixed email
	 *
	 * @param int $id
	 *        	: X-Attachment-Id
	 * @return string
	 */
	public function attach($id = null)
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$print = false;
			$cache = false;
			if (cache()->enabled() && $this->hasCacheKey()) {
				$key = $this->getCacheKey() . "_attached";
				if ($this->getCache()->hasAPCu($key)) {
					if ($print) {
						Debug::print("{$f} cache hit");
					}
					$chunks = $this->getCache()->getAPCu($key);
				} else {
					if ($print) {
						Debug::print("{$f} cache miss");
					}
					$cache = true;
				}
			} elseif ($print) {
				Debug::print("{$f} cache is disabled");
			}
			$dir = $this->getFullFileDirectory();
			$filename = $this->getFilename();
			if (! $cache) {
				$chunks = chunk_split(base64_encode(file_get_contents("{$dir}/{$filename}")));
			}
			$eol = "\r\n";
			$mime = $this->getMimeType();
			$attach .= "Content-Type:{$mime};name=\"{$filename}\"{$eol}";
			$attach .= "Content-Disposition:attachment{$eol}";
			$attach .= "Content-Transfer-Encoding:base64{$eol}";
			if ($id == null) {
				$id = rand(1000, 99999);
			}
			$attach .= "X-Attachment-Id:{$id}{$eol}{$eol}";
			$attach .= "{$chunks}{$eol}";
			if ($cache) {
				if ($print) {
					Debug::print("{$f} updating cache");
				}
				$this->getCache()->setAPCu($key, $attach, time() + 30 * 60);
			}
			return $attach;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasUserNormalizedName():bool{
		return $this->hasUserData() && $this->getUserData()->hasNormalizedName();
	}

	public function getUserNormalizedName():string{
		return $this->getUserData()->getNormalizedName();
	}

	public static function sendHeader(string $filename, $mime_code=null):string{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} filename is \"{$filename}\"");
			}
			if($mime_code === null){
				$splat = explode(".", $filename);
				$extension = strtolower($splat[count($splat) - 1]);
				if ($print) {
					Debug::print("{$f} extension is \"{$extension}\"");
				}
				switch ($extension) {
					case "gif":
						$mime_code = MIME_TYPE_GIF;
						break;
					case "jpg":
					case "jpe":
					case "jpeg":
						$mime_code = MIME_TYPE_JPEG;
						break;
					case "png":
						$mime_code = MIME_TYPE_PNG;
						break;
					case "pdf":
						$mime_code = MIME_TYPE_PDF;
						break;
					default:
						$mime_code = MIME_TYPE_OCTET_STREAM;
						break;
				}
			}
			header("Content-Type:{$mime_code}");
			switch ($mime_code) {
				case MIME_TYPE_GIF:
				case MIME_TYPE_JPEG:
				case MIME_TYPE_PNG:
					header("Content-Disposition:inline;filename=\"{$filename}\"");
					break;
				case MIME_TYPE_PDF:
					header("Content-Disposition:attachment;filename=\"{$filename}\"");
					break;
				default:
					header("Content-Transfer-Encoding:Binary");
					header("Content-Disposition:attachment;filename=\"{$filename}\"");
					break;
			}
			return $mime_code;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getOriginalFileExtension()
	{
		$string = $this->getOriginalFilename();
		$splat = explode(".", $string);
		return $splat[array_key_last($splat)];
	}

	public function getArrayMembershipConfiguration($config_id): ?array
	{
		// $f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$config = parent::getArrayMembershipConfiguration($config_id);
		switch ($config_id) {
			case "default":
			default:
				$config['originalFilename'] = true;
				$config['mimeType'] = true;
				// $config['mimeTypeString'] = true;
				$config['webFilePath'] = true;
				return $config;
		}
	}

	protected function beforeDeleteHook(mysqli $mysqli): int
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$filename = $this->getFullFilePath();
			if (! file_exists($filename)) {
				// Debug::print("{$f} already deleted");
				// return STATUS_DELETED;
			} else {
				$deleted = unlink($filename);
				if (! $deleted) {
					Debug::error("{$f} deletion failed");
				}
			}
			return parent::beforeDeleteHook($mysqli);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getExtension(): string
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$type = $this->getMimeType();
		switch ($type) {
			case MIME_TYPE_GIF:
				return EXTENSION_GIF;
			case MIME_TYPE_JPG:
				return EXTENSION_JPG;
			case MIME_TYPE_PNG:
				return EXTENSION_PNG;
			default:
				Debug::error("{$f} invalid mime type \"{$type}\"");
		}
	}

	public function getVirtualColumnValue(string $column_name)
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			switch ($column_name) {
				case "extension":
					return $this->getExtension();
				case "webFilePath":
					return $this->getWebFilePath();
				default:
					return parent::getVirtualColumnValue($column_name);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasVirtualColumnValue(string $column_name): bool
	{
		switch ($column_name) {
			case "extension":
			case "webFilePath":
				return true;
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try {
			parent::declareColumns($columns, $ds);
			$mime_type = new StringEnumeratedDatum("mimeType", 8);
			$mime_type->setValidEnumerationMap(mods()->getValidMimeTypes());
			$ofn = new FilenameDatum("originalFilename");
			$ofn->setHumanReadableName(_("Original filename"));
			$ofn->setAdminInterfaceFlag(true);
			$filename = new TextDatum("filename");
			// $filename->setUniqueFlag(true);
			$size = new UnsignedIntegerDatum("size", 32);
			$file_hash = new Sha1HashDatum("fileHash");
			$file_hash->setUniqueFlag(true);
			$web_path = new VirtualDatum("webFilePath");
			// $mime_str = new VirtualDatum("mimeTypeString");
			$extension = new VirtualDatum("extension");
			$uploadedTempFilename = new TextDatum("uploadedTempFilename");
			$uploadedTempFilename->volatilize();
			$uploadedTempFileSize = new UnsignedIntegerDatum("uploadedTempFileSize", 32);
			$uploadedTempFileSize->volatilize();
			$uploadDirectory = new TextDatum("uploadDirectory");
			$uploadDirectory->volatilize();
			static::pushTemporaryColumnsStatic($columns, $mime_type, $ofn, $filename, $size, $file_hash, $web_path, $extension, $uploadedTempFilename, $uploadedTempFileSize, $uploadDirectory);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setUploadedTempFilename($name){
		$f = __METHOD__;
		if (! isset($name)) {
			Debug::error("{$f} received null parameter");
		}
		// Debug::print("{$f} setting temp filename to \"{$name}\"");
		return $this->setColumnValue("uploadedTempFilename", $name);
	}

	public function hasUploadedTempFilename(){
		return $this->hasColumnValue("uploadedTempFilename");
	}

	public function getUploadedTempFilename(){
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasUploadedTempFilename()) {
			Debug::error("{$f} uploaded tempfilename is undefined");
		}
		return $this->getColumnValue("uploadedTempFilename");
	}

	protected function writeFile()
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$print = false;
			if ($print) {
				Debug::printStackTraceNoExit("{$f} entered");
			}
			$file = $this->getFileToWrite();
			if (! $this->hasSize()) {
				Debug::error("{$f} size is undefined");
			}
			$size = $this->getSize();
			if ($print) {
				Debug::print("{$f} file size is {$size} bytes");
			}
			$full_path = $this->getFullFilePath();
			$directory = $this->getFullFileDirectory();
			if (! is_dir($directory)) {
				if ($print) {
					Debug::print("{$f} directory \"{$directory}\" does not exist; making it now");
				}
				mkdir($directory, 0755, true);
			}
			if ($print) {
				Debug::print("{$f} about to write to file path \"{$full_path}\"");
			}
			$bytes = file_put_contents($full_path, $file);
			if ($bytes > 0) {
				if ($print) {
					Debug::print("{$f} successfully wrote file \"{$full_path}\"");
				}
				return SUCCESS;
			}
			Debug::error("{$f} failed to write file");
			return $this->setObjectStatus(ERROR_FILE_PUT_CONTENTS);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getUploadedTempFileSize()
	{
		return $this->getColumnValue("uploadedTempFileSize");
	}

	public function setUploadedTempFileSize($size)
	{
		return $this->setColumnValue("uploadedTempFileSize", $size);
	}

	protected function getCompressedFilename()
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$mime = $this->getMimeType();
		switch ($mime) {
			case MIME_TYPE_GIF:
			case MIME_TYPE_OCTET_STREAM:
				return $this->getUploadedTempFilename();
			case MIME_TYPE_JPEG:
			case MIME_TYPE_PNG:
				return $this->resampleImageToMaxDimensions();
			default:
				Debug::error("{$f} invalid mime type \"{$mime}\"");
		}
	}

	/**
	 * return filename of file containing compressed image data
	 *
	 * @return string
	 */
	protected function resampleImageToMaxDimensions()
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$uploaded_filename = $this->getUploadedTempFilename();
			$mime = $this->getMimeType();
			$dst_w = $this->getImageWidth();
			$dst_h = $this->getImageHeight();
			if (! isset($dst_h) || ! isset($dst_w)) {
				Debug::error("{$f} invalid dimensions");
			}
			$resampled_filename = ImageData::resampleImage($uploaded_filename, $mime, $dst_w, $dst_h);
			$this->setImageHeight($dst_h);
			$this->setImageWidth($dst_w);
			return $resampled_filename;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getOrientation(): string
	{
		return ImageData::getOrientationStatic($this);
	}

	protected function processImageDimensions()
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$src_w = null;
			$src_h = null;
			$uploaded_filename = $this->getUploadedTempFilename();
			list ($src_w, $src_h) = getimagesize($uploaded_filename);
			$mime = $this->getMimeType();
			if ($mime === MIME_TYPE_GIF) {
				// Debug::print("{$f} this is a gif");
				$this->setImageHeight($src_h);
				$this->setImageWidth($src_w);
				return SUCCESS;
			} elseif (false !== array_search($mime, [
				MIME_TYPE_JPEG,
				MIME_TYPE_PNG
			])) {
				// Debug::print("{$f} this is a JPEG/PNG");
				if ($src_h > $src_w) {
					if ($src_h > IMAGE_MAX_DIMENSION) {
						$dst_h = IMAGE_MAX_DIMENSION;
						$ratio = IMAGE_MAX_DIMENSION / $src_h;
						$dst_w = floor($ratio * $src_w);
						// Debug::print("{$f} image is too tall");
					} else {
						$dst_h = $src_h;
						$dst_w = $src_w;
						// Debug::print("{$f} image is within acceptible dimensions");
					}
				} elseif ($src_w > IMAGE_MAX_DIMENSION) {
					$dst_w = IMAGE_MAX_DIMENSION;
					$ratio = IMAGE_MAX_DIMENSION / $src_w;
					$dst_h = floor($ratio * $src_h);
				} else {
					$dst_h = $src_h;
					$dst_w = $src_w;
					// Debug::print("{$f} image is within acceptible dimensions ({$dst_w}x{$dst_h})");
				}
				$this->setImageHeight($dst_h);
				$this->setImageWidth($dst_w);
			} else {
				Debug::error("{$f} invalid mime type \"{$mime}\"");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasForeignDataStructureList(string $family): bool
	{
		return false;
	}

	public function processResizedFiles($arr)
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$posted = str_replace(' ', '+', $arr['resized']);
			$splat1 = explode(',', $posted);
			$splat2 = explode(";", $splat1[0]);
			$splat3 = explode(":", $splat2[0]);
			$splat4 = explode("/", $splat3[1]);
			$extension = $splat4[1];
			Debug::printArray($arr);
			$this->setOriginalFilename($arr['original_filename']);
			$temp_name = tempnam("tmp", $extension);
			$image = base64_decode($splat1[1]);
			$this->setFileHash(sha1($image));
			$put = file_put_contents($temp_name, $image);
			if (! $put) {
				Debug::error("{$f} file_put_contents returned false");
			}
			$this->setUploadedTempFilename($temp_name);
			$file_size = floor(strlen($posted));
			// Debug::print("{$f} calculated file size {$file_size}");
			$this->setUploadedTempFileSize($file_size);
			$status = $this->processMimeType();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} processMimeType returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// Debug::print("{$f} returning normally");
			$this->setObjectStatus(STATUS_READY_WRITE);
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function processMimeType()
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} yes, this gets called");
			}
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$temp_name = $this->getUploadedTempFilename();
			$needle = $finfo->file($temp_name);
			$mime_types = [
				EXTENSION_JPG => MIME_TYPE_JPEG,
				EXTENSION_JPEG => MIME_TYPE_JPEG,
				EXTENSION_PNG => MIME_TYPE_PNG,
				EXTENSION_GIF => MIME_TYPE_GIF
			];
			$extension = array_search($needle, $mime_types, true);
			if ($extension === false) {
				if ($print) {
					Debug::print("{$f} this is an arbitrary file attachment");
				}
				$this->setMimeType(MIME_TYPE_OCTET_STREAM);
			} else {
				if ($print) {
					Debug::print("{$f} image attachment");
				}
				$check = getimagesize($temp_name);
				$mime = $check["mime"];
				if ($check === false) {
					Debug::error("{$f} illegal self-reported mime type \"{$mime}\"");
					return $this->setObjectStatus(ERROR_MIME_TYPE);
				}
				$mime = $this->setMimeType($mime_types[$extension]);
				if ($mime === MIME_TYPE_GIF) {
					if ($print) {
						Debug::print("{$f} this is a GIF -- about to check its filesize");
					}
					$file_size = $this->getUploadedTempFileSize();
					if ($file_size > FILE_SIZE_LIMIT) {
						Debug::error("{$f} excessive file size");
						return $this->setObjectStatus(ERROR_FILE_SIZE);
					}
					if ($print) {
						Debug::print("{$f} file size of this GIF is ok");
					}
				} elseif ($print) {
					Debug::print("{$f} this is not a GIF upload");
				}
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function ejectFileHash()
	{
		return $this->ejectColumnValue("fileHash");
	}

	public function hasFileHash()
	{
		return $this->hasColumnValue("fileHash");
	}

	public function setFileHash($hash)
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($this->hasFileHash()) {
			Debug::error("{$f} there is no convceivable reason why we need to change file hash to something else");
		}
		// Debug::print("{$f} setting file hash to \"{$hash}\"");
		return $this->setColumnValue("fileHash", $hash);
	}

	public function getFileHash()
	{
		return $this->getColumnValue("fileHash");
	}

	public function hasFileContents(): bool
	{
		return isset($this->fileContents);
	}

	public function getFileContents()
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasFileContents()) {
			Debug::error("{$f} file contents are undefined");
		}
		return $this->fileContents;
	}

	public function processRepackedIncomingFiles($files)
	{
		$f = __METHOD__; //FileData::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered with the following array:");
				Debug::printArray($files);
			}
			if (! is_array($files)) { // instanceof Countable){
				Debug::error("{$f} files must be countable");
			} elseif (count($files) > 1) {
				Debug::error("{$f} not implemented: processing multiple file upload");
			} elseif (! array_key_exists(0, $files)) {
				Debug::warning("{$f} files array lacks an index 0");
				Debug::printArray(array_keys($files));
				Debug::printStackTrace();
			}
			$file = $files[0];
			if (is_array($file)) {
				Debug::print($file);
				Debug::error("{$f} file is the above printed array");
			}
			switch ($file->getError()) {
				case UPLOAD_ERR_OK:
					// Debug::print("{$f} OK");
					break;
				case UPLOAD_ERR_NO_FILE:
					// Debug::print("{$f} no file");
					return $this->setObjectStatus(ERROR_UPLOAD_NO_FILE);
				case UPLOAD_ERR_INI_SIZE:
					Debug::warning("{$f} file is too big (UPLOAD_ERR_INI_SIZE)");
					return $this->setObjectStatus(ERROR_FILE_SIZE);
				case UPLOAD_ERR_FORM_SIZE:
					Debug::error("{$f} file is too big (UPLOAD_ERR_FORM_SIZE)");
					return $this->setObjectStatus(ERROR_FILE_SIZE);
				default:
					Debug::error("{$f} default condition");
					return $this->setObjectStatus(ERROR_FILE_PARAMETERS);
			}
			$this->setOriginalFilename($file->getClientFilename());
			$temp_name = $this->setUploadedTempFilename($file->getTempName());
			$this->setUploadedTempFileSize($file->getSize());
			$this->setFileHash(sha1(file_get_contents($temp_name)));
			$status = $this->processMimeType();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} processMimeType returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$this->setObjectStatus(STATUS_READY_WRITE);
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::reconfigureColumns($columns, $ds);
		$columns['userTemporaryRole']->volatilize();
	}
}
