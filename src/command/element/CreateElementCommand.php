<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\ElementTagTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class CreateElementCommand extends Command implements AllocationModeInterface, JavaScriptInterface, ValueReturningCommandInterface{

	use AllocationModeTrait;
	use ElementTagTrait;
	
	protected $tag;

	protected $type;

	public static function getCommandId(): string
	{
		return "creatElement";
	}

	public function __construct($tag = null, $type = null, $mode = null){
		$f = __METHOD__;
		parent::__construct();
		if (isset($tag)) {
			if (is_string($tag) && is_a($tag, Element::class, true)) {
				if (method_exists($tag, "getElementTagStatic")) {
					$tag = $tag::getElementTagStatic();
				} else {
					Debug::error("{$f} element class does not have a static tag");
				}
			}
			$this->setElementTag($tag);
		}
		if (isset($type)) {
			$this->setType($type);
		}
		if (isset($mode)) {
			$this->setAllocationMode($mode);
		}
	}

	public function toJavaScript(): string{
		$tag = $this->getElementTag();
		if ($tag instanceof JavaScriptInterface) {
			$tag = $tag->toJavaScript();
		} elseif (is_string($tag) || $tag instanceof StringifiableInterface) {
			$tag = single_quote($tag);
		}
		return "document.createElement({$tag})";
	}

	public function setType($type){
		if ($type === null) {
			unset($this->type);
			return null;
		}
		return $this->type = $type;
	}

	public function hasType(){
		return isset($this->type);
	}

	public function getType(){
		$f = __METHOD__;
		if (! $this->hasType()) {
			Debug::error("{$f} type is undefined");
		}
		return $this->type;
	}

	public function evaluate(?array $params = null){
		$tag = $this->getElementTag();
		if ($tag instanceof ValueReturningCommandInterface) {
			while ($tag instanceof ValueReturningCommandInterface) {
				$tag = $tag->evaluate();
			}
		}
		if ($this->hasType()) {
			$type = $this->getType();
			if ($type instanceof ValueReturningCommandInterface) {
				while ($type instanceof ValueReturningCommandInterface) {
					$type = $type->evaluate();
				}
			}
		} else {
			$type = null;
		}
		if ($this->hasAllocationMode()) {
			$mode = $this->getAllocationMode();
		} else {
			$mode = ALLOCATION_MODE_UNDEFINED;
		}
		return Document::createElement($tag, $type, $mode);
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->allocationMode);
		unset($this->tag);
		unset($this->type);
	}
}
