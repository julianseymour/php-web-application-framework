<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ElementTagTrait
{

	protected $tag;

	public function setElementTag(?string $tag): ?string
	{
		if($tag === null) {
			unset($this->tag);
			return null;
		}
		return $this->tag = $tag;
	}

	public function hasElementTag(): bool{
		return isset($this->tag);
	}

	public function getElementTag(): string
	{
		$f = __METHOD__; //"ElementTagTrait(".static::getShortClass().")->getElementTag()";
		if(!$this->hasElementTag()) {
			Debug::error("{$f} element tag is undefined");
		}
		return $this->tag;
	}
	
	public function withElementTag(string $tag):object{
		$this->setElementTag($tag);
		return $this;
	}
}
