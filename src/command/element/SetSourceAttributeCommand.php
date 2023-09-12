<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SetSourceAttributeCommand extends ElementCommand implements ServerExecutableCommandInterface
{

	protected $src;

	public function __construct($element, $src)
	{
		parent::__construct($element);
		$this->setSourceAttribute($src);
	}

	public function hasSourceAttribute(): bool
	{
		return isset($this->src);
	}

	public function getSourceAttribute()
	{
		$f = __METHOD__; //SetSourceAttributeCommand::getShortClass()."(".static::getShortClass().")->getSourceAttribute()";
		if(!$this->hasSourceAttribute()) {
			Debug::error("{$f} source attribute is undefined");
		}
		return $this->src;
	}

	public function setSourceAttribute($src)
	{
		if($src === null) {
			unset($this->src);
			return null;
		}
		return $this->src = $src;
	}

	public function toJavaScript(): string
	{
		$id = $this->getIdCommandString();
		if($id instanceof JavaScriptInterface) {
			$id = $id->toJavaScript();
		}
		$src = $this->getSourceAttribute();
		if($src instanceof JavaScriptInterface) {
			$src = $src->toJavaScript();
		}
		return "{$id}.src = {$src}";
	}

	public static function getCommandId(): string
	{
		return "setSource";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$src = $this->getSourceAttribute();
		while ($src instanceof ValueReturningCommandInterface) {
			$src = $src->evaluate();
		}
		$element->setAttribute("src", $src);
	}
}
