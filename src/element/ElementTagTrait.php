<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ElementTagTrait{

	protected $tag;

	public function setElementTag(?string $tag): ?string{
		if($this->hasElementTag()){
			$this->release($this->tag);
		}
		return $this->tag = $this->claim($tag);
	}

	public function hasElementTag(): bool{
		return isset($this->tag);
	}

	public function getElementTag(): string{
		$f = __METHOD__;
		if(!$this->hasElementTag()){
			Debug::error("{$f} element tag is undefined for this ".$this->getDebugString());
		}
		return $this->tag;
	}
	
	public function withElementTag(string $tag):object{
		$this->setElementTag($tag);
		return $this;
	}
}
