<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ImageKeyColumnTrait
{

	public function getImageData(): ImageData
	{
		$f = __METHOD__; //"ImageKeyColumnTrait(".static::getShortClass().")->getImageData()";
		if(!$this->hasImageData()) {
			Debug::error("{$f} image data is undefined");
		}
		return $this->getForeignDataStructure("imageKey");
	}

	public function setImageData($struct): ImageData
	{
		return $this->setForeignDataStructure("imageKey", $struct);
	}

	public function hasImageData()
	{
		return $this->hasForeignDataStructure('imageKey');
	}

	public function hasImageKey(): bool
	{
		return $this->hasColumnValue("imageKey");
	}

	public function getImageKey(): string
	{
		return $this->getColumnValue("imageKey");
	}

	public function setImageKey(): string
	{
		return $this->setColumnValue("imageKey");
	}

	public function ejectImageKey(): ?string
	{
		return $this->ejectColumnValue("imageKey");
	}
}