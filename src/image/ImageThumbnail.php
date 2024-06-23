<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;
use mysqli;

class ImageThumbnail extends ImageData{

	protected $imageData;

	// the image that this object is a thumbnail of
	public function __construct($image = null){
		parent::__construct();
		if(isset($image)){
			$this->setImageData($image);
		}
	}

	public function setImageData($image){
		return $this->imageData = $image;
	}

	public function beforeDeleteHook(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$filename = $this->getFullFilePath();
			if(! file_exists($filename)){
				Debug::print("{$f} already deleted");
				return SUCCESS;
			}
			$deleted = unlink($filename);
			if(!$deleted){
				Debug::error("{$f} deletion failed");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 *
	 * @return ImageData
	 * {@inheritdoc}
	 * @see ImageData::getImageData()
	 */
	public function getImageData(){
		$f = __METHOD__;
		if(!$this->hasImageData()){
			Debug::error("{$f} image data is undefined");
		}
		return $this->imageData;
	}

	public function getFilename():string{
		return "__thumb-" . $this->getImageData()->getFilename();
	}

	public function getFullFileDirectory():string{
		return $this->getImageData()->getFullFileDirectory() . "/thumbs";
	}

	public function getWebFileDirectory():string{
		return $this->getImageData()->getWebFileDirectory() . '/thumbs';
	}

	public function getWebFilePath():string{
		return $this->getWebFileDirectory() . "/" . $this->getFilename();
	}

	public function getFullFilePath():string{
		return $this->getFullFileDirectory() . "/" . $this->getFilename();
	}

	public function getFocalLineRatio():float{
		return $this->getImageData()->getFocalLineRatio();
	}

	public function isThumbnail():bool{
		return true;
	}

	public function hasImageData():bool{
		return isset($this->imageData);
	}

	public function getSerialNumber():int{
		return $this->getImageData()->getSerialNumber();
	}

	public function getIdentifierValue():string{
		return $this->getImageData()->getIdentifierValue();
	}

	public function hasIdentifierValue(): bool{
		return $this->getImageData()->hasIdentifierValue();
	}

	public function getUserKey():string{
		return $this->getImageData()->getUserKey();
	}

	public function getUserData():UserData{
		return $this->getImageData()->getUserData();
	}

	public function getOriginalFilename():string{
		return $this->getImageData()->getOriginalFilename();
	}

	public function getMimeType():string{
		return $this->getImageData()->getMimeType();
	}

	public function getImageWidth():int{
		$img = $this->getImageData();
		if(!$img->hasImageHeight() || !$img->hasImageWidth()){
			return 0;
		}
		$height = $img->getImageHeight();
		$width = $img->getImageWidth();
		if($width < $height){
			return floor((THUMBNAIL_MAX_DIMENSION / $height) * $width);
		}
		return THUMBNAIL_MAX_DIMENSION;
	}

	public function getImageHeight():int{
		$img = $this->getImageData();
		if(!$img->hasImageHeight() || !$img->hasImageWidth()){
			return 0;
		}
		$height = $img->getImageHeight();
		$width = $img->getImageWidth();
		if($width > $height){
			return floor((THUMBNAIL_MAX_DIMENSION / $width) * $height);
		}
		return THUMBNAIL_MAX_DIMENSION;
	}

	public static function getSubtypeStatic():string{
		return IMAGE_TYPE_THUMBNAIL;
	}
}
