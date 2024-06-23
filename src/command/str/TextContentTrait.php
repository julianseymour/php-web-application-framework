<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait TextContentTrait{

	protected $textContent;

	public function hasTextContent(): bool{
		return isset($this->textContent);
	}

	public function getTextContent(){
		$f = __METHOD__;
		if(!$this->hasTextContent()){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} textContent is undefined. Created {$decl}");
		}
		return $this->textContent;
	}

	public function setTextContent($textContent){
		if($this->hasTextContent()){
			$this->release($this->textContent);
		}
		return $this->textContent = $this->claim($textContent);
	}
}
