<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetAttributeCommand extends ElementCommand implements ValueReturningCommandInterface
{

	protected $attributeName;

	public function __construct($element, $attr_name)
	{
		$f = __METHOD__; //GetAttributeCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($element);
		$this->setAttributeName($attr_name);
		if(!$this->hasElement()) {
			Debug::error("{$f} element is undefined");
		}
	}

	public function setAttributeName($attr_name)
	{
		if($attr_name == null) {
			unset($this->attributeName);
			return null;
		}
		return $this->attributeName = $attr_name;
	}

	public function hasAttributeName()
	{
		return isset($this->attributeName);
	}

	public function getAttributeName()
	{
		$f = __METHOD__; //GetAttributeCommand::getShortClass()."(".static::getShortClass().")->getAttributeName()";
		if(!$this->hasAttributeName()) {
			Debug::error("{$f} attribute name is undefined");
		}
		return $this->attributeName;
	}

	public function toJavaScript(): string
	{
		$idc = $this->getIdCommandString();
		if($idc instanceof JavaScriptInterface) {
			$idc = $idc->toJavaScript();
		}
		$attr_name = $this->getAttributeName();
		if($attr_name instanceof JavaScriptInterface) {
			$attr_name = $attr_name->toJavaScript();
		}elseif(is_string($attr_name) || $attr_name instanceof StringifiableInterface) {
			$attr_name = single_quote($attr_name);
		}
		return "{$idc}.getAttribute({$attr_name})";
	}

	public static function getCommandId(): string
	{
		return "getAttribute";
	}

	public function evaluate(?array $params = null)
	{
		$attr_name = $this->getAttributeName();
		while ($attr_name instanceof ValueReturningCommandInterface) {
			$attr_name = $attr_name->evaluate();
		}
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		return $element->getAttribute($attr_name);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->attributeName);
	}
}
