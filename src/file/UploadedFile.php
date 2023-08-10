<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\array_keys_exist;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class UploadedFile implements \Psr\Http\Message\UploadedFileInterface
{

	private $name;

	private $tmp_name;

	private $error;

	private $size;

	public function __construct($array = null)
	{
		if ($array !== null) {
			$this->setArray($array);
		}
	}

	public function setArray(array $array)
	{
		$f = __METHOD__; //UploadedFile::getShortClass()."(".static::getShortClass().")->setArray()";
		$print = false;
		if (! array_keys_exist($array, "error", "size", "name", "tmp_name")) {
			Debug::error("{$f} missing one or more array indices");
		} elseif ($print) {
			Debug::printArray($array);
		}
		$this->setError($array['error']);
		$this->setSize($array['size']);
		$this->setClientFilename($array['name']);
		$this->setTempName($array['tmp_name']);
		return $array;
	}

	public function getError():int
	{
		return $this->error;
	}

	public function getSize():?int
	{
		return $this->size;
	}

	public function getClientFilename():?string
	{
		return $this->name;
	}

	public function setError($error)
	{
		return $this->error = $error;
	}

	public function setSize($size)
	{
		return $this->size = $size;
	}

	public function setClientFilename($name)
	{
		return $this->name = $name;
	}

	public function getStream():\Psr\Http\Message\StreamInterface
	{
		$f = __METHOD__; //UploadedFile::getShortClass()."(".static::getShortClass().")->getStream()";
		ErrorMessage::unimplemented($f);
	}

	public function getClientMediaType():?string
	{
		$f = __METHOD__; //UploadedFile::getShortClass()."(".static::getShortClass().")->getClientMediaType()";
		ErrorMessage::unimplemented($f);
	}

	public function moveTo(string $targetPath):void
	{
		$f = __METHOD__; //UploadedFile::getClass() . "(" . static::class . ")->moveTo()";
		ErrorMessage::unimplemented($f);
	}

	public function getTempName()
	{
		return $this->tmp_name;
	}

	public function setTempName($tmp_name)
	{
		return $this->tmp_name = $tmp_name;
	}
}
