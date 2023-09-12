<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait TextContentTrait
{

	protected $textContent;

	public function hasTextContent(): bool
	{
		return isset($this->textContent); // || $this->textContent === "";
	}

	public function getTextContent()
	{
		$f = __METHOD__; //"TextContentTrait(".static::getShortClass().")->getTextContent()";
		if(!$this->hasTextContent()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} textContent is undefined. Created {$decl}");
		}
		return $this->textContent;
	}

	public function setTextContent($textContent)
	{
		if($textContent === null) {
			unset($this->textContent);
			return null;
		}
		return $this->textContent = $textContent;
	}
}
