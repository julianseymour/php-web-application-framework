<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\SubtypeColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\DoubleDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\CleartextFileData;
use JulianSeymour\PHPWebApplicationFramework\input\RangeInput;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;

class ImageData extends CleartextFileData implements StaticElementClassInterface, StaticSubtypeInterface, StaticTableNameInterface{

	use StaticTableNameTrait;
	use SubtypeColumnTrait;
	
	protected $resampledFilename;

	protected $resampledThumbnailFilename;

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$width = new UnsignedIntegerDatum("width", 16);
		$height = new UnsignedIntegerDatum("height", 16);
		$type = new StringEnumeratedDatum("subtype", 8);
		$map = [
			IMAGE_TYPE_GENERIC,
			IMAGE_TYPE_ENCRYPTED_ATTACHMENT,
			IMAGE_TYPE_SLIDE,
			IMAGE_TYPE_PROFILE,
			IMAGE_TYPE_KYC
		];
		$type->setValidEnumerationMap($map);
		$type->setValue(IMAGE_TYPE_GENERIC);
		$focal_line_ratio = new DoubleDatum("focalLineRatio");
		$focal_line_ratio->setDefaultValue(0.5);
		$focal_line_ratio->setUserWritableFlag(true);
		$focal_line_ratio->setElementClass(RangeInput::class);
		$focal_line_ratio->setHumanReadableName(_("Focal line ratio"));
		$thumb_h = new VirtualDatum("thumbnailHeight");
		$thumb_w = new VirtualDatum("thumbnailWidth");
		$thumb_path = new VirtualDatum("webThumbnailPath");
		$orientation = new VirtualDatum("orientation");
		$name = new NameDatum("name");
		$name->setNullable(true);
		array_push($columns, $width, $height, $type, $focal_line_ratio, $thumb_h, $thumb_w, $thumb_path, $orientation, $name);
	}

	public static function getImageTypeStatic(){
		return static::getSubtypeStatic();
	}

	public static function getSubtypeStatic():string{
		return CONST_ERROR;
	}
	
	public function getThumbnail(){
		if($this->hasThumbnail()) {
			return $this->getForeignDataStructure("thumbnail");
		}
		return $this->setThumbnail(new ImageThumbnail($this));
	}

	public function setThumbnail(?ImageThumbnail $thumb): ?ImageThumbnail{
		return $this->setForeignDataStructure("thumbnail", $thumb);
	}

	public static function getTableNameStatic(): string{
		return "images";
	}

	public function calculateAspectRatio(){
		return $this->getImageWidth() / $this->getImageHeight();
	}

	public static function getPhylumName(): string{
		return "images";
	}

	public static function getPrettyClassName():string{
		return _("Image");
	}

	public static function getPrettyClassNames():string{
		return _("Images");
	}

	public function hasImageHeight():bool{
		return $this->hasColumnValue("height");
	}

	public function hasImageWidth():bool{
		return $this->hasColumnValue("width");
	}

	public function getImageHeight():int{
		return $this->getColumnValue("height");
	}

	public function getImageWidth():int{
		return $this->getColumnValue("width");
	}

	public function setImageWidth(int $width):int{
		return $this->setColumnValue("width", $width);
	}

	public function setImageHeight(int $height):int{
		return $this->setColumnValue("height", $height);
	}

	public function setResampledThumbnailFilename(?string $rfn):?string{
		return $this->resampledThumbnailFilename = $rfn;
	}

	public function getResampledThumbnailFilename():string{
		$f = __METHOD__;
		if(!$this->hasResampledThumbnailFilename()){
			Debug::error("{$f} resampled thumbnail filename is undefined");
		}
		return $this->resampledThumbnailFilename;
	}

	public function hasResampledThumbnailFilename():bool{
		return isset($this->resampledThumbnailFilename);
	}

	private function getImageThumbnailToWrite()
	{
		$f = __METHOD__;
		try{
			if($this->hasResampledThumbnailFilename()) {
				$resampled_filename = $this->getResampledThumbnailFilename();
			}else{
				$resampled_filename = $this->resampleThumbnail();
				$this->setResampledThumbnailFilename($resampled_filename);
			}
			Debug::print("{$f} resampled thumbnail is \"{$resampled_filename}\"");
			return file_get_contents($resampled_filename);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getThumbnailFilename()
	{
		if($this->isTinyImage()) {
			return $this->getFilename();
		}
		return $this->getThumbnail()->getFilename();
	}

	public function getFullThumbnailDirectory()
	{
		if($this->isTinyImage()) {
			return $this->getFullFileDirectory();
		}
		return $this->getThumbnail()->getFullFileDirectory();
	}

	public function getWebThumbnailDirectory()
	{
		if($this->isTinyImage()) {
			return $this->getWebFileDirectory();
		}
		return $this->getThumbnail()->getWebFileDirectory();
	}

	public function getWebThumbnailPath()
	{
		if($this->isTinyImage()) {
			return $this->getWebFilePath();
		}
		return $this->getThumbnail()->getWebFilePath();
	}

	public function getFullThumbnailPath()
	{
		if($this->isTinyImage()) {
			return $this->getFullFilePath();
		}
		return $this->getThumbnail()->getFullFilePath();
	}

	public function getImageData()
	{
		return $this;
	}

	public function isTinyImage()
	{
		return $this->getImageHeight() <= THUMBNAIL_MAX_DIMENSION && $this->getImageWidth() <= THUMBNAIL_MAX_DIMENSION;
	}

	private function writeImageThumbnail()
	{
		$f = __METHOD__;
		if($this->isTinyImage()) {
			Debug::print("{$f} image does not require resizing");
			return SUCCESS;
		}
		$file = $this->getImageThumbnailToWrite();
		$full_path = $this->getFullThumbnailPath();
		$directory = $this->getFullThumbnailDirectory();
		if(!is_dir($directory)) {
			Debug::print("{$f} directory \"{$directory}\" does not exist");
			mkdir($directory, 0755, true);
		}
		Debug::print("{$f} about to write to thumbnail path \"{$full_path}\"");
		$bytes = file_put_contents($full_path, $file);
		if($bytes > 0) {
			Debug::print("{$f} successfully wrote thumbnail \"{$full_path}\"");
			return SUCCESS;
		}
		Debug::error("{$f} failed to write encrypted file");
		return $this->setObjectStatus(ERROR_FILE_PUT_CONTENTS);
	}

	protected function writeFile()
	{
		$f = __METHOD__;
		try{
			$print = false;
			if(!$this->isThumbnail()) {
				if($print) {
					Debug::print("{$f} about to write image thumbnail");
				}
				$status = $this->writeImageThumbnail();
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} writing image thumbnail returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}
			if($print) {
				Debug::print("{$f} about to call parent function");
			}
			$status = parent::writeFile();
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} parent function returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			if($print) {
				Debug::print("{$f} parent function executed successfully");
			}
			return $this->setObjectStatus($status);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function processRepackedIncomingFiles($files)
	{
		$f = __METHOD__;
		try{
			$print = false;
			if($print) {
				Debug::print("{$f} about to call parent function");
			}
			$status = parent::processRepackedIncomingFiles($files);
			if($status === ERROR_UPLOAD_NO_FILE) {
				if($print) {
					Debug::print("{$f} no file -- maybe the user is altering metadata for an existing file");
				}
				return $status;
			}elseif($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} parent function returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} parent function executed successfully");
			}
			$status = $this->processImageDimensions();
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processImageDimensions returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} returning successfully");
			}
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function afterGenerateInitialValuesHook(): int
	{
		$f = __METHOD__;
		try{
			$this->setImageType(static::getImageTypeStatic());
			return parent::afterGenerateInitialValuesHook();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::reconfigureColumns($columns, $ds);
		$columns["name"]->setNullable(true);
		$columns['originalFilename']->setElementClass(ImageThumbnailFileInput::class);
		$fields = [
			'userName',
			'userAccountType',
			'userKey'
		];
		foreach($fields as $field) {
			$columns[$field]->setNullable(true);
		}
		$fields = [
			//'userTemporaryRole',
			'userNameKey'
		];
		foreach($fields as $field) {
			$columns[$field]->setNullable(true); //volatilize();
		}
		$map = [
			MIME_TYPE_GIF,
			MIME_TYPE_JPEG,
			MIME_TYPE_PNG
		];
		$columns["mimeType"]->setValidEnumerationMap($map);
	}

	public function getImageType(){
		return $this->getSubtype();
	}

	public function hasImageType():bool{
		return $this->hasSubtype();
	}

	public function setImageType(string $type):string{
		return $this->setSubtype($type);
	}

	public function ejectImageType(){
		return $this->ejectSubtype();
	}

	public function getFocalLineRatio(){
		return $this->getColumnValue("focalLineRatio");
	}

	public function setResampledFilename(?string $rfn):?string{
		return $this->resampledFilename = $rfn;
	}

	public function getResampledFilename():string{
		return $this->resampledFilename;
	}

	public function hasResampledFilename():bool{
		return isset($this->resampledFilename);
	}

	public static function resampleImage(string $uploaded_filename, string $mime_type, int $dst_w, int $dst_h):string{
		$f = __METHOD__;
		try{
			$print = false;
			$src_w = null;
			$src_h = null;
			$mime_extensions = [
				MIME_TYPE_GIF => EXTENSION_GIF,
				MIME_TYPE_JPEG => EXTENSION_JPG,
				MIME_TYPE_PNG => EXTENSION_PNG
			];
			list ($src_w, $src_h) = getimagesize($uploaded_filename);
			$resampled_filename = @tempnam("tmp", $mime_extensions[$mime_type]);
			$resampled_file_resource = imagecreatetruecolor($dst_w, $dst_h);
			if($resampled_file_resource == false) {
				Debug::error("{$f} imagecreatetruecolor({$dst_w}, {$dst_h}) returned false");
			}
			if($mime_type === MIME_TYPE_JPEG) {
				if($print) {
					Debug::print("{$f} attachment is JPEG");
				}
				$uploaded_file_resource = imagecreatefromjpeg($uploaded_filename);
			}elseif($mime_type === MIME_TYPE_PNG) {
				if($print) {
					Debug::print("{$f} attachment is PNG");
				}
				imagealphablending($resampled_file_resource, false);
				$uploaded_file_resource = imagecreatefrompng($uploaded_filename);
			}elseif($mime_type === MIME_TYPE_GIF) {
				if($print) {
					Debug::print("{$f} image is a GIF");
				}
				$uploaded_file_resource = imagecreatefromgif($uploaded_filename);
			}
			if(!$uploaded_file_resource) {
				Debug::error("{$f} uploaded file resource returned false");
			}
			if(! imagecopyresampled($resampled_file_resource, $uploaded_file_resource, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h)) {
				Debug::error("{$f} imagecopyresampled failed");
			}elseif($print) {
				Debug::print("{$f} imagecopyresampled succeeded");
			}
			switch ($mime_type) {
				case MIME_TYPE_JPEG:
					if(! imagejpeg($resampled_file_resource, $resampled_filename, 95)) {
						Debug::error("{$f} image jpeg failed");
					}
					break;
				case MIME_TYPE_PNG:
					imagesavealpha($resampled_file_resource, true);
					// Note: PNG uses compression level 0-9, not quality level 0-100
					if(! imagepng($resampled_file_resource, $resampled_filename, 9)) {
						Debug::error("{$f} imagepng failed");
					}elseif(exif_imagetype($resampled_filename) !== IMAGETYPE_PNG) {
						Debug::error("{$f} not a PNG after imagepng");
					}
					break;
				case MIME_TYPE_GIF:
					if(! imagegif($resampled_file_resource, $resampled_filename)) {
						Debug::error("{$f} ");
					}
					break;
				default:
					Debug::error("{$f} illegal mime type \"{$mime_type}\"");
			}
			if($print) {
				Debug::print("{$f} put compressed/downscaled file into \"{$resampled_filename}\"");
			}
			return $resampled_filename;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function beforeDeleteHook(mysqli $mysqli): int
	{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->isTinyImage()) {
				if($print) {
					Debug::print("{$f} tiny image -- returning parent function");
				}
				return parent::beforeDeleteHook($mysqli);
			}
			$status = $this->getThumbnail()->delete($mysqli); // Thumbnail($mysqli);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} delete thumbnail returned error status \"{$err}\"");
			}elseif($print) {
				Debug::print("{$f} successfully deleted thumbnail -- about to return parent function");
			}
			return parent::beforeDeleteHook($mysqli);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getThumbnailWidth():int{
		return $this->getThumbnail()->getImageWidth();
	}

	public function getThumbnailHeight():int{
		return $this->getThumbnail()->getImageHeight();
	}

	public function isTall():bool{
		return $this->getImageHeight() > $this->getImageWidth();
	}

	private function resampleThumbnail(){
		$uploaded_filename = $this->getUploadedTempFilename();
		$mime_type = $this->getMimeType();
		$dst_w = $this->getThumbnailWidth();
		$dst_h = $this->getThumbnailHeight();
		return static::resampleImage($uploaded_filename, $mime_type, $dst_w, $dst_h);
	}

	public function isThumbnail():bool{
		return false;
	}

	public static function getOrientationStatic($that): string
	{
		$f = __METHOD__;
		$height = $that->getImageHeight();
		$width = $that->getImageWidth();
		if($height > $width) {
			return ORIENTATION_PORTRAIT;
		}elseif($width > $height) {
			return ORIENTATION_LANDSCAPE;
		}elseif($height === $width) {
			return ORIENTATION_SQUARE;
		}else{
			Debug::error("{$f} impossible");
		}
	}

	public function getVirtualColumnValue($index)
	{
		switch ($index) {
			case "orientation":
				return $this->getOrientation();
			case "thumbnailHeight":
				return $this->getThumbnailHeight();
			case "thumbnailWidth":
				return $this->getThumbnailWidth();
			case "webThumbnailPath":
				return $this->getWebThumbnailPath();
			default:
				return parent::getVirtualColumnValue($index);
		}
	}

	public function hasThumbnail()
	{
		return $this->hasForeignDataStructure("thumbnail");
	}

	public function hasVirtualColumnValue(string $column_name): bool
	{
		switch ($column_name) {
			case "orientation":
				return $this->hasImageHeight() && $this->hasImageWidth();
			case "thumbnailHeight":
				if($this->isTinyImage()) {
					return $this->hasImageHeight();
				}
				return $this->hasThumbnail() && $this->getThumbnail()->hasImageHeight();
			case "thumbnailWidth":
				if($this->isTinyImage()) {
					return $this->hasImageWidth();
				}
				return $this->hasThumbnail() && $this->getThumbnail()->hasImageWidth();
			case "webThumbnailPath":
				return $this->hasThumbnail() && $this->getThumbnail()->hasFilename();
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public static function getArrayMembershipConfigurationStatic($config_id, $that)
	{
		$config = [
			"height" => true,
			"width" => true,
			"extension" => true,
			"orientation" => true
		];
		if($that->hasColumn("focalLineRatio")) {
			$config['focalLineRatio'] = true;
		}
		return $config;
	}

	public function getArrayMembershipConfiguration($config_id): ?array
	{
		$f = __METHOD__;
		try{
			$config = parent::getArrayMembershipConfiguration($config_id);
			switch ($config_id) {
				case "default":
				default:
					$config = array_merge($config, static::getArrayMembershipConfigurationStatic($config_id, $this));
					return $config;
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getFileToWrite()
	{
		$f = __METHOD__;
		try{
			if($this->hasResampledFilename()) {
				$resampled_filename = $this->getResampledFilename();
			}else{
				$resampled_filename = $this->getCompressedFilename(); // resampleImageToMaxDimensions();
				$this->setResampledFilename($resampled_filename);
			}
			Debug::print("{$f} resampled filename is \"{$resampled_filename}\"");
			return file_get_contents($resampled_filename);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string
	{
		return ImageElement::class;
	}
}
